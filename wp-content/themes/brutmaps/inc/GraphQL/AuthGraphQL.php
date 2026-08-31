<?php

namespace Brut\GraphQL;

use Brut\Services\MailchimpService;
use Brut\Utils\ValidatorHelper;
use Brut\Utils\UserMetaHelper;
use Brut\Security\RateLimiter;
use GraphQL\Error\UserError;
use WPGraphQL\JWT_Authentication\Auth;
use WPGraphQL\JWT_Authentication\ManageTokens;
use WPGraphQL\Model\User as UserModel;

/**
 * Theme-specific auth mutations.
 *
 * `login`, `refreshJwtAuthToken` and per-request `Authorization: Bearer`
 * authentication are provided by the wp-graphql-jwt-authentication plugin.
 * This class only adds what the plugin does not: registration with the
 * project's extra profile fields, Google sign-in, an email availability
 * check, and logout (JWT secret revocation).
 */
class AuthGraphQL
{
    public function registerTypes(): void
    {
        if (!class_exists(Auth::class)) {
            return;
        }

        $this->guardTokenRefreshHeader();
        $this->registerSharedTypes();
        $this->registerMutations();
    }

    /**
     * ManageTokens::add_tokens_to_graphql_response_headers() (hooked by the
     * plugin on every HTTPS GraphQL response) tries to mint a fresh
     * X-JWT-Auth/X-JWT-Refresh pair for whoever the current Bearer token
     * belongs to. If that user's JWT secret has since been revoked - e.g. via
     * our logout mutation, or simply an old token being replayed - it throws
     * a UserError from inside a response-header filter, outside GraphQL's
     * error handling, which crashes the entire request. Re-wrap it so a
     * revoked user just doesn't get refreshed headers instead of a 500.
     */
    private function guardTokenRefreshHeader(): void
    {
        remove_filter('graphql_response_headers_to_send', [ManageTokens::class, 'add_tokens_to_graphql_response_headers']);

        add_filter('graphql_response_headers_to_send', static function (array $headers): array {
            try {
                return ManageTokens::add_tokens_to_graphql_response_headers($headers);
            } catch (\Throwable $e) {
                return $headers;
            }
        });
    }

    private function registerSharedTypes(): void
    {
        register_graphql_object_type('AuthPayload', [
            'description' => 'JWT auth tokens plus the authenticated user.',
            'fields'      => [
                'authToken'    => [
                    'type'    => 'String',
                    'resolve' => fn($data) => $data['auth_token'] ?? null,
                ],
                'refreshToken' => [
                    'type'    => 'String',
                    'resolve' => fn($data) => $data['refresh_token'] ?? null,
                ],
                'user'         => [
                    'type'    => 'User',
                    'resolve' => fn($data) => isset($data['user']) ? new UserModel($data['user']) : null,
                ],
            ],
        ]);

        register_graphql_object_type('CheckEmailResult', [
            'description' => 'Result of an email availability check.',
            'fields'      => [
                'exists'  => ['type' => 'Boolean'],
                'message' => ['type' => 'String'],
            ],
        ]);
    }

    private function registerMutations(): void
    {
        register_graphql_mutation('register', [
            'inputFields'         => [
                'firstName'             => ['type' => 'String'],
                'lastName'              => ['type' => 'String'],
                'email'                 => ['type' => ['non_null' => 'String']],
                'password'              => ['type' => ['non_null' => 'String']],
                'country'               => ['type' => 'String'],
                'subscribeToNewsletter' => ['type' => 'Boolean'],
                'photoUrl'              => ['type' => 'String'],
            ],
            'outputFields'        => [
                'authPayload' => ['type' => 'AuthPayload'],
            ],
            'mutateAndGetPayload' => [$this, 'resolveRegister'],
        ]);

        register_graphql_mutation('googleAuth', [
            'inputFields'         => [
                'email'     => ['type' => ['non_null' => 'String']],
                'firstName' => ['type' => 'String'],
                'lastName'  => ['type' => 'String'],
                'avatar'    => ['type' => 'String'],
            ],
            'outputFields'        => [
                'authPayload' => ['type' => 'AuthPayload'],
            ],
            'mutateAndGetPayload' => [$this, 'resolveGoogleAuth'],
        ]);

        register_graphql_mutation('checkEmail', [
            'inputFields'         => [
                'email' => ['type' => ['non_null' => 'String']],
            ],
            'outputFields'        => [
                'result' => ['type' => 'CheckEmailResult'],
            ],
            'mutateAndGetPayload' => [$this, 'resolveCheckEmail'],
        ]);

        register_graphql_mutation('logout', [
            'inputFields'         => [],
            'outputFields'        => [
                'success' => ['type' => 'Boolean'],
            ],
            'mutateAndGetPayload' => [$this, 'resolveLogout'],
        ]);
    }

    public function resolveRegister(array $input): array
    {
        $first_name = sanitize_text_field($input['firstName'] ?? '');
        $last_name  = sanitize_text_field($input['lastName'] ?? '');
        $email      = sanitize_email($input['email'] ?? '');
        $password   = $input['password'] ?? '';
        $country    = sanitize_text_field($input['country'] ?? '');
        $subscribe  = (bool) ($input['subscribeToNewsletter'] ?? false);
        $photo_url  = esc_url_raw($input['photoUrl'] ?? '');

        if (!ValidatorHelper::isValidEmail($email) || empty($password)) {
            throw new UserError('Invalid registration data');
        }

        if (email_exists($email)) {
            throw new UserError('Email already in use');
        }

        $user_id = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) {
            throw new UserError('Failed to create user');
        }

        $user = new \WP_User($user_id);
        $user->set_role('subscriber');

        UserMetaHelper::updateMeta($user_id, [
            'first_name'           => $first_name,
            'last_name'            => $last_name,
            'country'              => $country,
            'billing_country'      => $country,
            'mailchimp_subscribed' => $subscribe,
            'profile_photo'        => $photo_url,
        ]);

        if ($subscribe) {
            try {
                MailchimpService::subscribe($email, $first_name, $last_name);
            } catch (\Exception $e) {
                error_log('Mailchimp subscribe error: ' . $e->getMessage());
            }
        }

        return ['authPayload' => $this->issueTokens($user)];
    }

    public function resolveGoogleAuth(array $input): array
    {
        $email      = sanitize_email($input['email'] ?? '');
        $first_name = sanitize_text_field($input['firstName'] ?? '');
        $last_name  = sanitize_text_field($input['lastName'] ?? '');
        $avatar_url = esc_url_raw($input['avatar'] ?? '');

        if (!ValidatorHelper::isValidEmail($email)) {
            throw new UserError('Invalid email');
        }

        if (RateLimiter::isLimited('email_' . md5($email), 3, 300)) {
            throw new UserError('Too many attempts');
        }

        $user = get_user_by('email', $email);

        if (!$user) {
            $user_id = wp_create_user($email, wp_generate_password(), $email);
            if (is_wp_error($user_id)) {
                throw new UserError('User creation failed');
            }

            $user = new \WP_User($user_id);
            $user->set_role('subscriber');

            UserMetaHelper::updateMeta($user_id, [
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'profile_photo' => $avatar_url,
            ]);

            try {
                MailchimpService::subscribe($email, $first_name, $last_name);
            } catch (\Exception $e) {
                error_log('Mailchimp error: ' . $e->getMessage());
            }
        }

        return ['authPayload' => $this->issueTokens($user)];
    }

    public function resolveCheckEmail(array $input): array
    {
        $email = sanitize_email($input['email'] ?? '');

        if (!ValidatorHelper::isValidEmail($email)) {
            throw new UserError('Invalid email format');
        }

        if (email_exists($email)) {
            return ['result' => [
                'exists'  => true,
                'message' => 'Email is already in use',
            ]];
        }

        return ['result' => [
            'exists'  => false,
            'message' => 'Email is available',
        ]];
    }

    public function resolveLogout(): array
    {
        if (!is_user_logged_in()) {
            throw new UserError('You must be logged in to log out.');
        }

        Auth::revoke_user_secret(get_current_user_id());

        return ['success' => true];
    }

    /**
     * Issue a fresh JWT auth + refresh token pair for the given user via the
     * wp-graphql-jwt-authentication plugin (no capability check: the caller
     * has just authenticated this user).
     *
     * The plugin's refresh token needs a per-user secret, which it will only
     * hand out for the "current user" (see Auth::get_user_jwt_secret()) -
     * mirror Auth::login_and_get_token() and set the current user first, or
     * the refresh token silently comes back identical to the auth token.
     *
     * @return array{auth_token: string|null, refresh_token: string|null, user: \WP_User}
     */
    private function issueTokens(\WP_User $user): array
    {
        wp_set_current_user($user->ID);

        return [
            'auth_token'    => Auth::get_token($user, false),
            'refresh_token' => Auth::get_refresh_token($user, false),
            'user'          => $user,
        ];
    }
}

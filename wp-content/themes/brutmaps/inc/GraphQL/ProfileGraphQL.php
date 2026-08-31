<?php

namespace Brut\GraphQL;

use Brut\Security\RateLimiter;
use Brut\Services\MailchimpService;
use Brut\Services\UserDeletionService;
use Brut\Utils\UserMetaHelper;
use Brut\Utils\ValidatorHelper;
use GraphQL\Error\UserError;
use WPGraphQL\Model\User as UserModel;

/**
 * Profile and password mutations that replace the old REST
 * /profile/* and /auth/{lost,reset,change}-password endpoints.
 *
 * country/photoUrl/isSubscribed are added as extra fields on WPGraphQL's own
 * `User` type, so the profile is read via the existing `viewer` query
 * instead of a bespoke one.
 */
class ProfileGraphQL
{
    public function registerTypes(): void
    {
        $this->registerCountryType();
        $this->registerUserFields();
        $this->registerQueries();
        $this->registerMutations();
    }

    private function registerCountryType(): void
    {
        register_graphql_object_type('Country', [
            'description' => 'A country code/name pair.',
            'fields'      => [
                'code' => ['type' => 'String'],
                'name' => ['type' => 'String'],
            ],
        ]);
    }

    private function registerUserFields(): void
    {
        register_graphql_field('User', 'country', [
            'type'    => 'String',
            'resolve' => fn($user) => get_user_meta($user->databaseId, 'country', true) ?: null,
        ]);

        register_graphql_field('User', 'photoUrl', [
            'type'    => 'String',
            'resolve' => fn($user) => UserMetaHelper::getProfilePhotoUrl($user->databaseId),
        ]);

        register_graphql_field('User', 'isSubscribed', [
            'type'    => 'Boolean',
            'resolve' => function ($user) {
                try {
                    return MailchimpService::isSubscribed($user->email);
                } catch (\Exception $e) {
                    error_log('Mailchimp error: ' . $e->getMessage());
                    return false;
                }
            },
        ]);
    }

    private function registerQueries(): void
    {
        register_graphql_field('RootQuery', 'userCountries', [
            'type'        => ['list_of' => 'Country'],
            'description' => 'Countries available for the profile country field.',
            'resolve'     => [$this, 'resolveUserCountries'],
        ]);
    }

    private function registerMutations(): void
    {
        register_graphql_mutation('editProfile', [
            'inputFields'         => [
                'firstName' => ['type' => 'String'],
                'lastName'  => ['type' => 'String'],
                'country'   => ['type' => 'String'],
                'email'     => ['type' => 'String'],
                'photoUrl'  => ['type' => 'String'],
            ],
            'outputFields'        => [
                'user' => ['type' => 'User'],
            ],
            'mutateAndGetPayload' => [$this, 'resolveEditProfile'],
        ]);

        register_graphql_mutation('changePassword', [
            'inputFields'         => [
                'currentPassword' => ['type' => ['non_null' => 'String']],
                'newPassword'     => ['type' => ['non_null' => 'String']],
            ],
            'outputFields'        => [
                'success' => ['type' => 'Boolean'],
            ],
            'mutateAndGetPayload' => [$this, 'resolveChangePassword'],
        ]);

        register_graphql_mutation('lostPassword', [
            'inputFields'         => [
                'email' => ['type' => ['non_null' => 'String']],
            ],
            'outputFields'        => [
                'success' => ['type' => 'Boolean'],
            ],
            'mutateAndGetPayload' => [$this, 'resolveLostPassword'],
        ]);

        register_graphql_mutation('resetPassword', [
            'inputFields'         => [
                'key'         => ['type' => ['non_null' => 'String']],
                'login'       => ['type' => ['non_null' => 'String']],
                'newPassword' => ['type' => ['non_null' => 'String']],
            ],
            'outputFields'        => [
                'success' => ['type' => 'Boolean'],
            ],
            'mutateAndGetPayload' => [$this, 'resolveResetPassword'],
        ]);

        register_graphql_mutation('deleteAccount', [
            'inputFields'         => [],
            'outputFields'        => [
                'success' => ['type' => 'Boolean'],
            ],
            'mutateAndGetPayload' => [$this, 'resolveDeleteAccount'],
        ]);
    }

    // -------------------------------------------------------------------------
    // Resolvers
    // -------------------------------------------------------------------------

    public function resolveUserCountries(): array
    {
        if (!class_exists('WC_Countries')) {
            throw new UserError('WooCommerce not installed');
        }

        $countries = (new \WC_Countries())->get_countries();

        $result = [];
        foreach ($countries as $code => $name) {
            $result[] = ['code' => $code, 'name' => $name];
        }

        return $result;
    }

    public function resolveEditProfile(array $input): array
    {
        if (!is_user_logged_in()) {
            throw new UserError('You must be logged in to edit your profile.');
        }

        $user_id = get_current_user_id();
        $user    = get_userdata($user_id);

        $first_name = isset($input['firstName']) ? sanitize_text_field($input['firstName']) : null;
        $last_name  = isset($input['lastName']) ? sanitize_text_field($input['lastName']) : null;
        $country    = isset($input['country']) ? sanitize_text_field($input['country']) : null;
        $new_email  = isset($input['email']) ? sanitize_email($input['email']) : null;
        $photo_url  = isset($input['photoUrl']) ? esc_url_raw($input['photoUrl']) : null;

        if (!empty($new_email) && $new_email !== $user->user_email) {
            if (!ValidatorHelper::isValidEmail($new_email)) {
                throw new UserError('Invalid email');
            }

            if (email_exists($new_email)) {
                throw new UserError('Email already in use');
            }

            wp_update_user([
                'ID'         => $user_id,
                'user_email' => $new_email,
            ]);
        }

        UserMetaHelper::updateMeta($user_id, [
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'country'       => $country,
            'profile_photo' => $photo_url,
        ]);

        $current_email = get_userdata($user_id)->user_email;

        try {
            if (MailchimpService::isSubscribed($current_email)) {
                MailchimpService::unsubscribe($current_email);
            } else {
                MailchimpService::subscribe($current_email, $first_name, $last_name);
            }
        } catch (\Exception $e) {
            error_log('Mailchimp error: ' . $e->getMessage());
        }

        return ['user' => new UserModel(get_userdata($user_id))];
    }

    public function resolveChangePassword(array $input): array
    {
        if (!is_user_logged_in()) {
            throw new UserError('You must be logged in to change your password.');
        }

        $user_id = get_current_user_id();

        if (RateLimiter::isLimited('ip_' . $this->clientIp(), 5, 60)) {
            throw new UserError('Too many requests. Please try again later.');
        }

        $current = $input['currentPassword'];
        $new     = $input['newPassword'];

        $user = get_userdata($user_id);
        if (!wp_check_password($current, $user->user_pass, $user_id)) {
            throw new UserError('Current password is incorrect');
        }

        wp_set_password($new, $user_id);

        return ['success' => true];
    }

    public function resolveLostPassword(array $input): array
    {
        $email = sanitize_email($input['email']);

        if (!ValidatorHelper::isValidEmail($email)) {
            throw new UserError('Invalid email');
        }

        if (
            RateLimiter::isLimited('ip_' . $this->clientIp(), 5, 60)
            || RateLimiter::isLimited('email_' . md5($email), 3, 300)
        ) {
            throw new UserError('Too many requests. Please try again later.');
        }

        $user = get_user_by('email', $email);
        if (!$user) {
            throw new UserError('Email not found');
        }

        $reset_key = get_password_reset_key($user);
        if (is_wp_error($reset_key)) {
            throw new UserError('Could not generate reset key');
        }

        $reset_link = network_site_url('reset-password?key=' . $reset_key . '&login=' . rawurlencode($user->user_login));
        $subject    = 'Reset your password';
        $message    = "Click the link to reset your password: $reset_link";

        wp_mail($email, $subject, $message);

        return ['success' => true];
    }

    public function resolveResetPassword(array $input): array
    {
        $key          = sanitize_text_field($input['key']);
        $login        = sanitize_text_field($input['login']);
        $new_password = $input['newPassword'];

        if (
            RateLimiter::isLimited('ip_' . $this->clientIp(), 5, 60)
            || RateLimiter::isLimited('email_' . md5($login), 3, 300)
        ) {
            throw new UserError('Too many requests. Please try again later.');
        }

        if (!$key || !$login || !$new_password) {
            throw new UserError('All fields are required');
        }

        $user = get_user_by('login', $login);
        if (!$user) {
            throw new UserError('User not found');
        }

        $check = check_password_reset_key($key, $user->user_login);
        if (is_wp_error($check)) {
            throw new UserError('Invalid or expired reset key');
        }

        wp_set_password($new_password, $user->ID);

        return ['success' => true];
    }

    public function resolveDeleteAccount(): array
    {
        if (!is_user_logged_in()) {
            throw new UserError('You must be logged in to delete your account.');
        }

        $user_id = get_current_user_id();

        (new UserDeletionService())->delete($user_id);

        return ['success' => true];
    }

    private function clientIp(): string
    {
        return isset($_SERVER['REMOTE_ADDR'])
            ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']))
            : '';
    }
}

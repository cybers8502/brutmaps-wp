<?php

namespace Brut\Rest\Controllers;

use WP_REST_Request;
use Brut\Utils\ValidatorHelper;
use Brut\Utils\RequestSanitizer;
use Brut\Utils\ResponseHelper;
use Brut\Services\MailchimpService;
use Brut\Services\MediaUploadService;
use Brut\Utils\UserMetaHelper;
use Brut\Security\JwtService;
use Brut\Security\RateLimiter;

class AuthController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/login', [
            'methods' => 'POST',
            'callback' => [$this, 'login'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/google-login', [
            'methods' => 'POST',
            'callback' => [$this, 'googleLoginAndRegistration'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/registration', [
            'methods' => 'POST',
            'callback' => [$this, 'registration'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/google-registration', [
            'methods' => 'POST',
            'callback' => [$this, 'googleRegister'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/check-email', [
            'methods' => 'POST',
            'callback' => [$this, 'checkEmail'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/google-auth', [
            'methods' => 'POST',
            'callback' => [$this, 'googleLoginAndRegistration'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function login(WP_REST_Request $request): \WP_Error|\WP_REST_Response
    {
        $email = RequestSanitizer::sanitizeEmailParam($request, 'username');
        $password = $request->get_param('password');

        if (!ValidatorHelper::isValidEmail($email) || !$password) {
            return ResponseHelper::validationError('Invalid email or password');
        }

        if (RateLimiter::isLimited("email_" . md5($email), 3, 300)) {
            return ResponseHelper::tooManyRequests('Too many attempts');
        }

        $user = wp_authenticate($email, $password);
        if (is_wp_error($user)) {
            return ResponseHelper::unauthorized('Invalid credentials');
        }

        $user_id = $user->ID;
        $access_token = JwtService::generate($user_id);
        $refresh_token = wp_generate_uuid4();
        update_user_meta($user_id, 'custom_refresh_token', $refresh_token);

        return ResponseHelper::success([
            'access_token'  => $access_token,
            'refresh_token' => $refresh_token,
            'user'          => ['email' => $user->user_email],
        ]);
    }

    public function googleLogin(WP_REST_Request $request): \WP_Error|\WP_REST_Response
    {
        $params = $request->get_json_params();
        $email = sanitize_email($params['email'] ?? '');
        $user = get_user_by('email', $email);

        if (!ValidatorHelper::isValidEmail($email)) {
            return ResponseHelper::validationError('Invalid email');
        }

        if (RateLimiter::isLimited("email_" . md5($email), 3, 300)) {
            return ResponseHelper::tooManyRequests();
        }

        if (!$user) {
            return ResponseHelper::notFound('User not registered');
        }

        $access_token = JwtService::generate($user->ID);
        $refresh_token = wp_generate_uuid4();
        update_user_meta($user->ID, 'custom_refresh_token', $refresh_token);

        return ResponseHelper::success([
            'access_token'  => $access_token,
            'refresh_token' => $refresh_token,
            'user'          => ['email' => $user->user_email],
        ]);
    }

    public function googleLoginAndRegistration(WP_REST_Request $request): \WP_Error|\WP_REST_Response
    {
        $params = $request->get_json_params();
        $email = sanitize_email($params['email'] ?? '');
        $user = get_user_by('email', $email);

        if (!ValidatorHelper::isValidEmail($email)) {
            return ResponseHelper::validationError('Invalid email');
        }

        if (RateLimiter::isLimited("email_" . md5($email), 3, 300)) {
            return ResponseHelper::tooManyRequests();
        }

        if (!$user) {
            $first_name = sanitize_text_field($request['first_name'] ?? '');
            $last_name = sanitize_text_field($request['last_name'] ?? '');
            $avatar_url = esc_url_raw($request['avatar'] ?? '');

            $user_id = wp_create_user($email, wp_generate_password(), $email);
            if (is_wp_error($user_id)) {
                return ResponseHelper::serverError('User creation failed');
            }

            $user = new \WP_User($user_id);
            $user->set_role('subscriber');

            UserMetaHelper::updateMeta($user_id, [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'profile_photo' => $avatar_url,
            ]);

            try {
                MailchimpService::subscribe($email, $first_name, $last_name);
            } catch (\Exception $e) {
                error_log('Mailchimp error: ' . $e->getMessage());
            }
        }

        $access_token = JwtService::generate($user->ID);
        $refresh_token = wp_generate_uuid4();
        update_user_meta($user->ID, 'custom_refresh_token', $refresh_token);

        return ResponseHelper::success([
            'access_token'  => $access_token,
            'refresh_token' => $refresh_token,
            'user'          => ['email' => $user->user_email],
        ]);
    }

    public function registration(WP_REST_Request $request): \WP_Error|\WP_REST_Response
    {
        $params = $request->get_params();
        $files = $request->get_file_params();

        $first_name = sanitize_text_field($params['first_name'] ?? '');
        $last_name = sanitize_text_field($params['last_name'] ?? '');
        $email = sanitize_email($params['email'] ?? '');
        $password = $params['password'] ?? '';
        $country = sanitize_text_field($params['country'] ?? '');
        $subscribe = filter_var($params['subscribe_to_newsletter'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!ValidatorHelper::isValidEmail($email) || empty($password)) {
            return ResponseHelper::validationError('Invalid registration data');
        }

        if (email_exists($email)) {
            return ResponseHelper::error('Email already in use', 409, 'email_exists');
        }

        $user_id = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) {
            return ResponseHelper::serverError('Failed to create user');
        }

        $user = new \WP_User($user_id);
        $user->set_role('subscriber');

        UserMetaHelper::updateMeta($user_id, [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'country' => $country,
            'billing_country' => $country,
            'mailchimp_subscribed' => $subscribe,
        ]);

        // Upload photo
        if (!empty($files['photo']) && ValidatorHelper::isValidImage($files['photo'])) {
            $photo_id = MediaUploadService::handleUserPhotoUpload('photo');
            if (!is_wp_error($photo_id)) {
                update_user_meta($user_id, 'profile_photo', wp_get_attachment_url($photo_id));
            }
        }

        if ($subscribe) {
            try {
                MailchimpService::subscribe($email, $first_name, $last_name);
            } catch (\Exception $e) {
                error_log('Mailchimp subscribe error: ' . $e->getMessage());
            }
        }

        $access_token = JwtService::generate($user_id);
        $refresh_token = wp_generate_uuid4();
        update_user_meta($user_id, 'custom_refresh_token', $refresh_token);

        return ResponseHelper::success([
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'user' => ['email' => $email],
        ], 'User registered successfully');
    }

    public function googleRegister(WP_REST_Request $request): \WP_Error|\WP_REST_Response
    {
        $email = sanitize_email($request['email'] ?? '');
        $first_name = sanitize_text_field($request['first_name'] ?? '');
        $last_name = sanitize_text_field($request['last_name'] ?? '');
        $avatar_url = esc_url_raw($request['avatar'] ?? '');

        if (!ValidatorHelper::isValidEmail($email)) {
            return ResponseHelper::validationError('Invalid email');
        }

        if (email_exists($email)) {
            return ResponseHelper::error('Email already exists', 409, 'email_exists');
        }

        $user_id = wp_create_user($email, wp_generate_password(), $email);
        if (is_wp_error($user_id)) {
            return ResponseHelper::serverError('User creation failed');
        }

        $user = new \WP_User($user_id);
        $user->set_role('subscriber');

        UserMetaHelper::updateMeta($user_id, [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'profile_photo' => $avatar_url,
        ]);

        try {
            MailchimpService::subscribe($email, $first_name, $last_name);
        } catch (\Exception $e) {
            error_log('Mailchimp error: ' . $e->getMessage());
        }

        $access_token = JwtService::generate($user_id);
        $refresh_token = wp_generate_uuid4();
        update_user_meta($user_id, 'custom_refresh_token', $refresh_token);

        return ResponseHelper::success([
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'user' => [
                'id' => $user_id,
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ],
        ]);
    }

    public function checkEmail(WP_REST_Request $request): \WP_Error|\WP_REST_Response
    {
        $email = RequestSanitizer::sanitizeEmailParam($request, 'email');

        if (!ValidatorHelper::isValidEmail($email)) {
            return ResponseHelper::validationError('Invalid email format');
        }

        if (email_exists($email)) {
            return ResponseHelper::success([
                'exists' => true,
                'message' => 'Email is already in use',
            ]);
        }

        return ResponseHelper::success([
            'exists' => false,
            'message' => 'Email is available',
        ]);
    }
}

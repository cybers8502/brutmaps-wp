<?php

namespace Brut\Rest\Controllers;

use WP_REST_Request;
use Brut\Utils\RequestSanitizer;
use Brut\Utils\ValidatorHelper;
use Brut\Utils\ResponseHelper;
use Brut\Security\RateLimiter;

class PasswordController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/lost-password', [
            'methods' => 'POST',
            'callback' => [$this, 'lostPassword'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/reset-password', [
            'methods' => 'POST',
            'callback' => [$this, 'resetPassword'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/change-password', [
            'methods' => 'POST',
            'callback' => [$this, 'changePassword'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public function lostPassword(WP_REST_Request $request): \WP_Error|\WP_REST_Response
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $email = RequestSanitizer::sanitizeEmailParam($request, 'email');

        if (!ValidatorHelper::isValidEmail($email)) {
            return ResponseHelper::validationError('Invalid email');
        }

        if (RateLimiter::isLimited("ip_$ip", 5, 60) || RateLimiter::isLimited("email_" . md5($email), 3, 300)) {
            return ResponseHelper::tooManyRequests();
        }

        $user = get_user_by('email', $email);
        if (!$user) {
            return ResponseHelper::notFound('Email not found');
        }

        $reset_key = get_password_reset_key($user);
        if (is_wp_error($reset_key)) {
            return ResponseHelper::serverError('Could not generate reset key');
        }

        $reset_link = network_site_url("reset-password?key=$reset_key&login=" . rawurlencode($user->user_login));
        $subject = 'Reset your password';
        $message = "Click the link to reset your password: $reset_link";

        wp_mail($email, $subject, $message);

        return ResponseHelper::success([], 'Password reset link sent to your email.');
    }

    public function resetPassword(WP_REST_Request $request): \WP_Error|\WP_REST_Response
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $key = RequestSanitizer::sanitizeStringParam($request, 'key');
        $login = RequestSanitizer::sanitizeStringParam($request, 'login');
        $new_password = $request->get_param('new_password');

        if (RateLimiter::isLimited("ip_$ip", 5, 60) || RateLimiter::isLimited("email_" . md5($login), 3, 300)) {
            return ResponseHelper::tooManyRequests();
        }

        if (!$key || !$login || !$new_password) {
            return ResponseHelper::validationError('All fields are required');
        }

        $user = get_user_by('login', $login);
        if (!$user) {
            return ResponseHelper::notFound('User not found');
        }

        $check = check_password_reset_key($key, $user->user_login);
        if (is_wp_error($check)) {
            return ResponseHelper::validationError('Invalid or expired reset key');
        }

        wp_set_password($new_password, $user->ID);

        return ResponseHelper::success([], 'Password successfully updated');
    }

    public function changePassword(WP_REST_Request $request): \WP_Error|\WP_REST_Response
    {
        $user_id = get_current_user_id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        if (RateLimiter::isLimited("ip_$ip", 5, 60)) {
            return ResponseHelper::tooManyRequests();
        }

        $current = $request->get_param('current_password');
        $new = $request->get_param('new_password');

        if (!$current || !$new) {
            return ResponseHelper::validationError('Both passwords are required');
        }

        $user = get_userdata($user_id);
        if (!wp_check_password($current, $user->user_pass, $user_id)) {
            return ResponseHelper::validationError('Current password is incorrect');
        }

        wp_set_password($new, $user_id);

        return ResponseHelper::success([], 'Password updated successfully');
    }
}

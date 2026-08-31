<?php

namespace Brut\Rest\Controllers;

use WP_REST_Request;
use Brut\Utils\RequestSanitizer;
use Brut\Utils\ValidatorHelper;
use Brut\Utils\ResponseHelper;
use Brut\Utils\UserMetaHelper;
use Brut\Services\MediaUploadService;
use Brut\Services\MailchimpService;
use Brut\Services\UserDeletionService;

class UserProfileController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/profile/user-profile', [
            'methods' => 'GET',
            'callback' => [$this, 'getProfile'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route(BASE_URL, '/profile/edit-profile', [
            'methods' => 'POST',
            'callback' => [$this, 'editProfile'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route(BASE_URL, '/profile/user-countries', [
            'methods' => 'GET',
            'callback' => [$this, 'getUserCountries'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/profile/delete-account', [
            'methods' => 'DELETE',
            'callback' => [$this, 'deleteAccount'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public function getProfile(): \WP_REST_Response|\WP_Error
    {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        $meta = UserMetaHelper::getMeta($user_id, ['first_name', 'last_name', 'country', 'profile_photo']);
        $photo_url = UserMetaHelper::getProfilePhotoUrl($user_id);

        $data = [
            'user_id' => $user_id,
            'email' => $user->user_email,
            'first_name' => $meta['first_name'],
            'last_name' => $meta['last_name'],
            'country' => $meta['country'],
            'photo_url' => $photo_url,
            'is_subscribed' => false,
        ];

        try {
            $data['is_subscribed'] = MailchimpService::isSubscribed($user->user_email);
        } catch (\Exception $e) {
            error_log('Mailchimp error: ' . $e->getMessage());
        }

        return ResponseHelper::success($data);
    }

    public function editProfile(WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        $first_name = RequestSanitizer::sanitizeStringParam($request, 'first_name');
        $last_name = RequestSanitizer::sanitizeStringParam($request, 'last_name');
        $country = RequestSanitizer::sanitizeStringParam($request, 'country');
        $new_email = RequestSanitizer::sanitizeEmailParam($request, 'email');

        $photo = $request->get_file_params()['photo'] ?? null;

        if (!empty($new_email) && $new_email !== $user->user_email) {
            if (!ValidatorHelper::isValidEmail($new_email)) {
                return ResponseHelper::validationError('Invalid email');
            }

            if (email_exists($new_email)) {
                return ResponseHelper::error('Email already in use', 409);
            }

            wp_update_user([
                'ID' => $user_id,
                'user_email' => $new_email,
            ]);
        }

        UserMetaHelper::updateMeta($user_id, [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'country' => $country,
        ]);

        // Обробка зображення
        if (!empty($photo) && ValidatorHelper::isValidImage($photo)) {
            $old_photo_id = get_user_meta($user_id, 'profile_photo', true);
            MediaUploadService::deleteAttachmentIfExists($old_photo_id);

            $new_photo_id = MediaUploadService::handleUserPhotoUpload('photo');
            if (!is_wp_error($new_photo_id)) {
                update_user_meta($user_id, 'profile_photo', $new_photo_id);
            }
        }

        $current_email = get_userdata($user_id)->user_email;

        // Синхронізація з Mailchimp
        try {
            $mailchimp = new MailchimpService();

            if ($mailchimp->isSubscribed($current_email)) {
                $mailchimp->unsubscribe($current_email);
            } else {
                $mailchimp->subscribe($current_email, $first_name, $last_name);
            }
        } catch (\Exception $e) {
            error_log('Mailchimp error: ' . $e->getMessage());
        }

        return ResponseHelper::success([
            'email' => $current_email,
            'first_name' => get_user_meta($user_id, 'first_name', true),
            'last_name' => get_user_meta($user_id, 'last_name', true),
            'country' => get_user_meta($user_id, 'country', true),
            'photo_url' => UserMetaHelper::getProfilePhotoUrl($user_id),
        ], 'Profile updated');
    }

    public function getUserCountries(): \WP_REST_Response|\WP_Error
    {
        if (!class_exists('WC_Countries')) {
            return ResponseHelper::error('WooCommerce not installed', 500);
        }

        $countries = new \WC_Countries();
        return ResponseHelper::success($countries->get_countries());
    }

    public function deleteAccount(): \WP_REST_Response|\WP_Error
    {
        $user_id = get_current_user_id();

        try {
            (new UserDeletionService())->delete($user_id);

            return ResponseHelper::success([], 'User deleted');
        } catch (\Exception $e) {
            return ResponseHelper::serverError('Failed to delete account: ' . $e->getMessage());
        }
    }
}

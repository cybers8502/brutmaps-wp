<?php
namespace Brut\Services;

class UserService {
    public static function updateRefreshToken(int $user_id): string {
        $token = wp_generate_uuid4();
        update_user_meta($user_id, 'custom_refresh_token', $token);
        return $token;
    }

    public static function createGoogleUser(array $params): int {
        $user_id = wp_create_user($params['email'], wp_generate_password(), $params['email']);
        $user = new \WP_User($user_id);
        $user->set_role('subscriber');

        update_user_meta($user_id, 'first_name', $params['first_name']);
        update_user_meta($user_id, 'last_name', $params['last_name']);
        update_user_meta($user_id, 'profile_photo', $params['avatar'] ?? '');

        (new MailchimpService())->subscribe($params['email'], $params['first_name'], $params['last_name']);

        return $user_id;
    }

    public static function createRegisteredUser(array $params, array $files): int|\WP_Error {
        $email = sanitize_email($params['email']);
        if (email_exists($email)) {
            return new \WP_Error('user_exists', 'User already exists', ['status' => 409]);
        }

        $user_id = wp_create_user($email, $params['password'], $email);
        if (is_wp_error($user_id)) return $user_id;

        $user = new \WP_User($user_id);
        $user->set_role('subscriber');

        update_user_meta($user_id, 'first_name', sanitize_text_field($params['first_name']));
        update_user_meta($user_id, 'last_name', sanitize_text_field($params['last_name']));
        update_user_meta($user_id, 'billing_country', $params['country']);
        update_user_meta($user_id, 'country', $params['country']);
        update_user_meta($user_id, 'mailchimp_subscribed', (bool)$params['subscribe_to_newsletter']);

        if (!empty($files['photo']['name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $photo_id = media_handle_upload('photo', 0);
            if (!is_wp_error($photo_id)) {
                update_user_meta($user_id, 'profile_photo', wp_get_attachment_url($photo_id));
            }
        }

        if (!empty($params['subscribe_to_newsletter'])) {
            (new MailchimpService())->subscribe($email, $params['first_name'], $params['last_name']);
        }

        return $user_id;
    }
}

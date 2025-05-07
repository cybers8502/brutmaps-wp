<?php

use Services\MailchimpService;
use Services\UserDeletionService;

// - GET: /user-profile
function API_GET_PROFILE() {
    $user_id = get_current_user_id();

    if (!$user_id) {
        return new WP_Error('not_logged_in', 'User is not authorise.', array('status' => 401));
    }

    $user_data = get_userdata($user_id);
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name = get_user_meta($user_id, 'last_name', true);
    $email = $user_data->user_email;
    $country = get_user_meta($user_id, 'country', true);
    $photo_url = get_user_meta($user_id, 'profile_photo', true);

    $response = array(
        'user_id'           => $user_id,
        'email'             => $email,
        'first_name'        => $first_name,
        'last_name'         => $last_name,
        'country'           => $country,
        'photo_url'         => $photo_url,
    );

    try {
        $services = new MailchimpService();
        $response['is_subscribed'] = $services->isSubscribed($email);
    } catch (Exception $e) {
        error_log('Mailchimp error: ' . $e->getMessage());
    }

    return rest_ensure_response($response);
}

// - POST: /edit-profile
function API_POST_EDIT_PROFILE(WP_REST_Request $request) {
    $user_id = get_current_user_id();

    if (!$user_id || !current_user_can('edit_user', $user_id)) {
        return new WP_Error('not_logged_in', 'User is not logged in or cannot edit this profile.', array('status' => 401));
    }

    $first_name = sanitize_text_field($request->get_param('first_name'));
    $last_name = sanitize_text_field($request->get_param('last_name'));
    $country = sanitize_text_field($request->get_param('country'));
    $email = sanitize_email($request->get_param('email'));
    $photo = $request->get_file_params();

    $subscribeServices = new MailchimpService();
    $user = get_userdata($user_id);

    // Оновлення імені
    if (!empty($first_name)) {
        update_user_meta($user_id, 'first_name', $first_name);
    }

    // Оновлення прізвища
    if (!empty($last_name)) {
        update_user_meta($user_id, 'last_name', $last_name);
    }

    // Оновлення країни
    if (!empty($country)) {
        update_user_meta($user_id, 'country', $country);
    }

    // Оновлення email
    if (!empty($email) && $email !== $user->user_email) {
        if (!is_email($email)) {
            return new WP_Error('invalid_email', 'Invalid email address.', array('status' => 400));
        }

        if (email_exists($email)) {
            return new WP_Error('email_exists', 'This email address is already in use.', array('status' => 400));
        }

        wp_update_user(array(
            'ID' => $user_id,
            'user_email' => $email,
        ));
    }

    // Оновлення фото профілю
    if (!empty($photo['photo']) && !empty($photo['photo']['tmp_name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Видаляємо старе фото
        $old_photo_id = get_user_meta($user_id, 'profile_photo', true);
        if (!empty($old_photo_id)) {
            wp_delete_attachment($old_photo_id, true);
        }

        $attachment_id = media_handle_upload('photo', 0);
        if (is_wp_error($attachment_id)) {
            return new WP_Error('photo_upload_failed', 'Failed to upload photo.', array('status' => 400));
        }

        update_user_meta($user_id, 'profile_photo', $attachment_id);
    }

    $response = array(
        'status' => 'success',
        'message' => 'Profile updated successfully.',
        'data' => array(
            'email' => get_userdata($user_id)->user_email,
            'first_name' => get_user_meta($user_id, 'first_name', true),
            'last_name' => get_user_meta($user_id, 'last_name', true),
            'country' => get_user_meta($user_id, 'country', true),
            'photo_url' => wp_get_attachment_url(get_user_meta($user_id, 'profile_photo', true)),
            'subscribe' => $subscribeServices->isSubscribed($email),
        ),
    );

    // Оновлення підписки на розсилку
    try {
        $subscribeServices->isSubscribed($email) ?
            $subscribeServices->unsubscribe($email) :
            $subscribeServices->subscribe($email, $first_name, $last_name);
    } catch (Exception $e) {
        error_log('Mailchimp error: ' . $e->getMessage());
    }

    return rest_ensure_response($response);
}

// - GET: /user-countries
function API_GET_USER_COUNTRIES_LIST() {
    if (!class_exists('WC_Countries')) {
        return new WP_Error('woocommerce_not_installed', 'WooCommerce doesn\'t installed.', array('status' => 404));
    }

    $countries = new WC_Countries();
    return rest_ensure_response([
        'status' => 'success',
        'data' => $countries->get_countries()
    ]);
}

/**
 * DELETE: /delete-account
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function API_DELETE_USER_ACCOUNT(WP_REST_Request $request)
{
    $user_id = get_current_user_id();

    // Перевірка автентифікації
    if (!$user_id) {
        return new WP_Error('unauthorized', 'User not authenticated.', ['status' => 401]);
    }

    try {
        $deleteService = new UserDeletionService();
        $deleteService->delete($user_id);

        return rest_ensure_response([
            'status' => 'success',
            'message' => 'Account and all associated data deleted successfully.',
        ]);
    } catch (Exception $e) {
        return new WP_Error('delete_failed', 'Failed to delete account: ' . $e->getMessage(), ['status' => 500]);
    }
}

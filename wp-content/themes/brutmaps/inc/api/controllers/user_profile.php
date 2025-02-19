<?php

function API_POST_REGISTER_USER(WP_REST_Request $request) {
    $params = $request->get_params();
    $files = $request->get_file_params();

    // Перевіряємо, чи надійшли всі необхідні дані
    if (!isset($params['first_name']) || !isset($params['last_name']) || !isset($params['email']) || !isset($params['password']) || !isset($params['country'])) {
        return new WP_Error('missing_fields', 'Required fields: first_name, last_name, email, password, country.', array('status' => 400));
    }

    $first_name = sanitize_text_field($params['first_name']);
    $last_name = sanitize_text_field($params['last_name']);
    $email = sanitize_email($params['email']);
    $password = sanitize_text_field($params['password']);
    $country = sanitize_text_field($params['country']);

    $subscribe_to_newsletter = isset($params['subscribe_to_newsletter']) ? filter_var($params['subscribe_to_newsletter'], FILTER_VALIDATE_BOOLEAN) : false;

    // Формуємо username як комбінацію first_name та last_name
    $username = sanitize_user(strtolower($first_name . '.' . $last_name));

    // Генеруємо унікальне username, якщо вже існує такий
    $original_username = $username;
    $counter = 1;
    while (username_exists($username)) {
        $username = $original_username . $counter;
        $counter++;
    }

    // Перевіряємо, чи вже існує користувач із таким username або email
    if (username_exists($username) || email_exists($email)) {
        return new WP_Error('user_exists', 'A user with this name or email already exists.', array('status' => 400));
    }

    // Створюємо нового користувача
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        return new WP_Error('registration_failed', 'User registration failed.', array('status' => 500));
    }

    // Призначаємо роль "subscriber"
    $user = new WP_User($user_id);
    $user->set_role('subscriber');

    // Додаємо додаткові мета-дані
    update_user_meta($user_id, 'first_name', $first_name);
    update_user_meta($user_id, 'last_name', $last_name);
    update_user_meta($user_id, 'billing_country', $country);
    update_user_meta($user_id, 'country', $country);
    update_user_meta($user_id, 'mailchimp_subscribed', $subscribe_to_newsletter);

    // Завантажуємо фото, якщо воно є
    if (isset($files['photo']) && !empty($files['photo']['name'])) {
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif');

        if (!in_array($files['photo']['type'], $allowed_types)) {
            return new WP_Error('invalid_file_type', 'Invalid file type.', array('status' => 400));
        }

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $photo_id = media_handle_upload('photo', 0);

        if (is_wp_error($photo_id)) {
            return new WP_Error('photo_upload_failed', 'Couldn\'t download the photo.', array('status' => 500));
        }

        // Зберігаємо ID фото як мета-дані користувача
        update_user_meta($user_id, 'profile_photo', $photo_id);
    }

    // Підпуємо на розсилку
    if ($subscribe_to_newsletter && class_exists('WC_Integration_MailChimp')) {
        $mailchimp = new WC_Integration_MailChimp();
        $mailchimp->subscribe_email($email, array(
            'FNAME' => $first_name,
            'LNAME' => $last_name,
        ));
    }

    return new WP_REST_Response(array(
        'status' => 'success',
        'message' => 'User successfully registered.',
    ), 200);
}

function API_GET_PROFILE() {
    $user_id = get_current_user_id();

    if (!$user_id) {
        return new WP_Error('not_logged_in', 'User is not authorise.', array('status' => 401));
    }

    $user_data = get_userdata($user_id);
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name = get_user_meta($user_id, 'last_name', true);
    $email = get_user_meta($user_id, 'email', true);
    $country = get_user_meta($user_id, 'country', true);
    $photo_id = get_user_meta($user_id, 'profile_photo', true);
    $photo_url = $photo_id ? wp_get_attachment_url($photo_id) : null;

    $response = array(
        'email' => $user_data->user_email,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'country' => $country,
        'photo_url' => $photo_url,
    );

    if (class_exists('WC_Integration_MailChimp')) {
        $mailchimp = new WC_Integration_MailChimp();
        $response['is_subscribed'] = $mailchimp->is_subscribed($email);
    }

    return rest_ensure_response($response);
}

function API_GET_USER_COUNTRIES_LIST() {
    if (!class_exists('WC_Countries')) {
        return new WP_Error('woocommerce_not_installed', 'WooCommerce doesn\'t installed.', array('status' => 404));
    }

    $countries = new WC_Countries();
    return rest_ensure_response($countries->get_countries());
}

function API_POST_LOST_PASSWORD(WP_REST_Request $request) {
    $email = sanitize_email($request->get_param('email'));

    if (!email_exists($email)) {
        return new WP_Error('email_not_found', 'No user with this email was found.', array('status' => 404));
    }

    $user = get_user_by('email', $email);
    $reset_key = get_password_reset_key($user);

    if (is_wp_error($reset_key)) {
        return new WP_Error('reset_failed', 'Could not create a password reset key.', array('status' => 500));
    }

    $reset_link = network_site_url("reset-password?key=$reset_key&login=" . rawurlencode($user->user_login));
    $subject = 'Request for password reset';
    $message = "Follow the link to change your password: $reset_link";

    wp_mail($email, $subject, $message);

    return rest_ensure_response(array('status' => 'success', 'message' => 'The letter has been sent.'));
}

function API_POST_RESET_PASSWORD(WP_REST_Request $request) {
    $key = sanitize_text_field($request->get_param('key'));
    $login = sanitize_text_field($request->get_param('login'));
    $new_password = sanitize_text_field($request->get_param('new_password'));

    if (empty($key) || empty($login) || empty($new_password)) {
        return new WP_Error('missing_fields', 'All fields are required.', array('status' => 400));
    }

    $user = get_user_by('login', $login);
    if (!$user) {
        return new WP_Error('user_not_found', 'User not found.', array('status' => 404));
    }

    $check = check_password_reset_key($key, $user->user_login);
    if (is_wp_error($check)) {
        return new WP_Error('invalid_key', 'The password reset key is invalid or expired.', array('status' => 400));
    }

    // Оновлення пароля
    wp_set_password($new_password, $user->ID);

    return rest_ensure_response(array(
        'status' => 'success',
        'message' => 'Password has been successfully changed.',
    ));
}

function API_POST_CHANGE_PASSWORD(WP_REST_Request $request) {
    $user_id = get_current_user_id();

    if (!$user_id) {
        return new WP_Error('not_logged_in', 'User is not logged in.', array('status' => 401));
    }

    $current_password = $request->get_param('current_password');
    $new_password = $request->get_param('new_password');

    if (empty($current_password) || empty($new_password)) {
        return new WP_Error('missing_fields', 'Current and new password are required.', array('status' => 400));
    }

    $user = get_userdata($user_id);

    if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
        return new WP_Error('incorrect_password', 'The current password is incorrect.', array('status' => 400));
    }

    wp_set_password($new_password, $user_id);

    return rest_ensure_response(array(
        'status' => 'success',
        'message' => 'Password updated successfully.',
    ));
}

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
    $subscribe_to_newsletter = filter_var($request->get_param('subscribe_to_newsletter'), FILTER_VALIDATE_BOOLEAN);

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

    // Оновлення підписки на розсилку
    if (class_exists('WC_Integration_MailChimp')) {
        $mailchimp = new WC_Integration_MailChimp();
        if ($subscribe_to_newsletter) {
            $mailchimp->subscribe_email($email, array(
                'FNAME' => $first_name,
                'LNAME' => $last_name,
            ));
        } else {
            $mailchimp->unsubscribe_email($email);
        }
    }

    update_user_meta($user_id, 'mailchimp_subscribed', $subscribe_to_newsletter);

    return rest_ensure_response(array(
        'status' => 'success',
        'message' => 'Profile updated successfully.',
        'data' => array(
            'email' => get_userdata($user_id)->user_email,
            'first_name' => get_user_meta($user_id, 'first_name', true),
            'last_name' => get_user_meta($user_id, 'last_name', true),
            'country' => get_user_meta($user_id, 'country', true),
            'photo_url' => wp_get_attachment_url(get_user_meta($user_id, 'profile_photo', true)),
        ),
    ));
}

function API_POST_REFRESH_JWT_TOKEN(WP_REST_Request $request) {
    $refresh_token = sanitize_text_field($request->get_param('refresh_token'));

    if (!$refresh_token) {
        return new WP_Error('missing_token', 'Refresh token is required.', array('status' => 400));
    }

    try {
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : 'your_secret_key';
        $decoded = JWT::decode($refresh_token, new Key($secret_key, 'HS256'));

        if (!isset($decoded->exp) || $decoded->exp < time()) {
            return new WP_Error('token_expired', 'Refresh token expired.', array('status' => 401));
        }

        $user_id = $decoded->data->user->id;
        if (!$user_id || !get_userdata($user_id)) {
            return new WP_Error('invalid_token', 'Invalid refresh token.', array('status' => 401));
        }

        // Перевіряємо, чи refresh token не є застарілим
        $stored_refresh_token = get_user_meta($user_id, 'refresh_token', true);
        if ($stored_refresh_token !== $refresh_token) {
            return new WP_Error('jwt_auth_obsolete_refresh_token', 'Refresh token is obsolete', array('status' => 401));
        }

        // Генеруємо новий токен
        $issued_at = time();
        $expire_access = $issued_at + (10 * 60);
        $expire_refresh = $issued_at + (7 * 24 * 60 * 60); // 7 днів

        $new_access_token = JWT::encode([
            'iss' => get_bloginfo('url'),
            'iat' => $issued_at,
            'nbf' => $issued_at,
            'exp' => $expire_access,
            'data' => ['user' => ['id' => $user_id]],
        ], $secret_key);

        $new_refresh_token = JWT::encode([
            'iss' => get_bloginfo('url'),
            'iat' => $issued_at,
            'nbf' => $issued_at,
            'exp' => $expire_refresh,
            'data' => ['user' => ['id' => $user_id]],
        ], $secret_key);

        // Оновлюємо refresh-токен у БД
        update_user_meta($user_id, 'refresh_token', $new_refresh_token);

        return rest_ensure_response([
            'token' => $new_access_token,
            'refresh_token' => $new_refresh_token,
            'message' => 'Token refreshed successfully',
        ]);
    } catch (Exception $e) {
        return new WP_Error('invalid_token', 'Invalid refresh token.', array('status' => 401));
    }
}

function API_DELETE_USER_ACCOUNT(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', 'User not authenticated.', array('status' => 401));
    }

    delete_user_account($user_id);

    return rest_ensure_response([
        'status' => 'success',
        'message' => 'Account and all associated data deleted successfully.',
    ]);
};

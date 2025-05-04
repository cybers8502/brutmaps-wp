<?php

use services\MailchimpService;

// - POST: /login
function API_POST_LOGIN_USER(WP_REST_Request $request) {
    $ip         = $_SERVER['REMOTE_ADDR'];
    $email      = sanitize_user($request->get_param('username'));
    $password   = $request->get_param('password');

    // Rate limiting по IP
    if (is_rate_limited("ip_$ip", 5, 60)) {
        return new WP_Error('rate_limited', 'Too many requests from your IP. Try again later.', ['status' => 429]);
    }

    // Rate limiting по email
    if (is_rate_limited("email_" . md5($email), 3, 300)) {
        return new WP_Error('rate_limited_email', 'Too many registration attempts for this email.', ['status' => 429]);
    }

    $user = wp_authenticate($email, $password);

    if (is_wp_error($user)) {
        return new WP_Error('invalid_credentials', 'Invalid login or password', ['status' => 403]);
    }

    $user_id = $user->ID;

    // Генеруємо access_token
    $access_token = jwt_auth_generate_token($user_id);

    // Генеруємо refresh_token
    $refresh_token = wp_generate_uuid4();
    update_user_meta($user_id, 'custom_refresh_token', $refresh_token);

    return rest_ensure_response([
        'status' => 'success',
        'access_token'  => $access_token,
        'refresh_token' => $refresh_token,
        'user' => [
            'email'      => $user->user_email,
        ],
    ]);
}

// - POST: /google-login
function API_POST_LOGIN_GOOGLE_USER(WP_REST_Request $request) {
    $ip         = $_SERVER['REMOTE_ADDR'];
    $params = $request->get_json_params();
    $email = sanitize_email($params['email']);
    $user = get_user_by('email', $email);

    // Rate limiting по IP
    if (is_rate_limited("ip_$ip", 5, 60)) {
        return new WP_Error('rate_limited', 'Too many requests from your IP. Try again later.', ['status' => 429]);
    }

    // Rate limiting по email
    if (is_rate_limited("email_" . md5($email), 3, 300)) {
        return new WP_Error('rate_limited_email', 'Too many registration attempts for this email.', ['status' => 429]);
    }

    if (!$user) {
        $first_name = sanitize_text_field($request['first_name']);
        $last_name  = sanitize_text_field($request['last_name']);

        $user_id = wp_create_user($email, wp_generate_password(), $email);

        if (is_wp_error($user_id)) {
            return new WP_Error('registration_failed', 'User registration failed.', ['status' => 500]);
        }

        // Призначаємо роль "subscriber"
        $user = new WP_User($user_id);
        $user->set_role('subscriber');
        $avatar_url = esc_url_raw($params['avatar']);

        // Додаємо додаткові мета-дані
        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);

        // Зберегти аватар (як user_meta)
        if ($avatar_url) {
            update_user_meta($user_id, 'profile_photo', $avatar_url);
        }

        // Підпуємо на розсилку
        try {
            $subscribeService = new MailchimpService();
            $subscribeService->subscribe($email, $first_name, $last_name);
        } catch (Exception $e) {
            error_log('Mailchimp error: ' . $e->getMessage());
        }

    }

    $access_token = jwt_auth_generate_token($user->ID);
    $refresh_token = wp_generate_uuid4();
    update_user_meta($user->ID, 'custom_refresh_token', $refresh_token);

    return rest_ensure_response([
        'status' => 'success',
        'access_token'  => $access_token,
        'refresh_token' => $refresh_token,
        'user' => [
            'email' => $user->user_email,
        ],
    ]);
}

// - POST: /registration
function API_POST_REGISTER_USER(WP_REST_Request $request) {
    $ip         = $_SERVER['REMOTE_ADDR'];
    $params = $request->get_params();
    $files = $request->get_file_params();

    // Rate limiting по IP
    if (is_rate_limited("ip_$ip", 5, 60)) {
        return new WP_Error('rate_limited', 'Too many requests from your IP. Try again later.', ['status' => 429]);
    }

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

    // Rate limiting по email
    if (is_rate_limited("email_" . md5($email), 3, 300)) {
        return new WP_Error('rate_limited_email', 'Too many registration attempts for this email.', ['status' => 429]);
    }

    // Перевіряємо, чи вже існує користувач із таким username або email
    if (email_exists($email)) {
        return new WP_Error('user_exists', 'A user with this name or email already exists.', array('status' => 409));
    }

    // Створюємо нового користувача
    $user_id = wp_create_user($email, $password, $email);

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

        // Зберігаємо URL фото як мета-дані користувача
        update_user_meta($user_id, 'profile_photo', wp_get_attachment_url($photo_id));
    }

    // Підпуємо на розсилку
    if ($subscribe_to_newsletter) {
        try {
            $subscribeService = new MailchimpService();
            $subscribeService->subscribe($email, $first_name, $last_name);
        } catch (Exception $e) {
            error_log('Mailchimp error: ' . $e->getMessage());
        }
    }

    // 7. Генерація JWT токена
    $jwt_token = jwt_auth_generate_token($user_id);

    // 8. Генерація refresh_token UUID токена
    $refresh_token = wp_generate_uuid4();
    update_user_meta($user_id, 'custom_refresh_token', $refresh_token);

    if (is_wp_error($jwt_token)) {
        return new WP_Error('token_error', 'Could not generate token.', ['status' => 500]);
    }

    return rest_ensure_response([
        'status' => 'success',
        'access_token' => $jwt_token,
        'refresh_token' => $jwt_token,
        'user'    => [
            'email'      => $email,
        ],
    ]);
}

// - POST: /google-registration
function API_POST_REGISTER_GOOGLE_USER(WP_REST_Request $request) {
    $ip         = $_SERVER['REMOTE_ADDR'];
//    $nonce      = $request->get_header('X-WP-Nonce');
    $email      = sanitize_email($request['email']);
    $first_name = sanitize_text_field($request['first_name']);
    $last_name  = sanitize_text_field($request['last_name']);
    $avatar_url = esc_url_raw($request['avatar']);

    // Перевірка nonce
    /*if (!wp_verify_nonce($nonce, 'custom_register_google')) {
        return new WP_Error('invalid_nonce', 'Invalid request.', ['status' => 403]);
    }*/

    // Rate limiting по IP
    if (is_rate_limited("ip_$ip", 5, 60)) {
        return new WP_Error('rate_limited', 'Too many requests from your IP. Try again later.', ['status' => 429]);
    }

    // Rate limiting по email
    if (is_rate_limited("email_" . md5($email), 3, 300)) {
        return new WP_Error('rate_limited_email', 'Too many registration attempts for this email.', ['status' => 429]);
    }

    // Якщо користувач вже існує
    if (email_exists($email)) {
        return new WP_Error('user_exists', 'A user with this name or email already exists.', ['status' => 409]);
    }

    // Створення користувача
    $user_id = wp_create_user($email, wp_generate_password(), $email);

    if (is_wp_error($user_id)) {
        return new WP_Error('registration_failed', 'User registration failed.', ['status' => 500]);
    }

    // Призначаємо роль "subscriber"
    $user = new WP_User($user_id);
    $user->set_role('subscriber');

    // Додаємо додаткові мета-дані
    update_user_meta($user_id, 'first_name', $first_name);
    update_user_meta($user_id, 'last_name', $last_name);

    // 6. Зберегти аватар (як user_meta)
    if ($avatar_url) {
        update_user_meta($user_id, 'profile_photo', $avatar_url);
    }

    // Підпуємо на розсилку
    try {
        $subscribeService = new MailchimpService();
        $subscribeService->subscribe($email, $first_name, $last_name);
    } catch (Exception $e) {
        error_log('Mailchimp error: ' . $e->getMessage());
    }

    // 7. Генерація JWT токена
    $jwt_token = jwt_auth_generate_token($user_id);

    // 8. Генерація refresh_token UUID токена
    $refresh_token = wp_generate_uuid4();
    update_user_meta($user_id, 'custom_refresh_token', $refresh_token);

    if (is_wp_error($jwt_token)) {
        return new WP_Error('token_error', 'Could not generate token.', ['status' => 500]);
    }

    return rest_ensure_response([
        'status' => 'success',
        'user'    => [
            'id'         => $user_id,
            'email'      => $email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
        ],
        'access_token' => $jwt_token,
        'refresh_token' => $jwt_token,
    ]);
}

// - POST: /check-email
function API_POST_CHECK_EMAIL_EXISTENCE(WP_REST_Request $request) {
    $email = sanitize_email($request->get_param('email'));

    if (empty($email)) {
        return new WP_Error('missing_email', 'Email is required.', array('status' => 400));
    }

    if (!is_email($email)) {
        return new WP_Error('invalid_email', 'Invalid email address.', array('status' => 400));
    }

    if (email_exists($email)) {
        return rest_ensure_response([
            'status' => 'error',
            'message' => 'This email is already in use.',
            'exists' => true
        ]);
    }

    return rest_ensure_response([
        'status' => 'success',
        'message' => 'Email is available.',
        'exists' => false
    ]);
}

// - POST: /lost-password
function API_POST_LOST_PASSWORD(WP_REST_Request $request) {
    $ip         = $_SERVER['REMOTE_ADDR'];
    $email = sanitize_email($request->get_param('email'));

    // Rate limiting по IP
    if (is_rate_limited("ip_$ip", 5, 60)) {
        return new WP_Error('rate_limited', 'Too many requests from your IP. Try again later.', ['status' => 429]);
    }

    // Rate limiting по email
    if (is_rate_limited("email_" . md5($email), 3, 300)) {
        return new WP_Error('rate_limited_email', 'Too many registration attempts for this email.', ['status' => 429]);
    }

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

    return rest_ensure_response([
        'status' => 'success',
        'message' => 'The letter has been sent.'
    ]);
}

// - POST: /reset-password
function API_POST_RESET_PASSWORD(WP_REST_Request $request) {
    $ip         = $_SERVER['REMOTE_ADDR'];
    $key = sanitize_text_field($request->get_param('key'));
    $login = sanitize_text_field($request->get_param('login'));
    $new_password = sanitize_text_field($request->get_param('new_password'));

    // Rate limiting по IP
    if (is_rate_limited("ip_$ip", 5, 60)) {
        return new WP_Error('rate_limited', 'Too many requests from your IP. Try again later.', ['status' => 429]);
    }

    // Rate limiting по email
    if (is_rate_limited("email_" . md5($login), 3, 300)) {
        return new WP_Error('rate_limited_email', 'Too many registration attempts for this email.', ['status' => 429]);
    }

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

    return rest_ensure_response([
        'status' => 'success',
        'message' => 'Password has been successfully changed.',
    ]);
}

// - POST: /change-password
function API_POST_CHANGE_PASSWORD(WP_REST_Request $request) {
    $ip         = $_SERVER['REMOTE_ADDR'];
    $user_id = get_current_user_id();

    // Rate limiting по IP
    if (is_rate_limited("ip_$ip", 5, 60)) {
        return new WP_Error('rate_limited', 'Too many requests from your IP. Try again later.', ['status' => 429]);
    }

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

    return rest_ensure_response([
        'status' => 'success',
        'message' => 'Password updated successfully.',
    ]);
}

// - POST: /token/refresh
function API_POST_REFRESH_JWT_TOKEN(WP_REST_Request $request) {
    $email = sanitize_email($request->get_param('email'));
    $provided_refresh = sanitize_text_field($request->get_param('refresh_token'));

    $user = get_user_by('email', $email);

    if (!$user) {
        return new WP_Error('invalid_email', 'User not found.', ['status' => 403]);
    }

    $stored_refresh = get_user_meta($user->ID, 'custom_refresh_token', true);

    if (!$stored_refresh || $stored_refresh !== $provided_refresh) {
        return new WP_Error('invalid_token', 'Invalid refresh token.', ['status' => 403]);
    }

    $new_access_token = jwt_auth_generate_token($user->ID);

    $new_refresh_token = wp_generate_uuid4();
    update_user_meta($user->ID, 'custom_refresh_token', $new_refresh_token);

    return rest_ensure_response([
        'access_token'  => $new_access_token,
        'refresh_token' => $new_refresh_token,
    ]);
}

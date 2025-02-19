<?php
// Remove Menu
add_action( 'admin_menu', 'remove_menus' );

function remove_menus(){
    remove_menu_page( 'index.php' );
    remove_menu_page( 'edit-comments.php' );
}

add_action( 'admin_init', 'remove_menu_nonadmin' );

function remove_menu_nonadmin () {
    global $user_ID;

    remove_menu_page( 'edit-comments.php' );
//    remove_menu_page( 'edit.php?post_type=acf-field-group' );

    if ( !current_user_can('administrator') ) {
        remove_menu_page( 'index.php' );
        remove_menu_page( 'edit.php' );
        remove_menu_page( 'edit-comments.php' );
        remove_menu_page( 'themes.php' );
        remove_menu_page( 'plugins.php' );
        remove_menu_page( 'users.php' );
        remove_menu_page( 'tools.php' );
        remove_menu_page( 'theme-setup' );
        remove_menu_page( 'edit.php?post_type=page' );
    }
}

// Add Google Map Key
function my_acf_init() {
    acf_update_setting('google_api_key', '***REMOVED-GOOGLE-API-KEY***' ); // credentials max.tkh.ua@gmail.com
}

add_action( 'acf/init', 'my_acf_init' );

function theme_setup() {
    add_theme_support('post-thumbnails');
}

add_action('after_setup_theme', 'theme_setup');

/*add_filter('jwt_auth_token_before_dispatch', function ($data, $user) {
    error_log(print_r($data, true)); // Логування результату

    $issued_at = time();
    $expire_refresh = $issued_at + (7 * 24 * 60 * 60); // 7 днів

    $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : 'your_secret_key';

    // Створюємо refresh_token
    $refresh_token_data = array(
        'iss' => get_bloginfo('url'),
        'iat' => $issued_at,
        'nbf' => $issued_at,
        'exp' => $expire_refresh,
        'data' => array(
            'user' => array(
                'id' => $user->ID,
            ),
        ),
    );

    $data['refresh_token'] = JWT::encode($refresh_token_data, $secret_key);

    // Зберігаємо refresh-токен в user_meta
    update_user_meta($user->ID, 'refresh_token', $data['refresh_token']);

    return $data;
}, 10, 2);*/





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


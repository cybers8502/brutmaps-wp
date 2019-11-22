<?php
// Remove Menu
add_action( 'admin_menu', 'remove_menus' );

function remove_menus(){
    remove_menu_page( 'index.php' );
    remove_menu_page( 'edit.php' );
//    remove_menu_page( 'upload.php' );
//    remove_menu_page( 'edit.php?post_type=page' );
    remove_menu_page( 'edit-comments.php' );
    remove_menu_page( 'themes.php' );
    remove_menu_page( 'plugins.php' );
//    remove_menu_page( 'users.php' );
    remove_menu_page( 'tools.php' );
}

add_action( 'admin_init', 'remove_menu_nonadmin' );

function remove_menu_nonadmin () {
    global $user_ID;
    if ( !current_user_can('administrator') ) {
        remove_menu_page( 'index.php' );
        remove_menu_page( 'edit.php' );
//        remove_menu_page( 'upload.php' );
        remove_menu_page( 'edit.php?post_type=page' );
        remove_menu_page( 'edit-comments.php' );
        remove_menu_page( 'themes.php' );
        remove_menu_page( 'plugins.php' );
        remove_menu_page( 'users.php' );
        remove_menu_page( 'tools.php' );
    }
}

// Add Google Map Key
function my_acf_init() {
    acf_update_setting('google_api_key', '***REMOVED-GOOGLE-API-KEY***' );
}

add_action( 'acf/init', 'my_acf_init' );

function register_acf_options_pages() {
	if( !function_exists('acf_add_options_page') )
		return;
	$option_page = acf_add_options_page(array(
		'page_title'    => __('Global'),
		'menu_title'    => __('Global'),
		'menu_slug'     => 'theme-global',
		'capability'    => 'edit_posts',
		'redirect'      => false
	));
}

// Hook into acf initialization.
add_action('acf/init', 'register_acf_options_pages');

add_filter( 'wp_mail_from_name', 'wpb_sender_name' );

// Function to change sender name
function wpb_sender_name( $original_email_from ) {
	return 'BRUTMAPS';
}
<?php
// Remove Menu
add_action( 'admin_menu', 'remove_menus' );

function remove_menus(){
    remove_menu_page( 'index.php' );
    remove_menu_page( 'edit.php' );
    remove_menu_page( 'edit-comments.php' );
    remove_menu_page( 'themes.php' );
}

add_action( 'admin_init', 'remove_menu_nonadmin' );

function remove_menu_nonadmin () {
    global $user_ID;
    if ( !current_user_can('administrator') ) {
        remove_menu_page( 'index.php' );
        remove_menu_page( 'edit.php' );
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
		'page_title'    => __('Setup'),
		'menu_title'    => __('Setup'),
		'menu_slug'     => 'theme-setup',
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

// Add Styles and Scripts
add_action('wp_enqueue_scripts', 'add_js');

function add_js(){

    wp_deregister_script('jquery');

    wp_register_script('swiper_js',get_template_directory_uri().'/assets/js/vendors/swiper.min.js', false, false, true);
    wp_register_script('scrollbar',get_template_directory_uri().'/assets/js/vendors/perfect-scrollbar.min.js', false, false, true);
    wp_register_script('mapboxapi','https://api.tiles.mapbox.com/mapbox-gl-js/v1.6.1/mapbox-gl.js', false, false, true);
    wp_register_script('geocoder','https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.2/mapbox-gl-geocoder.min.js', false, false, true);

    wp_register_script('common_js',get_template_directory_uri().'/assets/js/common.min.js', false, false, true);

    if ( is_home() || is_singular( 'sight' ) ){
        wp_enqueue_script('mapboxapi');
        wp_enqueue_script('geocoder');
        wp_enqueue_script('scrollbar');
        wp_enqueue_script('common_js');
        wp_enqueue_style('map_css','https://api.tiles.mapbox.com/mapbox-gl-js/v1.5.0/mapbox-gl.css');
        wp_enqueue_style('common_css',get_template_directory_uri().'/assets/css/common.css');
    }

    if ( is_page_template( array ('pages/support-page.php', 'pages/article-page.php' ) ) ){
        wp_enqueue_script('swiper_js');
        wp_enqueue_style('swiper_css',get_template_directory_uri().'/assets/css/swiper.min.css');
    }


}

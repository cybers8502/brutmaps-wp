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
        wp_enqueue_script('swiper_js');
        wp_enqueue_script('common_js');

        wp_enqueue_style('map_css','https://api.tiles.mapbox.com/mapbox-gl-js/v1.5.0/mapbox-gl.css');
        wp_enqueue_style('swiper_css',get_template_directory_uri().'/assets/css/swiper.min.css');
        wp_enqueue_style('common_css',get_template_directory_uri().'/assets/css/common.css');
    }

    if ( is_page_template( array ('pages/support-page.php', 'pages/article-page.php' ) ) ){
        wp_enqueue_script('common_js');

        wp_enqueue_style('common_css',get_template_directory_uri().'/assets/css/common.css');
    }

}

// Change the columns for the edit CPT screen

function change_columns( $cols ) {
    $cols = array(
        'cb'                => '<input type="checkbox" />',
        'title'             => __( 'Title' ),
        'architects'        => __( 'Architects', 'trans' ),
        'author_photo'      => __( 'Author Photo', 'trans' ),
        'contributor'       => __( 'Contributor', 'trans' ),
        'date'              => __( 'Date' ),
    );
    return $cols;
}
add_filter( "manage_sight_posts_columns", "change_columns" );

function custom_columns( $column, $post_id ) {
    switch ( $column ) {

        case "contributor":
            $term =  get_post_meta( $post_id, 'contributor', true);

            if ( !empty( $term ) ) {
                echo get_the_title( $term );
            }

            break;

        case "architects":
            $arr =  get_post_meta( $post_id, 'choose_architects', true);

            if ( !empty( $arr ) ) {

                $numItems = count($arr);
                $i = 0;

                foreach ( $arr as $term ) {
                    if( ++$i === $numItems ) {
                        echo '<p>'. get_the_title( $term ) .'</p>';
                    } else {
                        echo '<p>'. get_the_title( $term ) .', </p>';
                    }
                }
            }

            break;

        case "author_photo":
            $authors[] =  get_post_meta( $post_id, 'main_image_author_id', true);
            $arr = get_field( 'gallery', $post_id );

            if ( !empty( $arr ) ) {

                foreach ( $arr as $item ) {
                    $authors[] = $item["gallery_image_author_id"];
                }

            }

            $unique = array_unique( $authors );

            $numItems = count($unique);
            $i = 0;

            foreach ( $unique as $term ) {
                if( ++$i === $numItems ) {
                    echo '<p>'. get_the_title( $term ) .'</p>';
                } else {
                    echo '<p>'. get_the_title( $term ) .', </p>';
                }
            }

            break;
    }
}
add_action( "manage_posts_custom_column", "custom_columns", 10, 2 );

// Prevent Update Plugins
add_filter( 'site_transient_update_plugins', 'filter_plugin_updates' );

function filter_plugin_updates( $update ) {
    global $DISABLE_UPDATE;

    if( !is_array($DISABLE_UPDATE) || count($DISABLE_UPDATE) == 0 ){  return $update;  }

    foreach( $update->response as $name => $val ){
        foreach( $DISABLE_UPDATE as $plugin ){
            if( stripos($name,$plugin) !== false ){
                unset( $update->response[ $name ] );
            }
        }
    }

    return $update;
}

// Make edit screen columns sortable

add_filter( 'manage_edit-sight_sortable_columns', 'my_sortable_sight_column' );

function my_sortable_sight_column( $columns ) {
    $columns['contributor'] = 'contributor';
    $columns['author_photo'] = 'author_photo';

    return $columns;
}

add_action( 'pre_get_posts', 'manage_wp_posts_be_qe_pre_get_posts', 1 );

function manage_wp_posts_be_qe_pre_get_posts( $query ) {
    if ( $query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) ) {
        switch( $orderby ) {

            case 'contributor':
                $query->set( 'meta_key', 'contributor' );
                $query->set( 'orderby', 'meta_value' );
                break;

            case 'architects':
                $query->set( 'meta_key', 'choose_architects' );
                $query->set( 'orderby', 'meta_value' );
                break;

        }
    }
}

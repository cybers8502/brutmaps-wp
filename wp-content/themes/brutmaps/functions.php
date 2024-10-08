<?php

define( 'TEMPLATEINC', TEMPLATEPATH . '/inc' );
define( 'TEMPLATEURI', get_template_directory_uri() );
define( 'DIRECT', TEMPLATEURI.'/assets/' );

define( 'ABOUT', 1211);

require_once( TEMPLATEINC . '/object/object_acf_fields.php' );
require_once( TEMPLATEINC . '/object/object_taxonomy.php' );
require_once( TEMPLATEINC . '/object/object_post_type.php' );
require_once( TEMPLATEINC . '/contributor/contributor_post_type.php' );
require_once( TEMPLATEINC . '/contributor/contributor_acf_fields.php' );
require_once( TEMPLATEINC . '/authors/author_post_type.php' );
require_once( TEMPLATEINC . '/authors/author_acf_fields.php' );
require_once( TEMPLATEINC . '/setup/setup_acf_register.php' );
require_once( TEMPLATEINC . '/setup/setup_acf_fields.php' );
require_once( TEMPLATEINC . '/about_fields/about_fields_acf_fields.php' );
require_once( TEMPLATEINC . '/about_fields/about_acf_register.php' );
require_once( TEMPLATEINC . '/creator/creator_acf_fields.php' );
require_once( TEMPLATEINC . '/creator/creator_post_type.php' );
require_once( TEMPLATEINC . '/actions.php' );
require_once( TEMPLATEINC . '/ajax.php' );
require_once( TEMPLATEINC . '/styles_and_scripts.php' );
require_once( TEMPLATEINC . '/email.php' );
require_once( TEMPLATEINC . '/post_redirect_rule.php' );

require_once( TEMPLATEINC . '/api/api_publicity_settings.php' );
require_once( TEMPLATEINC . '/api/routing.php' );
require_once( TEMPLATEINC . '/api/helper_functions.php' );

require_once( TEMPLATEINC . '/api/controllers/objects_controller.php' );
require_once( TEMPLATEINC . '/api/controllers/about_controller.php' );
require_once( TEMPLATEINC . '/api/controllers/creators_controller.php' );

require_once( TEMPLATEINC . '/object/object_update_interceptor.php' );

//require_once( TEMPLATEINC . '/insta_graber.php' );

add_filter( 'cfw_get_billing_checkout_fields', 'remove_checkout_fields', 100 );

function remove_checkout_fields( $fields ) {
    error_log( 'Checkout fields modified 1' );

    unset( $fields['billing_company'] );
    unset( $fields['billing_city'] );
    unset( $fields['billing_postcode'] );
    unset( $fields['billing_country'] );
    unset( $fields['billing_state'] );
    unset( $fields['billing_address_1'] );
    unset( $fields['billing_address_2'] );
    return $fields;
}

add_filter( 'woocommerce_checkout_fields', 'unrequire_checkout_fields', 100 );

function unrequire_checkout_fields( $fields ) {
    error_log( 'Checkout fields modified 2' );

    $fields['billing']['billing_company']['required']   = false;
    $fields['billing']['billing_city']['required']      = false;
    $fields['billing']['billing_postcode']['required']  = false;
    $fields['billing']['billing_country']['required']   = false;
    $fields['billing']['billing_state']['required']     = false;
    $fields['billing']['billing_address_1']['required'] = false;
    $fields['billing']['billing_address_2']['required'] = false;
}

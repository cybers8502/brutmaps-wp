<?php

define( 'TEMPLATEINC', TEMPLATEPATH . '/inc' );
define( 'TEMPLATEURI', get_template_directory_uri() );
define( 'DIRECT', TEMPLATEURI.'/assets/' );

define( 'ABOUT', 1211);

require_once( TEMPLATEINC . '/actions.php' );
require_once( TEMPLATEINC . '/ajax.php' );
require_once( TEMPLATEINC . '/styles_and_scripts.php' );
require_once( TEMPLATEINC . '/email.php' );
require_once( TEMPLATEINC . '/post_redirect_rule.php' );
require_once( TEMPLATEINC . '/shortcode.php' );

require_once( TEMPLATEINC . '/object/object_acf_fields.php' );
require_once( TEMPLATEINC . '/object/object_taxonomy.php' );
require_once( TEMPLATEINC . '/object/object_post_type.php' );
require_once( TEMPLATEINC . '/object/object_update_interceptor.php' );

require_once( TEMPLATEINC . '/product/product_acf_fields.php' );

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

require_once( TEMPLATEINC . '/api/api_publicity_settings.php' );
require_once( TEMPLATEINC . '/api/routing.php' );
require_once( TEMPLATEINC . '/api/helper_functions.php' );

//TODO delete routings
require_once( TEMPLATEINC . '/api/controllers/objects_controller.php' );
require_once( TEMPLATEINC . '/api/controllers/about_controller.php' );
require_once( TEMPLATEINC . '/api/controllers/creators_controller.php' );

//require_once( TEMPLATEINC . '/insta_graber.php' );


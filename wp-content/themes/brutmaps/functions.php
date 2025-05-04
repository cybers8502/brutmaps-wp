<?php

define( 'TEMPLATEINC', TEMPLATEPATH . '/inc' );
define( 'TEMPLATEURI', get_template_directory_uri() );
define( 'DIRECT', TEMPLATEURI.'/assets/' );

require_once( TEMPLATEINC . '/actions.php' );
require_once( TEMPLATEINC . '/ajax.php' );
require_once( TEMPLATEINC . '/styles_and_scripts.php' );
require_once( TEMPLATEINC . '/post_redirect_rule.php' );
require_once( TEMPLATEINC . '/shortcode.php' );
require_once( TEMPLATEINC . '/utilities.php' );

// - API
require_once( TEMPLATEINC . '/api/routing.php' );

require_once( TEMPLATEINC . '/api/controllers/objects_controller.php' );
require_once( TEMPLATEINC . '/api/controllers/blog_controller.php' );
require_once( TEMPLATEINC . '/api/controllers/store_controller.php' );
require_once( TEMPLATEINC . '/api/controllers/user_auth_flow_controller.php' );
require_once( TEMPLATEINC . '/api/controllers/user_profile_controller.php' );
require_once( TEMPLATEINC . '/api/controllers/user_favorite_objects_controller.php' );

// - Services
require_once( TEMPLATEINC . '/api/helper_functions.php' );

require_once( TEMPLATEINC . '/services/MailchimpService.php' );
require_once( TEMPLATEINC . '/services/jwt_auth.php' );
require_once( TEMPLATEINC . '/services/delete_user.php' );

// - ACF
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

require_once( TEMPLATEINC . '/creator/creator_acf_fields.php' );
require_once( TEMPLATEINC . '/creator/creator_post_type.php' );

<?php

define( 'TEMPLATEINC', TEMPLATEPATH . '/inc' );
define( 'TEMPLATEURI', get_template_directory_uri() );
define( 'DIRECT', TEMPLATEURI.'/assets/' );

define( 'ABOUT', 1211);

show_admin_bar( false );

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
require_once( TEMPLATEINC . '/email.php' );

require_once( TEMPLATEINC . '/api/routing.php' );
require_once( TEMPLATEINC . '/api/helper_functions.php' );
require_once( TEMPLATEINC . '/api/controllers/objects_controller.php' );
require_once( TEMPLATEINC . '/api/controllers/about_controller.php' );
require_once( TEMPLATEINC . '/api/controllers/creators_controller.php' );

//require_once( TEMPLATEINC . '/insta_graber.php' );

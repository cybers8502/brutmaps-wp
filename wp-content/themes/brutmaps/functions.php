<?php

define( 'TEMPLATEINC', TEMPLATEPATH . '/inc' );
define( 'TEMPLATEURI', get_template_directory_uri() );
define( 'DIRECT', TEMPLATEURI.'/assets/' );

define( 'ABOUT', 1211);

show_admin_bar( false );

require_once( TEMPLATEINC . '/object/object_acf_fields.php' );
require_once( TEMPLATEINC . '/object/object_taxonomy.php' );
require_once( TEMPLATEINC . '/object/object_post_type.php' );
require_once( TEMPLATEINC . '/associated_people/associated_post_type.php' );
require_once( TEMPLATEINC . '/associated_people/associated_acf_fields.php' );
require_once( TEMPLATEINC . '/cpt.php' );
require_once( TEMPLATEINC . '/actions.php' );
require_once( TEMPLATEINC . '/api.php' );
require_once( TEMPLATEINC . '/ajax.php' );
require_once( TEMPLATEINC . '/email.php' );
//require_once( TEMPLATEINC . '/insta_graber.php' );

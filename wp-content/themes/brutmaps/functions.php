<?php
define( 'TEMPLATEINC', TEMPLATEPATH . '/inc' );
define( 'ABOUT', 1211);
show_admin_bar( false );

@ini_set( 'upload_max_size' , '64M' );
@ini_set( 'post_max_size', '64M');
@ini_set( 'max_execution_time', '300' );

require_once( TEMPLATEINC . '/cpt.php' );
require_once( TEMPLATEINC . '/actions.php' );
require_once( TEMPLATEINC . '/api.php' );
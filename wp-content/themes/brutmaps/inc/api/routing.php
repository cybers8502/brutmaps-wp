<?php
//Custom API requests

define('BASE_URL', 'maps/data/v1/api/');
define('PLACEHOLDER', 'https://brutmaps.com/wp-content/uploads/2019/11/brutalist-architecture-7-1024x567.jpg');

require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

//New endpoints registration
add_action( 'rest_api_init', function () {

	register_rest_route( BASE_URL, '/posts', array(
		'methods' => 'GET',
		'callback' => 'API_GET_POSTS',
	) );

    register_rest_route( BASE_URL, '/posts/(?P<identifier>[\w-]+)', array(
        'methods' => 'GET',
        'callback' => 'API_GET_POST_BY_ID',
    ) );

	register_rest_route( BASE_URL, '/sights', array(
		'methods' => 'GET',
		'callback' => 'API_GET_SIGHTS',
	) );

    register_rest_route( BASE_URL, '/sights/(?P<identifier>[\w-]+)', array(
		'methods' => 'GET',
		'callback' => 'API_GET_SIGHT_BY_ID',
	) );

    register_rest_route( BASE_URL, '/products', array(
        'methods' => 'GET',
        'callback' => 'API_GET_PRODUCTS',
    ) );

	register_rest_route( BASE_URL, '/submit_sight', array(
		'methods' => 'POST',
		'callback' => 'API_POST_SIGHT',
	) );

	register_rest_route( BASE_URL, '/about', array(
		'methods' => 'GET',
		'callback' => 'API_GET_ABOUT_DATA',
	) );

    register_rest_route( BASE_URL, '/creators/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'API_GET_CREATOR_BY_ID',
    ) );

    register_rest_route( BASE_URL, '/creators/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'API_GET_MY_PROFILE_BY_ID',
    ) );

} );

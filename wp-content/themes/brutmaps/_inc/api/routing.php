<?php

// - Custom API requests
define('BASE_URL', 'maps/data/v1/api');
define('PLACEHOLDER', 'https://brutmaps.com/wp-content/uploads/2019/11/brutalist-architecture-7-1024x567.jpg');

// - New endpoints registration
add_action( 'rest_api_init', function () {

    // - Blog API
	register_rest_route( BASE_URL, '/posts', array(
		'methods' => 'GET',
		'callback' => 'API_GET_POSTS',
        'args' => array(
            'cat' => array(
                'required' => false,
                'type' => 'string',
                'description' => 'Category filter'
            ),
        ),
        'permission_callback' => '__return_true'
	) );

    register_rest_route( BASE_URL, '/posts/(?P<identifier>[\w-]+)', array(
        'methods' => 'GET',
        'callback' => 'API_GET_POST_BY_ID',
        'permission_callback' => '__return_true'
    ) );

    // - Map API
	register_rest_route( BASE_URL, '/sights', array(
		'methods' => 'GET',
		'callback' => 'API_GET_SIGHTS',
        'permission_callback' => '__return_true'
	) );

    // - Object Article API
    register_rest_route( BASE_URL, '/sights/(?P<identifier>[\w-]+)', array(
		'methods' => 'GET',
		'callback' => 'API_GET_SIGHT_BY_ID',
        'permission_callback' => '__return_true'
	) );

    // - Store API
    register_rest_route( BASE_URL, '/products', array(
        'methods' => 'GET',
        'callback' => 'API_GET_PRODUCTS',
        'permission_callback' => '__return_true'
    ) );

    // - Auth Flow API
    register_rest_route(BASE_URL, '/login', [
        'methods'  => 'POST',
        'callback' => 'API_POST_LOGIN_USER',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route(BASE_URL, '/google-login', [
        'methods'  => 'POST',
        'callback' => 'API_POST_LOGIN_GOOGLE_USER',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route(BASE_URL, '/registration', array(
        'methods' => 'POST',
        'callback' => 'API_POST_REGISTER_USER',
        'permission_callback' => '__return_true'
    ));

    register_rest_route(BASE_URL, '/google-registration', [
        'methods'  => 'POST',
        'callback' => 'API_POST_REGISTER_GOOGLE_USER',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route(BASE_URL, '/check-email', array(
        'methods' => 'POST',
        'callback' => 'API_POST_CHECK_EMAIL_EXISTENCE',
        'permission_callback' => '__return_true',
    ));

    register_rest_route(BASE_URL, '/lost-password', array(
        'methods' => 'POST',
        'callback' => 'API_POST_LOST_PASSWORD',
        'permission_callback' => '__return_true'
    ));

    register_rest_route(BASE_URL, '/reset-password', array(
        'methods' => 'POST',
        'callback' => 'API_POST_RESET_PASSWORD',
        'permission_callback' => '__return_true',
    ));

    register_rest_route(BASE_URL, '/change-password', array(
        'methods' => 'POST',
        'callback' => 'API_POST_CHANGE_PASSWORD',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route(BASE_URL, '/token/refresh', array(
        'methods' => 'POST',
        'callback' => 'API_POST_REFRESH_JWT_TOKEN',
        'permission_callback' => '__return_true',
    ));

    // - User Profile API
    register_rest_route(BASE_URL, '/user-profile', array(
        'methods' => 'GET',
        'callback' => 'API_GET_PROFILE',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route(BASE_URL, '/edit-profile', array(
        'methods' => 'POST',
        'callback' => 'API_POST_EDIT_PROFILE',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route(BASE_URL, '/user-countries', array(
        'methods' => 'GET',
        'callback' => 'API_GET_USER_COUNTRIES_LIST',
        'permission_callback' => '__return_true'
    ));

    register_rest_route(BASE_URL, '/delete-account', array(
        'methods' => 'DELETE',
        'callback' => 'API_DELETE_USER_ACCOUNT',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    // - User Favorites API
    register_rest_route(BASE_URL, '/favorites/toggle', array(
        'methods' => 'POST',
        'callback' => 'API_POST_TOGGLE_FAVORITE_SIGHT',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route(BASE_URL, '/favorites', array(
        'methods' => 'GET',
        'callback' => 'API_GET_FAVORITE_SIGHT',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));
} );

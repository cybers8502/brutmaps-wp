<?php
// Publicity API endpoints setup
add_filter( 'jwt_auth_default_whitelist', function ( $default_whitelist ) {
    // List of NON-Auth endpoints
    return array(
        '/wp-json/maps/data/v1/api/sights',
        '/wp-json/maps/data/v1/api/sights/(?P<id>\d+)',
        '/wp-json/maps/data/v1/api/submit_sight',
        '/wp-json/maps/data/v1/api/about',
        '/wp-json/maps/data/v1/api/creators/(?P<id>\d+)'
    );
} );

add_filter( 'jwt_auth_whitelist', function ( $endpoints ) {
    return array();
} );

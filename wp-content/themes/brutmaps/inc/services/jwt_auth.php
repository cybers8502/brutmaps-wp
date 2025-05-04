<?php

use Tmeister\Firebase\JWT\JWT;

function jwt_auth_generate_token($user_id) {
    if (!defined('JWT_AUTH_SECRET_KEY')) {
        return new WP_Error('jwt_config', 'JWT secret key not defined');
    }

    $issuedAt = time();
    $expire = $issuedAt + (15 * MINUTE_IN_SECONDS);

    $payload = [
        'iss'  => get_bloginfo('url'),
        'iat'  => $issuedAt,
        'nbf'  => $issuedAt,
        'exp'  => $expire,
        'data' => [
            'user' => [
                'id' => $user_id,
            ],
        ],
    ];

    return JWT::encode($payload, JWT_AUTH_SECRET_KEY, 'HS256');
}

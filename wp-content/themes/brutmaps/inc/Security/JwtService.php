<?php
namespace Brut\Security;

use Tmeister\Firebase\JWT\JWT;

class JwtService {
    public static function generate(int $user_id): \WP_Error|string
    {
        if (!defined('JWT_AUTH_SECRET_KEY')) {
            return new \WP_Error('jwt_config', 'JWT secret key not defined');
        }

        $issuedAt = time();
        $expire = $issuedAt + (15 * MINUTE_IN_SECONDS);

        $payload = [
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'nbf' => $issuedAt,
            'exp' => $expire,
            'data' => ['user' => ['id' => $user_id]],
        ];

        return JWT::encode($payload, JWT_AUTH_SECRET_KEY, 'HS256');
    }
}

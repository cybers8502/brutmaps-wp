<?php

namespace Brut\Utils;

use WP_REST_Response;
use WP_Error;

class ResponseHelper
{
    public static function success(array $data = [], string $message = 'OK', int $status = 200): WP_REST_Response
    {
        return new WP_REST_Response([
            'status' => 'success',
            'message' => $message,
            'data'   => $data,
        ], $status);
    }

    public static function error(string $message, int $status = 400, string $code = 'error'): WP_Error
    {
        return new WP_Error($code, $message, ['status' => $status]);
    }

    public static function validationError(string $message): WP_Error
    {
        return self::error($message, 422, 'validation_failed');
    }

    public static function unauthorized(string $message = 'Unauthorized'): WP_Error
    {
        return self::error($message, 401, 'unauthorized');
    }

    public static function notFound(string $message = 'Not found'): WP_Error
    {
        return self::error($message, 404, 'not_found');
    }

    public static function tooManyRequests(string $message = 'Too many requests'): WP_Error
    {
        return self::error($message, 429, 'rate_limited');
    }

    public static function serverError(string $message = 'Server error'): WP_Error
    {
        return self::error($message, 500, 'server_error');
    }
}

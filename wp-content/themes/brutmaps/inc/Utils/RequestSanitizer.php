<?php

namespace Brut\Utils;

use WP_REST_Request;

class RequestSanitizer
{

    public static function sanitizeArray(array $input): array
    {
        return array_values(array_filter($input, function ($item) {
            return !empty($item) && trim($item) !== '';
        }));
    }

    public static function sanitizeStringParam(WP_REST_Request $request, string $key): string
    {
        return sanitize_text_field($request->get_param($key));
    }

    public static function sanitizeEmailParam(WP_REST_Request $request, string $key): string
    {
        return sanitize_email($request->get_param($key));
    }

    public static function sanitizeBooleanParam(WP_REST_Request $request, string $key): bool
    {
        return filter_var($request->get_param($key), FILTER_VALIDATE_BOOLEAN);
    }

    public static function sanitizeJsonParam(WP_REST_Request $request, string $key): array
    {
        $raw = $request->get_param($key);
        return is_string($raw) ? json_decode($raw, true) ?? [] : (array) $raw;
    }

    public static function sanitizeFile(array $file): array
    {
        return [
            'name' => sanitize_file_name($file['name']),
            'type' => $file['type'],
            'tmp_name' => $file['tmp_name'],
            'size' => intval($file['size']),
        ];
    }
}

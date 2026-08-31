<?php

namespace Brut\Utils;

class RequestSanitizer
{
    public static function sanitizeArray(array $input): array
    {
        return array_values(array_filter($input, function ($item) {
            return !empty($item) && trim($item) !== '';
        }));
    }
}

<?php

namespace Brut\Utils;

class ValidatorHelper
{
    public static function isValidEmail(string $email): bool
    {
        return is_email($email);
    }

    public static function isValidImage(array $file, array $allowed_types = ['image/jpeg', 'image/png', 'image/gif']): bool
    {
        return isset($file['type']) && in_array($file['type'], $allowed_types, true);
    }

    public static function isNotEmptyString(?string $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    public static function isValidId($value): bool
    {
        return is_numeric($value) && $value > 0;
    }
}

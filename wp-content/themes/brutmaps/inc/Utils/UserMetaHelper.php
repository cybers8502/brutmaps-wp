<?php

namespace Brut\Utils;

class UserMetaHelper
{
    public static function updateMeta(int $user_id, array $fields): void
    {
        foreach ($fields as $key => $value) {
            if (!empty($value)) {
                update_user_meta($user_id, $key, $value);
            }
        }
    }

    public static function getMeta(int $user_id, array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = get_user_meta($user_id, $key, true);
        }
        return $result;
    }

    public static function deleteMeta(int $user_id, array $keys): void
    {
        foreach ($keys as $key) {
            delete_user_meta($user_id, $key);
        }
    }

    public static function getProfilePhotoUrl(int $user_id): ?string
    {
        $photo_id = get_user_meta($user_id, 'profile_photo', true);
        return $photo_id ? wp_get_attachment_url((int) $photo_id) : null;
    }
}

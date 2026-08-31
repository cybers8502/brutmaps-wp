<?php

namespace Brut\Services;

class MediaUploadService
{
    public static function handleUserPhotoUpload(string $field_name): int|\WP_Error|null
    {
        if (
            empty($_FILES[$field_name]) ||
            empty($_FILES[$field_name]['tmp_name'])
        ) {
            return null;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        return media_handle_upload($field_name, 0);
    }

    public static function deleteAttachmentIfExists(int|string $attachment_id): void
    {
        if (!empty($attachment_id)) {
            wp_delete_attachment((int) $attachment_id, true);
        }
    }

    public static function getAttachmentUrl(int $attachment_id): ?string
    {
        return wp_get_attachment_url($attachment_id) ?: null;
    }
}

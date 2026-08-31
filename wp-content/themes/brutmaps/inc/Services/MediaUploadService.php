<?php

namespace Brut\Services;

use Brut\Utils\ValidatorHelper;

class MediaUploadService
{
    /**
     * GraphQL has no native multipart upload here, so the registration flow
     * sends the photo as a base64 string instead (an optional `data:` URI
     * prefix is stripped if present). Mirrors what media_handle_upload() does
     * for a real $_FILES entry, minus the parts that assume one exists.
     */
    public static function handleBase64ImageUpload(string $base64_data, string $filename): int|\WP_Error
    {
        if (preg_match('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', $base64_data, $matches)) {
            $base64_data = substr($base64_data, strlen($matches[0]));
        }

        $decoded = base64_decode($base64_data, true);
        if ($decoded === false || $decoded === '') {
            return new \WP_Error('invalid_base64', 'Invalid base64 image data.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_buffer($finfo, $decoded) ?: '';
        finfo_close($finfo);

        if (!ValidatorHelper::isValidImage(['type' => $mime])) {
            return new \WP_Error('invalid_image_type', 'A valid image file is required.');
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $upload = wp_upload_bits(sanitize_file_name($filename), null, $decoded);
        if (!empty($upload['error'])) {
            return new \WP_Error('upload_failed', $upload['error']);
        }

        $attachment_id = wp_insert_attachment([
            'post_mime_type' => $mime,
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ], $upload['file']);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        wp_update_attachment_metadata(
            $attachment_id,
            wp_generate_attachment_metadata($attachment_id, $upload['file']),
        );

        return $attachment_id;
    }
}

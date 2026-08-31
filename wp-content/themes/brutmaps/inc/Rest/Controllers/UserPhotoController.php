<?php

namespace Brut\Rest\Controllers;

use WP_REST_Request;
use Brut\Utils\ValidatorHelper;
use Brut\Utils\ResponseHelper;
use Brut\Services\MediaUploadService;

class UserPhotoController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/user/photo', [
            'methods' => 'POST',
            'callback' => [$this, 'upload'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function upload(WP_REST_Request $request): \WP_Error|\WP_REST_Response
    {
        $photo = $request->get_file_params()['photo'] ?? null;

        if (empty($photo) || !ValidatorHelper::isValidImage($photo)) {
            return ResponseHelper::validationError('A valid image file is required');
        }

        $photo_id = MediaUploadService::handleUserPhotoUpload('photo');
        if (is_wp_error($photo_id)) {
            return ResponseHelper::serverError('Photo upload failed');
        }

        return ResponseHelper::success([
            'photo_url' => wp_get_attachment_url($photo_id),
        ]);
    }
}

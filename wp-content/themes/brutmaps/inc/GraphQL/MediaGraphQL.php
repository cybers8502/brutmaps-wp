<?php

namespace Brut\GraphQL;

use Brut\Services\MediaUploadService;
use GraphQL\Error\UserError;

/**
 * Media mutations with no auth requirement of their own.
 *
 * Currently just the photo upload used by the registration flow: register()
 * takes a photoUrl string, not a raw file, so the client uploads the photo
 * here first (as base64 — GraphQL has no native multipart upload) to get a
 * URL, then passes that to register().
 */
class MediaGraphQL
{
    public function registerTypes(): void
    {
        register_graphql_object_type('UploadUserPhotoPayload', [
            'description' => 'Result of uploading a user photo.',
            'fields'      => [
                'photoUrl' => ['type' => 'String'],
            ],
        ]);

        register_graphql_mutation('uploadUserPhoto', [
            'inputFields'         => [
                'fileBase64' => ['type' => ['non_null' => 'String']],
                'filename'   => ['type' => ['non_null' => 'String']],
            ],
            'outputFields'        => [
                'photoUrl' => ['type' => 'String'],
            ],
            'mutateAndGetPayload' => [$this, 'resolveUploadUserPhoto'],
        ]);
    }

    public function resolveUploadUserPhoto(array $input): array
    {
        $attachment_id = MediaUploadService::handleBase64ImageUpload(
            $input['fileBase64'],
            $input['filename'],
        );

        if (is_wp_error($attachment_id)) {
            throw new UserError(esc_html($attachment_id->get_error_message()));
        }

        return ['photoUrl' => wp_get_attachment_url($attachment_id)];
    }
}

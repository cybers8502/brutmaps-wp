<?php

namespace Brut\Admin\ACFFieldsManager;

use Brut\Admin\ACFFieldConfig;

class AttachmentACFFieldsManager
{
    public function boot(): void
    {
        add_action('acf/init', [$this, 'register']);
    }

    public function register(): void
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key' => 'group_attachment_fields',
            'title' => 'Image Metadata',
            'fields' => $this->getFields(),
            'location' => [
                [
                    [
                        'param' => 'attachment',
                        'operator' => '==',
                        'value' => 'image',
                    ],
                ],
            ]
        ]);
    }

    private function getFields(): array
    {
        return [
            [
                'key' => 'attached_image_author_id',
                'label' => 'Image Author',
                'name' => 'attached_image_author_id',
                'type' => 'post_object',
                'post_type' => ['authors'],
                'return_format' => 'id',
            ],
            [
                'key' => 'attached_image_author_source',
                'label' => 'Image Source',
                'name' => 'attached_image_author_source',
                'type' => 'url',
            ],
        ];
    }
}

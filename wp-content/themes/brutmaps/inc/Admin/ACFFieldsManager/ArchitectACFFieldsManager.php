<?php

namespace Brut\Admin\ACFFieldsManager;

class ArchitectACFFieldsManager
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
            'key' => 'architect',
            'title' => 'Creator',
            'fields' => $this->getFields(),
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'architect',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ]);
    }

    private function getFields(): array
    {
        return [
            [
                'key' => 'field_architect_message',
                'label' => 'Fill Rules!',
                'name' => 'message_field',
                'type' => 'message',
                'message' => "Filling out these fields is MANDATORY! 
You must fill out at least one of these combinations:
1. Instagram
OR
2. First Name + Link
<strong>PREFERABLY: fill out all the fields</strong>",
            ],
            [
                'key' => 'field_architect_first_name',
                'label' => 'First name',
                'name' => 'first_name',
                'type' => 'text',
                'required' => 1,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_architect_last_name',
                'label' => 'Last name',
                'name' => 'last_name',
                'type' => 'text',
                'required' => 1,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_architect_wiki_link',
                'label' => 'Wiki Link',
                'name' => 'wiki_link',
                'type' => 'text',
                'required' => 0,
            ],
            [
                'key' => 'field_architect_main_image',
                'label' => 'Main Image',
                'name' => 'main_image',
                'type' => 'image',
                'required' => 1,
                'return_format' => 'array',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_architect_main_image_author_id',
                'label' => 'Image Author',
                'name' => 'main_image_author_id',
                'type' => 'post_object',
                'required' => 1,
                'post_type' => ['authors'],
                'return_format' => 'id',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_architect_description',
                'label' => 'Description',
                'name' => 'description',
                'type' => 'wysiwyg',
                'required' => 0,
            ],
        ];
    }
}

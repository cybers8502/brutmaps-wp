<?php

namespace Brut\Admin\ACFFieldsManager;

class SightsACFFieldsManager
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
            'key' => 'object',
            'title' => 'Object',
            'fields' => $this->getFields(),
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'sight',
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
            $this->tab('field_object_general', 'General'),
            $this->googleMap('field_object_location', 'Location coordinates', 'location', true),
            $this->wysiwyg('field_object_main_content', 'Main Content', 'main_content'),
            $this->number('field_object_established', 'Established', 'established', '1975'),
            $this->postObject('field_object_choose_architects', 'Choose architects', 'choose_architects', 'architect', true, '50'),
            $this->postObject('field_object_choose_associated_people', 'Choose associated people', 'choose_associated_people', 'architect', true, '50'),
            $this->wysiwyg('field_object_source', 'Source', 'source'),
            $this->postObject('field_object_contributor', 'Contributor', 'contributor', 'contributor'),

            $this->tab('field_object_media', 'Media'),
            $this->image('field_object_main_image', 'Main Image', 'main_image', true, '40'),
            $this->postObject('field_object_main_image_author_id', 'Main Image Author', 'main_image_author_id', 'authors', false, '60', 'Search by instagram name'),
            $this->text('field_object_image_author_text', 'Author', 'main_image_author_name'),

            $this->repeater('field_object_gallery', 'Gallery', 'gallery', [
                $this->image('gallery_image', 'Gallery image', 'gallery_image', true),
                $this->postObject('gallery_image_author_id', 'Gallery author', 'gallery_image_author_id', 'authors', false),
                $this->text('gallery_image_author', 'Author', 'gallery_image_author'),
            ]),

            $this->tab('field_object_additional', 'Additional'),
            $this->textarea('field_object_working_hours', 'Working hours', 'working_hours'),
            $this->text('field_object_phone', 'Phone', 'phone'),
            $this->text('field_object_website', 'Website', 'website'),
        ];
    }

    private function tab(string $key, string $label): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $label,
            'type' => 'tab',
            'placement' => 'left',
        ];
    }

    private function googleMap(string $key, string $label, string $name, bool $required = false): array
    {
        return compact('key', 'label', 'name') + [
                'type' => 'google_map',
                'required' => $required ? 1 : 0,
            ];
    }

    private function wysiwyg(string $key, string $label, string $name): array
    {
        return compact('key', 'label', 'name') + [
                'type' => 'wysiwyg',
                'required' => 0,
            ];
    }

    private function number(string $key, string $label, string $name, string $placeholder = ''): array
    {
        return compact('key', 'label', 'name') + [
                'type' => 'number',
                'required' => 0,
                'placeholder' => $placeholder,
            ];
    }

    private function postObject(
        string $key,
        string $label,
        string $name,
        string $postType,
        bool   $multiple = false,
        string $width = '',
        string $instructions = ''
    ): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'post_object',
            'post_type' => [$postType],
            'return_format' => 'id',
            'required' => 0,
            'multiple' => $multiple,
            'instructions' => $instructions,
            'wrapper' => ['width' => $width],
        ];
    }

    private function image(string $key, string $label, string $name, bool $required = false, string $width = ''): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'image',
            'return_format' => 'array',
            'required' => $required ? 1 : 0,
            'wrapper' => ['width' => $width],
        ];
    }

    private function text(string $key, string $label, string $name): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'text',
            'required' => 0,
        ];
    }

    private function textarea(string $key, string $label, string $name): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'textarea',
            'required' => 0,
        ];
    }

    private function repeater(string $key, string $label, string $name, array $subFields): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'repeater',
            'required' => 0,
            'sub_fields' => $subFields,
        ];
    }
}

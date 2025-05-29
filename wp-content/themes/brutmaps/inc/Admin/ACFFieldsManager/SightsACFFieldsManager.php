<?php

namespace Brut\Admin\ACFFieldsManager;

use Brut\Admin\ACFFieldConfig;

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
            (new ACFFieldConfig())->googleMap('field_object_location', 'Location coordinates', 'location', true),
            (new ACFFieldConfig())->wysiwyg('field_object_main_content', 'Main Content', 'main_content'),
            (new ACFFieldConfig())->number('field_object_established', 'Established', 'established', '1975'),
            (new ACFFieldConfig())->postObject('field_object_choose_architects', 'Choose architects', 'choose_architects', 'architect', true, '50'),
            (new ACFFieldConfig())->postObject('field_object_choose_associated_people', 'Choose associated people', 'choose_associated_people', 'architect', true, '50'),
            (new ACFFieldConfig())->wysiwyg('field_object_source', 'Source', 'source'),
            (new ACFFieldConfig())->postObject('field_object_contributor', 'Contributor', 'contributor', 'contributor'),

            (new ACFFieldConfig())->image('field_object_main_image', 'Main Image', 'main_image', true, '40'),
            (new ACFFieldConfig())->postObject('field_object_main_image_author_id', 'Main Image Author', 'main_image_author_id', 'authors', false, '60', 'Search by instagram name'),
            (new ACFFieldConfig())->text('field_object_image_author_text', 'Author', 'main_image_author_name'),

            (new ACFFieldConfig())->repeater('field_object_gallery', 'Gallery', 'gallery', [
                (new ACFFieldConfig())->image('gallery_image', 'Gallery image', 'gallery_image', true),
                (new ACFFieldConfig())->postObject('gallery_image_author_id', 'Gallery author', 'gallery_image_author_id', 'authors', false),
                (new ACFFieldConfig())->text('gallery_image_author', 'Author', 'gallery_image_author'),
            ]),
        ];
    }
}

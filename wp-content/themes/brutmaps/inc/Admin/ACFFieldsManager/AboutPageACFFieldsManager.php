<?php

namespace Brut\Admin\ACFFieldsManager;

class AboutPageACFFieldsManager
{
    public function boot(): void
    {
        add_action('acf/init', [$this, 'registerOptionsPage']);
        add_action('acf/init', [$this, 'registerFieldGroup']);
    }

    public function registerOptionsPage(): void
    {
        if (!function_exists('acf_add_options_page')) {
            return;
        }

        acf_add_options_page([
            'page_title' => __('About Page', 'brut'),
            'menu_title' => __('About Page', 'brut'),
            'menu_slug'  => 'about-page',
            'capability' => 'edit_posts',
            'redirect'   => false,
        ]);
    }

    public function registerFieldGroup(): void
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key'                   => 'about_page',
            'title'                 => 'About Page',
            'fields'                => $this->getFields(),
            'location'              => [
                [
                    [
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'about-page',
                    ],
                ],
            ],
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
        ]);
    }

    private function getFields(): array
    {
        return [
            [
                'key'      => 'field_about_founderName',
                'label'    => 'Founder name',
                'name'     => 'founderName',
                'type'     => 'text',
                'required' => 0,
            ],
            [
                'key'      => 'field_about_founderRole',
                'label'    => 'Founder role',
                'name'     => 'founderRole',
                'type'     => 'text',
                'required' => 0,
            ],
            [
                'key'           => 'field_about_portrait',
                'label'         => 'Portrait',
                'name'          => 'portrait',
                'type'          => 'image',
                'required'      => 0,
                'return_format' => 'array',
            ],
            [
                'key'      => 'field_about_body',
                'label'    => 'Body',
                'name'     => 'body',
                'type'     => 'wysiwyg',
                'required' => 0,
            ],
            [
                'key'          => 'field_about_statLaunchYear',
                'label'        => 'Launch year',
                'name'         => 'statLaunchYear',
                'type'         => 'number',
                'required'     => 0,
                'instructions' => 'Buildings/countries/architects counts are computed live and not editable here.',
            ],
        ];
    }
}

<?php

namespace Brut\Admin\ACFFieldsManager;

class AuthorsACFFieldsManager
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
            'key' => 'authors',
            'title' => 'Author',
            'fields' => $this->getFields(),
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'authors',
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
                'key' => 'field_author_first_name',
                'label' => 'First name',
                'name' => 'first_name_author',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_author_second_name',
                'label' => 'Last name',
                'name' => 'second_name_author',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_author_instagram',
                'label' => 'Nickname',
                'name' => 'instagram',
                'type' => 'text',
            ],
            [
                'key' => 'field_author_link',
                'label' => 'Link',
                'name' => 'link',
                'type' => 'text',
            ],
        ];
    }
}

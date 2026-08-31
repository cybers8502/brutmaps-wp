<?php

namespace Brut\Admin\ACFFieldsManager;

class ContributorACFFieldsManager
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
            'key' => 'contributor',
            'title' => 'Contributor',
            'fields' => $this->getFields(),
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'contributor',
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
                'key' => 'field_contributor_first_name',
                'label' => 'First name',
                'name' => 'first_name',
                'type' => 'text',
                'required' => 0,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_contributor_last_name',
                'label' => 'Last name',
                'name' => 'last_name',
                'type' => 'text',
                'required' => 0,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_contributor_email',
                'label' => 'E-mail',
                'name' => 'email',
                'type' => 'email',
                'required' => 0,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_contributor_link',
                'label' => 'Link',
                'name' => 'link',
                'type' => 'url',
                'required' => 0,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_contributor_linked_sights',
                'label' => 'Linked objects',
                'name' => 'linked_sights',
                'type' => 'post_object',
                'required' => 0,
                'post_type' => ['sight'],
                'return_format' => 'id',
                'multiple' => true,
            ],
        ];
    }
}

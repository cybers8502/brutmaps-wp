<?php

namespace Brut\Admin\ACFFieldsManager;

class ThemeSetupOptionsACFFieldsManager
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
            'page_title' => __('Setup'),
            'menu_title' => __('Setup'),
            'menu_slug' => 'theme-setup',
            'capability' => 'edit_posts',
            'redirect' => false,
        ]);
    }

    public function registerFieldGroup(): void
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key' => 'setup',
            'title' => 'Setup',
            'fields' => $this->getFields(),
            'location' => [
                [
                    [
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'theme-setup',
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
                'key' => 'field_setup_stripe',
                'label' => 'Stripe link',
                'name' => 'stripe',
                'type' => 'url',
                'required' => 0,
            ],
            [
                'key' => 'field_setup_instagram',
                'label' => 'Instagram',
                'name' => 'instagram',
                'type' => 'text',
                'required' => 0,
            ],
            [
                'key' => 'field_setup_facebook',
                'label' => 'Facebook',
                'name' => 'facebook',
                'type' => 'text',
                'required' => 0,
            ],
            [
                'key' => 'field_setup_notifiedUsers',
                'label' => 'E-mails',
                'name' => 'notifiedUsers',
                'type' => 'repeater',
                'sub_fields' => [
                    [
                        'key' => 'email',
                        'label' => 'E-mail',
                        'name' => 'email',
                        'type' => 'text',
                        'required' => 1,
                    ],
                ],
            ],
        ];
    }
}

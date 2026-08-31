<?php

namespace Brut\Admin\ACFFieldsManager;

class ProductACFFieldsManager
{
    public function boot(): void
    {
        add_action('acf/init', [$this, 'registerStripeFieldGroup']);
    }

    public function registerStripeFieldGroup(): void
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key' => 'product_stripe_fields',
            'title' => 'Product Stripe Fields',
            'fields' => [$this->getStripeField()],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'product',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
        ]);
    }

    private function getStripeField(): array
    {
        return [
            'key' => 'product_stripe_link',
            'label' => 'Stripe',
            'name' => 'stripe',
            'type' => 'url',
            'required' => 0,
        ];
    }
}

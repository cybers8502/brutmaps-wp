<?php

if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array (
        'key' => 'product_stripe_fields',
        'title' => 'Product Stripe Fields',
        'fields' => get_product_acf_fields(),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'product', // Изменено на 'product' для WooCommerce продуктов
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
    ));
    add_action('acf/init', 'acf_add_local_field_group');
endif;

function get_product_acf_fields() {
    return array(
        get_stripe_field(),
    );
}

function get_stripe_field() {
    return array (
        'key' => 'product_stripe_link',
        'label' => 'Stripe',
        'name' => 'stripe',
        'type' => 'url',
        'required' => 0
    );
}

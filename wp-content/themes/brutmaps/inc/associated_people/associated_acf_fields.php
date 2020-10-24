<?php

if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array (
        'key' => 'associated',
        'title' => 'Associated',
        'fields' => get_associated_acf_fields(),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'associated_people',
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

function get_associated_acf_fields() {
    return array(
        get_associated_first_name_field(),
        get_associated_last_name_field(),
        get_associated_link_field(),
        get_associated_image_field()
    );
}

function get_associated_first_name_field() {
    return array (
        'key' => 'first_name',
        'label' => 'First name',
        'name' => 'First name',
        'type' => 'text',
        'required' => 1,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_associated_last_name_field() {
    return array (
        'key' => 'last_name',
        'label' => 'Last name',
        'name' => 'Last name',
        'type' => 'text',
        'required' => 1,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_associated_link_field() {
    return array (
        'key' => 'link',
        'label' => 'Link',
        'name' => 'Link',
        'type' => 'text',
        'required' => 0,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_associated_image_field() {
    return array (
        'key' => 'image',
        'label' => 'Avatar',
        'name' => 'Avatar',
        'type' => 'image',
        'required' => 0,
        'return_format' => 'array',
        'wrapper' => array (
            'width' => '50',
        )
    );
}

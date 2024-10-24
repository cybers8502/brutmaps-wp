<?php

if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array (
        'key' => 'authors',
        'title' => 'Author',
        'fields' => get_author_acf_fields(),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'authors',
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

function get_author_acf_fields() {
    return array(
        get_author_first_name_field(),
        get_author_last_name_field(),
        get_author_link_field(),
        get_author_instagram_field()
    );
}

function get_author_first_name_field() {
    return array (
        'key' => 'field_author_first_name',
        'label' => 'First name',
        'name' => 'first_name_author',
        'type' => 'text',
        'required' => 0,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_author_last_name_field() {
    return array (
        'key' => 'field_author_second_name',
        'label' => 'Last name',
        'name' => 'second_name_author',
        'type' => 'text',
        'required' => 0,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_author_instagram_field() {
    return array (
        'key' => 'field_author_instagram',
        'label' => 'Instagram',
        'name' => 'instagram',
        'type' => 'text',
        'required' => 0
    );
}

function get_author_link_field() {
    return array (
        'key' => 'field_author_link',
        'label' => 'Link',
        'name' => 'link',
        'type' => 'text',
        'required' => 0
    );
}

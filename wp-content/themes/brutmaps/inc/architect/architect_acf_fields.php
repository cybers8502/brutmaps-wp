<?php

if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array (
        'key' => 'architect',
        'title' => 'Creator',
        'fields' => get_architect_acf_fields(),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'architect',
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

function get_architect_acf_fields() {
    return array(
        get_architect_warning_message_field(),
        get_architect_first_name_field(),
        get_architect_last_name_field(),
        get_architect_link_field(),
        get_architect_image_field(),
        get_architect_description_field()
    );
}

function get_architect_warning_message_field() {

    $instructions = "Filling out these fields is MANDATORY! 
You must fill out at least one of these combinations:
1. Instagram
OR
2. First Name + Link
<strong>PREFERABLY: fill out all the fields</strong>";

    return array (
        'key' => 'field_architect_message',
        'label' => 'First name',
        'name' => 'first_name',
        'type' => 'message',
        'required' => 1,
        'instructions' => $instructions
    );
}

function get_architect_first_name_field() {

    return array (
        'key' => 'field_architect_first_name',
        'label' => 'First name',
        'name' => 'first_name',
        'type' => 'text',
        'required' => 1,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_architect_last_name_field() {
    return array (
        'key' => 'field_architect_last_name',
        'label' => 'Last name',
        'name' => 'last_name',
        'type' => 'text',
        'required' => 1,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_architect_link_field() {
    return array (
        'key' => 'field_architect_wiki_link',
        'label' => 'Wiki Link',
        'name' => 'wiki_link',
        'type' => 'text',
        'required' => 0,
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_architect_image_field() {
    return array (
        'key' => 'field_architect_main_image',
        'label' => 'Main Image',
        'name' => 'main_image',
        'type' => 'image',
        'required' => 1,
        'return_format' => 'url',
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_architect_description_field() {
    return array (
        'key' => 'field_architect_description',
        'label' => 'Description Part 1',
        'name' => 'description_1',
        'type' => 'wysiwyg',
        'required' => 0
    );
}

<?php

if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array (
        'key' => 'object_1',
        'title' => 'Object',
        'fields' => get_sight_acf_fields(),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'sight',
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
    add_action('acf/init', 'acf_add_local_field_group', 1);

endif;

function get_sight_acf_fields() {
    return array(
        get_title_field(),
        get_established_field(),
        get_architects_field(),
        get_main_image_field(),
        get_main_image_author_field(),
        get_main_content_field(),
        get_gallery_repeater_field(),
        get_source_field(),
        get_contributor_field()
    );
}

function get_title_field() {
    return array (
        'key' => 'location_2',
        'label' => 'Location coordinates',
        'name' => 'location_coordinates',
        'type' => 'google_map',
        'required' => 1
    );
}

function get_established_field() {
    return array (
        'key' => 'established_2',
        'label' => 'Established',
        'name' => 'Established',
        'type' => 'number',
        'placeholder' => '1975',
        'required' => 1
    );
}

function get_architects_field() {
    return array (
        'key' => 'choose_architects_2',
        'label' => 'Choose architects',
        'name' => 'Choose architects',
        'type' => 'post_object',
        'instructions' => 'Leave this field empty if architect is unknown',
        'required' => 0,
        'return_format' => 'id',
        'multiple' => 1,
        'post_type' => 'architect'
    );
}

function get_main_image_field() {
    return array (
        'key' => 'main_image_2',
        'label' => 'Main Image',
        'name' => 'Main Image',
        'type' => 'image',
        'required' => 1,
        'return_format' => 'array',
    );
}

function get_main_image_author_field() {
    return array (
        'key' => 'main_image_author_id_2',
        'label' => 'Main Image Author',
        'name' => 'Main Image Author',
        'type' => 'post_object',
        'required' => 1,
        'return_format' => 'id',
        'multiple' => 0,
        'post_type' => 'authors'
    );
}

function get_main_content_field() {
    return array (
        'key' => 'main_content_2',
        'label' => 'Main Content',
        'name' => 'Main Content',
        'type' => 'wysiwyg',
        'required' => 0
    );
}

function get_gallery_repeater_field() {

    return array (
        'key' => 'gallery_2',
        'label' => 'Gallery',
        'name' => 'Gallery',
        'type' => 'repeater',
        'required' => 0,
        'sub_fields' => array(
            array (
                'key' => 'gallery_image',
                'label' => 'Gallery image',
                'name' => 'Gallery image',
                'type' => 'image',
                'required' => 1,
                'return_format' => 'array',
                'parent' => 'gallery_2'
            ),
            array (
                'key' => 'gallery_image_author_id',
                'label' => 'Gallery author',
                'name' => 'Contributor',
                'type' => 'post_object',
                'required' => 1,
                'return_format' => 'id',
                'multiple' => 0,
                'post_type' => 'authors'
            )
        )
    );
}

function get_source_field() {
    return array (
        'key' => 'source_2',
        'label' => 'Source',
        'name' => 'Source',
        'type' => 'wysiwyg',
        'required' => 0
    );
}

function get_contributor_field() {
    return array (
        'key' => 'contributor_2',
        'label' => 'Contributor',
        'name' => 'Contributor',
        'type' => 'post_object',
        'required' => 0,
        'return_format' => 'id',
        'multiple' => 0,
        'post_type' => 'contributor'
    );
}

<?php

if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array (
        'key' => 'object',
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
    add_action('acf/init', 'acf_add_local_field_group');
endif;

function get_sight_acf_fields() {
    return array(
        get_tab_field('field_object_general','General'),
        get_title_field(),
        get_main_content_field(),
        get_established_field(),
        get_architects_field(),
        get_associated_people_field(),
        get_source_field(),
        get_contributor_field(),
        get_tab_field('field_object_media','Media'),
        get_main_image_field(),
        get_main_image_author_select_field(),
        get_main_image_author_field(),
        get_gallery_repeater_field(),
        get_tab_field('field_object_additional','Additional'),
        get_working_hours_field(),
        get_phone_field(),
        get_website_field()
    );
}

function get_tab_field($key, $field) {
    return array (
        'key' => $key,
        'label' => $field,
        'name' => $field,
        'type' => 'tab',
        'placement' => 'left'
    );
}

function get_title_field() {
    return array (
        'key' => 'field_object_location',
        'label' => 'Location coordinates',
        'name' => 'location',
        'type' => 'google_map',
        'required' => 1
    );
}

function get_established_field() {
    return array (
        'key' => 'field_object_established',
        'label' => 'Established',
        'name' => 'established',
        'type' => 'number',
        'placeholder' => '1975',
        'required' => 0
    );
}

function get_architects_field() {
    return array (
        'key' => 'field_object_choose_architects',
        'label' => 'Choose architects',
        'name' => 'choose_architects',
        'type' => 'post_object',
        'instructions' => 'Leave this field empty if architect is unknown',
        'required' => 0,
        'return_format' => 'id',
        'multiple' => 1,
        'post_type' => 'architect',
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_associated_people_field() {
    return array (
        'key' => 'field_object_choose_associated_people',
        'label' => 'Choose associated people',
        'name' => 'choose_associated_people',
        'type' => 'post_object',
        'instructions' => 'Leave this field empty if associated people are unknown',
        'required' => 0,
        'return_format' => 'id',
        'multiple' => 1,
        'post_type' => 'architect',
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_main_image_field() {
    return array (
        'key' => 'field_object_main_image',
        'label' => 'Main Image',
        'name' => 'main_image',
        'type' => 'image',
        'required' => 1,
        'return_format' => 'array',
        'wrapper' => array (
            'width' => '40',
        )
    );
}

function get_main_image_author_select_field() {
    return array (
        'key' => 'field_object_main_image_author_id',
        'label' => 'Main Image Author',
        'name' => 'main_image_author_id',
        'instructions' => 'Search by instagram name',
        'type' => 'post_object',
        'required' => 1,
        'return_format' => 'id',
        'multiple' => 0,
        'post_type' => 'authors',
        'wrapper' => array (
            'width' => '60',
        )
    );
}

function get_main_image_author_field() {
    return array (
        'key' => 'field_object_image_author_text',
        'label' => 'Author',
        'name' => 'main_image_author_name',
        'type' => 'text',
        'required' => 0
    );
}

function get_source_field() {
    return array (
        'key' => 'field_object_source',
        'label' => 'Source',
        'name' => 'source',
        'type' => 'wysiwyg',
        'required' => 0
    );
}

function get_contributor_field() {
    return array (
        'key' => 'field_object_contributor',
        'label' => 'Contributor',
        'name' => 'contributor',
        'type' => 'post_object',
        'required' => 0,
        'return_format' => 'id',
        'multiple' => 0,
        'post_type' => 'contributor'
    );
}

function get_main_content_field() {
    return array (
        'key' => 'field_object_main_content',
        'label' => 'Main Content',
        'name' => 'main_content',
        'type' => 'wysiwyg',
        'required' => 0
    );
}

function get_gallery_repeater_field() {
    return array (
        'key' => 'field_object_gallery',
        'label' => 'Gallery',
        'name' => 'gallery',
        'type' => 'repeater',
        'required' => 0,
        'sub_fields' => array(
            array (
                'key' => 'gallery_image',
                'label' => 'Gallery image',
                'name' => 'gallery_image',
                'type' => 'image',
                'required' => 1,
                'return_format' => 'array',
                'parent' => 'gallery_2'
            ),
            array (
                'key' => 'gallery_image_author_id',
                'label' => 'Gallery author',
                'name' => 'gallery_image_author_id',
                'type' => 'post_object',
                'required' => 1,
                'return_format' => 'id',
                'multiple' => 0,
                'post_type' => 'authors'
            ),
            array (
                'key' => 'gallery_image_author',
                'label' => 'Author',
                'name' => 'gallery_image_author',
                'type' => 'text',
                'required' => 0,
            )
        )
    );
}

function get_working_hours_field() {
    return array (
        'key' => 'field_object_working_hours',
        'label' => 'Working hours',
        'name' => 'working_hours',
        'type' => 'textarea',
        'required' => 0
    );
}

function get_phone_field() {
    return array (
        'key' => 'field_object_phone',
        'label' => 'Phone',
        'name' => 'phone',
        'type' => 'text',
        'required' => 0
    );
}

function get_website_field() {
    return array (
        'key' => 'field_object_website',
        'label' => 'Website',
        'name' => 'website',
        'type' => 'text',
        'required' => 0
    );
}

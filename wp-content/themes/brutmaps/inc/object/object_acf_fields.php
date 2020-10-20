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
    add_action('acf/init', 'acf_add_local_field_group');
endif;

function get_sight_acf_fields() {
    return array(
        get_tab_field('general','General'),
        get_title_field(),
        get_main_content_field(),
        get_established_field(),
        get_architects_field(),
        get_source_field(),
        get_contributor_field(),
        get_tab_field('media','Media'),
        get_main_image_field(),
        get_main_image_author_field(),
        get_gallery_repeater_field(),
        get_tab_field('additional','Additional'),
        get_working_hours_field(),
        get_phone_field(),
        get_google_review_rating_field()
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
        'required' => 0,
        'wrapper' => array (
            'width' => '20',
        )
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
        'post_type' => 'architect',
        'wrapper' => array (
            'width' => '80',
        )
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
        'wrapper' => array (
            'width' => '50',
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

function get_main_image_author_field() {
    return array (
        'key' => 'main_image_author_id_2',
        'label' => 'Main Image Author',
        'name' => 'Main Image Author',
        'type' => 'post_object',
        'required' => 1,
        'return_format' => 'id',
        'multiple' => 0,
        'post_type' => 'authors',
        'wrapper' => array (
            'width' => '50',
        )
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

function get_working_hours_field() {
    return array (
        'key' => 'working_hours',
        'label' => 'Working hours',
        'name' => 'Working hours',
        'type' => 'textarea',
        'required' => 0
    );
}

function get_phone_field() {
    return array (
        'key' => 'phone',
        'label' => 'Phone',
        'name' => 'Phone',
        'type' => 'text',
        'required' => 0
    );
}

function get_google_review_rating_field() {
    return array (
        'key' => 'google_review_rating',
        'label' => 'Google Review Rating',
        'name' => 'Rating',
        'type' => 'number',
        'required' => 0
    );
}

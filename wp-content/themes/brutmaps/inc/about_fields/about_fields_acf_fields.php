<?php

if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array (
        'key' => 'about',
        'title' => 'About',
        'fields' => get_about_acf_fields(),
        'location' => array (
            array (
                array (
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'theme-about',
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

function get_about_acf_fields() {
    return array(
        get_about_title_field(),
        get_about_main_image_field(),
        get_about_main_image_author_field(),
        get_about_description_1_field(),
        get_about_gallery_repeater_field(),
        get_about_sub_text_field(),
        get_about_description_2_field()
    );
}

function get_about_gallery_repeater_field() {

    return array (
        'key' => 'about_gallery',
        'label' => 'Gallery',
        'name' => 'about_gallery',
        'type' => 'repeater',
        'required' => 0,
        'sub_fields' => array(
            array (
                'key' => 'gallery_image',
                'label' => 'Gallery image',
                'name' => 'gallery_image',
                'type' => 'image',
                'required' => 1,
                'return_format' => 'id',
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
            )
        )
    );
}

function get_about_title_field() {
    return array (
        'key' => 'about_title',
        'label' => 'About Title',
        'name' => 'about_title',
        'type' => 'text',
        'required' => 1
    );
}

function get_about_main_image_field() {
    return array (
        'key' => 'about_main_image',
        'label' => 'Main Image',
        'name' => 'about_main_image',
        'type' => 'image',
        'required' => 1,
        'return_format' => 'url',
        'wrapper' => array (
            'width' => '50',
        )
    );
}

function get_about_main_image_author_field() {
    return array (
        'key' => 'about_main_image_author',
        'label' => 'Main Image Author',
        'name' => 'about_main_image_author',
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

function get_about_description_1_field() {
    return array (
        'key' => 'about_description_1',
        'label' => 'Description Part 1',
        'name' => 'about_description_1',
        'type' => 'wysiwyg',
        'required' => 0
    );
}

function get_about_description_2_field() {
    return array (
        'key' => 'about_description_2',
        'label' => 'Description Part 2',
        'name' => 'about_description_2',
        'type' => 'wysiwyg',
        'required' => 0
    );
}

function get_about_sub_text_field() {
    return array (
        'key' => 'about_gallery_sub_text',
        'label' => 'Gallery subtext',
        'name' => 'about_gallery_sub_text',
        'type' => 'text',
        'required' => 0
    );
}

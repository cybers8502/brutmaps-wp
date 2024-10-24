<?php

function object_post_type() {

    $labels = array(
        'name' => 'Object',
        'singular_name' => 'Object',
        'menu_name' => 'Objects',
        'all_items' => 'Objects',
        'view_item' => 'Object',
        'add_new_item' => 'Add Object',
        'add_new' => 'New Object',
        'edit_item' => 'Edit',
        'update_item' => 'Update',
        'search_items' => 'Search'
    );

    $args = array(
        'labels' => $labels,
        'supports' => array('title'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_admin_bar' => true,
        'can_export' => true,
        'has_archive' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
        'rewrite' => array(
            'with_front' => true
        )
    );

    register_post_type('sight', $args);

}

add_action('init', 'object_post_type');

<?php

function custom_post_type() {

    $labels = array(
        'name' => 'Sight',
        'singular_name' => 'Sight',
        'menu_name' => 'Sight',
        'all_items' => 'Sight',
        'view_item' => 'Sight',
        'add_new_item' => 'Add Showplace',
        'add_new' => 'New Showplace',
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

    $labels = array(
        'name' => 'Authors',
        'singular_name' => 'Authors',
        'menu_name' => 'Authors',
        'all_items' => 'Authors',
        'view_item' => 'Authors',
        'add_new_item' => 'Add Author',
        'add_new' => 'New Author',
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

    register_post_type('authors', $args);
}

add_action('init', 'custom_post_type');

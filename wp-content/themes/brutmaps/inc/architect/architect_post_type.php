<?php

function architect_post_type() {

    // Creator (Old name "Architect")

    $labels1 = array(
        'name' => 'Creator',
        'singular_name' => 'Creator',
        'menu_name' => 'Creators',
        'all_items' => 'Creators',
        'view_item' => 'Creator',
        'add_new_item' => 'Add Creator',
        'add_new' => 'New Creator',
        'edit_item' => 'Edit',
        'update_item' => 'Update',
        'search_items' => 'Search'
    );

    $args1 = array(
        'labels' => $labels1,
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

    register_post_type('architect', $args1);

}

add_action('init', 'architect_post_type');

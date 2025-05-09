<?php

function contributor_post_type() {

    $labels1 = array(
        'name' => 'Contributor',
        'singular_name' => 'Contributor',
        'menu_name' => 'Contributors',
        'all_items' => 'Contributors',
        'view_item' => 'Contributor',
        'add_new_item' => 'Add Contributor',
        'add_new' => 'New Contributor',
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

    register_post_type('contributor', $args1);

}

add_action('init', 'contributor_post_type');

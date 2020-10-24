<?php

function associated_post_type() {

    $labels1 = array(
        'name' => 'Associated people',
        'singular_name' => 'Associated',
        'menu_name' => 'Associated people',
        'all_items' => 'Associated',
        'view_item' => 'Associated',
        'add_new_item' => 'Add associated',
        'add_new' => 'New Associated',
        'edit_item' => 'Edit',
        'update_item' => 'Update',
        'search_items' => 'Search'
    );

    $args2 = array(
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

    register_post_type('associated_people', $args2);

}

add_action('init', 'associated_post_type');

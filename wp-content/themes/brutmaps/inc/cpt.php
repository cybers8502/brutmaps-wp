<?php

function custom_post_type() {

	$labels1 = array(
		'name' => 'Architect',
		'singular_name' => 'Architect',
		'menu_name' => 'Architects',
		'all_items' => 'Architects',
		'view_item' => 'Architect',
		'add_new_item' => 'Add Architect',
		'add_new' => 'New Architect',
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

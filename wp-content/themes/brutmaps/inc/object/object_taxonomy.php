<?php
add_action( 'init', 'object_taxonomy' );

function object_taxonomy() {
    register_taxonomy( 'taxonomy', [ 'sight' ], [
        'label'                 => '',
        'labels'                => [
            'name'              => 'Object Category',
            'singular_name'     => 'Category',
            'search_items'      => 'Search Categories',
            'all_items'         => 'All Categories',
            'view_item '        => 'View Category',
            'parent_item'       => 'Parent Category',
            'parent_item_colon' => 'Parent Category:',
            'edit_item'         => 'Edit Category',
            'update_item'       => 'Update Category',
            'add_new_item'      => 'Add New Category',
            'new_item_name'     => 'New Category Name',
            'menu_name'         => 'Category',
        ],
        'description'           => '',
        'public'                => true,
        'hierarchical'          => true,
        'rewrite'               => true,
        'capabilities'          => array(),
        'meta_box_cb'           => null,
        'show_admin_column'     => false,
        'show_in_rest'          => true,
        'rest_base'             => null
    ] );
}

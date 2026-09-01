<?php

namespace Brut\Admin\PostTypes;

class SightsPostTypeRegistrar
{
    public function boot(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerTaxonomy']);
        add_action('init', [$this, 'registerCountryTaxonomy']);
    }

    public function registerPostType(): void
    {
        register_post_type('sight', [
            'labels' => [
                'name'               => 'Objects',
                'singular_name'      => 'Object',
                'menu_name'          => 'Objects',
                'all_items'          => 'All Objects',
                'view_item'          => 'View Object',
                'add_new_item'       => 'Add Object',
                'add_new'            => 'New Object',
                'edit_item'          => 'Edit Object',
                'update_item'        => 'Update Object',
                'search_items'       => 'Search Objects',
            ],
            'supports'            => ['title', 'editor', 'thumbnail'],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_admin_bar'   => true,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'show_in_rest'        => true,
            'capability_type'     => 'post',
            'rewrite'             => ['with_front' => true],
        ]);
    }

    public function registerTaxonomy(): void
    {
        register_taxonomy('taxonomy', ['sight'], [
            'labels' => [
                'name'                       => 'Object Category',
                'singular_name'              => 'Category',
                'search_items'               => 'Search Categories',
                'all_items'                  => 'All Categories',
                'view_item'                  => 'View Category',
                'parent_item'                => 'Parent Category',
                'parent_item_colon'          => 'Parent Category:',
                'edit_item'                  => 'Edit Category',
                'update_item'                => 'Update Category',
                'add_new_item'               => 'Add New Category',
                'new_item_name'              => 'New Category Name',
                'menu_name'                  => 'Category',
            ],
            'hierarchical'          => true,
            'public'                => true,
            'show_in_rest'          => true,
            'rewrite'               => true,
            'show_admin_column'     => false,
        ]);
    }

    public function registerCountryTaxonomy(): void
    {
        register_taxonomy('country', ['sight'], [
            'labels' => [
                'name'          => 'Countries',
                'singular_name' => 'Country',
                'search_items'  => 'Search Countries',
                'all_items'     => 'All Countries',
                'edit_item'     => 'Edit Country',
                'update_item'   => 'Update Country',
                'add_new_item'  => 'Add New Country',
                'new_item_name' => 'New Country Name',
                'menu_name'     => 'Country',
            ],
            'hierarchical'      => false,
            'public'            => true,
            'show_in_rest'      => true,
            'rewrite'           => true,
            'show_admin_column' => true,
        ]);
    }
}

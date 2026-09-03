<?php

namespace Brut\Admin\PostTypes;

class ArchitectPostTypeRegistrar
{
    public function boot(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        register_post_type('architect', [
            'labels' => [
                'name' => 'Architects',
                'singular_name' => 'Architect',
                'menu_name' => 'Architects',
                'all_items' => 'All Architects',
                'view_item' => 'View Architect',
                'add_new_item' => 'Add Architect',
                'add_new' => 'New Architect',
                'edit_item' => 'Edit Architect',
                'update_item' => 'Update Architect',
                'search_items' => 'Search Architects',
            ],
            'supports' => ['title', 'thumbnail'],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'show_in_rest' => true,
            'capability_type' => 'post',
            // Matches the frontend's /architects/:slug detail route (plural,
            // like the /architects listing page), not the default singular
            // "architect" base WordPress would otherwise use.
            'rewrite' => ['slug' => 'architects', 'with_front' => true],
        ]);
    }
}

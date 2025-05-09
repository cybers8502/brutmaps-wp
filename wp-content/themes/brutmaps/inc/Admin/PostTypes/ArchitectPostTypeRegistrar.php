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
                'name' => 'Creators',
                'singular_name' => 'Creator',
                'menu_name' => 'Creators',
                'all_items' => 'All Creators',
                'view_item' => 'View Creator',
                'add_new_item' => 'Add Creator',
                'add_new' => 'New Creator',
                'edit_item' => 'Edit Creator',
                'update_item' => 'Update Creator',
                'search_items' => 'Search Creators',
            ],
            'supports' => ['title'],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'show_in_rest' => true,
            'capability_type' => 'post',
            'rewrite' => ['with_front' => true],
        ]);
    }
}

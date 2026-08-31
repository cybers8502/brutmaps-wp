<?php

namespace Brut\Admin\PostTypes;

class AuthorsPostTypeRegistrar
{
    public function boot(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        register_post_type('authors', [
            'labels' => [
                'name' => 'Authors',
                'singular_name' => 'Author',
                'menu_name' => 'Authors',
                'all_items' => 'All Authors',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Author',
                'edit_item' => 'Edit Author',
                'view_item' => 'View Author',
                'search_items' => 'Search Authors',
            ],
            'supports' => ['title'],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'show_in_rest' => true,
            'capability_type' => 'post',
            'rewrite' => ['with_front' => true],
        ]);
    }
}

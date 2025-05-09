<?php

namespace Brut\Admin\PostTypes;

class ContributorPostTypeRegistrar
{
    public function boot(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        register_post_type('contributor', [
            'labels' => [
                'name' => 'Contributors',
                'singular_name' => 'Contributor',
                'menu_name' => 'Contributors',
                'all_items' => 'All Contributors',
                'add_new_item' => 'Add Contributor',
                'add_new' => 'New Contributor',
                'edit_item' => 'Edit Contributor',
                'view_item' => 'View Contributor',
                'search_items' => 'Search Contributors',
            ],
            'supports' => ['title'],
            'hierarchical' => false,
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

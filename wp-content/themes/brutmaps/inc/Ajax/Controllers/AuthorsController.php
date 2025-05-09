<?php

namespace Brut\Ajax\Controllers;

class AuthorsController
{
    public function __construct() {
        add_action('wp_ajax_create_author_by_name', [$this, 'create']);
        add_action('wp_ajax_nopriv_create_author_by_name', [$this, 'create']);
    }

    public function create(): void
    {
        $name = $_POST['name'] ?? null;
        if (!$name) {
            wp_send_json_error(['done' => false]);
        }

        if ($this->authorExists($name)) {
            wp_send_json_success(['done' => true, 'message' => 'Author already exists']);
        }

        $this->createAuthor($name);
        wp_send_json_success(['done' => true, 'message' => 'Author created']);
    }

    private function authorExists($name): bool {
        $args = [
            'numberposts'   => 1,
            'post_type'		=> 'authors',
            'fields'        => 'ids',
            'post_status'   => ['publish', 'pending', 'draft'],
            'meta_query'	=> [
                [
                    'key'		=> 'instagram',
                    'value'		=> $name,
                    'compare'	=> 'LIKE'
                ]
            ]
        ];
        return count(get_posts($args)) > 0;
    }

    private function createAuthor($name): ?int {
        $post_id = wp_insert_post([
            'post_title'    => $name,
            'post_type'     => 'authors',
            'post_status'   => 'publish'
        ]);
        if ($post_id > 0) {
            update_field('instagram', $name, $post_id);
            return $post_id;
        }
        return null;
    }
}

<?php

namespace Brut\Rest\Controllers;

use WP_REST_Response;
use Brut\Utils\ResponseHelper;

class ArchitectsController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/architecture-types', [
            'methods' => 'GET',
            'callback' => [$this, 'getArchitectureTypes'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function getArchitectureTypes(): WP_REST_Response
    {
        $terms = get_terms([
            'taxonomy'   => 'taxonomy',
            'hide_empty' => false,
        ]);

        $data = [];

        foreach ($terms as $term) {
            $data[] = [
                'id'    => $term->term_id,
                'slug'  => $term->slug,
                'name'  => $term->name,
            ];
        }

        return ResponseHelper::success($data);
    }
}

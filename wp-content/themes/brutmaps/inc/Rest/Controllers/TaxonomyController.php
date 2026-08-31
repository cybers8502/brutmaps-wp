<?php

namespace Brut\Rest\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use Brut\Utils\ResponseHelper;
use Brut\Services\CacheService;

class TaxonomyController
{
    public function register(): void
    {

        register_rest_route(BASE_URL, '/taxonomies', [
            'methods' => 'GET',
            'callback' => [$this, 'getTaxonomies'],
            'args' => [
                'taxonomy' => [
                    'type' => 'string',
                    'required' => true,
                    'description' => 'Taxonomy slug (e.g., taxonomy)',
                ],
            ],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Отримати всі терміни заданої таксономії.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function getTaxonomies(WP_REST_Request $request): WP_REST_Response
    {
        $taxonomy = $request->get_param('taxonomy');

        if (empty($taxonomy)) {
            return new WP_REST_Response([
                'status' => 'error',
                'message' => 'Missing taxonomy param'
            ], 400);
        }

        $cacheKey = "taxonomy_{$taxonomy}";

        $data = CacheService::getOrSet($cacheKey, function () use ($taxonomy) {
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]);

            $parentTerms = array_filter($terms, fn($term) => $term->parent == 0);
            $childTerms = array_filter($terms, fn($term) => $term->parent != 0);

            $structured = [];

            foreach ($parentTerms as $parent) {
                $children = array_filter($childTerms, fn($child) => $child->parent == $parent->term_id);

                $structured[] = [
                    'id'    => $parent->term_id,
                    'label' => $parent->name,
                    'slug'  => $parent->slug,
                    'count' => $parent->count,
                    'subcategories' => array_map(fn($child) => [
                        'id'    => $child->term_id,
                        'label' => $child->name,
                        'slug'  => $child->slug,
                        'count' => $child->count,
                    ], array_values($children)),
                ];
            }

            return $structured;
        });

        return ResponseHelper::success($data);
    }
}

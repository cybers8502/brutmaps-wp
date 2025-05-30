<?php

namespace Brut\Rest\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use Brut\Utils\ResponseHelper;
use Brut\Services\TaxonomyService;
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
            return TaxonomyService::getTerms($taxonomy);
        }, HOUR_IN_SECONDS);

        return ResponseHelper::success($data);
    }
}


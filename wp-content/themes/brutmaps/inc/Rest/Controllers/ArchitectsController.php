<?php

namespace Brut\Rest\Controllers;

use Brut\Services\ArchitectStatsService;
use Brut\Services\CacheService;
use Brut\Utils\ContentHelper;
use Brut\Utils\RequestSanitizer;
use WP_REST_Request;
use WP_REST_Response;
use Brut\Utils\ResponseHelper;

class ArchitectsController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/architects', [
            'methods' => 'GET',
            'callback' => [$this, 'getArchitects'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/architects/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getArchitectById'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'type' => 'integer',
                    'required' => true,
                    'description' => 'Architect ID',
                ],
            ],
        ]);

        register_rest_route(BASE_URL, '/architects/search', [
            'methods' => 'GET',
            'callback' => [$this, 'searchArchitects'],
            'args' => [
                'query' => [
                    'type' => 'string',
                    'required' => true,
                    'description' => 'Search query by first name, last name or title'
                ],
            ],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/architects/popular', [
            'methods'  => 'GET',
            'callback' => [$this, 'getSixPopularOrFallbackArchitects'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/architecture-types', [
            'methods' => 'GET',
            'callback' => [$this, 'getArchitectureTypes'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function getArchitects(): WP_REST_Response
    {
        $cacheKey = 'architects';

        $response = CacheService::getOrSet($cacheKey, function () {

            $architects = get_posts([
                'post_type'   => 'architect',
                'post_status' => 'publish',
                'numberposts' => -1,
                'orderby'     => 'title',
                'order'       => 'ASC',
                'fields'      => 'ids',
            ]);

            $result = [];

            foreach ($architects as $architectID) {
                $result[] = ContentHelper::mapArchitect($architectID);
            }

            return $result;
        });

        return ResponseHelper::success($response);
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

    public function getArchitectById(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');

        $cacheKey = "architect_{$id}";

        $data = CacheService::getOrSet($cacheKey, function () use ($id) {
            $post = get_post($id);

            if (!$post || $post->post_type !== 'architect' || $post->post_status !== 'publish') {
                return null;
            }

            return ContentHelper::mapArchitect($id);
        });

        if (!$data) {
            return new WP_REST_Response([
                'status' => 'error',
                'message' => 'Architect not found'
            ], 404);
        }

        return ResponseHelper::success($data);
    }

    public function getSixPopularOrFallbackArchitects(): \WP_REST_Response
    {
        global $wpdb;
        $cacheKey = 'architects_popular';

        $response = CacheService::getOrSet($cacheKey, function () use ($wpdb) {
            $popular = [];
            $fallback = [];

            // Статистика переглядів
            $options = $wpdb->get_results("
            SELECT option_name, option_value 
            FROM $wpdb->options 
            WHERE option_name LIKE 'architect_views_%'
            ");

            foreach ($options as $opt) {
                $id = (int) str_replace('architect_views_', '', $opt->option_name);
                $popular[$id] = (int) $opt->option_value;
            }

            // Архітектори з найбільшою кількістю обʼєктів (використовуємо meta_query)
            $all_architects = get_posts([
                'post_type' => 'architect',
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields' => 'ids',
            ]);

            foreach ($all_architects as $architectID) {
                if (isset($popular[$architectID])) {
                    continue;
                }

                $linked = get_posts([
                    'post_type' => 'sight',
                    'post_status' => 'publish',
                    'meta_query' => [
                        [
                            'key' => 'choose_architects',
                            'value' => '"' . $architectID . '"',
                            'compare' => 'LIKE'
                        ]
                    ],
                    'fields' => 'ids',
                ]);

                if (count($linked) > 0) {
                    $fallback[$architectID] = count($linked);
                }
            }

            // Комбінуємо та сортуємо
            arsort($popular);
            arsort($fallback);

            $top = [];

            foreach (array_keys($popular) as $id) {
                $top[] = ContentHelper::mapArchitect($id, $popular[$id]);
                if (count($top) >= 6) {
                    break;
                }
            }

            if (count($top) < 6) {
                foreach (array_keys($fallback) as $id) {
                    $top[] = ContentHelper::mapArchitect($id, $fallback[$id]);
                    if (count($top) >= 6) {
                        break;
                    }
                }
            }

            return $top;
        }, DAY_IN_SECONDS);

        return ResponseHelper::success($response);
    }

    public function searchArchitects(WP_REST_Request $request): WP_REST_Response
    {
        $query = RequestSanitizer::sanitizeStringParam($request, 'query');

        if (empty($query)) {
            return new WP_REST_Response([
                'status' => 'error',
                'message' => 'Missing query param'
            ], 400);
        }

        $posts = ArchitectStatsService::searchArchitectsByQuery($query);

        $result = array_map(fn($post) => ContentHelper::mapArchitect($post->ID), $posts);

        return ResponseHelper::success($result);
    }
}

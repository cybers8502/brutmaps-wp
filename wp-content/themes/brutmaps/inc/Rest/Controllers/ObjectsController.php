<?php

namespace Brut\Rest\Controllers;

use Brut\Services\ArchitectStatsService;
use Brut\Services\CacheService;
use Brut\Utils\ContentHelper;
use Brut\Utils\PostHelper;
use Brut\Utils\RequestSanitizer;
use Brut\Utils\ResponseHelper;
use DOMException;

class ObjectsController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/map/sights', [
            'methods' => 'GET',
            'callback' => [$this, 'getObjects'],
            'permission_callback' => '__return_true',
            'args' => [
                'architects' => [
                    'type' => 'array',
                    'required' => false,
                    'description' => 'Array of architect post IDs',
                ],
                'taxonomy_terms' => [
                    'type' => 'array',
                    'required' => false,
                    'description' => 'Array of taxonomy slugs',
                ],
            ],
        ]);

        register_rest_route(BASE_URL, '/map/sights/(?P<identifier>[\w-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getObjectByIdOrSlug'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/sights-images', [
            'methods' => 'GET',
            'callback' => [$this, 'getSightsImages'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function getObjects(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $architectIDs = $request->get_param('architects') ?? [];
        $taxonomyTerms = $request->get_param('taxonomy_terms') ?? [];
        $user_id = get_current_user_id();

        $getFilteredObjects = function () use ($architectIDs, $taxonomyTerms, $user_id) {
            $args = [
                'post_type'      => 'sight',
                'post_status'    => 'publish',
                'numberposts'    => -1,
                'fields'         => 'ids',
                'meta_query'     => [],
                'tax_query'      => [],
            ];

            $architectIDs = RequestSanitizer::sanitizeArray($architectIDs);
            if (!empty($architectIDs)) {
                $meta_conditions = [];

                foreach ($architectIDs as $id) {
                    $meta_conditions[] = [
                        'key'     => 'choose_architects',
                        'value'   => '"' . intval($id) . '"',
                        'compare' => 'LIKE',
                    ];

                    (new ArchitectStatsService())->incrementSearchCount($id);
                }

                $args['meta_query'] = [
                    'relation' => 'OR',
                    ...$meta_conditions,
                ];
            }

            $taxonomyTerms = RequestSanitizer::sanitizeArray($taxonomyTerms);
            if (!empty($taxonomyTerms)) {
                $args['tax_query'][] = [
                    'taxonomy' => 'taxonomy',
                    'field'    => 'slug',
                    'terms'    => $taxonomyTerms,
                    'operator' => 'IN',
                ];
            }

            $ids = get_posts($args);

            // Фільтруємо за вподобаннями користувача
            $categoriesMap = [];
            if ($user_id) {
                $preferences = get_user_meta($user_id, 'sight_preferences', true);
                if (!is_array($preferences)) {
                    $preferences = array_fill_keys(FavoritesController::CATEGORIES, []);
                }

                // Побудуємо: [sightId => category]
                foreach ($preferences as $category => $idsInCategory) {
                    foreach ($idsInCategory as $id) {
                        // Якщо один об'єкт у кількох — зберігаємо першу категорію
                        if (!isset($categoriesMap[$id]) && $category !== 'hidden') {
                            $categoriesMap[$id] = $category;
                        }
                    }
                }

                // Видаляємо hidden
                $hidden = $preferences['hidden'] ?? [];
                $ids = array_values(array_diff($ids, $hidden));
            }

            // GeoJSON із доданою категорією
            $geoJson = ContentHelper::getSightsGeoJSONByIDs($ids, $categoriesMap);

            return [
                'featureCollection' => $geoJson,
            ];
        };

        // Якщо користувач є — не кешуємо
        if ($user_id) {
            $response = $getFilteredObjects();
        } else {
            $cacheKey = 'sights_cache_' . md5(json_encode([
                    'architects' => $architectIDs,
                    'taxonomy'   => $taxonomyTerms,
                ]));

            $response = CacheService::getOrSet($cacheKey, $getFilteredObjects);
        }

        return ResponseHelper::success($response);
    }

    /**
     * @throws DOMException
     */
    public function getObjectByIdOrSlug($data): \WP_REST_Response|\WP_Error
    {
        $identifier = $data['identifier'];
        $post = is_numeric($identifier)
            ? PostHelper::getPublishedPost((int) $identifier, 'sight')
            : PostHelper::getPostBySlug($identifier, 'sight');

        if (!$post) {
            return ResponseHelper::notFound('Sight not found');
        }

        $id = $post->ID;
        $location = get_field('location', $id);

        if (!$location || !isset($location['lat'], $location['lng'])) {
            return ResponseHelper::validationError('Location not defined');
        }

        $cacheKey = 'sight_cache_' . $id;

        $response = CacheService::getOrSet($cacheKey, function () use ($post, $location, $id) {
            $blocks = parse_blocks($post->post_content);
            $topGallery = null;

            // Перевіряємо, чи перший блок — галерея
            if (!empty($blocks) && $blocks[0]['blockName'] === 'core/gallery') {
                foreach ($blocks[0]['innerBlocks'] as $inner) {
                    if ($inner['blockName'] === 'core/image' && isset($inner['attrs']['id'])) {
                        $image_id = $inner['attrs']['id'];
                        $topGallery[] = ContentHelper::mapImage($image_id);
                    }
                }

                array_shift($blocks);
            }

            // Рендеримо залишки контенту
            $content = '';
            foreach ($blocks as $block) {
                $content .= render_block($block);
            }

            $content = apply_filters('the_content', $content);
            $content = ContentHelper::addAcfDataToImages($content);

            return [
                'id'            => $id,
                'title'         => html_entity_decode(get_the_title($id)),
                'address'       => html_entity_decode($location['address']),
                'slug'          => $post->post_name,
                'description'   => $content,
                'coordinates'   => [
                    'lat'   => doubleval($location['lat']),
                    'long'  => doubleval($location['lng']),
                ],
                'gallery'       => ContentHelper::getGutenbergImages($id),
                'topGallery'    => $topGallery
            ];
        });

        return ResponseHelper::success($response);
    }

    public function getSightsImages(\WP_REST_Request $request): \WP_REST_Response
    {
        $seed = $request->get_param('seed') ?: 'default';
        $paged = (int) $request->get_param('page') ?: 1;
        $per_page = (int) $request->get_param('per_page') ?: 20;

        // Якщо seed не вказаний, то використовуємо дефолтний
        if ($seed === 'default') {
            $seed = 'default_' . time();
        }

        $orderKey = sprintf('sights:order:%s', $seed);
        $orderedIds = CacheService::getOrSet($orderKey, function () use ($seed) {

            // Отримаємо всі ID постів з превʼю
            $ids = get_posts([
                'post_type' => 'sight',
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields' => 'ids',
            ]);

            // Стабільно перемішуємо за seed
            $hash = crc32($seed);
            srand($hash);
            shuffle($ids);
            srand();

            return $ids;
        });

        $cacheKey = sprintf('sights:%s:p%d:pp%d', $seed, $paged, $per_page);

        $response = CacheService::getOrSet($cacheKey, function () use ($orderedIds, $paged, $per_page) {
            $offset = ($paged - 1) * $per_page;

            $sliced_ids = array_slice($orderedIds, $offset, $per_page);
            $total = count($orderedIds);
            $total_pages = ceil($total / $per_page);

            $items = [];

            foreach ($sliced_ids as $post_id) {
                $post = get_post($post_id);
                $item_ID = $post->ID;
                $preview_id = get_post_thumbnail_id($item_ID);
                $preview_image = get_the_post_thumbnail_url($item_ID, 'large');

                if (!$preview_image) {
                    continue;
                }

                $blocks = parse_blocks($post->post_content);

                foreach ($blocks as $block) {
                    if ($block['blockName'] === 'core/gallery' && !empty($block['attrs']['ids'][0])) {
                        $img_id = $block['attrs']['ids'][0];
                        $preview_id = $img_id;
                        $preview_image = wp_get_attachment_image_url($img_id, 'large');
                        break;
                    }
                }

                $items[] = [
                    'post_id' => $item_ID,
                    'title' => get_the_title($post),
                    'link' => get_permalink($post),
                    'preview_image_url' => $preview_image,
                    'author' => ContentHelper::getAuthorByMediaId($preview_id)
                ];
            }

            return [
                'current_page' => $paged,
                'total_pages' => $total_pages,
                'total_posts' => $total,
                'posts' => $items,
            ];
        });

        return ResponseHelper::success($response);
    }
}

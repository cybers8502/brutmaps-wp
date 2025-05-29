<?php

namespace Brut\Rest\Controllers;

use Brut\Services\ArchitectStatsService;
use Brut\Services\CacheService;
use Brut\Utils\ContentHelper;
use Brut\Utils\MediaHelper;
use Brut\Utils\PostHelper;
use Brut\Utils\RequestSanitizer;
use Brut\Utils\ResponseHelper;

class ObjectsController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/sights', [
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

        register_rest_route(BASE_URL, '/sights/(?P<identifier>[\w-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getObjectByIdOrSlug'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function getObjects(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $architectIDs = $request->get_param('architects') ?? [];
        $taxonomyTerms = $request->get_param('taxonomy_terms') ?? [];

        // Ключ кешу залежить від параметрів запиту
        $cacheKey = 'sights_cache_' . md5(json_encode([
                'architects' => $architectIDs,
                'taxonomy'   => $taxonomyTerms,
            ]));

        $response = CacheService::getOrSet($cacheKey, function () use ($architectIDs, $taxonomyTerms) {
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

                    // Додаємо балів популярності архітектору
                    (new ArchitectStatsService)->incrementSearchCount($id);
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
            $geoJson = ContentHelper::getSightsGeoJSONByIDs($ids);

            return [
                'featureCollection' => $geoJson,
                '$args' => $args
            ];
        });

        return ResponseHelper::success($response);
    }

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

        $main_image = get_field('main_image', $id);
        $main_image_url = is_array($main_image) && isset($main_image['url']) ? $main_image['url'] : PLACEHOLDER;

        $gallery = get_field('gallery', $id) ?? [];
        $gallery_formatted = [];

        foreach ($gallery as $item) {
            $img = $item['gallery_image'] ?? null;
            $author = $item['gallery_image_author_id'] ?? null;
            $imgData = MediaHelper::getSmartImage($img, $author);
            if ($imgData) {
                $gallery_formatted[] = $imgData;
            }
        }

        $mainData = [
            'id' => $id,
            'main_data' => [
                'title' => html_entity_decode(get_the_title($id)),
                'sub_title' => html_entity_decode($location['address']),
                'image' => $main_image_url,
                'main_image_author' => ContentHelper::getAuthorData(get_field('main_image_author_id', $id)),
                'main_image_author_name' => get_field('main_image_author_name', $id),
            ],
            'architects' => ContentHelper::getCreatorsSmallDataByIDs(get_field('choose_architects', $id) ?? []),
            'associated_people' => ContentHelper::getCreatorsSmallDataByIDs(get_field('choose_associated_people', $id) ?? []),
            'year' => intval(get_field('established', $id)),
            'slug' => $post->post_name,
            'description' => html_entity_decode(get_field('main_content', $id)),
            'image_gallery' => $gallery_formatted,
            'extra_data' => html_entity_decode(get_field('source', $id)),
            'coordinates' => [
                'lat' => doubleval($location['lat']),
                'long' => doubleval($location['lng']),
            ],
        ];

        return ResponseHelper::success($mainData);
    }
}

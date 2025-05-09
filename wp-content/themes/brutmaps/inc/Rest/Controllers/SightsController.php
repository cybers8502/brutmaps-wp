<?php

namespace Brut\Rest\Controllers;

use Brut\Utils\ContentHelper;
use Brut\Utils\PostHelper;
use Brut\Utils\ResponseHelper;

class SightsController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/sights', [
            'methods' => 'GET',
            'callback' => [$this, 'getSights'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/sights/(?P<identifier>[\w-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getSightByIdOrSlug'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function getSights(): \WP_REST_Response
    {
        $ids = ContentHelper::getFilterdPostsIDbyCategories('sight');
        $geoJson = ContentHelper::getSightsGeoJSONByIDs($ids);

        $center = get_field('initial_center_for_users', 'options');
        $lat = $center['lat'] ?? 40.6971494;
        $long = $center['lng'] ?? -74.2598626;

        return ResponseHelper::success([
            'featureCollection' => $geoJson,
            'settings' => [
                'default_center' => [
                    'coordinates' => [
                        'lat' => doubleval($lat),
                        'long' => doubleval($long),
                    ],
                ],
            ],
        ]);
    }

    public function getSightByIdOrSlug(array $data): \WP_REST_Response|\WP_Error
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
            $imgData = ContentHelper::getSmartImage($img, $author);
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

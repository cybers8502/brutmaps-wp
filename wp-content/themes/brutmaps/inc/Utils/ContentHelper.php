<?php

namespace Brut\Utils;

use WP_REST_Response;
use Brut\Utils\MediaHelper;

class ContentHelper
{

    public static function getAuthorData($authorID): ?array
    {
        if (!$authorID) return null;

        return [
            'first_name_author' => get_field('first_name_author', $authorID),
            'second_name_author' => get_field('second_name_author', $authorID),
            'link' => get_field('link', $authorID),
            'instagram' => get_field('instagram', $authorID),
        ];
    }

    public static function getSightImageURLs(int $sightID): array
    {
        $urls = [];
        $image = get_field('main_image', $sightID);
        $gallery = get_field('gallery', $sightID);

        if (is_array($image) && isset($image['url'])) {
            $urls[] = $image['url'];
        } elseif (is_string($image)) {
            $urls[] = $image;
        }

        if (is_array($gallery)) {
            foreach ($gallery as $item) {
                $src = wp_get_attachment_image_src($item['gallery_image'], 'full');
                if ($src && isset($src[0])) {
                    $urls[] = $src[0];
                }
            }
        }

        return !empty($urls) ? $urls : [PLACEHOLDER];
    }

    public static function getCreatorsSmallDataByIDs($IDs): array
    {
        $result = [];

        foreach ($IDs as $id) {
            $post = get_post($id);
            if (!$post || $post->post_type !== 'architect' || $post->post_status !== 'publish') {
                continue;
            }

            $image = get_field('main_image', $id);
            $author = get_field('main_image_author_id', $id);

            $result[] = [
                'id' => $id,
                'first_name' => html_entity_decode(get_field('first_name', $id)),
                'last_name' => html_entity_decode(get_field('last_name', $id)),
                'name' => html_entity_decode(get_the_title($id)),
                'description' => html_entity_decode(get_field('description', $id)),
                'image' => MediaHelper::getImageWithSizes($image),
                'main_image_author' => self::getAuthorData($author),
            ];
        }

        return $result;
    }

    public static function getSightsGeoJSONByIDs(array $IDs): array
    {
        $features = [];

        foreach ($IDs as $id) {
            $post = get_post($id);
            $location = get_field('location', $id);

            if (!$post || !$location || !isset($location['lat'], $location['lng'])) continue;

            $features[] = [
                'type' => 'Feature',
                'id' => $id,
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [doubleval($location['lng']), doubleval($location['lat'])],
                ],
                'properties' => [
                    'id' => $id,
                    'slug' => $post->post_name,
                    'title' => html_entity_decode(get_the_title($id)),
                    'address' => html_entity_decode($location['address']),
                    'year' => intval(get_field('established', $id)),
                    'images' => self::getSightImageURLs($id),
                ],
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    public static function getFilteredPostsIDbyCategories(string $type, array $categories = []): array
    {
        $args = [
            'post_type' => $type,
            'post_status' => 'publish',
            'numberposts' => -1,
        ];

        if (!empty($categories)) {
            $args['category_name'] = implode(',', $categories);
        }

        return wp_list_pluck(get_posts($args), 'ID');
    }

    public static function getPostsByIDs(array $IDs): array
    {
        $result = [];

        foreach ($IDs as $id) {
            $post = get_post($id);
            if (!$post) continue;

            $categories = array_map(fn($cat) => $cat->name, get_the_category($id) ?: []);

            $result[] = [
                'id' => $id,
                'slug' => $post->post_name,
                'title' => $post->post_title,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'thumbnail' => get_the_post_thumbnail_url($id, 'full'),
                'permalink' => get_permalink($id),
                'categories' => $categories,
            ];
        }

        return $result;
    }
}

<?php

namespace Brut\Utils;

use DOMDocument;
use DOMException;

class ContentHelper
{
    /**
     * @throws DOMException
     */
    public static function addAcfDataToImages($content): string
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><!DOCTYPE html><html lang="en"><body>' . $content . '</body></html>');

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $class = $img->getAttribute('class');

            if (preg_match('/wp-image-(\d+)/', $class, $matches)) {
                $image_id = $matches[1];

                $acf_fields = get_fields($image_id);

                $author_id = $acf_fields['attached_image_author_id'];

                $caption = $dom->createElement('figcaption', self::getFigcaption($author_id));

                $parent = $img->parentNode;

                if (!$parent instanceof \DOMElement) {
                    continue;
                }

                // Обгортаємо <img> + <figcaption> у <figure>
                $figure = $dom->createElement('figure');
                $figure->appendChild($img->cloneNode(true));
                $figure->appendChild($caption);

                $parent->append($caption, $img);
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);

        $newContent = '';

        foreach ($body->childNodes as $child) {
            $newContent .= $dom->saveHTML($child);
        }

        return $newContent;
    }

    public static function getFigcaption($author_id): string
    {
        $author = get_field('field_author_first_name', $author_id) . ' ' . get_field('second_name_author', $author_id);

        $author = trim($author);

        if (!$author) {
            $author = get_field('instagram', $author_id);
        }

        return $author ? 'Image by: ' . $author : 'Unknown Author';
    }

    public static function getAuthorByMediaId($image_id): array
    {
        $id = get_field('attached_image_author_id', $image_id);

        $post = get_post($id);

        if (!$post || $post->post_type !== 'authors' || $post->post_status !== 'publish') {
            return self::mapAuthor(null);
        }

        return self::mapAuthor($id);
    }

    public static function getGutenbergImages($post_id): array
    {
        $blocks = parse_blocks(get_post_field('post_content', $post_id));
        $images = [];

        foreach ($blocks as $block) {
            if ($block['blockName'] === 'core/image' && isset($block['attrs']['id'])) {
                $image_id = $block['attrs']['id'];
                $images[] = self::mapImage($image_id);
            }

            if (!empty($block['innerBlocks'])) {
                foreach ($block['innerBlocks'] as $inner) {
                    if ($inner['blockName'] === 'core/image' && isset($inner['attrs']['id'])) {
                        $image_id = $inner['attrs']['id'];
                        $images[] = self::mapImage($image_id);
                    }
                }
            }
        }

        return $images;
    }

    public static function getSightsGeoJSONByIDs(array $IDs, array $categoriesMap = []): array
    {
        $features = [];

        foreach ($IDs as $id) {
            $post = get_post($id);
            $location = get_field('location', $id);

            if (!$post || !$location || !isset($location['lat'], $location['lng'])) {
                continue;
            }

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
                    'image' => self::getPostThumbnailInformationById($id),
                    'category' => $categoriesMap[$id] ?? null,
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
            if (!$post) {
                continue;
            }

            $categories = array_map(fn($cat) => $cat->name, get_the_category($id) ?: []);

            $result[] = [
                'id' => $id,
                'slug' => $post->post_name,
                'title' => $post->post_title,
                'author' => get_the_author_meta('display_name', (int) $post->post_author),
                'thumbnail' => get_the_post_thumbnail_url($id, 'full'),
                'permalink' => get_permalink($id),
                'categories' => $categories,
            ];
        }

        return $result;
    }

    public static function getPostThumbnailInformationById($post_id): array
    {
        $thumbnail_id = get_post_thumbnail_id($post_id);

        return self::getThumbnailInformationById($thumbnail_id);
    }

    public static function getThumbnailInformationById($thumbnail_id): array
    {
        return self::mapImage($thumbnail_id);
    }

    public static function mapImage($image_id): array
    {
        $placeholder = defined('PLACEHOLDER') ? PLACEHOLDER : '';

        if ($image_id) {
            return [
                'id' => $image_id,
                'url' => wp_get_attachment_image_url($image_id, 'full'),
                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
                'title' => get_the_title($image_id),
                'author' => self::getAuthorByMediaId($image_id),
                'source' => get_field('attached_image_author_source', $image_id),
            ];
        }

        return [
            'id' => null,
            'url' => $placeholder,
            'alt' => 'Placeholder',
            'title' => 'Placeholder',
            'author' => self::getAuthorByMediaId(null),
            'source' => null,
        ];
    }

    public static function mapArchitect(int $id, int $count = 0): array
    {
        return [
            'id'         => $id,
            'count'      => $count,
            'title'      => html_entity_decode(get_the_title($id)),
            'first_name' => get_field('first_name', $id),
            'last_name'  => get_field('last_name', $id),
            'full_name'  => trim(get_field('first_name', $id) . ' ' . get_field('last_name', $id)),
            'image'      => ContentHelper::getPostThumbnailInformationById($id),
        ];
    }

    public static function mapAuthor($id): array
    {
        if (!$id) {
            return [
                'figcaption' => self::getFigcaption(null),
            ];
        }

        return [
            'id'        => $id,
            'title'     => html_entity_decode(get_the_title($id)),
            'figcaption' => self::getFigcaption($id),
            'first_name' => get_field('first_name_author', $id),
            'last_name' => get_field('field_author_second_name', $id),
            'full_name' => trim(get_field('first_name_author', $id) . ' ' . get_field('field_author_second_name', $id)),
            'instagram' => get_field('instagram', $id),
            'link'      => get_field('Link', $id),
        ];
    }
}

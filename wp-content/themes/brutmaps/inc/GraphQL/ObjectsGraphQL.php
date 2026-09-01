<?php

namespace Brut\GraphQL;

use Brut\Services\ArchitectStatsService;
use Brut\Services\CacheService;
use Brut\Services\FavoritesService;
use Brut\Utils\ContentHelper;
use Brut\Utils\PostHelper;
use Brut\Utils\RequestSanitizer;
use DOMException;

class ObjectsGraphQL
{
    public function register(): void
    {
        add_action('graphql_register_types', [$this, 'registerTypes']);
    }

    public function registerTypes(): void
    {
        $this->registerSharedTypes();
        $this->registerSightsMapTypes();
        $this->registerSightDetailTypes();
        $this->registerSightsImagesTypes();
        $this->registerQueries();
    }

    private function registerSharedTypes(): void
    {
        register_graphql_object_type('BrutAuthor', [
            'description' => 'Author of a media attachment',
            'fields'      => [
                'id'         => ['type' => 'Integer'],
                'title'      => ['type' => 'String'],
                'figcaption' => ['type' => 'String'],
                'firstName'  => [
                    'type'    => 'String',
                    'resolve' => fn($author) => $author['first_name'] ?? null,
                ],
                'lastName'   => [
                    'type'    => 'String',
                    'resolve' => fn($author) => $author['last_name'] ?? null,
                ],
                'fullName'   => [
                    'type'    => 'String',
                    'resolve' => fn($author) => $author['full_name'] ?? null,
                ],
                'instagram'  => ['type' => 'String'],
                'link'       => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('BrutImage', [
            'description' => 'An image with author metadata',
            'fields'      => [
                'id'     => ['type' => 'Integer'],
                'url'    => ['type' => 'String'],
                'alt'    => ['type' => 'String'],
                'title'  => ['type' => 'String'],
                'author' => ['type' => 'BrutAuthor'],
                'source' => ['type' => 'String'],
            ],
        ]);
    }

    private function registerSightsMapTypes(): void
    {
        register_graphql_object_type('SightGeometry', [
            'description' => 'GeoJSON Point geometry',
            'fields'      => [
                'type'        => ['type' => 'String'],
                'coordinates' => ['type' => ['list_of' => 'Float']],
            ],
        ]);

        register_graphql_object_type('SightFeatureProperties', [
            'description' => 'Properties of a GeoJSON Feature',
            'fields'      => [
                'id'       => ['type' => 'Integer'],
                'slug'     => ['type' => 'String'],
                'title'    => ['type' => 'String'],
                'image'    => ['type' => 'BrutImage'],
                'category' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('SightFeature', [
            'description' => 'GeoJSON Feature for a sight',
            'fields'      => [
                'type'       => ['type' => 'String'],
                'id'         => ['type' => 'Integer'],
                'geometry'   => ['type' => 'SightGeometry'],
                'properties' => ['type' => 'SightFeatureProperties'],
            ],
        ]);

        register_graphql_object_type('SightsFeatureCollection', [
            'description' => 'GeoJSON FeatureCollection of sights',
            'fields'      => [
                'type'     => ['type' => 'String'],
                'features' => ['type' => ['list_of' => 'SightFeature']],
            ],
        ]);

        register_graphql_object_type('SightsMapResult', [
            'description' => 'Result of sightsMap query',
            'fields'      => [
                'featureCollection' => ['type' => 'SightsFeatureCollection'],
            ],
        ]);
    }

    private function registerSightDetailTypes(): void
    {
        register_graphql_object_type('SightCoordinates', [
            'description' => 'Lat/long coordinates of a sight',
            'fields'      => [
                'lat'  => ['type' => 'Float'],
                'long' => ['type' => 'Float'],
            ],
        ]);

        register_graphql_object_type('SightDetail', [
            'description' => 'Full detail of a single sight',
            'fields'      => [
                'id'          => ['type' => 'Integer'],
                'title'       => ['type' => 'String'],
                'address'     => ['type' => 'String'],
                'slug'        => ['type' => 'String'],
                'description' => ['type' => 'String'],
                'coordinates' => ['type' => 'SightCoordinates'],
                'gallery'     => ['type' => ['list_of' => 'BrutImage']],
                'topGallery'  => ['type' => ['list_of' => 'BrutImage']],
            ],
        ]);
    }

    private function registerSightsImagesTypes(): void
    {
        register_graphql_object_type('SightItem', [
            'description' => 'Sight preview item for image grid',
            'fields'      => [
                'postId'          => [
                    'type'    => 'Integer',
                    'resolve' => fn($item) => $item['postId'] ?? null,
                ],
                'title'           => ['type' => 'String'],
                'link'            => ['type' => 'String'],
                'previewImageUrl' => [
                    'type'    => 'String',
                    'resolve' => fn($item) => $item['previewImageUrl'] ?? null,
                ],
                'author'          => ['type' => 'BrutAuthor'],
            ],
        ]);

        register_graphql_object_type('SightsImagesResult', [
            'description' => 'Paginated sights images result',
            'fields'      => [
                'currentPage' => [
                    'type'    => 'Integer',
                    'resolve' => fn($r) => $r['currentPage'] ?? null,
                ],
                'totalPages'  => [
                    'type'    => 'Integer',
                    'resolve' => fn($r) => $r['totalPages'] ?? null,
                ],
                'totalPosts'  => [
                    'type'    => 'Integer',
                    'resolve' => fn($r) => $r['totalPosts'] ?? null,
                ],
                'posts'       => ['type' => ['list_of' => 'SightItem']],
            ],
        ]);
    }

    private function registerQueries(): void
    {
        register_graphql_field('RootQuery', 'sightsMap', [
            'type'        => 'SightsMapResult',
            'description' => 'Get all sights as a GeoJSON FeatureCollection',
            'args'        => [
                'architects'    => ['type' => ['list_of' => 'ID']],
                'taxonomyTerms' => ['type' => ['list_of' => 'String']],
            ],
            'resolve'     => [$this, 'resolveSightsMap'],
        ]);

        register_graphql_field('RootQuery', 'sight', [
            'type'        => 'SightDetail',
            'description' => 'Get a single sight by ID or slug',
            'args'        => [
                'identifier' => ['type' => ['non_null' => 'String']],
            ],
            'resolve'     => [$this, 'resolveSight'],
        ]);

        register_graphql_field('RootQuery', 'sightsCount', [
            'type'        => 'Int',
            'description' => 'Total number of published sights.',
            'resolve'     => [$this, 'resolveSightsCount'],
        ]);

        register_graphql_field('RootQuery', 'sightsImages', [
            'type'        => 'SightsImagesResult',
            'description' => 'Get paginated sight preview images',
            'args'        => [
                'seed'    => ['type' => 'String'],
                'page'    => ['type' => 'Integer'],
                'perPage' => ['type' => 'Integer'],
            ],
            'resolve'     => [$this, 'resolveSightsImages'],
        ]);
    }

    // -------------------------------------------------------------------------
    // Resolvers
    // -------------------------------------------------------------------------

    public function resolveSightsMap($root, array $args): array
    {
        $architectIDs  = RequestSanitizer::sanitizeArray($args['architects'] ?? []);
        $taxonomyTerms = RequestSanitizer::sanitizeArray($args['taxonomyTerms'] ?? []);
        $user_id       = get_current_user_id();

        $getFilteredObjects = function () use ($architectIDs, $taxonomyTerms, $user_id) {
            $queryArgs = [
                'post_type'   => 'sight',
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields'      => 'ids',
                'meta_query'  => [],
                'tax_query'   => [],
            ];

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
                $queryArgs['meta_query'] = ['relation' => 'OR', ...$meta_conditions];
            }

            if (!empty($taxonomyTerms)) {
                $queryArgs['tax_query'][] = [
                    'taxonomy' => 'taxonomy',
                    'field'    => 'slug',
                    'terms'    => $taxonomyTerms,
                    'operator' => 'IN',
                ];
            }

            $ids = get_posts($queryArgs);

            $categoriesMap = [];
            if ($user_id) {
                $preferences = FavoritesService::getPreferences($user_id);

                foreach ($preferences as $category => $idsInCategory) {
                    foreach ($idsInCategory as $id) {
                        if (!isset($categoriesMap[$id]) && $category !== 'hidden') {
                            $categoriesMap[$id] = $category;
                        }
                    }
                }

                $hidden = $preferences['hidden'] ?? [];
                $ids    = array_values(array_diff($ids, $hidden));
            }

            return [
                'featureCollection' => ContentHelper::getSightsGeoJSONByIDs($ids, $categoriesMap),
            ];
        };

        if ($user_id) {
            return $getFilteredObjects();
        }

        $cacheKey = 'sights_cache_' . md5(json_encode([
            'architects' => $architectIDs,
            'taxonomy'   => $taxonomyTerms,
        ]));

        return CacheService::getOrSet($cacheKey, $getFilteredObjects);
    }

    public function resolveSightsCount(): int
    {
        return CacheService::getOrSet('sights_count', function () {
            return (int) (wp_count_posts('sight')->publish ?? 0);
        }, HOUR_IN_SECONDS);
    }

    /**
     * @throws DOMException
     */
    public function resolveSight($root, array $args): array
    {
        $identifier = $args['identifier'];
        $post = is_numeric($identifier)
            ? PostHelper::getPublishedPost((int) $identifier, 'sight')
            : PostHelper::getPostBySlug($identifier, 'sight');

        if (!$post) {
            throw new UserError('Sight not found');
        }

        $id       = $post->ID;
        $location = get_field('location', $id);

        if (!$location || !isset($location['lat'], $location['lng'])) {
            throw new UserError('Location not defined');
        }

        $cacheKey = 'sight_cache_' . $id;

        return CacheService::getOrSet($cacheKey, function () use ($post, $location, $id) {
            $blocks     = parse_blocks($post->post_content);
            $topGallery = null;

            if (!empty($blocks) && $blocks[0]['blockName'] === 'core/gallery') {
                foreach ($blocks[0]['innerBlocks'] as $inner) {
                    if ($inner['blockName'] === 'core/image' && isset($inner['attrs']['id'])) {
                        $topGallery[] = ContentHelper::mapImage($inner['attrs']['id']);
                    }
                }
                array_shift($blocks);
            }

            $content = '';
            foreach ($blocks as $block) {
                $content .= render_block($block);
            }

            $content = apply_filters('the_content', $content);
            $content = ContentHelper::addAcfDataToImages($content);

            return [
                'id'          => $id,
                'title'       => html_entity_decode(get_the_title($id)),
                'address'     => html_entity_decode($location['address']),
                'slug'        => $post->post_name,
                'description' => $content,
                'coordinates' => [
                    'lat'  => doubleval($location['lat']),
                    'long' => doubleval($location['lng']),
                ],
                'gallery'    => ContentHelper::getGutenbergImages($id),
                'topGallery' => $topGallery,
            ];
        });
    }

    public function resolveSightsImages($root, array $args): array
    {
        $seed    = $args['seed'] ?? 'default';
        $paged   = (int) ($args['page'] ?? 1);
        $perPage = (int) ($args['perPage'] ?? 20);

        if ($seed === 'default') {
            $seed = 'default_' . time();
        }

        $orderKey   = sprintf('sights:order:%s', $seed);
        $orderedIds = CacheService::getOrSet($orderKey, function () use ($seed) {
            $ids = get_posts([
                'post_type'   => 'sight',
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields'      => 'ids',
            ]);

            $hash = crc32($seed);
            srand($hash);
            shuffle($ids);
            srand();

            return $ids;
        });

        $cacheKey = sprintf('sights:%s:p%d:pp%d', $seed, $paged, $perPage);

        return CacheService::getOrSet($cacheKey, function () use ($orderedIds, $paged, $perPage) {
            $offset      = ($paged - 1) * $perPage;
            $sliced_ids  = array_slice($orderedIds, $offset, $perPage);
            $total       = count($orderedIds);
            $total_pages = ceil($total / $perPage);

            $items = [];

            foreach ($sliced_ids as $post_id) {
                $post          = get_post($post_id);
                $item_ID       = $post->ID;
                $preview_id    = get_post_thumbnail_id($item_ID);
                $preview_image = get_the_post_thumbnail_url($item_ID, 'large');

                if (!$preview_image) {
                    continue;
                }

                $blocks = parse_blocks($post->post_content);
                foreach ($blocks as $block) {
                    if ($block['blockName'] === 'core/gallery' && !empty($block['attrs']['ids'][0])) {
                        $img_id        = $block['attrs']['ids'][0];
                        $preview_id    = $img_id;
                        $preview_image = wp_get_attachment_image_url($img_id, 'large');
                        break;
                    }
                }

                $items[] = [
                    'postId'          => $item_ID,
                    'title'           => get_the_title($post),
                    'link'            => get_permalink($post),
                    'previewImageUrl' => $preview_image,
                    'author'          => ContentHelper::getAuthorByMediaId($preview_id),
                ];
            }

            return [
                'currentPage' => $paged,
                'totalPages'  => $total_pages,
                'totalPosts'  => $total,
                'posts'       => $items,
            ];
        });
    }
}

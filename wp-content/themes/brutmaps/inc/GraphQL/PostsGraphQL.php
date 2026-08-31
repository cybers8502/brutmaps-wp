<?php

namespace Brut\GraphQL;

use Brut\Services\CacheService;
use Brut\Utils\ContentHelper;
use Brut\Utils\PostHelper;
use Brut\Utils\RequestSanitizer;
use DOMException;
use GraphQL\Error\UserError;

class PostsGraphQL
{
    public function registerTypes(): void
    {
        $this->registerListTypes();
        $this->registerDetailTypes();
        $this->registerQueries();
    }

    private function registerListTypes(): void
    {
        register_graphql_object_type('BlogPostSummary', [
            'description' => 'A blog post as shown in a list.',
            'fields'      => [
                'id'         => ['type' => 'Int'],
                'slug'       => ['type' => 'String'],
                'title'      => ['type' => 'String'],
                'author'     => ['type' => 'String'],
                'thumbnail'  => ['type' => 'String'],
                'permalink'  => ['type' => 'String'],
                'categories' => ['type' => ['list_of' => 'String']],
            ],
        ]);

        register_graphql_object_type('BlogPostsPagination', [
            'fields' => [
                'currentPage' => ['type' => 'Int', 'resolve' => fn($p) => $p['current_page'] ?? null],
                'perPage'     => ['type' => 'Int', 'resolve' => fn($p) => $p['per_page'] ?? null],
                'totalPosts'  => ['type' => 'Int', 'resolve' => fn($p) => $p['total_posts'] ?? null],
                'totalPages'  => ['type' => 'Int', 'resolve' => fn($p) => $p['total_pages'] ?? null],
            ],
        ]);

        register_graphql_object_type('BlogPostsResult', [
            'description' => 'Result of the posts query.',
            'fields'      => [
                'posts'      => ['type' => ['list_of' => 'BlogPostSummary']],
                'categories' => ['type' => ['list_of' => 'String']],
                'pagination' => ['type' => 'BlogPostsPagination'],
            ],
        ]);
    }

    private function registerDetailTypes(): void
    {
        register_graphql_object_type('BlogBanner', [
            'fields' => [
                'dataBanner' => ['type' => 'String', 'resolve' => fn($b) => $b['data-banner'] ?? null],
                'html'       => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('BlogPost', [
            'description' => 'A single blog post.',
            'fields'      => [
                'id'        => ['type' => 'Int'],
                'title'     => ['type' => 'String'],
                'content'   => ['type' => 'String'],
                'banners'   => ['type' => ['list_of' => 'BlogBanner']],
                'excerpt'   => ['type' => 'String'],
                'date'      => ['type' => 'String'],
                'author'    => ['type' => 'String'],
                'thumbnail' => ['type' => 'String'],
                'permalink' => ['type' => 'String'],
                'gallery'   => ['type' => ['list_of' => 'BrutImage']],
            ],
        ]);
    }

    private function registerQueries(): void
    {
        register_graphql_field('RootQuery', 'posts', [
            'type'        => 'BlogPostsResult',
            'description' => 'Paginated blog posts, optionally filtered by category slug/ID.',
            'args'        => [
                'categories' => ['type' => ['list_of' => 'String']],
                'page'       => ['type' => 'Int'],
                'perPage'    => ['type' => 'Int'],
            ],
            'resolve'     => [$this, 'resolvePosts'],
        ]);

        register_graphql_field('RootQuery', 'post', [
            'type'        => 'BlogPost',
            'description' => 'A single blog post by ID or slug.',
            'args'        => [
                'identifier' => ['type' => ['non_null' => 'String']],
            ],
            'resolve'     => [$this, 'resolvePost'],
        ]);
    }

    public function resolvePosts($root, array $args): array
    {
        $categorySlugs = RequestSanitizer::sanitizeArray($args['categories'] ?? []);
        $page          = max((int) ($args['page'] ?? 1), 1);
        $perPage       = min((int) ($args['perPage'] ?: 10), 50);

        $queryArgs = [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $perPage,
            'paged'          => $page,
            'fields'         => 'ids',
            'tax_query'      => [],
        ];

        if (!empty($categorySlugs)) {
            $queryArgs['tax_query'][] = [
                'taxonomy' => 'category',
                'field'    => is_numeric($categorySlugs[0]) ? 'term_id' : 'slug',
                'terms'    => $categorySlugs,
            ];
        }

        $query = new \WP_Query($queryArgs);

        return [
            'posts'      => ContentHelper::getPostsByIDs($query->posts),
            'categories' => $categorySlugs,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total_posts'  => (int) $query->found_posts,
                'total_pages'  => (int) $query->max_num_pages,
            ],
        ];
    }

    /**
     * @throws DOMException
     */
    public function resolvePost($root, array $args): array
    {
        $identifier = $args['identifier'];
        $post       = is_numeric($identifier)
            ? PostHelper::getPublishedPost((int) $identifier, 'post')
            : PostHelper::getPostBySlug($identifier, 'post');

        if (!$post) {
            throw new UserError('Post not found');
        }

        $id       = $post->ID;
        $cacheKey = 'post_cache_' . $id;

        return CacheService::getOrSet($cacheKey, function () use ($id, $post) {
            $post_content = apply_filters('the_content', $post->post_content);
            $banners      = $this->extractBannersFromContent($post_content);
            $content      = ContentHelper::addAcfDataToImages($post_content);

            return [
                'id'        => $id,
                'title'     => $post->post_title,
                'content'   => $content,
                'banners'   => $banners,
                'excerpt'   => $post->post_excerpt,
                'date'      => $post->post_date,
                'author'    => get_the_author_meta('display_name', (int) $post->post_author),
                'thumbnail' => get_the_post_thumbnail_url($id, 'full'),
                'permalink' => get_permalink($id),
                'gallery'   => ContentHelper::getGutenbergImages($id),
            ];
        });
    }

    private function extractBannersFromContent(string $content): array
    {
        $banners = [];

        preg_match_all('/<div\s+[^>]*data-banner[^>]*>(.*?)<\/div>/s', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $banners[] = [
                'data-banner' => $match[1],
                'html'        => $match[0],
            ];
        }

        return $banners;
    }
}

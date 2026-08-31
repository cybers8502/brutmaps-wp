<?php

namespace Brut\Rest\Controllers;

use Brut\Services\CacheService;
use DOMDocument;
use DOMException;
use WP_REST_Request;
use Brut\Utils\ResponseHelper;
use Brut\Utils\RequestSanitizer;
use Brut\Utils\ContentHelper;
use Brut\Utils\PostHelper;

class PostsController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/blog/posts', [
            'methods' => 'GET',
            'callback' => [$this, 'getPosts'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/blog/posts/(?P<identifier>[\w-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getPostByIdOrSlug'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function getPosts(WP_REST_Request $request): \WP_Error|\WP_REST_Response
    {
        $categorySlugs = RequestSanitizer::sanitizeJsonParam($request, 'cat');

        $page     = max((int) $request->get_param('page'), 1); // за замовчуванням сторінка 1
        $perPage  = min((int) $request->get_param('per_page') ?: 10, 50); // максимум 50 на сторінку

        // Отримати ID постів, які належать до заданих категорій (по slug або ID)
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
        $ids   = $query->posts;

        return ResponseHelper::success([
            'posts'      => ContentHelper::getPostsByIDs($ids),
            'categories' => $categorySlugs,
            'pagination'  => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total_posts'  => (int) $query->found_posts,
                'total_pages'  => (int) $query->max_num_pages,
            ],
        ]);
    }

    /**
     * @throws DOMException
     */
    public function getPostByIdOrSlug($data): \WP_Error|\WP_REST_Response
    {
        $identifier = $data['identifier'];
        $post = is_numeric($identifier)
            ? PostHelper::getPublishedPost((int) $identifier, 'post')
            : PostHelper::getPostBySlug($identifier, 'post');

        if (!$post) {
            return ResponseHelper::notFound('Post not found');
        }

        $id = $post->ID;

        $cacheKey = 'post_cache_' . $id;

        $response = CacheService::getOrSet($cacheKey, function () use ($id, $post) {
            $post_content = apply_filters('the_content', $post->post_content);
            $banners = $this->extractBannersFromContent($post_content);
            $content = ContentHelper::addAcfDataToImages($post_content);

            return [
                'id'            => $id,
                'title'         => $post->post_title,
                'content'       => $content,
                'banners'       => $banners,
                'excerpt'       => $post->post_excerpt,
                'date'          => $post->post_date,
                'author'        => get_the_author_meta('display_name', (int) $post->post_author),
                'thumbnail'     => get_the_post_thumbnail_url($id, 'full'),
                'permalink'     => get_permalink($id),
                'gallery'       => ContentHelper::getGutenbergImages($id)
            ];
        });

        return ResponseHelper::success($response);
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

<?php

namespace Brut\Rest\Controllers;

use WP_REST_Request;
use Brut\Utils\ResponseHelper;
use Brut\Utils\RequestSanitizer;
use Brut\Utils\ContentHelper;
use Brut\Utils\PostHelper;

class PostsController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/posts', [
            'methods' => 'GET',
            'callback' => [$this, 'getPosts'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(BASE_URL, '/posts/(?P<identifier>[\w-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getPostByIdOrSlug'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function getPosts(WP_REST_Request $request): \WP_REST_Response
    {
        $categories = RequestSanitizer::sanitizeJsonParam($request, 'cat');

        $ids = ContentHelper::getFilteredPostsIDbyCategories('post', $categories);

        return ResponseHelper::success([
            'posts' => ContentHelper::getPostsByIDs($ids),
            'categories' => $categories,
        ]);
    }

    public function getPostByIdOrSlug($data): \WP_Error|\WP_REST_Response
    {
        $identifier = $data['identifier'];
        $post = is_numeric($identifier)
            ? PostHelper::getPublishedPost((int) $identifier, 'post')
            : PostHelper::getPostBySlug($identifier, 'post');

        if (!$post) {
            return ResponseHelper::notFound('Post not found');
        }

        $content = apply_filters('the_content', $post->post_content);
        $banners = $this->extractBannersFromContent($content);

        return ResponseHelper::success([
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $content,
            'banners' => $banners,
            'excerpt' => $post->post_excerpt,
            'date' => $post->post_date,
            'author' => get_the_author_meta('display_name', $post->post_author),
            'thumbnail' => get_the_post_thumbnail_url($post->ID, 'full'),
            'permalink' => get_permalink($post->ID),
        ]);
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

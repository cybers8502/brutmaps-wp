<?php

namespace Brut\Utils;

class PostHelper
{
    public static function getPostBySlug(string $slug, string $type = 'post'): ?\WP_Post
    {
        $posts = get_posts([
            'name'        => sanitize_title($slug),
            'post_type'   => $type,
            'post_status' => 'publish',
            'numberposts' => 1,
        ]);
        return $posts[0] ?? null;
    }

    public static function getPublishedPost(int $id, string $type = 'post'): ?\WP_Post
    {
        $post = get_post($id);
        if ($post && $post->post_status === 'publish' && $post->post_type === $type) {
            return $post;
        }
        return null;
    }

    public static function isValidTypeAndStatus(?\WP_Post $post, string $type): bool
    {
        return $post && $post->post_type === $type && $post->post_status === 'publish';
    }
}

<?php

namespace Brut\Routing;

class PermalinkService
{
    public function __construct()
    {
        add_action('init', [$this, 'setPermastruct']);
        add_filter('rewrite_rules_array', [$this, 'addRewriteRules']);
        add_filter('post_link', [$this, 'customPostLink'], 10, 2);
        add_filter('rank_math/sitemap/xml_post_url', [$this, 'fixSitemapEntryUrl'], 10, 2);
    }

    public function setPermastruct(): void
    {
        global $wp_rewrite;
        $wp_rewrite->add_permastruct('post', '/blog/%postname%/', false);
    }

    public function addRewriteRules(array $rules): array
    {
        $new_rules = [
            'blog/([^/]+)$' => 'index.php?post_type=post&name=$matches[1]',
        ];
        return $new_rules + $rules;
    }

    public function customPostLink(string $permalink, $post): string
    {
        if ($post instanceof \WP_Post && $post->post_type === 'post') {
            return home_url('/blog/' . $post->post_name . '/');
        }
        return $permalink;
    }
}

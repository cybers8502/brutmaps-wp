<?php

namespace Brut\SEO;

class SitemapService
{
    public function __construct()
    {
        add_filter('rank_math/sitemap/xml_post_url', [$this, 'fixSitemapEntryUrl'], 10, 2);
    }

    public function fixSitemapEntryUrl($url, $post)
    {
        if (isset($post->post_type) && 'post' === $post->post_type) {
            $url = home_url('/blog/' . $post->post_name . '/');
        }
        return $url;
    }
}

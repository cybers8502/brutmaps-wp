<?php
function custom_post_permalink_structure() {
    global $wp_rewrite;

    $wp_rewrite->add_permastruct('post', '/blog/%postname%/', false);
}

add_action('init', 'custom_post_permalink_structure');

function custom_post_rewrite_rules($rules) {
    $new_rules = array();

    $new_rules['blog/([^/]+)$'] = 'index.php?post_type=post&name=$matches[1]';

    return $new_rules + $rules;
}

add_filter('rewrite_rules_array', 'custom_post_rewrite_rules');

function custom_post_permalink($permalink, $post) {
    if ($post->post_type == 'post') {
        $permalink = home_url('/blog/' . $post->post_name . '/');
    }

    return $permalink;
}

add_filter('post_link', 'custom_post_permalink', 10, 2);

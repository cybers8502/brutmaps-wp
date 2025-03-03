<?php

// - GET: /posts
function API_GET_POSTS( $request ) {
    $categories = $request->get_param('cat');

    if ($categories && is_string($categories)) {
        $categories = json_decode($categories, true);
    }

    if (!is_array($categories)) {
        $categories = [];
    }

    $ids = getFilterdPostsIDbyCategories('post', $categories);

    $mainData = [
        'posts' => getPostsByIDs($ids),
        'categories' => $categories
    ];

    return successResponse($mainData);
}

// - GET /posts/$id
function API_GET_POST_BY_ID( $data ) {
    $identifier = $data['identifier'];

    if (is_numeric($identifier)) {
        $postID = intval($identifier);
        $post = get_post($postID);
    } else {
        $slug = sanitize_title($identifier);
        $args = [
            'name'        => $slug,
            'post_type'   => 'post',
            'post_status' => 'publish',
            'numberposts' => 1
        ];
        $posts = get_posts($args);
        $post = !empty($posts) && isset($posts[0]) ? $posts[0] : null;
        $postID = $post ? $post->ID : null;
    }

    if (!is_null($post) && $post->post_status === 'publish' && $post->post_type == 'post') {
        $content = apply_filters('the_content', $post->post_content);
        $banners = extract_banners_from_content($content);

        $mainData = [
            'id'            => $postID,
            'title'         => $post->post_title,
            'content'       => $content,
            'banners'       => $banners,
            'excerpt'       => $post->post_excerpt,
            'date'          => $post->post_date,
            'author'        => get_the_author_meta('display_name', $post->post_author),
            'thumbnail'     => get_the_post_thumbnail_url($post->ID, 'full'),
            'permalink'     => get_permalink($post->ID),
        ];
    } else {
        return failureResponse('Sight does not exist');
    }
    return successResponse($mainData);
}

// - HELPER FUNCTIONS
function getPosts($type) {
    $args = array(
        'numberposts'   => -1,
        'post_type'		=> $type,
        'orderby' 		=> 'title',
        'order' 		=> 'ASC',
        'fields'        => 'ids',
        'post_status' => array('publish')
    );
    return get_posts($args);
}

function extract_banners_from_content($content) {
    $banners = [];

    $pattern = '/<div\s+[^>]*data-banner[^>]*>(.*?)<\/div>/s';

    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $banners[] = [
            'data-banner' => $match[1],
            'html'        => $match[0],
            'content'     => $match[2]
        ];
    }

    return $banners;
}

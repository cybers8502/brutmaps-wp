<?php

function getSmartImage($imageID, $authorID)
{
    $size = wp_get_attachment_image_src( $imageID, 'full' );
    if (!$size || count($size) < 3) {
        return null;
    }
    return [
        'source' => $size[0],
        'width' => $size[1],
        'height' => $size[2],
        'author' => getAuthorData($authorID)
    ];
}

function getAuthorData($authorID)
{
    if (!$authorID) {
        return null;
    }
    $firstName = get_field('first_name_author', $authorID);
    $lastName = get_field('second_name_author', $authorID);
    $link = get_field('link', $authorID);
    $instagram = get_field('instagram', $authorID);
    return [
        'first_name_author' => $firstName,
        'second_name_author' => $lastName,
        'link' => $link,
        'instagram' => $instagram,
    ];
}

function getImageWithSizes($imageObject)
{
    $images = [
        'image_full' => PLACEHOLDER,
        'image_small' => PLACEHOLDER,
        'image_medium' => PLACEHOLDER
    ];

    if (is_array($imageObject) && isset($imageObject['url']) && isset($imageObject['sizes'])) {
        $images['image_full'] = $imageObject['url'];

        if (isset($imageObject['sizes']['thumbnail'])) {
            $images['image_small'] = $imageObject['sizes']['thumbnail'];
        }

        if (isset($imageObject['sizes']['medium'])) {
            $images['image_medium'] = $imageObject['sizes']['medium'];
        }
    }

    return $images;
}

function getSightImageURLs($sightID)
{
    $galleryField = get_field('gallery', $sightID);
    $imageObject = get_field('main_image', $sightID);
    $galleryURLs = [];

    if ($imageObject) {
        $galleryURLs[] = $imageObject['url'];
    }

    if ($galleryField && is_array($galleryField)) {
        foreach ($galleryField as $item) {
            $galleryURLs[] = wp_get_attachment_image_src( $item["gallery_image"], 'full' )[0];
        }
    }

    if (empty($galleryURLs)) {
        $galleryURLs[] = PLACEHOLDER;
    }

    return $galleryURLs;
}


function getCreatorsSmallDataByIDs($IDs)
{
    $architects = [];
    if (is_array($IDs) && count($IDs) > 0) {
        foreach ($IDs as $architectID) {
            $architect = get_post($architectID);
            if ($architect->post_type !== 'architect' || $architect->post_status !== 'publish') {
                continue;
            }
            $imageObject = get_field('main_image', $architectID);
            $images = getImageWithSizes($imageObject);
            $author = get_field('main_image_author_id', $architectID);
            $architects[] = [
                'id' => intval($architectID),
                'first_name' => html_entity_decode(get_field('first_name', $architectID)),
                'last_name' => html_entity_decode(get_field('last_name', $architectID)),
                'name' => html_entity_decode(get_the_title($architectID)),
                'image' => $images,
                'main_image_author' => getAuthorData($author),
                'description' => html_entity_decode(get_field('description', $architectID))
            ];
        }
    }
    return $architects;
}

function getSightsGeoJSONByIDs($IDs)
{
    $features = [];

    foreach ($IDs as $sightID) {
        $sight = get_post($sightID);

        $location = get_field('location', $sightID);
        if (!$location || !isset($location['lat']) || !isset($location['lng'])) {
            continue;
        }

        $features[] = [
            'type' => 'Feature',
            'id' => intval($sightID),
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    doubleval($location['lng']),
                    doubleval($location['lat']),
                ],
            ],
            'properties' => [
                'id' => intval($sightID),
                'slug' => $sight->post_name,
                'title' => html_entity_decode(get_the_title($sightID)),
                'address' => html_entity_decode($location['address']),
                'year' => intval(get_field('established', $sightID)),
                'images' => getSightImageURLs($sightID),
            ],
        ];
    }

    return [
        'type' => 'FeatureCollection',
        'features' => $features,
    ];
}

function getFilterdPostsIDbyCategories($type, $categories = [])
{
    $args = [
        'post_type' => $type,
        'post_status' => 'publish',
        'numberposts' => -1
    ];

    // Добавляем фильтрацию по категориям, если они переданы
    if (!empty($categories)) {
        $args['category_name'] = implode(',', $categories); // Используем WP Query для фильтрации по именам категорий
    }

    // Получаем посты по параметрам
    $posts = get_posts($args);

    // Возвращаем их ID
    return wp_list_pluck($posts, 'ID');
}

function getPostsByIDs($IDs)
{
    $result = [];
    foreach ($IDs as $id) {
        $post = get_post($id);

        if ($post) {
            $categories = get_the_category($post->ID);
            $category_names = [];

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $category_names[] = $category->name;
                }
            }

            $result[] = [
                'id' => $post->ID,
                'slug' => $post->post_name,
                'title' => $post->post_title,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'thumbnail' => get_the_post_thumbnail_url($post->ID, 'full'),
                'permalink' => get_permalink($post->ID),
                'categories' => $category_names // Add categories to the result
            ];
        }
    }
    return $result;
}

function failureResponse($message)
{
    $mainWrap = array(
        'done'    => false,
        'message' => $message
    );
    $statusCode = 422;
    $response = new WP_REST_Response($mainWrap);
    $response->set_status($statusCode);
    $response->header( 'Content-type', 'application/json; charset=utf-8' );
    return $response;
}

function successResponse($data)
{
    $mainWrap = array(
        'done' => true,
        'data' => $data
    );
    $statusCode = 200;
    $response = new WP_REST_Response($mainWrap);
    $response->set_status($statusCode);
    $response->header( 'Content-type', 'application/json; charset=utf-8' );
    return $response;
}


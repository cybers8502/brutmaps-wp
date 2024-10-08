<?php

function getSmartImage($imageID, $authorID) {
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

function getAuthorData($authorID) {
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

function getImageWithSizes($imageObject) {
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

function getCreatorsSmallDataByIDs($IDs) {
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

function getSightsSmallDataByIDs($IDs) {
    $result = [];
    foreach ($IDs as $sightID) {
        $sight = get_post($sightID);

        $architectsIDs = get_field('choose_architects', $sightID);
        if (!$architectsIDs) {
            $architectsIDs = [];
        }
        $location = get_field('location', $sightID);
        $imageObject = get_field('main_image', $sightID);
        $result[] = [
            'id' => $sightID,
            'slug' => $sight->post_name,
            'link' => get_permalink($sightID),
            'title' => html_entity_decode(get_the_title($sightID)),
            'architects' => $architectsIDs,
            'coordinates' => [
                'lat' => doubleval($location['lat']),
                'long' => doubleval($location['lng'])
            ],
            'address' => html_entity_decode($location['address']),
            'year' => intval(get_field('established', $sightID)),
            'images' => getImageWithSizes($imageObject)
        ];
    }
    return $result;
}

function getPostsByIDs($IDs) {
    $result = [];
    foreach ($IDs as $id) {
        $post = get_post($id);

        if ($post) {
            $result[] = [
                'id' => $post->ID,
                'slug' => $post->post_name,
                'title' => $post->post_title,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'thumbnail' => get_the_post_thumbnail_url($post->ID, 'full'),
                'permalink' => get_permalink($post->ID),
            ];
        }
    }
    return $result;
}

function failureResponse($message) {
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

function successResponse($data) {
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


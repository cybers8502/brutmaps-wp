<?php

function getSmartImage($imageID, $authorID) {
    $size = wp_get_attachment_image_src( $imageID, 'full' );
    if (!$size || count($size) < 3) {
        return null;
    }
    $result = [];
    $result['source'] = $size[0];
    $result['width'] = $size[1];
    $result['height'] = $size[2];
    $result['author'] = getAuthorData($authorID);
    return $result;
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
    if (is_null($imageObject) || !$imageObject) {
        $images = [
            'image_full' => PLACEHOLDER,
            'image_small' => PLACEHOLDER,
            'image_medium' => PLACEHOLDER
        ];
    } else {
        $images = [
            'image_full' => $imageObject['url'],
            'image_small' => $imageObject['sizes']['thumbnail'],
            'image_medium' => $imageObject['sizes']['medium']
        ];
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
            $item = [];
            $item['id'] = intval($architectID);
            $item['first_name'] = html_entity_decode(get_field('first_name', $architectID));
            $item['last_name'] = html_entity_decode(get_field('last_name', $architectID));
            $item['name'] = html_entity_decode(get_the_title($architectID));
            $item['image'] = $images;
            $item['main_image_author'] = getAuthorData($author);
            $item['description'] = html_entity_decode(get_field('description', $architectID));
            $architects[] = $item;
        }
    }
    return $architects;
}

function getSightsSmallDataByIDs($IDs) {
    $result = [];
    foreach ($IDs as $sightID) {
        $architectsIDs = get_field('choose_architects', $sightID);
        if (!$architectsIDs) {
            $architectsIDs = [];
        }
        $location = get_field('location', $sightID);
        $item = [];
        $item['id'] = $sightID;
        $item['link'] = get_permalink($sightID);
        $item['title'] = html_entity_decode(get_the_title($sightID));
        $item['architects'] = $architectsIDs;
        $item['coordinates'] = [
            'lat' => doubleval($location['lat']),
            'long' => doubleval($location['lng'])
        ];
        $item['address'] = html_entity_decode($location['address']);
        $item['year'] = intval(get_field('established', $sightID));
        $imageObject = get_field('main_image', $sightID);
        $images = getImageWithSizes($imageObject);
        $item['images'] = $images;
        $result[] = $item;
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

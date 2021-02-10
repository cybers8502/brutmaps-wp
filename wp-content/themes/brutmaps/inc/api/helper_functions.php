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

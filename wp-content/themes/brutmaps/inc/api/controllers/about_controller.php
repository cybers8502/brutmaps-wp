<?php

//TODO delete routings

// - GET /about
function API_GET_ABOUT_DATA() {
    $mainWrap = ['done' => true];
    $galleryField = get_field('gallery', 'options');
    $gallery = [];
    foreach ($galleryField as $item) {
        $image = $item['gallery_image'];
        $authorID = $item['gallery_image_author_id'];
        if ($image) {
            $newImage = getSmartImage($image, $authorID);
            if (!is_null($newImage)) {
                $gallery[] = $newImage;
            }
        }
    }
    $mainImage = get_field('main_image', 'options');
    if (!$mainImage) {
        $mainImage = null;
    }
    $mainImageAuthor = get_field('main_image_author', 'options');
    $instagram = get_field('instagram', 'options');
    $facebook = get_field('facebook', 'options');
    $mainData = [
        'title'             => html_entity_decode(get_field('title', 'options')),
        'main_image'        => $mainImage,
        'description_1'     => html_entity_decode(get_field('description_1', 'options')),
        'gallery_sub_text'  => html_entity_decode(get_field('gallery_sub_text', 'options')),
        'description_2'     => html_entity_decode(get_field('description_2', 'options')),
        'gallery'           => $gallery,
        'main_image_author' => getAuthorData($mainImageAuthor),
        'instagram'         => $instagram,
        'facebook'          => $facebook
    ];
    return successResponse($mainData);
}

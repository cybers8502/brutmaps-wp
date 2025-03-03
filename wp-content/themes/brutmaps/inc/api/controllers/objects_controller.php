<?php

// - GET: /sights
function API_GET_SIGHTS() {
    $ids = getPosts('sight');
    $lat = 40.6971494;
    $long = -74.2598626;
    $address = get_field('initial_center_for_users', 'options');
    if ($address) {
        $lat = doubleval($address['lat']);
        $long = doubleval($address['lng']);
    }
    $mainData = [
        'featureCollection' => getSightsGeoJSONByIDs($ids),
        'settings' => [
            'default_center' => [
                'coordinates' => [
                    'lat' => $lat,
                    'long' => $long
                ]
            ]
        ]
    ];

    return successResponse($mainData);
}

// - GET: /sights/$id
function API_GET_SIGHT_BY_ID( $data ) {
    $identifier = $data['identifier'];

    if (is_numeric($identifier)) {
        $sightID = intval($identifier);
        $sight = get_post($sightID);
    } else {
        $slug = sanitize_title($identifier);
        $args = [
            'name'        => $slug,
            'post_type'   => 'sight',
            'post_status' => 'publish',
            'numberposts' => 1
        ];
        $sights = get_posts($args);
        $sight = !empty($sights) && isset($sights[0]) ? $sights[0] : null;
        $sightID = $sight ? $sight->ID : null;
    }

    if (!is_null($sight) && $sight->post_status === 'publish' && $sight->post_type == 'sight') {
        $location = get_field('location', $sightID);
        $galleryField = get_field('gallery', $sightID);
        $gallery = [];
        if ($galleryField) {
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
        }
        $imageObject = get_field('main_image', $sightID);
        if (is_null($imageObject) || is_null($imageObject['url'])) {
            $mainImage = PLACEHOLDER;
        } else {
            $mainImage = $imageObject['url'];
        }
        $architectsIDs = get_field('choose_architects', $sightID);
        $associatedPeopleIDs = get_field('choose_associated_people', $sightID);
        $architects = getCreatorsSmallDataByIDs($architectsIDs);
        $associatedPeople = getCreatorsSmallDataByIDs($associatedPeopleIDs);
        $author = get_field('main_image_author_id', $sightID);
        $authorName = get_field('main_image_author_name', $sightID);
        $mainData = [
            'id'            => $sightID,
            'main_data'     => [
                'title'     => html_entity_decode(get_the_title($sightID)),
                'sub_title' => html_entity_decode($location['address']),
                'image'     => html_entity_decode($mainImage),
                'main_image_author' => getAuthorData($author),
                'main_image_author_name' => $authorName
            ],
            'architects'           => $architects,
            'associated_people'    => $associatedPeople,
            'year'          => intval(get_field('established', $sightID)),
            'slug'          => $sight->post_name,
            'description'   => html_entity_decode(get_field('main_content', $sightID)),
            'image_gallery' => $gallery,
            'extra_data'    => html_entity_decode(get_field('source', $sightID)),
            'coordinates'   => [
                'lat' => doubleval($location['lat']),
                'long' => doubleval($location['lng'])
            ]
        ];
//        $sights = getSightsSmallDataByIDs([$sightID]);
//        $mergedData = array_merge($mainData, $sights[0]);
    } else {
        return failureResponse('Sight does not exist');
    }
    return successResponse($mainData);
}

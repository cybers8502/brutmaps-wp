<?php

function API_GET_SIGHTS() {
    $ids = getSights();
    $mainWrap = ['done' => true];
    $lat = 40.6971494;
    $long = -74.2598626;
    $address = get_field('initial_center_for_users', 'options');
    if ($address) {
        $lat = doubleval($address['lat']);
        $long = doubleval($address['lng']);
    }
    $mainData = [
        'sights' => [],
        'settings' => [
            'default_center' => [
                'coordinates' => [
                    'lat' => $lat,
                    'long' => $long
                ]
            ]
        ]
    ];
    $result = [];
    foreach ($ids as $sightID) {
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
        $item['images'] = $images;
        $result[] = $item;
    }
    $mainData['sights'] = $result;
    $mainWrap['data'] = $mainData;
    $response = new WP_REST_Response( $mainWrap );
    $response->set_status( 200 );
    $response->header( 'Content-type', 'application/json; charset=utf-8' );
    return $response;
}

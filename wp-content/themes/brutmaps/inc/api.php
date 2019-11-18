<?php 
//Custom API requests

add_action( 'rest_api_init', function () {
    register_rest_route( 'brutmaps/data/v1/api', '/sights', array(
      'methods' => 'GET',
      'callback' => 'get_all_sights',
    ) );
  } );

function getSights() {
    $args = array( 
        'numberposts'		=> -1,
        'post_type'		=> 'sight',
        'orderby' 		=> 'title',
        'order' 		=> 'ASC',
        'fields'        => 'ids'
      );
    return get_posts($args);
}

function get_all_sights( $data ) {
    $ids = getSights();
    $mainWrap = ['done' => true];
    $mainData = [
        'sights' => [],
        'settings' => [
            'default_center' => [
                'coordinates' => [
                    'lat' => 40.6971494,
                    'long' => -74.2598626
                ]
            ]
        ]
    ];
    $result = [];
    foreach ($ids as $sightID) {
        $location = get_field('location', $sightID);
        $item = [];
        $item['id'] = $sightID;
        $item['title'] = get_the_title($sightID);
        $item['coordinates'] = [
            'lat' => $location['lat'],
            'long' => $location['lng']
        ];
        $item['address'] = $location['address'];
        $item['year'] = get_field('established', $sightID);
        $imageObject = get_field('main_image', $sightID);
        $images = [
            'image_full' => $imageObject['url'],
            'image_small' => $imageObject['sizes']['thumbnail'],
            'image_medium' => $imageObject['sizes']['medium']
        ];
        $result[] = $item;
    }
    $mainData['sights'] = $result;
    $mainWrap['data'] = $mainData;
    return json_encode($mainWrap);
}
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
        $location = get_field('location');
        $item = [];
        $item['id'] = $sightID;
        $item['title'] = get_the_title($sightID);
        $item['coordinates'] = $location;
        $item['address'] = $location;
        $item['year'] = get_field('established');
        $imageObject = get_field('main_image');
        $images = [
            'image_full' => '',
            'image_small' => '',
            'image_medium' => ''
        ];
        $item['images'] = $imageObject;
        $result[] = $item;
    }
    $mainData['sights'] = $result;
    $mainWrap['data'] = $mainData;
    return json_encode($mainWrap);
}
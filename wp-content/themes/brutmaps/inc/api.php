<?php 
//Custom API requests

define('BASE_URL', 'brutmaps/data/v1/api/');

add_action( 'rest_api_init', function () {
    register_rest_route( BASE_URL, '/sights', array(
      'methods' => 'GET',
      'callback' => 'API_GET_SIGHTS',
    ) );
	register_rest_route( BASE_URL, '/sights/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'getAllSights',
	) );
} );

function API_GET_SIGHTS( $data ) {
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
            'lat' => doubleval($location['lat']),
            'long' => doubleval($location['lng'])
        ];
        $item['address'] = $location['address'];
        $item['year'] = intval(get_field('established', $sightID));
        $imageObject = get_field('main_image', $sightID);
        if (is_null($imageObject)) {
        	$testImage = 'https://brutmaps.designstudio.ag/wp-content/uploads/2019/11/brutalist-architecture-7-1024x567.jpg';
	        $images = [
		        'image_full' => $testImage,
		        'image_small' => $testImage,
		        'image_medium' => $testImage
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
    $response->header( 'Content-type', 'application/json' );
    return $response;
}

function getSights() {
	$args = array(
		'numberposts'   => -1,
		'post_type'		=> 'sight',
		'orderby' 		=> 'title',
		'order' 		=> 'ASC',
		'fields'        => 'ids',
		'post_status'   => 'publish'
	);
	return get_posts($args);
}
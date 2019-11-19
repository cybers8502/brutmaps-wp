<?php 
//Custom API requests

add_action( 'rest_api_init', function () {
    register_rest_route( 'brutmaps/data/v1/api', '/sights', array(
      'methods' => 'GET',
      'callback' => 'getAllSights',
    ) );
} );

function getSights() {
    $args = array( 
        'numberposts'   => -1,
        'post_type'		=> 'sight',
        'orderby' 		=> 'title',
        'order' 		=> 'ASC',
        'fields'        => 'ids',
	    'post_status'   => 'draft'
      );
    return get_posts($args);
}

function getAllSights( $data ) {
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

function createFakeSight($inputData) {
	$data = array(
		'post_type'     => 'sight',
		'post_status'   => 'draft',
		'post_title'    => $inputData['post_title'],
	);
	$newID = wp_insert_post($data);
	if (is_int($newID)) {
		update_field('field_5dce8b9776ae7', $inputData['year'], $newID);
		update_field('field_5dce8c6f76aed', $inputData['address'], $newID);
	}
}

function createFakeSights() {
	echo get_template_directory_uri();
	$str = file_get_contents(get_template_directory_uri().'/inc/BrutData.json');
	$json = json_decode($str, true);
	$collection = array_filter($json, function($obj)
	{
		static $idList = array();
		if(in_array($obj['id'], $idList)) {
			return false;
		}
		$idList[]= $obj['id'];
		return true;
	});
	//Only 1 item for test
	$output = array_slice($collection, 0, 1);
	$GOOGLE_KEY = '***REMOVED-GOOGLE-API-KEY***';
	foreach ($output as $item) {
		$geolocation = $item['lat'].','.$item['lng'];
		$request = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$geolocation.'&sensor=false&key='.$GOOGLE_KEY.'';
		$file_contents = file_get_contents($request);
		$addressString = json_decode($file_contents)->results[0]->formatted_address;
		echo $addressString;
		$address = [
			'lng' => $item['lng'],
			'lat' => $item['lat'],
			'address' => $addressString
		];
		$data = array(
			'year'          => intval($item['ende_jahr']),
			'post_title'    => $item['titel'],
			'address'       => $address
		);
		createFakeSight($data);
	}
}
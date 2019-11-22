<?php 
//Custom API requests

define('BASE_URL', 'brutmaps/data/v1/api/');
define('PLACEHOLDER', 'https://brutmaps.designstudio.ag/wp-content/uploads/2019/11/brutalist-architecture-7-1024x567.jpg');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

add_action( 'rest_api_init', function () {
    register_rest_route( BASE_URL, '/sights', array(
      'methods' => 'GET',
      'callback' => 'API_GET_SIGHTS',
    ) );
	register_rest_route( BASE_URL, '/sights/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'API_GET_SIGHT_BY_ID',
	) );
	register_rest_route( BASE_URL, '/submit_sight', array(
		'methods' => 'POST',
		'callback' => 'API_POST_SIGHT',
	) );
	register_rest_route( BASE_URL, '/about', array(
		'methods' => 'GET',
		'callback' => 'API_GET_ABOUT_DATA',
	) );
} );

function API_GET_ABOUT_DATA() {
	$status = 200;
	$mainWrap = ['done' => true];
	if ( 'publish' == get_post_status ( ABOUT ) ) {
		$galleryField = get_field('gallery', ABOUT);
		$gallery = [];
		foreach ($galleryField as $item) {
			$image = $item['gallery_image'];
			if (!is_null($image)) {
				$gallery[] = $image;
			}
		}
		$mainData = [
			'title'     => get_the_title(ABOUT),
			'main_image'     => get_field('main_image', ABOUT),
			'description_1'     => get_field('description_1', ABOUT),
			'gallery_sub_text'     => get_field('gallery_sub_text', ABOUT),
			'description_2'     => get_field('description_2', ABOUT),
			'gallery'     => $gallery,
		];
		$mainWrap['data'] = $mainData;
	} else {
		$mainWrap['false'] = false;
		$mainWrap['message'] = 'Something went wrong';
	}
	$response = new WP_REST_Response( $mainWrap );
	$response->set_status( 200 );
	$response->header( 'Content-type', 'application/json' );
	return $response;
}

function API_GET_SIGHTS() {
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
    $response->header( 'Content-type', 'application/json' );
    return $response;
}

function API_GET_SIGHT_BY_ID( $data ) {
	$mainWrap = ['done' => true];
	$sightID = intval($data['id']);
	$sight = get_post($sightID);
	$statusCode = 200;
	if (!is_null($sight) && $sight->post_status === 'publish' && $sight->post_type == 'sight') {
		$location = get_field('location', $sightID);
		$galleryField = get_field('gallery', $sightID);
		$gallery = [];
		foreach ($galleryField as $item) {
			$image = $item['gallery_image'];
			if (!is_null($image)) {
				$gallery[] = $image;
			}
		}
		$imageObject = get_field('main_image', $sightID);
		if (is_null($imageObject) || is_null($imageObject['url'])) {
			$mainImage = PLACEHOLDER;
		} else {
			$mainImage = $imageObject['url'];
		}
		$architectsIDs = get_field('choose_architects', $sightID);
		$architects = [];
		if (is_array($architectsIDs) && count($architectsIDs) > 0) {
			foreach ($architectsIDs as $architectID) {
				$item = [];
				$item['id'] = intval($architectID);
				$item['first_name'] = get_field('first_name', $architectID);
				$item['last_name'] = get_field('last_name', $architectID);
				$architects[] = $item;
			}
		}
		$mainData = [
			'id'            => $sightID,
			'main_data'     => [
				'title'     => get_the_title($sightID),
				'sub_title' => $location['address'],
				'image'     => $mainImage
			],
			'architects'    => $architects,
			'year'          => intval(get_field('established', $sightID)),
			'description'   => get_field('main_content', $sightID),
			'image_gallery' => $gallery,
			'extra_data'    => get_field('source', $sightID),
			'coordinates'   => [
				'lat' => doubleval($location['lat']),
				'long' => doubleval($location['lng'])
			]
		];
		$mainWrap['data'] = $mainData;
	} else {
		$mainWrap['done'] = false;
		$mainWrap['message'] = 'Sight does not exist';
		$statusCode = 422;
	}
	$response = new WP_REST_Response( $mainWrap );
	$response->set_status( $statusCode );
	$response->header( 'Content-type', 'application/json' );
	return $response;
}

function API_POST_SIGHT ( $data ) {
	$name = $data['name'];
	$email = $data['email'];
	$link = $data['link'];
	$statusCode = 200;
	$description = $data['description'];
	if (is_null($name)) {
		$name = "";
	}
	if (is_null($email)) {
		$email = "";
	}
	if (is_null($link)) {
		$link = "";
	}
	$args = [
		'post_title'    => 'New Sight',
		'post_type'     => 'sight',
		'post_status'   => 'pending'
	];
	$sightID = wp_insert_post($args);
	$imageNames = ['image_1', 'image_2', 'image_3', 'image_4', 'image_5'];
	$imagesIDs = [];
	foreach ($imageNames as $imageName) {
		if (!is_null($_FILES[$imageName])) {
			$uploadData = uploadFile($_FILES[$imageName]);
			if ( !isset( $upload['error'] ) ){
				$imagesIDs[$imageName] = $uploadData;
			}
		}
	}
	if (!is_null($sightID)) {
		update_field('main_content', $description, $sightID);
		foreach ($imagesIDs as $imageID) {
			$row = array(
				'gallery_image' => $imageID
			);
			add_row('gallery', $row, $sightID);
		}
		notifyAboutNewSight();
	}
	$output = [
		'done' => true,
		'message' => null
	];
	$response = new WP_REST_Response( $output );
	$response->set_status( $statusCode );
	$response->header( 'Content-type', 'application/json' );
	return $response;
}

function uploadFile($file) {
	$mimes = array(
		'bmp'  => 'image/bmp',
		'gif'  => 'image/gif',
		'jpe'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg'  => 'image/jpeg',
		'png'  => 'image/png',
		'tif'  => 'image/tiff',
		'tiff' => 'image/tiff'
	);
	$overrides = array(
		'mimes'     => $mimes,
		'test_form' => false
	);
	$upload = wp_handle_upload( $file, $overrides );
	$attachment = array(
		"guid" => $upload['file'],
		"post_mime_type" => $upload['type'],
		"post_title" => $_POST['who'],
		"post_content" => "",
		"post_status" => "draft",
		"post_author" => 1
	);
	$id = wp_insert_attachment( $attachment, $upload['file'],0);
	$attach_data = wp_generate_attachment_metadata( $id, $upload['file'] );
	wp_update_attachment_metadata( $id, $attach_data );
	return $id;
}

function getSights() {
	$args = array(
		'numberposts'   => -1,
		'post_type'		=> 'sight',
		'orderby' 		=> 'title',
		'order' 		=> 'ASC',
		'fields'        => 'ids',
		'post_status' => array('publish')
	);
	return get_posts($args);
}

function notifyAboutNewSight() {
	$emails = get_field('notifiedUsers', 'options');
	if (is_array($emails) && count($emails) > 0) {
		$emailsArray = [];
		foreach ($emails as $item) {
			$emailsArray[] = $item['email'];
		}
		$to = $emailsArray;
		$subject = 'New sight offer on BRUTMAPS';
		$body = 'We have a new offered sight';
		$headers = array('Content-Type: text/html; charset=UTF-8');
		wp_mail( $to, $subject, $body, $headers );
	}
}
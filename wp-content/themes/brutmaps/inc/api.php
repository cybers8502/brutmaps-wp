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
			$authorID = $item['gallery_image_author_id'];
			if ($image) {
				$newImage = getSmartImage($image, $authorID);
				if (!is_null($newImage)) {
					$gallery[] = $newImage;
				}
			}
		}
		$mainImage = get_field('main_image', ABOUT);
		if (!$mainImage) {
			$mainImage = null;
		}
		$mainImageAuthor = get_field('main_image_author', ABOUT);
		$mainData = [
			'title'             => html_entity_decode(get_the_title(ABOUT)),
			'main_image'        => $mainImage,
			'description_1'     => html_entity_decode(get_field('description_1', ABOUT)),
			'gallery_sub_text'  => html_entity_decode(get_field('gallery_sub_text', ABOUT)),
			'description_2'     => html_entity_decode(get_field('description_2', ABOUT)),
			'gallery'           => $gallery,
			'main_image_author' => getAuthorData($mainImageAuthor)
		];
		$mainWrap['data'] = $mainData;
	} else {
		$mainWrap['false'] = false;
		$mainWrap['message'] = 'Something went wrong';
	}
	$response = new WP_REST_Response( $mainWrap );
	$response->set_status( 200 );
	$response->header( 'Content-type', 'application/json; charset=utf-8' );
	return $response;
}

function API_GET_SIGHTS() {
	$ids = getSights();
	$mainWrap = ['done' => true];
	$lat = 40.6971494;
	$long = -74.2598626;
	$address = html_entity_decode(get_field('initial_center_for_users', 'options'));
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
		$location = get_field('location', $sightID);
		$item = [];
		$item['id'] = $sightID;
		$item['link'] = get_permalink($sightID);
		$item['title'] = html_entity_decode(get_the_title($sightID));
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
			$authorID = $item['gallery_image_author_id'];
			if ($image) {
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
		$architects = [];
		if (is_array($architectsIDs) && count($architectsIDs) > 0) {
			foreach ($architectsIDs as $architectID) {
				$item = [];
				$item['id'] = intval($architectID);
				$item['first_name'] = html_entity_decode(get_field('first_name', $architectID));
				$item['last_name'] = html_entity_decode(get_field('last_name', $architectID));
				$architects[] = $item;
			}
		}
		$mainData = [
			'id'            => $sightID,
			'main_data'     => [
				'title'     => html_entity_decode(get_the_title($sightID)),
				'sub_title' => html_entity_decode($location['address']),
				'image'     => html_entity_decode($mainImage)
			],
			'architects'    => $architects,
			'year'          => intval(get_field('established', $sightID)),
			'description'   => html_entity_decode(get_field('main_content', $sightID)),
			'image_gallery' => $gallery,
			'extra_data'    => html_entity_decode(get_field('source', $sightID)),
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
	$response->header( 'Content-type', 'application/json; charset=utf-8' );
	return $response;
}

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

function API_POST_SIGHT ( $data ) {
	$firstName = $data['first_name'];
	$lastName = $data['last_name'];
	$email = $data['email'];
	$link = $data['link'];
	$statusCode = 200;
	$description = $data['description'];
	if (is_null($firstName)) {
		$firstName = "";
	}
	if (is_null($lastName)) {
		$lastName = "";
	}
	if (is_null($email)) {
		$email = "";
	}
	if (is_null($link)) {
		$link = "";
	}
	$args = [
		'post_title'    => 'New Object',
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
		createUpdateContributor($firstName, $lastName, $email, $link, $sightID);
	}
	$output = [
		'done' => true,
		'message' => null
	];
	$response = new WP_REST_Response( $output );
	$response->set_status( $statusCode );
	$response->header( 'Content-type', 'application/json; charset=utf-8' );
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
		$subject = 'New object offer on BRUTMAPS';
		$body = 'We have a new offered sight';
		$headers = array('Content-Type: text/html; charset=UTF-8');
		wp_mail( $to, $subject, $body, $headers );
	}
}

function createUpdateContributor($firstName, $lastName, $email, $link, $sightID) {
	$args = array(
		'numberposts'   => 1,
		'post_type'		=> 'contributor',
		'fields'        => 'ids',
		'post_status'   => array('publish', 'pending', 'draft'),
		'meta_key'		=> 'email',
		'meta_value'	=> $email
	);
	$contributor = get_posts($args);
	if (count($contributor) > 0) {
		//Existed Contributor
		$contributorID = $contributor[0];
		$linkedSights = get_field('linked_sights', $contributorID);
		if (is_null($linkedSights)) {
			$linkedSights = [];
		}
		$linkedSights[] = $sightID;
	} else {
		//New Contributor
		$args = [
			'post_title'    => $email,
			'post_type'     => 'contributor',
			'post_status'   => 'publish'
		];
		$contributorID = wp_insert_post($args);
		$linkedSights = [$sightID];
	}
	$contributorID = intval($contributorID);
	if (is_int($contributorID) && $contributorID > 0) {
		update_field('first_name', $firstName, $contributorID);
		update_field('last_name', $lastName, $contributorID);
		update_field('email', $email, $contributorID);
		update_field('link', $link, $contributorID);
		update_field('linked_sights', $linkedSights, $contributorID);
		update_field('contributor', $contributorID, $sightID);
	}
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

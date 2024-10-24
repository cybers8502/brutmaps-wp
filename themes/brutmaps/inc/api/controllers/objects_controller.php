<?php

// - GET: /posts
function API_GET_POSTS( $request ) {
    $categories = $request->get_param('cat');

    if ($categories && is_string($categories)) {
        $categories = json_decode($categories, true);
    }

    if (!is_array($categories)) {
        $categories = [];
    }

    $ids = getFilterdPostsIDbyCategories('post', $categories);

    $mainData = [
        'posts' => getPostsByIDs($ids),
        'categories' => $categories
    ];

    return successResponse($mainData);
}

// - GET /posts/$id
function API_GET_POST_BY_ID( $data ) {
    $identifier = $data['identifier'];

    if (is_numeric($identifier)) {
        $postID = intval($identifier);
        $post = get_post($postID);
    } else {
        $slug = sanitize_title($identifier);
        $args = [
            'name'        => $slug,
            'post_type'   => 'post',
            'post_status' => 'publish',
            'numberposts' => 1
        ];
        $posts = get_posts($args);
        $post = !empty($posts) && isset($posts[0]) ? $posts[0] : null;
        $postID = $post ? $post->ID : null;
    }

    if (!is_null($post) && $post->post_status === 'publish' && $post->post_type == 'post') {
        $content = apply_filters('the_content', $post->post_content);
        $banners = extract_banners_from_content($content);

        $mainData = [
            'id'            => $postID,
            'title'         => $post->post_title,
            'content'       => $content,
            'banners'       => $banners,
            'excerpt'       => $post->post_excerpt,
            'date'          => $post->post_date,
            'author'        => get_the_author_meta('display_name', $post->post_author),
            'thumbnail'     => get_the_post_thumbnail_url($post->ID, 'full'),
            'permalink'     => get_permalink($post->ID),
        ];
    } else {
        return failureResponse('Sight does not exist');
    }
    return successResponse($mainData);
}

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
        'sights' => getSightsSmallDataByIDs($ids),
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

// - GET /sights/$id
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
        $mainData = [
            'id'            => $sightID,
            'main_data'     => [
                'title'     => html_entity_decode(get_the_title($sightID)),
                'sub_title' => html_entity_decode($location['address']),
                'image'     => html_entity_decode($mainImage),
                'main_image_author' => getAuthorData($author)
            ],
            'architects'           => $architects,
            'associated_people'    => $associatedPeople,
            'year'          => intval(get_field('established', $sightID)),
            'description'   => html_entity_decode(get_field('main_content', $sightID)),
            'image_gallery' => $gallery,
            'extra_data'    => html_entity_decode(get_field('source', $sightID)),
            'coordinates'   => [
                'lat' => doubleval($location['lat']),
                'long' => doubleval($location['lng'])
            ]
        ];
        $sights = getSightsSmallDataByIDs([$sightID]);
        $mergedData = array_merge($mainData, $sights[0]);
    } else {
        return failureResponse('Sight does not exist');
    }
    return successResponse($mergedData);
}

// - GET: /store
function API_GET_PRODUCTS() {
    if (!class_exists('WooCommerce')) {
        return errorResponse('WooCommerce plugin is not activated.');
    }

    $args = [
        'status' => 'publish',
        'limit' => -1,
    ];

    $products = wc_get_products($args);

    $product_data = [];
    foreach ($products as $product) {

        $image_id = $product->get_image_id();
        $image = [
            'id' => $image_id,
            'src' => wp_get_attachment_url($image_id),
            'name' => get_the_title($image_id)
        ];

        $images = array_map(function($image_id) {
            return [
                'id' => $image_id,
                'src' => wp_get_attachment_url($image_id),
                'name' => get_the_title($image_id)
            ];
        }, $product->get_gallery_image_ids());

        $product_data[] = [
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'slug' => $product->get_slug(),
            'description' => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'stripe' => get_field('stripe', $product->get_id()),
            'image' => $image,
            'images' => $images,
            'categories' => wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names'])
        ];
    }

    $mainData = [
        'products' => $product_data
    ];

    return successResponse($mainData);
}

// - POST: /submit_sight
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

// - HELPER FUNCTIONS
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

function getPosts($type) {
    $args = array(
        'numberposts'   => -1,
        'post_type'		=> $type,
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

function extract_banners_from_content($content) {
    $banners = [];

    $pattern = '/<div\s+[^>]*data-banner[^>]*>(.*?)<\/div>/s';

    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $banners[] = [
            'data-banner' => $match[1],
            'html'        => $match[0],
            'content'     => $match[2]
        ];
    }

    return $banners;
}

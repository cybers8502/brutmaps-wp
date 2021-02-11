<?php

add_action('wp_ajax_get_sight','get_sight');
add_action('wp_ajax_nopriv_get_sight', 'get_sight');

add_action('wp_ajax_create_author_by_name','create_author_by_name');
add_action('wp_ajax_nopriv_create_author_by_name', 'create_author_by_name');

add_action('wp_ajax_get_instagram_data','get_instagram_data');
add_action('wp_ajax_nopriv_get_instagram_data', 'get_instagram_data');

//Ajax Response to recipes by taxId
function get_sight() {

    $out = json_encode( API_GET_SIGHTS() );

    echo $out;
    exit;

}

//Ajax Get Instagram Data
function get_instagram_data() {

    exit;
}

function create_author_by_name() {

    $errorResponse = array(
        'done' => false
    );

    $successResponse = array(
        'done' => true,
        'message' => 'Successfully added!'
    );

    $name = $_POST['name'];

    if (!$name || is_null($name)) {
        echo wp_json_encode($errorResponse);
        exit;
    }

    $existedAuthorCount = checkIfAuthorExistWithName($name);

    if ($existedAuthorCount != 0) {
        $successResponse['message'] = 'Author with this instagram already exists';
    } else {
        createAuthorByInstagramName($name);
    }
    echo wp_json_encode($successResponse);
    exit;
}

function checkIfAuthorExistWithName($name) {
    $args = array(
        'numberposts'   => 1,
        'post_type'		=> 'authors',
        'fields'        => 'ids',
        'post_status'   => array('publish', 'pending', 'draft'),
        'meta_query'	=> array(
            array(
                'key'		=> 'instagram',
                'value'		=> $name,
                'compare'	=> 'LIKE'
            )
        )
    );
    return count(get_posts($args));
}

function createAuthorByInstagramName($name) {
    $args = [
        'post_title'    => $name,
        'post_type'     => 'authors',
        'post_status'   => 'publish'
    ];
    $newAuthorID = intval(wp_insert_post($args));
    if (is_int($newAuthorID) && $newAuthorID > 0) {
        update_field('instagram', $name, $newAuthorID);
        return $newAuthorID;
    }
    return null;
}

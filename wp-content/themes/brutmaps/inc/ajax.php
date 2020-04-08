<?php

add_action('wp_ajax_get_sight','get_sight');
add_action('wp_ajax_nopriv_get_sight', 'get_sight');

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

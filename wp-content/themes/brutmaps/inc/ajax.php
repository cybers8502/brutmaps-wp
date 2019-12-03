<?php

add_action('wp_ajax_get_sight','get_sight');

add_action('wp_ajax_nopriv_get_sight', 'get_sight');

//Ajax Response to recipes by taxId
function get_sight() {

    $out = json_encode( API_GET_SIGHTS() );

    echo $out;
    exit;

}
<?php 
//Custom API requests

add_action( 'rest_api_init', function () {
    register_rest_route( 'brutmaps/data/v1', '/sights', array(
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
    $result = [];
    foreach ($ids as $sightID) {
        $item = [];
        $item['id'] = $sightID;
        $item['title'] = get_the_title($sightID);
        $result[] = $item;
    }
    return json_encode($result);
}
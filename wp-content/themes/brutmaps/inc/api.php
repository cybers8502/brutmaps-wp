<?php 
//Custom API requests

add_action( 'rest_api_init', function () {
    register_rest_route( 'brutmaps/data/v1', '/sights', array(
      'methods' => 'GET',
      'callback' => 'my_awesome_func',
    ) );
  } );

  function my_awesome_func( $data ) {
    $posts = get_posts();
   
    if ( empty( $posts ) ) {
      return null;
    }
   
    return '{
        "data": true,
        "message": "Hello"
    }';
  }
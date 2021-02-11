<?php

add_action( 'save_post', 'intercept_publishing', 10, 3 );

function intercept_publishing( $post_ID, $post, $update ) {

    if ( ! is_admin() ) {
        return;
    }

    if ( 'sight' !== get_post_type( $post_ID ) ) {
        return;
    }

    $authorType = get_field('main_image_author_type', $post_ID);
    $newAuthorName = get_field('main_image_free_author_field', $post_ID);

    if ($authorType == 'new_author' && $newAuthorName != "" && !is_null($newAuthorName)) {
        $newAuthor = createAuthorByInstagramName($newAuthorName);
        if (!is_null($newAuthor) && $newAuthor != 0) {
            update_field('main_image_author_type', 'existed', $post_ID);
            update_field('main_image_free_author_field', null, $post_ID);
            update_field('main_image_author_id', $newAuthor, $post_ID);
        }
    }

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


add_filter('acf/fields/post_object/query/key=field_object_main_image_author_id', 'authorSpecificSearchByAdditionalFields', 10, 3);

function authorSpecificSearchByAdditionalFields( $args, $field, $post_id ) {

    $s = $args['s'];
    if (!$s) {
        $s = '';
    }
    unset($args['s']);

    $args['meta_query'] = array(
        'relation'  => 'OR',
        array(
            'key'		=> 'instagram',
            'value'		=> $s,
            'compare'	=> 'LIKE'
        )
    );
    return $args;
}

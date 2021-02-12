<?php

add_filter('acf/fields/post_object/query/key=field_object_main_image_author_id', 'authorSpecificSearchByAdditionalFields', 10, 3);
add_filter('acf/fields/post_object/query/key=gallery_image_author_id', 'authorSpecificSearchByAdditionalFields', 10, 3);
add_filter('acf/fields/post_object/query/key=field_about_main_image_author', 'authorSpecificSearchByAdditionalFields', 10, 3);

add_action( 'add_meta_boxes', 'global_notice_meta_box' );
add_action('admin_head', 'createAuthorHandler');

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

function global_notice_meta_box() {

    add_meta_box(
        'global-notice',
        __( 'Add New author by instagram', 'sitepoint' ),
        'global_notice_meta_box_callback',
        'sight'
    );
}

function global_notice_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'global_notice_nonce', 'global_notice_nonce' );

    $value = get_post_meta( $post->ID, '_global_notice', true );

    echo '<div id="sections_structure_box" class="postbox ">
        <div class="handlediv" title="Click to toggle"><br></div>
        <h2 class="hndle ui-sortable-handle"></h2>
        <div style="height: 40px" id="author_result_message"></div>
        <div class="inside">
            <input type="text" name="author_name_field" size="50" value="" id="author_name_field" spellcheck="true" autocomplete="off">
            <input type="button" name="save_author_by_name" id="save_author_by_name" class="button button-primary button-large" value="Create">
        </div>
        </div>';
}

function createAuthorHandler() { ?>
    <script>
        jQuery(document).ready(function ($) {
            $('#save_author_by_name').on('click', function () {
                let errorBlock = $('#author_result_message');
                let field = $('#author_name_field');
                let authorName = field.val();
                if (authorName.length === 0) {
                    errorBlock.text("Field is empty!");
                    return;
                }
                field.val("");
                $.post(ajaxurl, {
                    action: 'create_author_by_name',
                    name: authorName
                }, function (data) {
                    let response = JSON.parse(data);
                    if (response['done']) {
                        errorBlock.text(response['message']);
                    } else {
                        errorBlock.text("Something went wrong!");
                    }
                    field.val("");
                });
            });
        });
    </script>
    <?php
}

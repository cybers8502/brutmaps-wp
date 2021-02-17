<?php

add_filter('acf/fields/post_object/query/key=field_object_main_image_author_id', 'authorSpecificSearchByAdditionalFields', 10, 3);
add_filter('acf/fields/post_object/query/key=gallery_image_author_id', 'authorSpecificSearchByAdditionalFields', 10, 3);
add_filter('acf/fields/post_object/query/key=field_about_main_image_author', 'authorSpecificSearchByAdditionalFields', 10, 3);

add_action( 'add_meta_boxes', 'global_notice_meta_box' );
add_action( 'admin_head', 'createAuthorHandler' );

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

    echo '<div id="sections_structure_box" class="wp-author-meta-box">
            <input type="text" name="author_name_field" size="50" value="" id="author_name_field" spellcheck="true" autocomplete="off">
            <input type="button" name="save_author_by_name" id="save_author_by_name" class="button button-primary button-large" value="Create">
            <div id="author_result_message"></div>
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
                    errorBlock.addClass('is-show');
                    return;
                }
                field.val("");
                errorBlock.removeClass('is-show');
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
            $('#author_name_field').on('keydown', function (e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    $('#save_author_by_name').trigger('click');
                }
            });
        });
    </script>
    <style>
        .wp-author-meta-box {
            position: relative;
            display: flex;
            align-items: stretch;
            margin-top: 15px;
        }
        .wp-author-meta-box input[type=text] {
            width: 100%;
            max-width: 300px;
            margin-right: 5px;
        }
        .wp-author-meta-box #author_result_message {
            position: absolute;
            bottom: 100%;
            left: 30px;
            padding: 5px 10px;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgb(0 0 0 / 4%);
            background-color: #fff;
            opacity: 0;
            transition: opacity .3s ease;
            color: #f11;
        }
        .wp-author-meta-box #author_result_message.is-show {
            opacity: 1;
        }
        .wp-author-meta-box #author_result_message:after,
        .wp-author-meta-box #author_result_message:before {
            top: 100%;
            left: 50%;
            border: solid transparent;
            content: "";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
        }
        .wp-author-meta-box #author_result_message:after {
            border-color: rgba(255, 255, 255, 0);
            border-top-color: #fff;
            border-width: 5px;
            margin-left: -5px;
        }
        .wp-author-meta-box #author_result_message:before {
            border-color: rgba(204, 208, 212, 0);
            border-top-color: #ccd0d4;
            border-width: 6px;
            margin-left: -6px;
        }
    </style>
    <?php
}

<?php

function delete_user_account($user_id) {
    require_once ABSPATH . 'wp-admin/includes/user.php';

    delete_user_favorites($user_id);
    delete_user_photo($user_id);
    delete_user_posts($user_id);
    delete_user_meta_data($user_id);

    wp_delete_user($user_id);
}

function delete_user_meta_data($user_id) {
    global $wpdb;
    $wpdb->delete($wpdb->usermeta, array('user_id' => $user_id));
}

function delete_user_posts($user_id) {
    $user_posts = get_posts(array(
        'author' => $user_id,
        'post_type' => 'any',
        'numberposts' => -1
    ));

    foreach ($user_posts as $post) {
        wp_delete_post($post->ID, true);
    }
}

function delete_user_favorites($user_id) {
    delete_user_meta($user_id, 'favorite_sights');
}

function delete_user_photo($user_id) {
    $photo_id = get_user_meta($user_id, 'profile_photo', true);
    if (!empty($photo_id)) {
        wp_delete_attachment($photo_id, true);
        delete_user_meta($user_id, 'profile_photo');
    }
}

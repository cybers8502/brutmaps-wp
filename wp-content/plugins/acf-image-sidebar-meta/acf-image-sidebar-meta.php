<?php
/*
Plugin Name: ACF Image Sidebar Meta
Description: Gutenberg sidebar for editing ACF fields on image attachments.
Version: 1.0
Author: Max + ChatGPT
*/

add_action('enqueue_block_editor_assets', function () {
    $screen = get_current_screen();

    if ($screen && $screen->base === 'post' && in_array($screen->post_type, ['post', 'sight'])) {
        // 1. Визначаємо шлях до .asset.php
        $asset_file = plugin_dir_path(__FILE__) . 'app/build/index.asset.php';
        $asset = file_exists($asset_file) ? require($asset_file) : [
            'dependencies' => [],
            'version' => filemtime(plugin_dir_path(__FILE__) . 'app/build/index.js'),
        ];

        // 2. Підключаємо JS із динамічними залежностями/версією
        wp_enqueue_script(
            'acf-image-sidebar-meta',
            plugin_dir_url(__FILE__) . 'app/build/index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );
    }
});

add_action('wp_ajax_get_image_meta', function() {
    $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
    $acf_fields = [
        'attached_image_author_source' => get_field('attached_image_author_source', $attachment_id),
        'attached_image_author_id' => get_field('attached_image_author_id', $attachment_id),
    ];

    wp_send_json_success($acf_fields);
});

add_action('wp_ajax_update_image_meta', function() {
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Недостатньо прав.']);
    }

    $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
    $author_source = isset($_POST['author_source']) ? sanitize_text_field($_POST['author_source']) : '';
    $author_id = isset($_POST['author_id']) ? intval($_POST['author_id']) : 0;

    if ($attachment_id > 0) {
        update_field('attached_image_author_source', $author_source, $attachment_id);
        update_field('attached_image_author_id', $author_id, $attachment_id);

        wp_send_json_success(['message' => 'Поля успішно оновлено.']);
    } else {
        wp_send_json_error(['message' => 'Некоректний ID зображення.']);
    }
});

add_action('wp_ajax_get_authors_list', function() {
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Недостатньо прав.']);
    }

    $authors = get_posts([
        'post_type' => 'authors',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $result = array_map(function($author) {
        return [
            'id' => $author->ID,
            'title' => get_the_title($author),
        ];
    }, $authors);

    wp_send_json_success($result);
});

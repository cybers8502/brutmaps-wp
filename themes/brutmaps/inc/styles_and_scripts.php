<?php
function add_files()
{

    wp_deregister_script('jquery');
    wp_enqueue_script('custom_module_script', get_template_directory_uri() . '/assets/index-B7GYH9i3.js', array(), null, true);
    wp_enqueue_style('custom_style', get_template_directory_uri() . '/assets/index-HaltlK1N.css', array(), null);

    switch (true) {
        case is_checkout():
            wp_enqueue_style('checkout_page_style', get_template_directory_uri() . '/assets/checkout-page.css', array(), null);
            break;
        case is_order_received_page():
            wp_enqueue_style('order_received_page_style', get_template_directory_uri() . '/assets/order-received-page.css', array(), null);
            break;
    }
}

add_action('wp_enqueue_scripts', 'add_files');

function add_module_attributes($tag, $handle, $src) {
    if ('custom_module_script' === $handle) {
        $tag = '<script type="module" crossorigin src="' . esc_url($src) . '"></script>';
    }
    return $tag;
}
add_filter('script_loader_tag', 'add_module_attributes', 10, 3);


function add_crossorigin_to_styles($html, $handle) {
    if ('custom_style' === $handle) {
        $html = str_replace('<link ', '<link crossorigin ', $html);
    }
    return $html;
}
add_filter('style_loader_tag', 'add_crossorigin_to_styles', 10, 2);

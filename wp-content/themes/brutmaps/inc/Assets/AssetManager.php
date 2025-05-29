<?php
namespace Brut\Assets;

class AssetManager {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
        add_filter('script_loader_tag', [$this, 'addModuleAttribute'], 10, 3);
        add_filter('style_loader_tag', [$this, 'addCrossoriginToStyles'], 10, 2);
    }

    public function enqueue() {
        wp_deregister_script('jquery');
        wp_enqueue_script('custom_module_script', get_template_directory_uri() . '/assets/index-DhGOH0jm.js', [], null, true);
        wp_enqueue_style('custom_style', get_template_directory_uri() . '/assets/index-CT65bLT9.css', [], null);

        if (is_checkout()) {
            wp_enqueue_style('checkout_page_style', get_template_directory_uri() . '/assets/checkout-page.css', [], null);
        }

        if (is_order_received_page()) {
            wp_enqueue_style('order_received_page_style', get_template_directory_uri() . '/assets/order-received-page.css', [], null);
        }
    }

    public function addModuleAttribute($tag, $handle, $src) {
        if ($handle === 'custom_module_script') {
            return '<script type="module" crossorigin src="' . esc_url($src) . '"></script>';
        }
        return $tag;
    }

    public function addCrossoriginToStyles($html, $handle) {
        if ($handle === 'custom_style') {
            return str_replace('<link ', '<link crossorigin ', $html);
        }
        return $html;
    }
}

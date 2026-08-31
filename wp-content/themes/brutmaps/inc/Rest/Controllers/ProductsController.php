<?php

namespace Brut\Rest\Controllers;

use Brut\Utils\ResponseHelper;

class ProductsController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/shop/products', [
            'methods' => 'GET',
            'callback' => [$this, 'getProducts'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function getProducts(): \WP_REST_Response|\WP_Error
    {
        if (!class_exists('WooCommerce')) {
            return ResponseHelper::error('WooCommerce plugin is not active', 500);
        }

        $products = wc_get_products([
            'status' => 'publish',
            'limit' => -1,
        ]);

        $items = array_map([$this, 'formatProduct'], $products);

        return ResponseHelper::success([
            'products' => $items,
        ]);
    }

    private function formatProduct(\WC_Product $product): array
    {
        $id = $product->get_id();
        $image_id = (int) $product->get_image_id();

        return [
            'id' => $id,
            'name' => $product->get_name(),
            'slug' => $product->get_slug(),
            'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'description' => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'stripe' => get_field('stripe', $id),
            'image' => $this->getImageData($image_id),
            'images' => array_map([$this, 'getImageData'], $product->get_gallery_image_ids()),
            'categories' => wp_get_post_terms($id, 'product_cat', ['fields' => 'names']),
        ];
    }

    private function getImageData(int $image_id): array
    {
        return [
            'id' => $image_id,
            'src' => wp_get_attachment_url($image_id),
            'name' => get_the_title($image_id),
        ];
    }
}

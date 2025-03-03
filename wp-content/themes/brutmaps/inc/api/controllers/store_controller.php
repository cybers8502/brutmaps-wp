<?php

// - GET: /products
function API_GET_PRODUCTS() {
    if (!class_exists('WooCommerce')) {
        return errorResponse('WooCommerce plugin is not activated.');
    }

    $args = [
        'status' => 'publish',
        'limit' => -1,
    ];

    $products = wc_get_products($args);

    $product_data = [];
    foreach ($products as $product) {

        $image_id = $product->get_image_id();
        $image = [
            'id' => $image_id,
            'src' => wp_get_attachment_url($image_id),
            'name' => get_the_title($image_id)
        ];

        $images = array_map(function($image_id) {
            return [
                'id' => $image_id,
                'src' => wp_get_attachment_url($image_id),
                'name' => get_the_title($image_id)
            ];
        }, $product->get_gallery_image_ids());

        $product_data[] = [
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'slug' => $product->get_slug(),
            'description' => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'stripe' => get_field('stripe', $product->get_id()),
            'image' => $image,
            'images' => $images,
            'categories' => wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names'])
        ];
    }

    $mainData = [
        'products' => $product_data
    ];

    return successResponse($mainData);
}

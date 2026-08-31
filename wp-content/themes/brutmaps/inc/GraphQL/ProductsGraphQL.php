<?php

namespace Brut\GraphQL;

use GraphQL\Error\UserError;

class ProductsGraphQL
{
    public function registerTypes(): void
    {
        register_graphql_object_type('ProductImage', [
            'fields' => [
                'id'   => ['type' => 'Int'],
                'src'  => ['type' => 'String'],
                'name' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('Product', [
            'description' => 'A WooCommerce product.',
            'fields'      => [
                'id'                => ['type' => 'Int'],
                'name'              => ['type' => 'String'],
                'slug'              => ['type' => 'String'],
                'price'             => ['type' => 'String'],
                'regularPrice'      => ['type' => 'String', 'resolve' => fn($p) => $p['regular_price'] ?? null],
                'salePrice'         => ['type' => 'String', 'resolve' => fn($p) => $p['sale_price'] ?? null],
                'description'       => ['type' => 'String'],
                'shortDescription'  => [
                    'type'    => 'String',
                    'resolve' => fn($p) => $p['short_description'] ?? null,
                ],
                'stripe'            => ['type' => 'String'],
                'image'             => ['type' => 'ProductImage'],
                'images'            => ['type' => ['list_of' => 'ProductImage']],
                'categories'        => ['type' => ['list_of' => 'String']],
            ],
        ]);

        register_graphql_field('RootQuery', 'products', [
            'type'        => ['list_of' => 'Product'],
            'description' => 'All published WooCommerce products.',
            'resolve'     => [$this, 'resolveProducts'],
        ]);
    }

    public function resolveProducts(): array
    {
        if (!class_exists('WooCommerce')) {
            throw new UserError('WooCommerce plugin is not active');
        }

        $products = wc_get_products([
            'status' => 'publish',
            'limit'  => -1,
        ]);

        return array_map([$this, 'formatProduct'], $products);
    }

    private function formatProduct(\WC_Product $product): array
    {
        $id       = $product->get_id();
        $image_id = (int) $product->get_image_id();

        return [
            'id'                => $id,
            'name'              => $product->get_name(),
            'slug'              => $product->get_slug(),
            'price'             => $product->get_price(),
            'regular_price'     => $product->get_regular_price(),
            'sale_price'        => $product->get_sale_price(),
            'description'       => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'stripe'            => get_field('stripe', $id),
            'image'             => $this->getImageData($image_id),
            'images'            => array_map([$this, 'getImageData'], $product->get_gallery_image_ids()),
            'categories'        => wp_get_post_terms($id, 'product_cat', ['fields' => 'names']),
        ];
    }

    private function getImageData(int $image_id): array
    {
        return [
            'id'   => $image_id,
            'src'  => wp_get_attachment_url($image_id),
            'name' => get_the_title($image_id),
        ];
    }
}

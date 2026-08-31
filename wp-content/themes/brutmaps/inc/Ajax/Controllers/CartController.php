<?php

namespace Brut\Ajax\Controllers;

class CartController
{
    public function __construct()
    {
        add_action('wp_ajax_create_author_by_name', [$this, 'addToCart']);
        add_action('wp_ajax_nopriv_create_author_by_name', [$this, 'addToCart']);
    }

    public function addToCart()
    {
        $product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($product_id > 0) {
            $cart = WC()->cart->get_cart();
            $found = false;

            foreach ($cart as $cart_item_key => $cart_item) {
                if ($cart_item['product_id'] == $product_id) {
                    WC()->cart->set_quantity($cart_item_key, 1);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $added = WC()->cart->add_to_cart($product_id, 1);

                if ($added) {
                    wp_send_json_success(array('message' => 'Товар добавлен в корзину!'));
                } else {
                    wp_send_json_error(array('message' => 'Не удалось добавить товар в корзину.'));
                }
            } else {
                wp_send_json_success(array('message' => 'Товар уже был в корзине, его количество обновлено до 1.'));
            }
        } else {
            wp_send_json_error(array('message' => 'Некорректный ID товара.'));
        }
    }
}

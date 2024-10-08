<?php

add_action('wp_ajax_get_sight','get_sight');
add_action('wp_ajax_nopriv_get_sight', 'get_sight');

add_action('wp_ajax_create_author_by_name','create_author_by_name');
add_action('wp_ajax_nopriv_create_author_by_name', 'create_author_by_name');

add_action('wp_ajax_get_instagram_data','get_instagram_data');
add_action('wp_ajax_nopriv_get_instagram_data', 'get_instagram_data');

add_action('wp_ajax_add_to_cart_book','add_to_cart_book');
add_action('wp_ajax_nopriv_add_to_cart_book', 'add_to_cart_book');

//Ajax Response to recipes by taxId
function get_sight() {

    $out = json_encode( API_GET_SIGHTS() );

    echo $out;
    exit;

}

//Ajax Get Instagram Data
function get_instagram_data() {

    exit;
}

function create_author_by_name() {

    $errorResponse = array(
        'done' => false
    );

    $successResponse = array(
        'done' => true,
        'message' => 'Successfully added!'
    );

    $name = $_POST['name'];

    if (!$name || is_null($name)) {
        echo wp_json_encode($errorResponse);
        exit;
    }

    $existedAuthorCount = checkIfAuthorExistWithName($name);

    if ($existedAuthorCount != 0) {
        $successResponse['message'] = 'Author with this instagram already exists';
    } else {
        createAuthorByInstagramName($name);
    }
    echo wp_json_encode($successResponse);
    exit;
}

function checkIfAuthorExistWithName($name) {
    $args = array(
        'numberposts'   => 1,
        'post_type'		=> 'authors',
        'fields'        => 'ids',
        'post_status'   => array('publish', 'pending', 'draft'),
        'meta_query'	=> array(
            array(
                'key'		=> 'instagram',
                'value'		=> $name,
                'compare'	=> 'LIKE'
            )
        )
    );
    return count(get_posts($args));
}

function createAuthorByInstagramName($name) {
    $args = [
        'post_title'    => $name,
        'post_type'     => 'authors',
        'post_status'   => 'publish'
    ];
    $newAuthorID = intval(wp_insert_post($args));
    if (is_int($newAuthorID) && $newAuthorID > 0) {
        update_field('instagram', $name, $newAuthorID);
        return $newAuthorID;
    }
    return null;
}

function add_to_cart_book() {
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

    wp_die();
}

<?php

// - POST: /favorites/toggle
function API_POST_TOGGLE_FAVORITE_SIGHT(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', 'User not authenticated.', array('status' => 401));
    }

    $sight_id = sanitize_text_field($request->get_param('sight_id'));
    if (!$sight_id || !get_post($sight_id)) {
        return new WP_Error('invalid_sight', 'Invalid sight ID.', array('status' => 400));
    }

    $favorites = get_user_meta($user_id, 'favorite_sights', true);

    // Гарантуємо, що значення - це масив
    if (!is_array($favorites)) {
        $favorites = [];
    }

    if (in_array($sight_id, $favorites)) {
        // Видаляємо з вподобаних
        $favorites = array_diff($favorites, [$sight_id]);
        $message = 'Removed from favorites.';
    } else {
        // Додаємо до вподобаних
        $favorites[] = $sight_id;
        $message = 'Added to favorites.';
    }

    // Перетворюємо на чистий індексований масив, щоб уникнути проблеми з асоціативними ключами
    $favorites = array_values($favorites);

    update_user_meta($user_id, 'favorite_sights', $favorites);

    return rest_ensure_response([
        'status' => 'success',
        'message' => $message,
        'favorites' => $favorites,
    ]);
}

// - GET: /favorites
function API_GET_FAVORITE_SIGHT(WP_REST_Request $request) {
    $user_id = get_current_user_id();

    if (!$user_id) {
        return new WP_Error('unauthorized', 'User not authenticated.', array('status' => 401));
    }

    $favorites = get_user_meta($user_id, 'favorite_sights', true);

    if (!is_array($favorites)) {
        $favorites = [];
    }

    $favorites = array_values($favorites);

    return rest_ensure_response([
        'status' => 'success',
        'favorites' => $favorites,
    ]);
}

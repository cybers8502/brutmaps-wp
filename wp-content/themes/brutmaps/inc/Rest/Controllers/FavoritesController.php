<?php

namespace Brut\Rest\Controllers;

use WP_REST_Request;
use Brut\Utils\ResponseHelper;
use Brut\Utils\RequestSanitizer;
use Brut\Utils\ValidatorHelper;

class FavoritesController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/favorites', [
            'methods' => 'GET',
            'callback' => [$this, 'getFavorites'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route(BASE_URL, '/favorites/toggle', [
            'methods' => 'POST',
            'callback' => [$this, 'toggleFavorite'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public function getFavorites(): \WP_REST_Response
    {
        $user_id = get_current_user_id();
        $favorites = get_user_meta($user_id, 'favorite_sights', true);

        return ResponseHelper::success([
            'favorites' => is_array($favorites) ? array_values($favorites) : [],
        ]);
    }

    public function toggleFavorite(WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $user_id = get_current_user_id();
        $sight_id = RequestSanitizer::sanitizeStringParam($request, 'sight_id');

        if (!ValidatorHelper::isValidId($sight_id) || !get_post($sight_id)) {
            return ResponseHelper::validationError('Invalid sight ID');
        }

        $favorites = get_user_meta($user_id, 'favorite_sights', true);
        if (!is_array($favorites)) {
            $favorites = [];
        }

        if (in_array($sight_id, $favorites)) {
            $favorites = array_values(array_diff($favorites, [$sight_id]));
            $message = 'Removed from favorites';
        } else {
            $favorites[] = $sight_id;
            $message = 'Added to favorites';
        }

        update_user_meta($user_id, 'favorite_sights', $favorites);

        return ResponseHelper::success([
            'message' => $message,
            'favorites' => $favorites,
        ], $message);
    }
}

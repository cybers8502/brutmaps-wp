<?php

namespace Brut\Rest\Controllers;

use WP_REST_Request;
use Brut\Utils\ResponseHelper;
use Brut\Utils\RequestSanitizer;
use Brut\Utils\ValidatorHelper;

class FavoritesController
{
    public const CATEGORIES = ['favorite', 'want_to_go', 'visited', 'hidden'];

    public function register(): void
    {
        register_rest_route(BASE_URL, '/user/favorites', [
            'methods' => 'GET',
            'callback' => [$this, 'getFavorites'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route(BASE_URL, '/user/favorites/toggle', [
            'methods' => 'POST',
            'callback' => [$this, 'toggleFavorite'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public function getFavorites(): \WP_REST_Response
    {
        $user_id = get_current_user_id();
        $favorites = get_user_meta($user_id, 'sight_preferences', true);

        if (!is_array($favorites)) {
            $favorites = array_fill_keys(self::CATEGORIES, []);
        }

        return ResponseHelper::success([
            'favorites' => $favorites,
        ]);
    }

    public function toggleFavorite(WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $user_id = get_current_user_id();
        $sight_id = RequestSanitizer::sanitizeStringParam($request, 'sight_id');
        $category = sanitize_text_field($request->get_param('category') ?? 'favorite');

        if (!ValidatorHelper::isValidId($sight_id) || !get_post((int) $sight_id)) {
            return ResponseHelper::validationError('Invalid sight ID');
        }

        if (!in_array($category, self::CATEGORIES, true)) {
            return ResponseHelper::validationError('Invalid category');
        }

        $preferences = get_user_meta($user_id, 'sight_preferences', true);
        if (!is_array($preferences)) {
            $preferences = array_fill_keys(self::CATEGORIES, []);
        }

        $categoryItems = $preferences[$category] ?? [];

        if (in_array($sight_id, $categoryItems)) {
            $preferences[$category] = array_values(array_diff($categoryItems, [$sight_id]));
            $message = "Removed from $category";
        } else {
            $preferences[$category][] = $sight_id;
            $message = "Added to $category";
        }

        update_user_meta($user_id, 'sight_preferences', $preferences);

        return ResponseHelper::success([
            'message' => $message,
            'favorites' => $preferences,
        ], $message);
    }
}

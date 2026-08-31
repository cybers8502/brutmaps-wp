<?php

namespace Brut\Rest\Controllers;

use Brut\Services\UserDeletionService;
use Brut\Utils\ResponseHelper;

class UserProfileController
{
    public function register(): void
    {
        register_rest_route(BASE_URL, '/profile/delete-account', [
            'methods' => 'DELETE',
            'callback' => [$this, 'deleteAccount'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public function deleteAccount(): \WP_REST_Response|\WP_Error
    {
        $user_id = get_current_user_id();

        try {
            (new UserDeletionService())->delete($user_id);

            return ResponseHelper::success([], 'User deleted');
        } catch (\Exception $e) {
            return ResponseHelper::serverError('Failed to delete account: ' . $e->getMessage());
        }
    }
}

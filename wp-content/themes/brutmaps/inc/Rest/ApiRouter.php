<?php

namespace Brut\Rest;

use Brut\Rest\Controllers\AuthController;
use Brut\Rest\Controllers\FavoritesController;
use Brut\Rest\Controllers\PasswordController;
use Brut\Rest\Controllers\PostsController;
use Brut\Rest\Controllers\ProductsController;
use Brut\Rest\Controllers\SightsController;
use Brut\Rest\Controllers\UserProfileController;

class ApiRouter
{

    public function __construct() {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes() {
        (new AuthController())->register();
        (new FavoritesController())->register();
        (new PasswordController())->register();
        (new PostsController())->register();
        (new ProductsController())->register();
        (new SightsController())->register();
        (new UserProfileController())->register();
    }
}

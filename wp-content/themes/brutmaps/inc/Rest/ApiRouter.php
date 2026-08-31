<?php

namespace Brut\Rest;

use Brut\Rest\Controllers\ArchitectsController;
use Brut\Rest\Controllers\FavoritesController;
use Brut\Rest\Controllers\PasswordController;
use Brut\Rest\Controllers\PostsController;
use Brut\Rest\Controllers\ProductsController;
use Brut\Rest\Controllers\ObjectsController;
use Brut\Rest\Controllers\TaxonomyController;
use Brut\Rest\Controllers\UserPhotoController;
use Brut\Rest\Controllers\UserProfileController;

class ApiRouter
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        (new ArchitectsController())->register();
        (new FavoritesController())->register();
        (new PasswordController())->register();
        (new PostsController())->register();
        (new ProductsController())->register();
        (new ObjectsController())->register();
        (new TaxonomyController())->register();
        (new UserPhotoController())->register();
        (new UserProfileController())->register();
    }
}

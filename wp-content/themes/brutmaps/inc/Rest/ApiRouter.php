<?php

namespace Brut\Rest;

use Brut\Rest\Controllers\ArchitectsController;
use Brut\Rest\Controllers\ProductsController;
use Brut\Rest\Controllers\ObjectsController;
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
        (new ProductsController())->register();
        (new ObjectsController())->register();
        (new UserProfileController())->register();
    }
}

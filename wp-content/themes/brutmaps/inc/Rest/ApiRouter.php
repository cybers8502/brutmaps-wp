<?php

namespace Brut\Rest;

use Brut\Rest\Controllers\ArchitectsController;
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
        (new UserProfileController())->register();
    }
}

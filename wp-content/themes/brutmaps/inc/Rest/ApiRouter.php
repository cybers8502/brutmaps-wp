<?php

namespace Brut\Rest;

use Brut\Rest\Controllers\ArchitectsController;

class ApiRouter
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        (new ArchitectsController())->register();
    }
}

<?php

namespace Brut\Admin;

class AcfConfigService
{
    public function boot(): void
    {
        add_action('acf/init', [$this, 'registerApiKey']);
    }

    public function registerApiKey(): void
    {
        $apiKey = defined('ACF_GOOGLE_MAP_KEY')
            ? ACF_GOOGLE_MAP_KEY
            : 'AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

        if (!function_exists('acf_update_setting')) {
            return;
        }

        acf_update_setting('google_api_key', $apiKey);
    }
}

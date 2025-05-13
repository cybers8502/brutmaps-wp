<?php

namespace Brut;

class App
{
    public function boot(): void
    {
        new ThemeSetupService();

        new Assets\AssetManager();

        new Rest\ApiRouter();

        new Security\CorsService();

        new Routing\PermalinkService();

        new Shortcodes\PostBannerShortcode();

        new Ajax\Controllers\CartController();

        $this->bootAdmin();
    }

    public function bootAdmin(): void
    {
        new Admin\AdminMenuManager();

        (new Admin\AcfConfigService())->boot();

        (new Admin\PostTypes\ArchitectPostTypeRegistrar())->boot();
        (new Admin\PostTypes\AuthorsPostTypeRegistrar())->boot();
        (new Admin\PostTypes\ContributorPostTypeRegistrar())->boot();
        (new Admin\PostTypes\SightsPostTypeRegistrar())->boot();

        (new Admin\ACFFieldsManager\ArchitectACFFieldsManager())->boot();
        (new Admin\ACFFieldsManager\AuthorsACFFieldsManager())->boot();
        (new Admin\ACFFieldsManager\ContributorACFFieldsManager())->boot();
        (new Admin\ACFFieldsManager\ProductACFFieldsManager())->boot();
        (new Admin\ACFFieldsManager\SightsACFFieldsManager())->boot();
        (new Admin\ACFFieldsManager\ThemeSetupOptionsACFFieldsManager())->boot();
    }
}

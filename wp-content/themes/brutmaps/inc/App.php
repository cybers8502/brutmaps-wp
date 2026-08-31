<?php

namespace Brut;

class App
{
    public function boot(): void
    {

        new Assets\AssetManager();

        new Security\CorsService();

        new Routing\PermalinkService();

        new SEO\SitemapService();

        new Shortcodes\PostBannerShortcode();

        new Ajax\Controllers\CartController();

        $this->bootAdmin();
        $this->bootRestApi();
        $this->bootGraphQL();
    }

    private function bootAdmin(): void
    {
        new Admin\ThemeSetupService();

        new Admin\AdminMenuManager();

        new Admin\Dashboard\PopularArchitectsWidget();

        new Admin\Filters\SightAdminFiltersService();

        (new Admin\AcfConfigService())->boot();

        (new Admin\PostTypes\ArchitectPostTypeRegistrar())->boot();
        (new Admin\PostTypes\AuthorsPostTypeRegistrar())->boot();
        (new Admin\PostTypes\ContributorPostTypeRegistrar())->boot();
        (new Admin\PostTypes\SightsPostTypeRegistrar())->boot();

        (new Admin\ACFFieldsManager\ArchitectACFFieldsManager())->boot();
        (new Admin\ACFFieldsManager\AttachmentACFFieldsManager())->boot();
        (new Admin\ACFFieldsManager\AuthorsACFFieldsManager())->boot();
        (new Admin\ACFFieldsManager\ContributorACFFieldsManager())->boot();
        (new Admin\ACFFieldsManager\ProductACFFieldsManager())->boot();
        (new Admin\ACFFieldsManager\SightsACFFieldsManager())->boot();
        (new Admin\ACFFieldsManager\ThemeSetupOptionsACFFieldsManager())->boot();
    }

    private function bootRestApi(): void
    {
        new Rest\ApiRouter();
    }

    private function bootGraphQL(): void
    {
        if (!function_exists('register_graphql_field')) {
            return;
        }

        (new GraphQL\GraphQLRegistrar())->boot();
    }
}

<?php

namespace Brut\GraphQL;

use Brut\Admin\ACFFieldsManager\AboutPageACFFieldsManager;
use Brut\Services\CacheService;

class AboutGraphQL
{
    public function registerTypes(): void
    {
        register_graphql_object_type('AboutPage', [
            'description' => 'Static "About" page content, managed via the About page (Pages admin screen).',
            'fields'      => [
                'founderName'    => ['type' => 'String'],
                'founderRole'    => ['type' => 'String'],
                'portraitUrl'    => ['type' => 'String'],
                'portraitAlt'    => ['type' => 'String'],
                'body'           => ['type' => 'String', 'description' => 'Rendered HTML.'],
                'buildingsCount' => ['type' => 'Int'],
                'countriesCount' => ['type' => 'Int'],
                'architectsCount' => ['type' => 'Int'],
                'launchYear'     => ['type' => 'Int'],
            ],
        ]);

        register_graphql_field('RootQuery', 'aboutPage', [
            'type'        => 'AboutPage',
            'description' => 'Content for the "About" page.',
            'resolve'     => [$this, 'resolveAboutPage'],
        ]);
    }

    public function resolveAboutPage(): ?array
    {
        $page = AboutPageACFFieldsManager::getPage();

        if (!$page) {
            return null;
        }

        return CacheService::getOrSet('about_page', function () use ($page) {
            $portrait = get_field('portrait', $page->ID);
            $body     = get_field('body', $page->ID);

            $countryTerms = wp_count_terms([
                'taxonomy'   => 'country',
                'hide_empty' => true,
            ]);

            return [
                'founderName'     => get_field('founderName', $page->ID),
                'founderRole'     => get_field('founderRole', $page->ID),
                'portraitUrl'     => $portrait['url'] ?? null,
                'portraitAlt'     => $portrait['alt'] ?? null,
                'body'            => $body ? apply_filters('the_content', $body) : null,
                // buildings/architects/countries are live counts, not editable content.
                'buildingsCount'  => (int) wp_count_posts('sight')->publish ?: null,
                'architectsCount' => (int) wp_count_posts('architect')->publish ?: null,
                'countriesCount'  => is_wp_error($countryTerms) ? null : ((int) $countryTerms ?: null),
                'launchYear'      => (int) get_field('statLaunchYear', $page->ID) ?: null,
            ];
        }, HOUR_IN_SECONDS);
    }
}

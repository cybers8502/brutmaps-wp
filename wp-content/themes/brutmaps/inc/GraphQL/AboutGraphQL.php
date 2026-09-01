<?php

namespace Brut\GraphQL;

use Brut\Services\CacheService;

class AboutGraphQL
{
    public function registerTypes(): void
    {
        register_graphql_object_type('AboutPage', [
            'description' => 'Static "About" page content, managed via the About Page options screen.',
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

    public function resolveAboutPage(): array
    {
        return CacheService::getOrSet('about_page', function () {
            $portrait = get_field('portrait', 'option');
            $body     = get_field('body', 'option');

            return [
                'founderName'     => get_field('founderName', 'option'),
                'founderRole'     => get_field('founderRole', 'option'),
                'portraitUrl'     => $portrait['url'] ?? null,
                'portraitAlt'     => $portrait['alt'] ?? null,
                'body'            => $body ? apply_filters('the_content', $body) : null,
                'buildingsCount'  => (int) get_field('statBuildings', 'option') ?: null,
                'countriesCount'  => (int) get_field('statCountries', 'option') ?: null,
                'architectsCount' => (int) get_field('statArchitects', 'option') ?: null,
                'launchYear'      => (int) get_field('statLaunchYear', 'option') ?: null,
            ];
        }, HOUR_IN_SECONDS);
    }
}

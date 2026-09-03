<?php

namespace Brut\GraphQL;

use Brut\Services\ArchitectStatsService;
use Brut\Services\CacheService;
use Brut\Utils\ContentHelper;
use Brut\Utils\PostHelper;
use GraphQL\Error\UserError;

class ArchitectsGraphQL
{
    public function registerTypes(): void
    {
        $this->registerSharedTypes();
        $this->registerQueries();
    }

    private function registerSharedTypes(): void
    {
        register_graphql_object_type('Architect', [
            'description' => 'An architect and their linked sights.',
            'fields'      => [
                'id'          => ['type' => 'Int'],
                'count'       => ['type' => 'Int'],
                'title'       => ['type' => 'String'],
                'slug'        => ['type' => 'String'],
                'firstName'   => ['type' => 'String', 'resolve' => fn($a) => $a['first_name'] ?? null],
                'lastName'    => ['type' => 'String', 'resolve' => fn($a) => $a['last_name'] ?? null],
                'fullName'    => ['type' => 'String', 'resolve' => fn($a) => $a['full_name'] ?? null],
                'description' => ['type' => 'String'],
                'wikiLink'    => ['type' => 'String', 'resolve' => fn($a) => $a['wiki_link'] ?? null],
                'image'       => ['type' => 'BrutImage'],
            ],
        ]);
    }

    private function registerQueries(): void
    {
        register_graphql_field('RootQuery', 'architects', [
            'type'        => ['list_of' => 'Architect'],
            'description' => 'All published architects, ordered by title.',
            'resolve'     => [$this, 'resolveArchitects'],
        ]);

        register_graphql_field('RootQuery', 'architect', [
            'type'        => 'Architect',
            'description' => 'Get a single architect by ID or slug.',
            'args'        => [
                'identifier' => ['type' => ['non_null' => 'String']],
            ],
            'resolve'     => [$this, 'resolveArchitect'],
        ]);

        register_graphql_field('RootQuery', 'popularArchitects', [
            'type'        => ['list_of' => 'Architect'],
            'description' => 'Up to 6 architects, ranked by map search views, falling back to linked sight count.',
            'resolve'     => [$this, 'resolvePopularArchitects'],
        ]);

        register_graphql_field('RootQuery', 'searchArchitects', [
            'type'        => ['list_of' => 'Architect'],
            'description' => 'Architects matching a first/last name or title search.',
            'args'        => [
                'query' => ['type' => ['non_null' => 'String']],
            ],
            'resolve'     => [$this, 'resolveSearchArchitects'],
        ]);
    }

    public function resolveArchitects(): array
    {
        return CacheService::getOrSet('architects', function () {
            $architects = get_posts([
                'post_type'   => 'architect',
                'post_status' => 'publish',
                'numberposts' => -1,
                'orderby'     => 'title',
                'order'       => 'ASC',
                'fields'      => 'ids',
            ]);

            $counts = $this->getArchitectSightCounts();

            return array_map(fn($id) => ContentHelper::mapArchitect($id, $counts[$id] ?? 0), $architects);
        });
    }

    public function resolveArchitect($root, array $args): array
    {
        $identifier = $args['identifier'];
        $post = is_numeric($identifier)
            ? PostHelper::getPublishedPost((int) $identifier, 'architect')
            : PostHelper::getPostBySlug($identifier, 'architect');

        if (!$post) {
            throw new UserError('Architect not found');
        }

        $id = $post->ID;

        return CacheService::getOrSet("architect_{$id}", function () use ($id) {
            $counts = $this->getArchitectSightCounts();

            return ContentHelper::mapArchitect($id, $counts[$id] ?? 0);
        });
    }

    public function resolvePopularArchitects(): array
    {
        global $wpdb;

        return CacheService::getOrSet('architects_popular', function () use ($wpdb) {
            $popular = [];

            $options = $wpdb->get_results("
                SELECT option_name, option_value
                FROM $wpdb->options
                WHERE option_name LIKE 'architect_views_%'
            ");

            foreach ($options as $opt) {
                $id           = (int) str_replace('architect_views_', '', $opt->option_name);
                $popular[$id] = (int) $opt->option_value;
            }

            $fallback = $this->getArchitectSightCounts();

            arsort($popular);
            arsort($fallback);

            $top = [];

            foreach (array_keys($popular) as $id) {
                $top[] = ContentHelper::mapArchitect($id, $popular[$id]);
                if (count($top) >= 6) {
                    break;
                }
            }

            if (count($top) < 6) {
                $alreadyPicked = array_column($top, 'id');

                foreach (array_keys($fallback) as $id) {
                    if (in_array($id, $alreadyPicked, true)) {
                        continue;
                    }

                    $top[] = ContentHelper::mapArchitect($id, $fallback[$id]);
                    if (count($top) >= 6) {
                        break;
                    }
                }
            }

            return $top;
        }, DAY_IN_SECONDS);
    }

    public function resolveSearchArchitects($root, array $args): array
    {
        $query = sanitize_text_field($args['query'] ?? '');

        if (empty($query)) {
            throw new UserError('Missing query param');
        }

        $posts  = ArchitectStatsService::searchArchitectsByQuery($query);
        $counts = $this->getArchitectSightCounts();

        return array_map(fn($post) => ContentHelper::mapArchitect($post->ID, $counts[$post->ID] ?? 0), $posts);
    }

    /**
     * Number of published sights linked to each architect, keyed by architect post ID.
     * Computed with a single query instead of one per architect.
     */
    private function getArchitectSightCounts(): array
    {
        global $wpdb;

        $rows = $wpdb->get_col($wpdb->prepare("
            SELECT pm.meta_value
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
            WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'
        ", 'choose_architects', 'sight'));

        $counts = [];

        foreach ($rows as $serialized) {
            $architectIDs = maybe_unserialize($serialized);

            if (!is_array($architectIDs)) {
                continue;
            }

            foreach ($architectIDs as $architectID) {
                $architectID          = (int) $architectID;
                $counts[$architectID] = ($counts[$architectID] ?? 0) + 1;
            }
        }

        return $counts;
    }
}

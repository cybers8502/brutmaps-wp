<?php

namespace Brut\GraphQL;

use Brut\Services\ArchitectStatsService;
use Brut\Services\CacheService;
use Brut\Utils\ContentHelper;
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
                'id'        => ['type' => 'Int'],
                'count'     => ['type' => 'Int'],
                'title'     => ['type' => 'String'],
                'firstName' => ['type' => 'String', 'resolve' => fn($a) => $a['first_name'] ?? null],
                'lastName'  => ['type' => 'String', 'resolve' => fn($a) => $a['last_name'] ?? null],
                'fullName'  => ['type' => 'String', 'resolve' => fn($a) => $a['full_name'] ?? null],
                'image'     => ['type' => 'BrutImage'],
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
            'description' => 'A single architect by ID.',
            'args'        => [
                'id' => ['type' => ['non_null' => 'Int']],
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

            return array_map(fn($id) => ContentHelper::mapArchitect($id), $architects);
        });
    }

    public function resolveArchitect($root, array $args): array
    {
        $id = (int) $args['id'];

        $data = CacheService::getOrSet("architect_{$id}", function () use ($id) {
            $post = get_post($id);

            if (!$post || $post->post_type !== 'architect' || $post->post_status !== 'publish') {
                return null;
            }

            return ContentHelper::mapArchitect($id);
        });

        if (!$data) {
            throw new UserError('Architect not found');
        }

        return $data;
    }

    public function resolvePopularArchitects(): array
    {
        global $wpdb;

        return CacheService::getOrSet('architects_popular', function () use ($wpdb) {
            $popular  = [];
            $fallback = [];

            $options = $wpdb->get_results("
                SELECT option_name, option_value
                FROM $wpdb->options
                WHERE option_name LIKE 'architect_views_%'
            ");

            foreach ($options as $opt) {
                $id           = (int) str_replace('architect_views_', '', $opt->option_name);
                $popular[$id] = (int) $opt->option_value;
            }

            $all_architects = get_posts([
                'post_type'   => 'architect',
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields'      => 'ids',
            ]);

            foreach ($all_architects as $architectID) {
                if (isset($popular[$architectID])) {
                    continue;
                }

                $linked = get_posts([
                    'post_type'   => 'sight',
                    'post_status' => 'publish',
                    'meta_query'  => [
                        [
                            'key'     => 'choose_architects',
                            'value'   => '"' . $architectID . '"',
                            'compare' => 'LIKE',
                        ],
                    ],
                    'fields'      => 'ids',
                ]);

                if (count($linked) > 0) {
                    $fallback[$architectID] = count($linked);
                }
            }

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
                foreach (array_keys($fallback) as $id) {
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

        $posts = ArchitectStatsService::searchArchitectsByQuery($query);

        return array_map(fn($post) => ContentHelper::mapArchitect($post->ID), $posts);
    }
}

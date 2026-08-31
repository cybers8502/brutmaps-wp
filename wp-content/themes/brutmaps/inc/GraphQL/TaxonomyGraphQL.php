<?php

namespace Brut\GraphQL;

use Brut\Services\CacheService;
use GraphQL\Error\UserError;

class TaxonomyGraphQL
{
    public function registerTypes(): void
    {
        register_graphql_object_type('TaxonomyTerm', [
            'description' => 'A taxonomy term, with its direct children (if any).',
            'fields'      => [
                'id'            => ['type' => 'Int'],
                'label'         => ['type' => 'String'],
                'slug'          => ['type' => 'String'],
                'count'         => ['type' => 'Int'],
                'subcategories' => ['type' => ['list_of' => 'TaxonomyTerm']],
            ],
        ]);

        register_graphql_field('RootQuery', 'taxonomy', [
            'type'        => ['list_of' => 'TaxonomyTerm'],
            'description' => 'Top-level terms (with their children nested) of the given taxonomy.',
            'args'        => [
                'taxonomy' => ['type' => ['non_null' => 'String']],
            ],
            'resolve'     => [$this, 'resolveTaxonomy'],
        ]);
    }

    public function resolveTaxonomy($root, array $args): array
    {
        $taxonomy = $args['taxonomy'];

        if (empty($taxonomy)) {
            throw new UserError('Missing taxonomy param');
        }

        return CacheService::getOrSet("taxonomy_{$taxonomy}", function () use ($taxonomy) {
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]);

            $parentTerms = array_filter($terms, fn($term) => $term->parent == 0);
            $childTerms  = array_filter($terms, fn($term) => $term->parent != 0);

            $structured = [];

            foreach ($parentTerms as $parent) {
                $children = array_filter($childTerms, fn($child) => $child->parent == $parent->term_id);

                $structured[] = [
                    'id'            => $parent->term_id,
                    'label'         => $parent->name,
                    'slug'          => $parent->slug,
                    'count'         => $parent->count,
                    'subcategories' => array_map(fn($child) => [
                        'id'    => $child->term_id,
                        'label' => $child->name,
                        'slug'  => $child->slug,
                        'count' => $child->count,
                    ], array_values($children)),
                ];
            }

            return $structured;
        });
    }
}

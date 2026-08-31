<?php

namespace Brut\GraphQL;

use Brut\Services\FavoritesService;
use GraphQL\Error\UserError;

class FavoritesGraphQL
{
    public function registerTypes(): void
    {
        $this->registerSharedTypes();
        $this->registerQueries();
        $this->registerMutations();
    }

    private function registerSharedTypes(): void
    {
        register_graphql_object_type('Favorites', [
            'description' => 'A user\'s sights, bucketed by category.',
            'fields'      => [
                'favorite' => ['type' => ['list_of' => 'Int']],
                'wantToGo' => [
                    'type'    => ['list_of' => 'Int'],
                    'resolve' => fn($preferences) => $preferences['want_to_go'] ?? [],
                ],
                'visited'  => ['type' => ['list_of' => 'Int']],
                'hidden'   => ['type' => ['list_of' => 'Int']],
            ],
        ]);
    }

    private function registerQueries(): void
    {
        register_graphql_field('RootQuery', 'favorites', [
            'type'        => 'Favorites',
            'description' => 'The current user\'s favorite/want-to-go/visited/hidden sights.',
            'resolve'     => [$this, 'resolveFavorites'],
        ]);
    }

    private function registerMutations(): void
    {
        register_graphql_mutation('toggleFavorite', [
            'inputFields'         => [
                'sightId'  => ['type' => ['non_null' => 'Int']],
                'category' => ['type' => 'String'],
            ],
            'outputFields'        => [
                'added'     => ['type' => 'Boolean'],
                'favorites' => ['type' => 'Favorites'],
            ],
            'mutateAndGetPayload' => [$this, 'resolveToggleFavorite'],
        ]);
    }

    public function resolveFavorites(): array
    {
        if (!is_user_logged_in()) {
            throw new UserError('You must be logged in to view favorites.');
        }

        return FavoritesService::getPreferences(get_current_user_id());
    }

    public function resolveToggleFavorite(array $input): array
    {
        if (!is_user_logged_in()) {
            throw new UserError('You must be logged in to change favorites.');
        }

        $sight_id = (int) $input['sightId'];
        $category = $input['category'] ?? 'favorite';

        if (!get_post($sight_id)) {
            throw new UserError('Invalid sight ID');
        }

        if (!in_array($category, FavoritesService::CATEGORIES, true)) {
            throw new UserError('Invalid category');
        }

        $result = FavoritesService::toggle(get_current_user_id(), $sight_id, $category);

        return [
            'added'     => $result['added'],
            'favorites' => $result['preferences'],
        ];
    }
}

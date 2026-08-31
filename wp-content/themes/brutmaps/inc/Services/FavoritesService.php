<?php

namespace Brut\Services;

/**
 * Reads/writes the `sight_preferences` user meta: sight IDs bucketed into
 * favorite/want_to_go/visited/hidden. Shared by FavoritesGraphQL and the
 * sightsMap resolver (ObjectsGraphQL) that filters out hidden sights and
 * annotates the rest with their category.
 */
class FavoritesService
{
    public const CATEGORIES = ['favorite', 'want_to_go', 'visited', 'hidden'];

    public static function getPreferences(int $user_id): array
    {
        $preferences = get_user_meta($user_id, 'sight_preferences', true);

        return is_array($preferences) ? $preferences : array_fill_keys(self::CATEGORIES, []);
    }

    /**
     * @return array{preferences: array, added: bool}
     */
    public static function toggle(int $user_id, int $sight_id, string $category): array
    {
        $preferences = self::getPreferences($user_id);
        $items       = $preferences[$category] ?? [];
        $added       = !in_array($sight_id, $items);

        if ($added) {
            $items[] = $sight_id;
        } else {
            $items = array_values(array_diff($items, [$sight_id]));
        }

        $preferences[$category] = $items;
        update_user_meta($user_id, 'sight_preferences', $preferences);

        return ['preferences' => $preferences, 'added' => $added];
    }
}

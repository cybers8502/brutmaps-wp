<?php

namespace Brut\Services;

use WP_Query;

class ArchitectStatsService
{
    public static function searchArchitectsByQuery(string $query): array
    {
        $parts = explode(' ', $query);
        $meta_subqueries = ['relation' => 'OR'];

        foreach ($parts as $part) {
            $meta_subqueries[] = [
                'key'     => 'first_name',
                'value'   => $part,
                'compare' => 'LIKE',
            ];
            $meta_subqueries[] = [
                'key'     => 'last_name',
                'value'   => $part,
                'compare' => 'LIKE',
            ];
        }

        $args = [
            'post_type'      => 'architect',
            'post_status'    => 'publish',
            'numberposts'    => 50,
            's'              => $query,
            'meta_query'     => $meta_subqueries,
        ];

        // Використовуємо WP_Query замість get_posts, бо get_posts ігнорує частину 's' + meta_query комбінацій
        $query_obj = new WP_Query($args);

        return $query_obj->posts;
    }


    /**
     * Збільшує кількість переглядів (популярність) кожного з переданих архітекторів.
     *
     * @param int $architectId ID архітекторів
     * @return void
     */
    public static function incrementSearchCount(int $architectId): void
    {
        $optionKey = 'architect_views_' . $architectId;
        $current = (int) get_option($optionKey, 0);
        update_option($optionKey, $current + 1);
    }
}

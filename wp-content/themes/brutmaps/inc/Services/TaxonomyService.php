<?php

namespace Brut\Services;

class TaxonomyService
{
    /**
     * Отримати всі терміни заданої таксономії.
     *
     * @param string $taxonomy
     * @return array
     */
    public static function getTerms(string $taxonomy): array
    {
        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms)) {
            return [];
        }

        return array_map(function ($term) {
            return [
                'parent'    => $term->parent,
                'id'        => $term->term_id,
                'slug'      => $term->slug,
                'name'      => $term->name,
                'count'     => $term->count,
            ];
        }, $terms);
    }
}

<?php

namespace Brut\Admin\Filters;

class SightAdminFiltersService
{
    public function __construct()
    {
        add_action('restrict_manage_posts', [$this, 'addFilters']);
        add_action('pre_get_posts', [$this, 'filterQuery']);
    }

    public function addFilters(): void
    {
        global $typenow;

        if ($typenow !== 'sight') {
            return;
        }

        $this->renderArchitectFilter();
        $this->renderTaxonomyFilter();
    }

    private function renderArchitectFilter(): void
    {
        $architects = get_posts([
            'post_type'   => 'architect',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC'
        ]);

        $selected = $_GET['filter_architect'] ?? '';

        echo '<select name="filter_architect">';
        echo '<option value="">' . __('All Architects', 'brut') . '</option>';
        foreach ($architects as $architect) {
            printf(
                '<option value="%d" %s>%s</option>',
                $architect->ID,
                selected($selected, $architect->ID, false),
                esc_html($architect->post_title)
            );
        }
        echo '</select>';
    }

    private function renderTaxonomyFilter(): void
    {
        $taxonomy = 'taxonomy';
        $selected = $_GET['filter_taxonomy'] ?? '';

        wp_dropdown_categories([
            'show_option_all' => __('All Object Types', 'brut'),
            'taxonomy'        => $taxonomy,
            'name'            => 'filter_taxonomy',
            'orderby'         => 'name',
            'selected'        => $selected,
            'hierarchical'    => true,
            'depth'           => 2,
            'show_count'      => false,
            'hide_empty'      => true,
            'value_field'     => 'slug',
        ]);
    }

    public function filterQuery(\WP_Query $query): void
    {
        global $pagenow, $typenow;

        if (
            is_admin() &&
            $pagenow === 'edit.php' &&
            $typenow === 'sight' &&
            $query->is_main_query()
        ) {
            // Architect filter (ACF field: choose_architects)
            if (!empty($_GET['filter_architect']) && is_numeric($_GET['filter_architect'])) {
                $query->set('meta_query', [
                    [
                        'key'     => 'choose_architects',
                        'value'   => '"' . intval($_GET['filter_architect']) . '"',
                        'compare' => 'LIKE'
                    ]
                ]);
            }
            error_log('Current filter: ' . $_GET['filter_taxonomy'] ?? 'none');
            error_log(print_r($query->query_vars, true));

            // Taxonomy filter
            if (!empty($_GET['taxonomy']) && is_numeric($_GET['taxonomy'])) {
                $query->set('tax_query', [
                    [
                        'taxonomy' => 'taxonomy',
                        'field'    => 'term_id',
                        'terms'    => sanitize_title($_GET['filter_taxonomy']),
                    ]
                ]);
            }
        }
    }
}

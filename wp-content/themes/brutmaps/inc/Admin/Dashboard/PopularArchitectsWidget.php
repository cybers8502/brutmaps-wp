<?php

namespace Brut\Admin\Dashboard;

class PopularArchitectsWidget
{
    public function __construct()
    {
        add_action('wp_dashboard_setup', [$this, 'registerWidget']);
    }

    public function registerWidget(): void
    {
        wp_add_dashboard_widget(
            'popular_architects_widget',
            'Popular Architects (Last 24h)',
            [$this, 'renderWidget']
        );
    }

    public function renderWidget(): void
    {
        $architects = get_transient('popular_architects_cached');

        if (!$architects) {
            $architects = $this->generateData();
            set_transient('popular_architects_cached', $architects, DAY_IN_SECONDS);
        }

        if (empty($architects)) {
            echo '<p>No data available.</p>';
            return;
        }

        echo '<ul>';
        foreach ($architects as $item) {
            echo '<li><strong>' . esc_html($item['name']) . '</strong> — ' . esc_html($item['count']) . ' views</li>';
        }
        echo '</ul>';
    }

    private function generateData(): array
    {
        global $wpdb;

        $options = $wpdb->get_results("
            SELECT option_name, option_value 
            FROM $wpdb->options 
            WHERE option_name LIKE 'architect_views_%'
        ");

        $data = [];

        foreach ($options as $opt) {
            $id = (int) str_replace('architect_views_', '', $opt->option_name);
            if (!$id) {
                continue;
            }

            $data[] = [
                'id'    => $id,
                'count' => (int) $opt->option_value,
                'name'  => get_the_title($id) ?: "Unknown (#$id)",
            ];
        }

        usort($data, fn($a, $b) => $b['count'] <=> $a['count']);

        return array_slice($data, 0, 10);
    }
}

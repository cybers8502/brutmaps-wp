<?php

namespace Brut\Admin\ACFFieldsManager;

use WP_Post;

class AboutPageACFFieldsManager
{
    private const PAGE_SLUG = 'about';

    public function boot(): void
    {
        add_action('init', [$this, 'ensurePageExists']);
        add_action('acf/init', [$this, 'registerFieldGroup']);
    }

    /**
     * The About page is real WP content (not an options page) specifically
     * so it gets normal Page treatment — shows up under Pages, and gets a
     * Rank Math SEO meta box, unlike an ACF options page.
     */
    public function ensurePageExists(): void
    {
        if (self::getPage()) {
            return;
        }

        wp_insert_post([
            'post_title'  => 'About',
            'post_name'   => self::PAGE_SLUG,
            'post_type'   => 'page',
            'post_status' => 'publish',
        ]);
    }

    public static function getPage(): ?WP_Post
    {
        $page = get_page_by_path(self::PAGE_SLUG);

        return $page instanceof WP_Post ? $page : null;
    }

    public function registerFieldGroup(): void
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        $page = self::getPage();

        if (!$page) {
            return;
        }

        acf_add_local_field_group([
            'key'                   => 'about_page',
            'title'                 => 'About Page',
            'fields'                => $this->getFields(),
            'location'              => [
                [
                    [
                        'param'    => 'page',
                        'operator' => '==',
                        'value'    => $page->ID,
                    ],
                ],
            ],
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
        ]);
    }

    private function getFields(): array
    {
        return [
            [
                'key'      => 'field_about_founderName',
                'label'    => 'Founder name',
                'name'     => 'founderName',
                'type'     => 'text',
                'required' => 0,
            ],
            [
                'key'      => 'field_about_founderRole',
                'label'    => 'Founder role',
                'name'     => 'founderRole',
                'type'     => 'text',
                'required' => 0,
            ],
            [
                'key'           => 'field_about_portrait',
                'label'         => 'Portrait',
                'name'          => 'portrait',
                'type'          => 'image',
                'required'      => 0,
                'return_format' => 'array',
            ],
            [
                'key'      => 'field_about_body',
                'label'    => 'Body',
                'name'     => 'body',
                'type'     => 'wysiwyg',
                'required' => 0,
            ],
            [
                'key'          => 'field_about_statLaunchYear',
                'label'        => 'Launch year',
                'name'         => 'statLaunchYear',
                'type'         => 'number',
                'required'     => 0,
                'instructions' => 'Buildings/countries/architects counts are computed live and not editable here.',
            ],
        ];
    }
}

<?php

namespace Brut\Console;

use WP_CLI;
use WP_Query;

class ClearCacheCommand
{
    public static function register(): void
    {
        WP_CLI::add_command('brut cache clear', [self::class, 'clear']);

        //TODO delete this migrate line
        WP_CLI::add_command('migrate image_authors', [self::class, 'migrate']);
    }

    /**
     * Очищення кешу по ключу або префіксу.
     *
     * ## Опції
     *
     * [--key=<key>]
     * : Очищує кеш по точному ключу.
     *
     * [--prefix=<prefix>]
     * : Очищує всі кеші з певним префіксом (перебір в options таблиці).
     *
     * ## Приклади
     *     wp brut cache clear --key=sights_cache_xxx
     *     wp brut cache clear --prefix=sights_cache_
     *
     * @param array $args
     * @param array $assoc_args
     */
    public static function clear(array $args, array $assoc_args): void
    {
        global $wpdb;

        if (!empty($assoc_args['key'])) {
            $key = $assoc_args['key'];
            delete_transient($key);
            WP_CLI::success("Кеш '$key' видалено.");
        } elseif (!empty($assoc_args['prefix'])) {
            $prefix = '_transient_' . $assoc_args['prefix'] . '%';
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $prefix
                )
            );
            WP_CLI::success("Кеші з префіксом '{$assoc_args['prefix']}' очищено.");
        } else {
            WP_CLI::error('Потрібно вказати --key або --prefix.');
        }
    }

    //TODO delete this migrate function
    public static function migrate(array $args, array $assoc_args): void
    {
        $count_updated = 0;

        $query = new WP_Query([
            'post_type' => 'sight',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'draft'),
        ]);

        $fieldsToDelete = [
            'main_content',
            'main_image',
            'choose_associated_people',
            'source',
            'main_image_author_id',
            'main_image_author_name',
        ];

        if ($query->have_posts()) {
            WP_CLI::log('Starting migration...');

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                // MAIN IMAGE
                $main_image = get_field('main_image', $post_id);
                $main_image_id = is_array($main_image) ? $main_image['ID'] : $main_image;
                $main_image_author_id = get_field('main_image_author_id', $post_id);

                if ($main_image_id && $main_image_author_id && $main_image_author_id != 2982) {
                    update_field('attached_image_author_id', $main_image_author_id, $main_image_id);
                    $count_updated++;
                    WP_CLI::log("Updated main image ID {$main_image_id} with author ID {$main_image_author_id}");
                }

                // GALLERY IMAGES
                if (have_rows('gallery', $post_id)) {
                    while (have_rows('gallery', $post_id)) {
                        the_row();

                        $gallery_image = get_sub_field('gallery_image');
                        $gallery_image_id = is_array($gallery_image) ? $gallery_image['ID'] : $gallery_image;
                        $gallery_image_author_id = get_sub_field('gallery_image_author_id');

                        if ($gallery_image_id && $gallery_image_author_id && $gallery_image_author_id != 2982) {
                            update_field('attached_image_author_id', $gallery_image_author_id, $gallery_image_id);
                            $count_updated++;
                            WP_CLI::log("Updated gallery image ID {$gallery_image_id} with author ID {$gallery_image_author_id}");
                        }
                    }
                }

                // 1️⃣ Отримуємо опис
                $description = get_field('main_content', $post_id);

                // 2️⃣ Отримуємо main_image
                $main_image = get_field('main_image', $post_id);
                $main_image_id = is_array($main_image) ? $main_image['ID'] : $main_image;

                $gallery_ids = [];

                if ($main_image_id && $main_image_id != 3176) {
                    $gallery_ids[] = $main_image_id;
                    set_post_thumbnail($post_id, $main_image_id);
                }

                // 3️⃣ Отримуємо галерею
                $gallery = get_field('field_object_gallery', $post_id);
                if ($gallery) {
                    foreach ($gallery as $item) {
                        $image = $item['gallery_image'];
                        if (is_array($image) && isset($image['ID'])) {
                            $gallery_ids[] = $image['ID'];
                        }
                    }
                }

                $gallery_block = '';
                if (!empty($gallery_ids)) {
                    $gallery_block = '<!-- wp:gallery {"columns":3,"linkTo":"none","className":"columns-default"} -->
<figure class="wp-block-gallery has-nested-images columns-3 is-cropped columns-default">';
                    foreach ($gallery_ids as $id) {
                        $gallery_block .= '
    <!-- wp:image {"id":' . $id . ',"sizeSlug":"large","linkDestination":"none"} --><figure class="wp-block-image size-large">
    <img src="' . wp_get_attachment_image_url($id, 'large') . '" alt="' . esc_attr(get_post_meta($id, '_wp_attachment_image_alt', true)) . '" class="wp-image-' . $id . '"/>
    </figure><!-- /wp:image -->';
                    }
                    $gallery_block .= '
</figure>
<!-- /wp:gallery -->';
                }

                if ($gallery_block != '' || $description != '') {
                    // 4️⃣ Формуємо повний контент
                    $new_content = $gallery_block . "\n\n" . $description;

                    // 5️⃣ Оновлюємо пост
                    wp_update_post([
                        'ID' => $post_id,
                        'post_content' => $new_content,
                    ]);

                    WP_CLI::success("Post {$post_id} updated with Gutenberg content.");
                }

                foreach ($fieldsToDelete as $metaKey) {
                    delete_post_meta($post_id, $metaKey);
                }
            }

            wp_reset_postdata();
            WP_CLI::success("Migration completed! Total images updated: {$count_updated}");
        } else {
            WP_CLI::warning('No objects found.');
        }

        $query_a = new WP_Query([
            'post_type' => 'architect',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'draft'),
        ]);

        if ($query_a->have_posts()) {
            WP_CLI::log('Starting architect migration...');

            while ($query_a->have_posts()) {
                $query_a->the_post();
                $post_id = get_the_ID();


                // MAIN IMAGE
                $main_image = get_field('main_image', $post_id);
                $main_image_id = is_array($main_image) ? $main_image['ID'] : $main_image;
                $main_image_author_id = get_field('main_image_author_id', $post_id);
                $main_image_source = get_field('wiki_link', $post_id);

                if ($main_image_id && $main_image_id != 2982) {
                    set_post_thumbnail($post_id, $main_image_id);
                }

                if ($main_image_id && $main_image_author_id && $main_image_author_id != 2982) {
                    update_field('attached_image_author_id', $main_image_author_id, $main_image_id);
                    update_field('attached_image_author_id', $main_image_source, $main_image_id);

                    $count_updated++;
                    WP_CLI::log("Updated main image ID {$main_image_id} with author ID {$main_image_author_id}");
                }

                delete_post_meta($post_id, 'field_architect_main_image');
            }

            wp_reset_postdata();
            WP_CLI::success("Migration completed! Total images updated: {$count_updated}");
        } else {
            WP_CLI::warning('No objects found.');
        }
    }
}

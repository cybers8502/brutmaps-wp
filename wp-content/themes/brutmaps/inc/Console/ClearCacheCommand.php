<?php

namespace Brut\Console;

use WP_CLI;

class ClearCacheCommand
{
    public static function register(): void
    {
        WP_CLI::add_command('brut cache clear', [self::class, 'clear']);
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
            $prefix = '_transient_'. $assoc_args['prefix'] . '%';
            $sql = $wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $prefix);
            $wpdb->query($sql);
            WP_CLI::success("Кеші з префіксом '{$assoc_args['prefix']}' очищено.");
        } else {
            WP_CLI::error('Потрібно вказати --key або --prefix.');
        }
    }
}

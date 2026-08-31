<?php

namespace Brut\Services;

class CacheService
{
    /**
     * Отримати з кешу або зберегти, якщо не існує.
     *
     * @param string $key Унікальний ключ кешу.
     * @param callable $callback Колбек, який повертає значення для кешування.
     * @param int $ttl Час життя кешу у секундах.
     * @return mixed
     */
    public static function getOrSet(string $key, callable $callback, int $ttl = WEEK_IN_SECONDS): mixed
    {
        $cached = get_transient($key);

        if ($cached !== false) {
            return $cached;
        }

        $value = call_user_func($callback);
        set_transient($key, $value, $ttl);

        return $value;
    }

    /**
     * Примусово очищує кеш по ключу.
     *
     * @param string $key
     * @return bool
     */
    public static function forget(string $key): bool
    {
        return delete_transient($key);
    }
}

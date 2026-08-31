<?php

namespace Brut\Security;

class RateLimiter
{
    /**
     * Перевіряє, чи перевищено ліміт запитів.
     *
     * @param string $key Унікальний ключ (IP, email hash тощо)
     * @param int $limit Кількість дозволених запитів
     * @param int $interval Інтервал у секундах (час життя ліміту)
     * @return bool true = перевищено ліміт
     */
    public static function isLimited(string $key, int $limit = 5, int $interval = 60): bool
    {
        $transientKey = 'rate_limit_' . md5($key);
        $current = get_transient($transientKey);

        if ($current === false) {
            set_transient($transientKey, 1, $interval);
            return false;
        }

        if ($current >= $limit) {
            return true;
        }

        set_transient($transientKey, $current + 1, $interval);
        return false;
    }

    /**
     * Скидає ліміт для заданого ключа.
     *
     * @param string $key
     * @return void
     */
    public static function reset(string $key): void
    {
        $transientKey = 'rate_limit_' . md5($key);
        delete_transient($transientKey);
    }

    /**
     * Отримує поточну кількість запитів.
     *
     * @param string $key
     * @return int
     */
    public static function getAttempts(string $key): int
    {
        $transientKey = 'rate_limit_' . md5($key);
        return (int) get_transient($transientKey);
    }
}

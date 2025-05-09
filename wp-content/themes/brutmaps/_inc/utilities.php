<?php

/**
 * Simple rate limiting through transient
 */
function is_rate_limited($key, $limit = 5, $interval = 60): bool
{
    $data = get_transient($key);

    if ($data === false) {
        set_transient($key, 1, $interval);
        return false;
    }

    if ($data >= $limit) {
        return true;
    }

    set_transient($key, $data + 1, $interval);
    return false;
}

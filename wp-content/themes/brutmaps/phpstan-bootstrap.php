<?php

/**
 * Constants that are defined outside the theme (by a companion plugin or
 * wp-config.php) but referenced in theme code. Declared here so PHPStan does
 * not report them as undefined.
 */

declare(strict_types=1);

defined('BASE_URL') || define('BASE_URL', 'brut/v1');

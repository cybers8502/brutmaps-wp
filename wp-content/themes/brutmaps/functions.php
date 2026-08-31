<?php

$brutmapsAutoload = __DIR__ . '/vendor/autoload.php';

if (is_readable($brutmapsAutoload)) {
    require_once $brutmapsAutoload;
} else {
    // No Composer install on the target (e.g. shared hosting). The project has
    // no runtime Composer dependencies, so a minimal PSR-4 autoloader for the
    // theme's own Brut\ classes is enough to boot. Run `composer install` for
    // the dev/QA toolchain.
    spl_autoload_register(static function (string $class): void {
        if (strpos($class, 'Brut\\') !== 0) {
            return;
        }

        $path = __DIR__ . '/inc/' . str_replace('\\', '/', substr($class, 5)) . '.php';

        if (is_readable($path)) {
            require $path;
        }
    });
}

(new \Brut\App())->boot();

if (defined('WP_CLI') && WP_CLI) {
    \Brut\Console\ClearCacheCommand::register();
}

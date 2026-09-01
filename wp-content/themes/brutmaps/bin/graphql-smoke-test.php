<?php

/**
 * Post-deploy smoke test: runs against a *live* bootstrapped WordPress (no
 * fixtures needed) to catch the two classes of bug that unit tests can't —
 * they only show up when the real WPGraphQL schema and real plugins are
 * loaded:
 *
 *  1. A theme-registered GraphQL field silently losing to a same-named field
 *     already registered by WPGraphQL core or a plugin (e.g. WooGraphQL),
 *     so it's permanently unreachable — no error at registration time, only
 *     a confusing schema mismatch on every request from the frontend.
 *  2. A plugin that's required at runtime (ACF, WooCommerce, ...) being
 *     absent — WordPress's `active_plugins` option can say "active" even
 *     when the plugin's files aren't on disk, so it has to be checked by
 *     actually calling something the plugin defines, not by asking WP
 *     whether it thinks the plugin is active.
 *
 * Usage (from the WP root):
 *   wp eval-file wp-content/themes/brutmaps/bin/graphql-smoke-test.php
 * Or from this theme directory:
 *   composer smoke-test
 *
 * Run this after every deploy (see the deploy steps in the project's repo
 * notes) — it exits non-zero on any failure.
 */

if (!function_exists('graphql')) {
    fwrite(STDERR, "FAIL: WPGraphQL isn't loaded (function graphql() doesn't exist). Wrong --path, or the plugin is missing/inactive.\n");
    exit(1);
}

$failures = [];
$pass     = function (string $label) {
    echo "  OK   {$label}\n";
};
$fail = function (string $label, string $detail) use (&$failures) {
    echo "  FAIL {$label}: {$detail}\n";
    $failures[] = $label;
};

echo "== Runtime plugin checks ==\n";
echo "(checked by calling something the plugin defines, not by asking WP whether it *thinks* it's active — see [[deploy_architecture]])\n";

$runtimeChecks = [
    'WPGraphQL'   => fn() => class_exists('WPGraphQL'),
    'ACF (get_field)' => fn() => function_exists('get_field'),
    'WooCommerce' => fn() => class_exists('WooCommerce'),
    'WooGraphQL'  => fn() => defined('WPGRAPHQL_WOOCOMMERCE_VERSION'),
];

foreach ($runtimeChecks as $label => $check) {
    if ($check()) {
        $pass($label);
    } else {
        $fail($label, 'expected function/class/constant not found at runtime');
    }
}

echo "\n== GraphQL field checks ==\n";
echo "(each of these is public, unauthenticated, and needs no fixture data — a plain query should always come back with no `errors`)\n";

/**
 * @var array<string, array{query: string, variables?: array<string, mixed>}>
 */
$queries = [
    'sightsMap (custom RootQuery field)' => [
        'query' => '{ sightsMap { featureCollection { type } } }',
    ],
    'sightTaxonomy (custom RootQuery field)' => [
        'query'     => 'query($taxonomy: String!) { sightTaxonomy(taxonomy: $taxonomy) { slug } }',
        'variables' => ['taxonomy' => 'taxonomy'],
    ],
    'sightsImages (custom RootQuery field)' => [
        'query' => '{ sightsImages(perPage: 1) { currentPage } }',
    ],
    'sightsCount (custom RootQuery field)' => [
        'query' => '{ sightsCount }',
    ],
    'architects (custom RootQuery field)' => [
        'query' => '{ architects { id } }',
    ],
    'popularArchitects (custom RootQuery field)' => [
        'query' => '{ popularArchitects { id } }',
    ],
    'searchArchitects (custom RootQuery field)' => [
        'query'     => 'query($q: String!) { searchArchitects(query: $q) { id } }',
        'variables' => ['q' => 'a'],
    ],
    'blogPosts (custom RootQuery field)' => [
        'query' => '{ blogPosts(perPage: 1) { posts { id } } }',
    ],
    'userCountries (custom RootQuery field)' => [
        'query' => '{ userCountries { code } }',
    ],
    'products (WooGraphQL RootQuery field)' => [
        'query' => '{ products(first: 1) { nodes { id } } }',
    ],
    'paymentGateways (WooGraphQL RootQuery field)' => [
        'query' => '{ paymentGateways { nodes { id } } }',
    ],
];

foreach ($queries as $label => $request) {
    $result = graphql([
        'query'     => $request['query'],
        'variables' => $request['variables'] ?? [],
    ]);

    $errors = $result['errors'] ?? null;

    if (empty($errors)) {
        $pass($label);
        continue;
    }

    $messages = implode('; ', array_map(fn($e) => $e['message'] ?? 'unknown error', $errors));
    $fail($label, $messages);
}

echo "\n";

if (!empty($failures)) {
    echo count($failures) . ' check(s) failed: ' . implode(', ', $failures) . "\n";
    exit(1);
}

echo "All smoke checks passed.\n";
exit(0);

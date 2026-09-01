<?php

/**
 * One-time (and re-runnable) backfill: tags every published `sight` with a
 * `country` taxonomy term, reverse-geocoded from its ACF `location` lat/lng
 * via OpenStreetMap's free Nominatim API (no API key needed).
 *
 * Idempotent — already-tagged sights are skipped, so safe to re-run after
 * adding new objects.
 *
 * Respects Nominatim's usage policy (https://operations.osmfoundation.org/policies/nominatim/):
 * max 1 request/second, and a descriptive User-Agent identifying the app.
 *
 * Usage (from the WP root):
 *   wp eval-file wp-content/themes/brutmaps/bin/backfill-country-taxonomy.php
 *
 * ~350 sights takes ~6 minutes (rate-limited to 1 req/sec).
 */

$ids = get_posts([
    'post_type'   => 'sight',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields'      => 'ids',
]);

$total   = count($ids);
$tagged  = 0;
$skipped = 0;
$failed  = 0;

echo "Found {$total} published sights.\n";

foreach ($ids as $id) {
    $existing = wp_get_post_terms($id, 'country', ['fields' => 'ids']);

    if (!is_wp_error($existing) && !empty($existing)) {
        $skipped++;
        continue;
    }

    $location = get_field('location', $id);

    if (!$location || !isset($location['lat'], $location['lng'])) {
        $failed++;
        echo "FAIL (no location) #{$id}\n";
        continue;
    }

    $url = add_query_arg([
        'format'        => 'jsonv2',
        'lat'           => $location['lat'],
        'lon'           => $location['lng'],
        'zoom'          => 3,
        'addressdetails' => 1,
    ], 'https://nominatim.openstreetmap.org/reverse');

    $response = wp_remote_get($url, [
        'headers' => [
            'User-Agent' => 'Brutmaps/1.0 (https://brutmaps.com; one-time sight country backfill)',
        ],
        'timeout' => 10,
    ]);

    if (is_wp_error($response)) {
        $failed++;
        echo "FAIL (request error) #{$id}: " . $response->get_error_message() . "\n";
        sleep(1);
        continue;
    }

    $body    = json_decode(wp_remote_retrieve_body($response), true);
    $country = $body['address']['country'] ?? null;

    if (!$country) {
        $failed++;
        echo "FAIL (no country in response) #{$id} lat={$location['lat']} lng={$location['lng']}\n";
        sleep(1);
        continue;
    }

    $result = wp_set_post_terms($id, [$country], 'country', false);

    if (is_wp_error($result)) {
        $failed++;
        echo "FAIL (term assign) #{$id}: " . $result->get_error_message() . "\n";
    } else {
        $tagged++;
        echo "OK #{$id} -> {$country}\n";
    }

    sleep(1);
}

echo "\nDone. Tagged: {$tagged}, Skipped (already tagged): {$skipped}, Failed: {$failed}, Total: {$total}\n";

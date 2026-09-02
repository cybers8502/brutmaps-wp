<?php

namespace Brut\Assets;

class AssetManager
{
    private const MANIFEST_PATH = '/publish/.vite/manifest.json';
    private const MANIFEST_ENTRY = 'index.html';
    private const STYLE_HANDLE_PREFIX = 'custom_style';

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
        add_filter('script_loader_tag', [$this, 'addModuleAttribute'], 10, 3);
        add_filter('style_loader_tag', [$this, 'addCrossOriginToStyles'], 10, 2);
    }

    public function enqueue()
    {
        wp_deregister_script('jquery');

        $entry = $this->readManifestEntry();

        if ($entry === null) {
            return;
        }

        wp_enqueue_script('custom_module_script', get_template_directory_uri() . '/publish/' . $entry['file'], [], null, true);

        foreach ($entry['css'] ?? [] as $index => $css) {
            $handle = $index === 0 ? self::STYLE_HANDLE_PREFIX : self::STYLE_HANDLE_PREFIX . '_' . $index;
            wp_enqueue_style($handle, get_template_directory_uri() . '/publish/' . $css, [], null);
        }

        if (is_checkout()) {
            wp_enqueue_style('checkout_page_style', get_template_directory_uri() . '/publish/checkout-page.css', [], null);
        }

        if (is_order_received_page()) {
            wp_enqueue_style('order_received_page_style', get_template_directory_uri() . '/publish/order-received-page.css', [], null);
        }
    }

    public function addModuleAttribute($tag, $handle, $src)
    {
        if ($handle === 'custom_module_script') {
            return '<script type="module" crossorigin src="' . esc_url($src) . '"></script>';
        }
        return $tag;
    }

    public function addCrossOriginToStyles($html, $handle)
    {
        if (strpos($handle, self::STYLE_HANDLE_PREFIX) === 0) {
            return str_replace('<link ', '<link crossorigin ', $html);
        }
        return $html;
    }

    /**
     * Reads the Vite build manifest to resolve the current hashed entry
     * filenames — the frontend build hashes assets on every build, so
     * these can't be hardcoded here.
     */
    private function readManifestEntry(): ?array
    {
        $manifestFile = get_template_directory() . self::MANIFEST_PATH;

        if (!is_readable($manifestFile)) {
            error_log('brutmaps: Vite manifest not found at ' . $manifestFile . ' — run the frontend build.');
            return null;
        }

        $manifest = json_decode((string) file_get_contents($manifestFile), true);

        if (!isset($manifest[self::MANIFEST_ENTRY]['file'])) {
            error_log('brutmaps: Vite manifest is missing the "' . self::MANIFEST_ENTRY . '" entry.');
            return null;
        }

        return $manifest[self::MANIFEST_ENTRY];
    }
}

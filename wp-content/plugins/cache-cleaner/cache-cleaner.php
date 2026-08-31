<?php
/*
    Plugin Name: BM Cache Cleaner
    Description: Adds a cache clean button in the admin bar and Gutenberg editor for administrators.
    Version: 1.0
*/

if (!defined('ABSPATH')) exit;

class BM_Cache_Cleaner {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_bar_menu', [$this, 'add_admin_bar_item'], 100);
        add_action('admin_footer', [$this, 'render_interface']);
        add_action('wp_ajax_bm_cache_cleaner_clear', [$this, 'ajax_clear']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_script']);
    }

    // підключення скрипта для шапки
    public function enqueue_scripts() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            return;
        }

        if (!is_admin_bar_showing()) {
            return;
        }

        wp_enqueue_script(
            'bm-cache-cleaner',
            plugin_dir_url(__FILE__) . 'cache-cleaner.js',
            [],
            '1.0',
            true
        );

        wp_localize_script('bm-cache-cleaner', 'BMCacheCleaner', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('bm-cache-cleaner'),
        ]);
    }

    // підключення скрипта для гутенберг
    public function enqueue_editor_script() {
        $asset_file = plugin_dir_path(__FILE__) . 'build/index.asset.php';
        $asset = file_exists($asset_file) ? require $asset_file : [
            'dependencies' => ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data'],
            'version'      => filemtime(plugin_dir_path(__FILE__) . 'build/index.js'),
        ];

        wp_enqueue_script(
            'bm-cache-cleaner-editor',
            plugin_dir_url(__FILE__) . 'build/index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_localize_script('bm-cache-cleaner-editor', 'BMCacheCleaner', [
            'nonce'    => wp_create_nonce('bm-cache-cleaner'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    public function add_admin_bar_item($admin_bar) {
        if (!current_user_can('manage_options')) return;

        $admin_bar->add_node([
            'id'    => 'bm-cache-cleaner',
            'title' => '🧹 Cache Cleaner',
            'href'  => '#',
            'meta'  => ['title' => 'Cache Cleaner']
        ]);
    }

    public function render_interface() {
        if (!current_user_can('manage_options')) return;

        global $pagenow;

        // 🟡 1. Якщо це сторінка редагування поста
        if ($pagenow === 'post.php' && isset($_GET['post'])) {
            $post_id   = (int) $_GET['post'];
            $post_type = get_post_type($post_id);
            if ($post_id && $post_type) {
                $cache_key = "{$post_type}_cache_{$post_id}";
                ?>
              <div id="bm-cache-cleaner-panel" style="position:fixed;top:32px;left:340px;background:#fff;padding:10px;border:1px solid #ccc;z-index:9999;display:none">
                <strong>Clean catch:</strong> <code><?php echo esc_html($cache_key); ?></code><br>
                <button type="button" id="bm-cache-cleaner-btn" data-key="<?php echo esc_attr($cache_key); ?>">🧼 Clean</button>
                <span id="bm-cache-cleaner-msg" style="margin-left:10px;"></span>
              </div>
                <?php
                return;
            }
        }

        // 🟡 2. Якщо це сторінка редагування терміну (таксономії)
        if ($pagenow === 'term.php' && isset($_GET['taxonomy'])) {
            $taxonomy = sanitize_key($_GET['taxonomy']);
            $cache_key = "taxonomy_{$taxonomy}";
            ?>
          <div id="bm-cache-cleaner-panel" style="position:fixed;top:32px;left:340px;background:#fff;padding:10px;border:1px solid #ccc;z-index:9999;display:none">
            <strong>Clean catch:</strong> <code><?php echo esc_html($cache_key); ?></code><br>
            <button type="button" id="bm-cache-cleaner-btn" data-key="<?php echo esc_attr($cache_key); ?>">🧼 Clean</button>
            <span id="bm-cache-cleaner-msg" style="margin-left:10px;"></span>
          </div>
            <?php
            return;
        }

        // 🟡 3. Інші випадки — селект зі всіх
        $options = $this->get_transients();
        ?>
      <div id="bm-cache-cleaner-panel" style="position:fixed;top:32px;left:340px;background:#fff;padding:10px;border:1px solid #ccc;z-index:9999;display:none">
        <?php if (count($options) > 1) : ?>
        <select id="bm-cache-cleaner-key">
            <?php foreach ($options as $key): ?>
              <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($key); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" id="bm-cache-cleaner-btn">🧼 Clean</button>
        <span id="bm-cache-cleaner-msg" style="margin-left:10px;"></span>
        <?php else : ?>
          <p>🧼 Cache is empty.</p>
        <?php endif; ?>
        </div>
        <?php
    }

    private function get_transients(): array {
        global $wpdb;

        $results = $wpdb->get_col("
            SELECT option_name
            FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_%'
            AND option_name NOT LIKE '_transient_timeout_%'
        ");

        // Пост-типи: вбудовані + кастомні
        $post_types = get_post_types([], 'names');

        // Фіксовані кеші, які не залежать від типу
        $allowed_prefixes = [
            'taxonomy_',
            'architects',
            'architects_popular',
            'sights_cache_',
        ];

        // Додаткові динамічні префікси на основі post_type
        foreach ($post_types as $type) {
            $allowed_prefixes[] = "{$type}_cache_";
            $allowed_prefixes[] = "{$type}_";
        }

        $filtered = [];

        foreach ($results as $name) {
            $key = str_replace('_transient_', '', $name);
            foreach ($allowed_prefixes as $prefix) {
                if (strpos($key, $prefix) === 0) {
                    $filtered[] = $key;
                    break;
                }
            }
        }

        return $filtered;
    }

    public function ajax_clear() {
        check_ajax_referer('bm-cache-cleaner', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Access denied');

        $key = sanitize_text_field($_POST['key'] ?? '');
        if (!$key) wp_send_json_error('No key');

        $success = delete_transient($key);
        if ($success) {
            wp_send_json_success("Deleted: $key");
        } else {
            wp_send_json_error("Key not found: $key");
        }
    }
}

new BM_Cache_Cleaner();

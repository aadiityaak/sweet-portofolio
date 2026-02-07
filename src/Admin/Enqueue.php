<?php
namespace SweetPortofolio\Admin;

/**
 * Class Enqueue
 * 
 * Handles admin asset enqueueing.
 */
class Enqueue {

    /**
     * Initialize the class.
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_head', array($this, 'inline_scripts'));
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts() {
        // Only load on our admin page
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'portofolio-settings') !== false) {
            // Enqueue WordPress API script
            wp_enqueue_script('wp-api');

            // Add Alpine.js CDN with defer attribute - use unpkg to reduce truncation issues
            wp_enqueue_script( 'sweet-alpine-js-admin', 'https://unpkg.com/alpinejs@3.13.3/dist/cdn.min.js', array(), '3.13.3', false );

            // Debug: Log that scripts are being enqueued
            error_log('Enqueueing wp-api and alpine-js scripts for portofolio-settings page');

            // Add defer and data-no-optimize to Alpine.js script to avoid cache optimizer modifications
            add_filter('script_loader_tag', function($tag, $handle, $src) {
                if ($handle === 'sweet-alpine-js-admin') {
                    $tag = str_replace(' src', ' defer data-no-optimize="1" src', $tag);
                    return $tag;
                }
                return $tag;
            }, 10, 3);

            // WP Rocket exclusions (safe even if plugin not active)
            add_filter('rocket_delay_js_exclusions', function ($excluded) {
                $excluded[] = 'sweet-alpine-js-admin';
                $excluded[] = 'alpinejs@3.13.3/dist/cdn.min.js';
                return array_unique($excluded);
            });
            add_filter('rocket_exclude_defer_js', function ($excluded) {
                $excluded[] = 'sweet-alpine-js-admin';
                $excluded[] = 'alpinejs@3.13.3/dist/cdn.min.js';
                return array_unique($excluded);
            });

            // Localize script with REST API nonce
            wp_localize_script('sweet-alpine-js-admin', 'wpApiSettings', array(
                'nonce' => wp_create_nonce('wp_rest'),
                'root' => esc_url_raw(rest_url()),
            ));
        }
    }

    /**
     * Add inline scripts
     */
    public function inline_scripts() {
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'portofolio-settings') !== false) {
            // Debug: Log that inline scripts are being added
            error_log('Adding inline scripts for wpApiSettings');
            ?>
            <script>
            // Make sure wpApiSettings is available
            window.wpApiSettings = window.wpApiSettings || {};
            window.wpApiSettings.nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
            window.wpApiSettings.root = '<?php echo esc_url_raw(rest_url()); ?>';
            </script>
            <?php
        }
    }
}

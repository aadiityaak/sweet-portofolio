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

            // Add Alpine.js CDN with defer attribute - use unique handle for admin
            wp_enqueue_script( 'sweet-alpine-js-admin', 'https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js', array(), '3.13.3', false );

            // Debug: Log that scripts are being enqueued
            error_log('Enqueueing wp-api and alpine-js scripts for portofolio-settings page');

            // Add defer attribute to Alpine.js script
            add_filter('script_loader_tag', function($tag, $handle, $src) {
                if ($handle === 'sweet-alpine-js-admin') {
                    return str_replace(' src', ' defer src', $tag);
                }
                return $tag;
            }, 10, 3);

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

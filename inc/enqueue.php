<?php
/**
 * sweet-portofolio
 *
 * @package       SWEETPORTOFOLIO
 * @author        Aditya K
 * @license       gplv2
 * @version       1.0.0
 *
 * 
 **/

 function sweet_portofolio_frontend_style() {
    wp_enqueue_style('sweet-portofolio-style', SWEETPORTOFOLIO_URL.'assets/css/style.css', array(), SWEETPORTOFOLIO_VERSION);
    wp_enqueue_script('jquery');

    // Load portfolio script normally for all pages
    wp_enqueue_script('sweet-portofolio-script', SWEETPORTOFOLIO_URL.'assets/js/script.js', array('jquery'), SWEETPORTOFOLIO_VERSION, true);

    // Only load Alpine.js on portfolio list page or when needed
    if (is_page_template('page-portfolio-list.php') || get_query_var('pagename') === 'portfolio') {
        // Remove any existing Alpine.js to prevent conflicts
        wp_dequeue_script('alpine-js');
        wp_dequeue_script('sweet-alpine-js-admin');

        // Add Alpine.js to footer with proper dependencies
        wp_enqueue_script('sweet-alpine-js-frontend', 'https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js', array(), '3.13.3', true);

        // Defer script loading for better performance
        add_filter('script_loader_tag', function($tag, $handle) {
            if ($handle === 'sweet-alpine-js-frontend') {
                return str_replace('<script ', '<script defer ', $tag);
            }
            return $tag;
        }, 10, 2);
    }
}
add_action('wp_enqueue_scripts', 'sweet_portofolio_frontend_style');

function sweet_portofolio_admin_scripts() {
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
add_action('admin_enqueue_scripts', 'sweet_portofolio_admin_scripts');

// Add inline script to ensure Alpine.js is loaded
function sweet_portofolio_admin_inline_scripts() {
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'portofolio-settings') !== false) {
        // Debug: Log that inline scripts are being added
        error_log('Adding inline scripts for wpApiSettings');
        ?>
        <script>
        // Make sure wpApiSettings is available
        console.log('Setting up wpApiSettings');
        window.wpApiSettings = window.wpApiSettings || {};
        window.wpApiSettings.nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
        window.wpApiSettings.root = '<?php echo esc_url_raw(rest_url()); ?>';
        console.log('wpApiSettings configured:', window.wpApiSettings);
        </script>
        <?php
    }
}
add_action('admin_head', 'sweet_portofolio_admin_inline_scripts');

// Add inline script for frontend to ensure Alpine.js works properly
function sweet_portofolio_frontend_inline_scripts() {
    // Only add on portfolio list page
    if (is_page_template('page-portfolio-list.php') || get_query_var('pagename') === 'portfolio') {
        ?>
        <script>
        // Ensure Alpine.js is properly loaded
        window.addEventListener('load', function() {
            console.log('Page loaded, checking Alpine.js availability...');

            // Check if Alpine is available after page load
            if (typeof window.Alpine !== 'undefined') {
                console.log('Alpine.js already available on frontend');
                return;
            }

            console.warn('Alpine.js not yet loaded, loading it manually...');

            // Load Alpine.js manually
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js';
            script.defer = true;
            script.onload = function() {
                console.log('Alpine.js loaded successfully via fallback');
                // Trigger Alpine initialization if needed
                if (typeof window.Alpine !== 'undefined') {
                    console.log('Alpine.js initialized after manual load');
                }
            };
            script.onerror = function() {
                console.error('Failed to load Alpine.js via fallback');
            };
            document.head.appendChild(script);
        });

        // Listen for Alpine.js initialization
        document.addEventListener('alpine:init', function() {
            console.log('Alpine.js initialized successfully on frontend');
        });
        </script>
        <?php
    }
}
add_action('wp_head', 'sweet_portofolio_frontend_inline_scripts');

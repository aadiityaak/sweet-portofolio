<?php
namespace SweetPortofolio\Frontend;

/**
 * Class Enqueue
 * 
 * Handles frontend asset enqueueing.
 */
class Enqueue {

    /**
     * Initialize the class.
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_head', array($this, 'inline_scripts'));
    }

    /**
     * Enqueue styles and scripts
     */
    public function enqueue_styles() {
        wp_enqueue_style('sweet-portofolio-style', SWEETPORTOFOLIO_URL . 'assets/css/style.css', array(), SWEETPORTOFOLIO_VERSION);
        wp_enqueue_script('jquery');

        // Load portfolio script normally for all pages
        wp_enqueue_script('sweet-portofolio-script', SWEETPORTOFOLIO_URL . 'assets/js/script.js', array('jquery'), SWEETPORTOFOLIO_VERSION, true);

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

    /**
     * Add inline scripts
     */
    public function inline_scripts() {
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
}

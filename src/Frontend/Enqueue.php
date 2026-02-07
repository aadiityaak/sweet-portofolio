<?php

namespace SweetPortofolio\Frontend;

/**
 * Class Enqueue
 * 
 * Handles frontend asset enqueueing.
 */
class Enqueue
{

    /**
     * Initialize the class.
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_head', array($this, 'inline_scripts'));
    }

    /**
     * Enqueue styles and scripts
     */
    public function enqueue_styles()
    {
        wp_enqueue_style('sweet-portofolio-style', SWEETPORTOFOLIO_URL . 'assets/css/frontend.css', array(), SWEETPORTOFOLIO_VERSION);
        wp_enqueue_script('jquery');

        // Load portfolio script normally for all pages
        wp_enqueue_script('sweet-portofolio-script', SWEETPORTOFOLIO_URL . 'assets/js/script.js', array('jquery'), SWEETPORTOFOLIO_VERSION, true);

        // Define filters GLOBALLY to ensure they apply when script is loaded via Shortcode or Template
        // Defer script loading and add cache optimizer exclusions
        add_filter('script_loader_tag', function ($tag, $handle) {
            if ($handle === 'sweet-alpine-js-frontend') {
                // Add defer and data-no-optimize to prevent minify/combine altering script
                $tag = str_replace('<script ', '<script defer data-no-optimize="1" ', $tag);
                return $tag;
            }
            return $tag;
        }, 10, 2);

        // WP Rocket exclusions (safe even if plugin not active)
        add_filter('rocket_delay_js_exclusions', function ($excluded) {
            if (!is_array($excluded)) {
                $excluded = array();
            }
            $excluded[] = 'sweet-alpine-js-frontend';
            $excluded[] = 'alpinejs@3.13.3/dist/cdn.min.js';
            $excluded[] = 'alpinejs@3.x.x/dist/cdn.min.js';
            return array_unique($excluded);
        });
        add_filter('rocket_exclude_defer_js', function ($excluded) {
            if (!is_array($excluded)) {
                $excluded = array();
            }
            $excluded[] = 'sweet-alpine-js-frontend';
            $excluded[] = 'alpinejs@3.13.3/dist/cdn.min.js';
            $excluded[] = 'alpinejs@3.x.x/dist/cdn.min.js';
            return array_unique($excluded);
        });

        // Only load Alpine.js on portfolio list page or when needed
        if (is_page_template('page-portfolio-list.php') || get_query_var('pagename') === 'portfolio') {
            // Remove any existing Alpine.js to prevent conflicts
            wp_dequeue_script('alpine-js');
            wp_dequeue_script('sweet-alpine-js-admin');

            // Add Alpine.js to footer with proper dependencies (use unpkg to avoid CDN truncation issues)
            wp_enqueue_script('sweet-alpine-js-frontend', 'https://unpkg.com/alpinejs@3.13.3/dist/cdn.min.js', array(), '3.13.3', true);
        }
    }

    /**
     * Add inline scripts
     */
    public function inline_scripts()
    {
        // Only add on portfolio list page
        if (is_page_template('page-portfolio-list.php') || get_query_var('pagename') === 'portfolio') {
?>
            <script>
                // Ensure Alpine.js is properly loaded
                window.addEventListener('load', function() {

                    // Check if Alpine is available after page load
                    if (typeof window.Alpine !== 'undefined') {
                        return;
                    }

                    console.warn('Alpine.js not yet loaded, loading it manually...');

                    // Load Alpine.js manually
                    var script = document.createElement('script');
                    script.src = 'https://unpkg.com/alpinejs@3.13.3/dist/cdn.min.js';
                    script.defer = true;
                    script.setAttribute('data-no-optimize', '1');
                    script.onerror = function() {
                        console.error('Failed to load Alpine.js via fallback');
                    };
                    document.head.appendChild(script);
                });
            </script>
<?php
        }
    }
}

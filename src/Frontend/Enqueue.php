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
     * Check whether Alpine.js is already present via a known script handle.
     *
     * @return bool
     */
    private function has_alpine_script()
    {
        $handles = array(
            'sweet-alpine-js-frontend',
            'sweet-alpine-js-admin',
            'alpinejs',
            'alpine-js',
        );

        foreach ($handles as $handle) {
            if (
                wp_script_is($handle, 'registered') ||
                wp_script_is($handle, 'enqueued') ||
                wp_script_is($handle, 'done')
            ) {
                return true;
            }
        }

        return false;
    }

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
        wp_enqueue_style('sweet-portofolio-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Space+Grotesk:wght@500;600;700&display=swap', array(), null);
        wp_enqueue_style('sweet-portofolio-style', SWEETPORTOFOLIO_URL . 'assets/css/frontend.css', array(), SWEETPORTOFOLIO_VERSION);
        wp_enqueue_script('jquery');
        $script_path = SWEETPORTOFOLIO_PATH . 'assets/js/script.js';
        $script_version = file_exists($script_path) ? (string) filemtime($script_path) : SWEETPORTOFOLIO_VERSION;

        // Load the readable source file directly so frontend behavior always matches current code changes.
        wp_enqueue_script('sweet-portofolio-script', SWEETPORTOFOLIO_URL . 'assets/js/script.js', array('jquery'), $script_version, true);

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
            // Skip enqueue if Alpine.js has already been loaded by the theme or another plugin.
            if (!$this->has_alpine_script()) {
                wp_enqueue_script('sweet-alpine-js-frontend', 'https://unpkg.com/alpinejs@3.13.3/dist/cdn.min.js', array(), '3.13.3', true);
            }
        }
    }

    /**
     * Add inline scripts
     */
    public function inline_scripts() {}
}

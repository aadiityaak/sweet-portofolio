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
     * Enqueue styles and scripts (vanilla JS — no Alpine.js dependency)
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
    }

    /**
     * Add inline scripts
     */
    public function inline_scripts() {}
}

<?php

namespace SweetPortofolio\Frontend;

/**
 * Class Shortcode
 * 
 * Handles shortcode registration and rendering.
 */
class Shortcode
{

    /**
     * Initialize the class.
     */
    public function __construct()
    {
        add_shortcode('portofolio_list', array($this, 'render_portofolio_list'));
        add_shortcode('sweet-portofolio-list', array($this, 'render_sweet_portofolio_list'));
    }

    /**
     * Render portofolio_list shortcode (Legacy/Alternative)
     */
    public function render_portofolio_list($atts = array())
    {
        $atts = shortcode_atts(array(
            'ids' => '',
            'filter' => 'yes',
            'category' => ''
        ), $atts, 'portofolio_list');

        $shortcode_ids = array();
        if (!empty($atts['ids'])) {
            $shortcode_ids = array_filter(array_map('intval', array_map('trim', explode(',', $atts['ids']))));
        }

        $shortcode_category = '';
        if (!empty($atts['category'])) {
            $shortcode_category = sanitize_text_field($atts['category']);
        }

        // Pass to common render method
        return $this->render_output($shortcode_ids, $shortcode_category, 'yes', $atts['filter']);
    }

    /**
     * Render sweet-portofolio-list shortcode (Primary)
     */
    public function render_sweet_portofolio_list($atts = array())
    {
        $atts = shortcode_atts(array(
            'default' => '',
            'include' => '',
            'title' => 'yes',
            'filter' => 'yes'
        ), $atts, 'sweet-portofolio-list');

        $shortcode_ids = array();
        if (!empty($atts['include'])) {
            $shortcode_ids = array_filter(array_map('intval', array_map('trim', explode(',', $atts['include']))));
        }

        $shortcode_category = '';
        if (!empty($atts['default'])) {
            $shortcode_category = sanitize_text_field($atts['default']);
        }

        $shortcode_title = sanitize_text_field($atts['title']);

        // Pass to common render method
        return $this->render_output($shortcode_ids, $shortcode_category, $shortcode_title, $atts['filter']);
    }

    /**
     * Common render method for portfolio list
     * 
     * @param array $shortcode_ids
     * @param string $shortcode_category
     * @param string $shortcode_title
     * @param string $filter
     * @return string
     */
    private function render_output($shortcode_ids, $shortcode_category, $shortcode_title, $filter)
    {
        // Setup variables expected by the template
        $atts = array('filter' => $filter);

        ob_start();
        if (!defined('SWEETPORTOFOLIO_SHORTCODE')) {
            define('SWEETPORTOFOLIO_SHORTCODE', true);
        }

        // Ensure assets are loaded
        wp_enqueue_style('sweet-portofolio-style', SWEETPORTOFOLIO_URL . 'assets/css/frontend.css', array(), SWEETPORTOFOLIO_VERSION);
        wp_enqueue_script('jquery');
        wp_enqueue_script('sweet-portofolio-script', SWEETPORTOFOLIO_URL . 'assets/js/script.js', array('jquery'), SWEETPORTOFOLIO_VERSION, true);
        // Load Alpine.js from unpkg and add optimizer exclusions via script_loader_tag in Enqueue
        wp_enqueue_script('sweet-alpine-js-frontend', 'https://unpkg.com/alpinejs@3.13.3/dist/cdn.min.js', array(), '3.13.3', true);

        // Include template
        $template_path = SWEETPORTOFOLIO_PATH . 'templates/page-portfolio-list.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo 'Template not found: ' . $template_path;
        }

        $output = ob_get_clean();

        // Minify output to prevent wpautop from breaking Alpine.js attributes
        // We replace newlines with spaces to maintain attribute separation
        return str_replace(array("\r\n", "\r", "\n"), ' ', $output);
    }
}

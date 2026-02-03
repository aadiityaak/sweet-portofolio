<?php
namespace SweetPortofolio\Core;

/**
 * Class TemplateLoader
 * 
 * Handles template loading.
 */
class TemplateLoader {

    /**
     * Initialize the class.
     */
    public function __construct() {
        add_filter('page_template', array($this, 'load_template'));
        add_filter('theme_page_templates', array($this, 'add_template_to_select'), 10, 4);
    }

    /**
     * Load template from specific page
     */
    public function load_template($page_template) {
        if (get_page_template_slug() == 'page-preview.php') {
            $page_template = SWEETPORTOFOLIO_PATH . 'templates/page-preview.php';
        }

        if (get_page_template_slug() == 'page-portfolio-list.php') {
            $page_template = SWEETPORTOFOLIO_PATH . 'templates/page-portfolio-list.php';
        }

        return $page_template;
    }

    /**
     * Add custom template to select dropdown
     */
    public function add_template_to_select($post_templates, $wp_theme, $post, $post_type) {
        // Add custom template named template-custom.php to select dropdown 
        $post_templates['page-preview.php'] = __('Preview Portofolio');
        $post_templates['page-portfolio-list.php'] = __('Portfolio List');

        return $post_templates;
    }
}

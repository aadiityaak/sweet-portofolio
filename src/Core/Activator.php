<?php
namespace SweetPortofolio\Core;

/**
 * Class Activator
 * 
 * Handles plugin activation.
 */
class Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {
        // Check if pages already exist
        $portfolio_page_id = get_option('portofolio_page');
        $preview_page_id = get_option('portofolio_preview_page');

        // Create portfolio page if it doesn't exist
        if (!$portfolio_page_id || $portfolio_page_id == '-1' || !get_post($portfolio_page_id)) {
            $portfolio_page = array(
                'post_title'    => 'Portofolio',
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type'     => 'page',
                'post_name'     => 'portofolio'
            );

            $new_portfolio_page_id = wp_insert_post($portfolio_page);

            if ($new_portfolio_page_id && !is_wp_error($new_portfolio_page_id)) {
                update_option('portofolio_page', $new_portfolio_page_id);
                update_post_meta($new_portfolio_page_id, '_wp_page_template', 'page-portfolio-list.php');
            }
        }

        // Create preview page if it doesn't exist
        if (!$preview_page_id || $preview_page_id == '-1' || !get_post($preview_page_id)) {
            $preview_page = array(
                'post_title'    => 'Preview Portofolio',
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type'     => 'page',
                'post_name'     => 'preview-portofolio'
            );

            $new_preview_page_id = wp_insert_post($preview_page);

            if ($new_preview_page_id && !is_wp_error($new_preview_page_id)) {
                update_option('portofolio_preview_page', $new_preview_page_id);
                update_post_meta($new_preview_page_id, '_wp_page_template', 'page-preview.php');
            }
        }
    }
}

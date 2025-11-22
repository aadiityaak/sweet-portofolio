<?php
/**
 * REST API endpoints for Sweet Portofolio
 *
 * @package       SWEETPORTOFOLIO
 * @author        Aditya K
 * @license       gplv2
 * @version       1.0.0
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) exit;

/**
 * Register REST API routes
 */
function sweet_portofolio_register_rest_routes() {
    register_rest_route('sweet-portofolio/v1', '/generate-portfolio-page', array(
        'methods' => 'POST',
        'callback' => 'sweet_portofolio_generate_portfolio_page',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => array(
            'force' => array(
                'required' => false,
                'validate_callback' => function($param) {
                    return is_bool($param) || $param === 'true' || $param === 'false';
                }
            )
        )
    ));
    
    register_rest_route('sweet-portofolio/v1', '/generate-preview-page', array(
        'methods' => 'POST',
        'callback' => 'sweet_portofolio_generate_preview_page',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => array(
            'force' => array(
                'required' => false,
                'validate_callback' => function($param) {
                    return is_bool($param) || $param === 'true' || $param === 'false';
                }
            )
        )
    ));
}
add_action('rest_api_init', 'sweet_portofolio_register_rest_routes');

/**
 * Generate portfolio page via REST API
 */
function sweet_portofolio_generate_portfolio_page($request) {
    // Log the request for debugging
    error_log('REST API: generate_portfolio_page called');
    
    // Log headers for debugging
    $headers = $request->get_headers();
    error_log('REST API: Request headers: ' . print_r($headers, true));
    
    // Get force parameter from request
    $force = $request->get_param('force') === 'true';
    error_log('REST API: force parameter = ' . ($force ? 'true' : 'false'));
    
    // Check if portfolio page already exists
    $portfolio_page_id = get_option('portofolio_page');
    error_log('REST API: existing portfolio_page_id = ' . $portfolio_page_id);
    
    if ($portfolio_page_id && $portfolio_page_id != '-1' && get_post($portfolio_page_id) && !$force) {
        error_log('REST API: Portfolio page already exists');
        return new WP_Error('page_exists', 'Portfolio page already exists.', array('status' => 400));
    }
    
    // Create portfolio page
    $portfolio_page = array(
        'post_title'    => 'Portofolio',
        'post_content'  => '[sweet-portofolio-jenis-web]' . "\n\n" . '[sweet-portofolio-list default="profil-perusahaan"]',
        'post_status'   => 'publish',
        'post_author'   => get_current_user_id(),
        'post_type'     => 'page',
        'post_name'     => 'portofolio'
    );
    
    $new_portfolio_page_id = wp_insert_post($portfolio_page);
    error_log('REST API: wp_insert_post result = ' . $new_portfolio_page_id);
    
    if ($new_portfolio_page_id && !is_wp_error($new_portfolio_page_id)) {
        // Save the page ID to options
        update_option('portofolio_page', $new_portfolio_page_id);
        error_log('REST API: Portfolio page created successfully with ID = ' . $new_portfolio_page_id);
        
        $response_data = array(
            'success' => true,
            'message' => 'Portfolio page created successfully.',
            'page_id' => $new_portfolio_page_id,
            'page_title' => get_the_title($new_portfolio_page_id),
            'edit_url' => get_edit_post_link($new_portfolio_page_id),
            'view_url' => get_permalink($new_portfolio_page_id)
        );
        
        return new WP_REST_Response($response_data, 200);
    } else {
        error_log('REST API: Failed to create portfolio page');
        return new WP_Error('creation_failed', 'Failed to create portfolio page.', array('status' => 500));
    }
}

/**
 * Generate preview page via REST API
 */
function sweet_portofolio_generate_preview_page($request) {
    // Log the request for debugging
    error_log('REST API: generate_preview_page called');
    
    // Log headers for debugging
    $headers = $request->get_headers();
    error_log('REST API: Request headers: ' . print_r($headers, true));
    
    // Get force parameter from request
    $force = $request->get_param('force') === 'true';
    error_log('REST API: force parameter = ' . ($force ? 'true' : 'false'));
    
    // Check if preview page already exists
    $preview_page_id = get_option('portofolio_preview_page');
    error_log('REST API: existing preview_page_id = ' . $preview_page_id);
    
    if ($preview_page_id && $preview_page_id != '-1' && get_post($preview_page_id) && !$force) {
        error_log('REST API: Preview page already exists');
        return new WP_Error('page_exists', 'Preview page already exists.', array('status' => 400));
    }
    
    // Create preview page
    $preview_page = array(
        'post_title'    => 'Preview Portofolio',
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_author'   => get_current_user_id(),
        'post_type'     => 'page',
        'post_name'     => 'preview-portofolio'
    );
    
    $new_preview_page_id = wp_insert_post($preview_page);
    
    if ($new_preview_page_id && !is_wp_error($new_preview_page_id)) {
        // Save the page ID to options
        update_option('portofolio_preview_page', $new_preview_page_id);
        
        // Set the page template to 'Preview Portofolio'
        update_post_meta($new_preview_page_id, '_wp_page_template', 'page-preview.php');
        
        $response_data = array(
            'success' => true,
            'message' => 'Preview page created successfully with correct template.',
            'page_id' => $new_preview_page_id,
            'page_title' => get_the_title($new_preview_page_id),
            'edit_url' => get_edit_post_link($new_preview_page_id),
            'view_url' => get_permalink($new_preview_page_id)
        );
        
        return new WP_REST_Response($response_data, 200);
    } else {
        return new WP_Error('creation_failed', 'Failed to create preview page.', array('status' => 500));
    }
}
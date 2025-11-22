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

 function sweet_portofolio_admin_style() {
    wp_enqueue_style('sweet-portofolio-style', SWEETPORTOFOLIO_URL.'assets/css/style.css', array(), SWEETPORTOFOLIO_VERSION);
    wp_enqueue_script( 'jquery' );
    
    // Add Alpine.js CDN with defer attribute
    wp_enqueue_script( 'alpine-js', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', array(), '3.13.3', false );
    
    // Add defer attribute to Alpine.js script
    add_filter('script_loader_tag', function($tag, $handle, $src) {
        if ($handle === 'alpine-js') {
            return str_replace(' src', ' defer src', $tag);
        }
        return $tag;
    }, 10, 3);
    
    wp_enqueue_script( 'sweet-portofolio-script', SWEETPORTOFOLIO_URL. 'assets/js/script.js', array( 'jquery' ), SWEETPORTOFOLIO_VERSION, true );

}
add_action('wp_enqueue_scripts', 'sweet_portofolio_admin_style');

function sweet_portofolio_admin_scripts() {
    // Only load on our admin page
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'portofolio-settings') !== false) {
        // Enqueue WordPress API script
        wp_enqueue_script('wp-api');
        
        // Add Alpine.js CDN with defer attribute
        wp_enqueue_script( 'alpine-js', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', array(), '3.13.3', false );
        
        // Debug: Log that scripts are being enqueued
        error_log('Enqueueing wp-api and alpine-js scripts for portofolio-settings page');
        
        // Add defer attribute to Alpine.js script
        add_filter('script_loader_tag', function($tag, $handle, $src) {
            if ($handle === 'alpine-js') {
                return str_replace(' src', ' defer src', $tag);
            }
            return $tag;
        }, 10, 3);
        
        // Localize script with REST API nonce
        wp_localize_script('alpine-js', 'wpApiSettings', array(
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

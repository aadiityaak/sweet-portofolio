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

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
    // wp_enqueue_style('my-admin-theme', SWEETPORTOFOLIO_URL.'asset/css/style-admin.css');
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'custom-script', SWEETPORTOFOLIO_URL. 'assets/js/script.js', array( 'jquery' ) );

}
add_action('wp_enqueue_scripts', 'sweet_portofolio_admin_style');

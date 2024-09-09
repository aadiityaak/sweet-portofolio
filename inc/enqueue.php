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
    wp_enqueue_script( 'sweet-portofolio-script', SWEETPORTOFOLIO_URL. 'assets/js/script.js', array( 'jquery' ), SWEETPORTOFOLIO_VERSION );

}
add_action('wp_enqueue_scripts', 'sweet_portofolio_admin_style');

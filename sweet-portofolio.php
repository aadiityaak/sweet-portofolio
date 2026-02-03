<?php

/**
 * sweet-portofolio
 *
 * @package       SWEETPORTOFOLIO
 * @author        Aditya K
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Sweet Portofolio
 * Plugin URI:    Portofolio Website Simple untuk mitra websweetstudio.com
 * Description:   Plugin untuk web utama
 * Version:       1.1.2
 * Author:        Aditya K
 * Author URI:    https://websweetstudio.com
 * Text Domain:   sweet-portofolio
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) exit;

/**
 * Currently plugin version.
 */
define('SWEETPORTOFOLIO_VERSION', '1.0.616');

/**
 * Define plugin path url
 */
define('SWEETPORTOFOLIO_URL', plugin_dir_url(__FILE__));

/**
 * Define plugin path
 */
define('SWEETPORTOFOLIO_PATH', plugin_dir_path(__FILE__));

/**
 * Register Autoloader
 */
spl_autoload_register(function ($class) {
    $prefix = 'SweetPortofolio\\';
    $base_dir = plugin_dir_path(__FILE__) . 'src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Activation Hook
 */
register_activation_hook(__FILE__, ['SweetPortofolio\Core\Activator', 'activate']);

/**
 * Initialize the plugin
 */
function run_sweet_portofolio()
{
    $plugin = new SweetPortofolio\Core\Plugin();
    $plugin->run();
}
run_sweet_portofolio();

/**
 * Initialize session if not started
 */
function sweet_portofolio_init()
{
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'sweet_portofolio_init');

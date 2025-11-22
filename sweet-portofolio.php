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
 * Version:       1.1.0
 * Author:        Aditya K
 * Author URI:    https://websweetstudio.com
 * Text Domain:   sweet-portofolio
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with sweet-portofolio. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) exit;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('SWEETPORTOFOLIO_VERSION', '1.0.613');

/**
 * Define plugin path url
 */
define('SWEETPORTOFOLIO_URL', plugin_dir_url(__FILE__));

$files = array(
    'inc/enqueue.php',
    'inc/sweet-options.php',
    'inc/rest-api.php',
);
foreach ($files as $file) {
    require_once plugin_dir_path(__FILE__) . $file;
}

//Load template from specific page
add_filter('page_template', 'wpa3396_page_template');
function wpa3396_page_template($page_template)
{

    if (get_page_template_slug() == 'page-preview.php') {
        $page_template = dirname(__FILE__) . '/inc/page-preview.php';
    }

    if (get_page_template_slug() == 'page-portfolio-list.php') {
        $page_template = dirname(__FILE__) . '/inc/page-portfolio-list.php';
    }

    return $page_template;
}

add_filter('theme_page_templates', 'wpse_288589_add_template_to_select', 10, 4);
function wpse_288589_add_template_to_select($post_templates, $wp_theme, $post, $post_type)
{

    // Add custom template named template-custom.php to select dropdown 
    $post_templates['page-preview.php'] = __('Preview Portofolio');
    $post_templates['page-portfolio-list.php'] = __('Portfolio List');

    return $post_templates;
}

function sweet_portofolio_init()
{
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'sweet_portofolio_init');

// Activation hook
register_activation_hook(__FILE__, 'sweet_portofolio_activate');

function sweet_portofolio_activate()
{
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

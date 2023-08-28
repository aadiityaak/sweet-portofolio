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
 * Version:       1.0.0
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
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SWEETPORTOFOLIO_VERSION', '1.0.0' );

/**
 * Define plugin path url
 */
define( 'SWEETPORTOFOLIO_URL', plugin_dir_url( __FILE__ ) );

$files = array(
  	'inc/shortcode.php',
    'inc/sweet-options.php',
);
foreach ( $files as $file ) {
	require_once plugin_dir_path( __FILE__ ) . $file;
}
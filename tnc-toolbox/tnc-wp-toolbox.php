<?php
/**
 * TNC Toolbox: Web Performance
 *
 * @package       TNCWPTBOX
 * @author        The Network Crew Pty Ltd (TNC)
 * @license       gplv3
 * @version       1.3.7
 *
 * @wordpress-plugin
 * Plugin Name:   TNC Toolbox: Web Performance
 * Plugin URI:    https://merlot.digital
 * Description:   Adds functionality to WP - designed for NGINX-powered Servers on cPanel+WHM. Made to help you fly online.
 * Version:       1.3.7
 * Author:        The Network Crew Pty Ltd (TNC)
 * Author URI:    https://tnc.works
 * Domain Path:   /languages
 * License:       GPLv3
 * License URI:   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with TNC Toolbox. If not, see <https://www.gnu.org/licenses/gpl-3.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HELPER COMMENT START
 * 
 * This file contains the main information about the plugin.
 * It is used to register all components necessary to run the plugin.
 * 
 * The comment above contains all information about the plugin 
 * that are used by WordPress to differenciate the plugin and register it properly.
 * It also contains further PHPDocs parameter for a better documentation
 * 
 * The function TNCWPTBOX() is the main function that you will be able to 
 * use throughout your plugin to extend the logic. Further information
 * about that is available within the sub classes.
 * 
 * HELPER COMMENT END
 */

// Plugin name
define( 'TNCWPTBOX_NAME',		'TNC Toolbox' );

// Plugin version
define( 'TNCWPTBOX_VERSION',		'1.3.7' );

// Plugin Root File
define( 'TNCWPTBOX_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'TNCWPTBOX_PLUGIN_BASE',	plugin_basename( TNCWPTBOX_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'TNCWPTBOX_PLUGIN_DIR',	plugin_dir_path( TNCWPTBOX_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'TNCWPTBOX_PLUGIN_URL',	plugin_dir_url( TNCWPTBOX_PLUGIN_FILE ) );

// Plugin Config Folder
define( 'TNCWPTBOX_CONFIG_DIR', WP_CONTENT_DIR . '/tnc-toolbox-config/' );

// Load the main class for the core functionality
require_once TNCWPTBOX_PLUGIN_DIR . 'core/class-tnc-wp-toolbox.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @return  object|Tnc_Wp_Toolbox
 */
function TNCWPTBOX() {
	return Tnc_Wp_Toolbox::instance();
}

TNCWPTBOX();

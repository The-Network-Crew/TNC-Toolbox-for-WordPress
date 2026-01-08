<?php
/**
 * TNC Toolbox: Web Performance
 *
 * @package           TNCTOOLBOX
 * @author            The Network Crew Pty Ltd (Merlot Digital)
 * @license           gplv3
 * @version           2.1.0
 *
 * @wordpress-plugin
 * Plugin Name:       TNC Toolbox: Web Performance
 * Plugin URI:        https://merlot.digital
 * Description:       Designed for ea-NGINX (Cache/Proxy) on cPanel+WHM. Now with selective cache purging support!
 * Version:           2.1.0
 * Author:            The Network Crew Pty Ltd (Merlot Digital)
 * Author URI:        https://tnc.works
 * Domain Path:       /locale
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with TNC Toolbox. If not, see <https://www.gnu.org/licenses/gpl-3.0.html/>.
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

// Plugin name
define('TNCTOOLBOX_NAME', 'TNC Toolbox');

// Plugin version
define('TNCTOOLBOX_VERSION', '2.1.0');

// Plugin Root File
define('TNCTOOLBOX_PLUGIN_FILE', __FILE__);

// Plugin base
define('TNCTOOLBOX_PLUGIN_BASE', plugin_basename(TNCTOOLBOX_PLUGIN_FILE));

// Plugin Folder Path
define('TNCTOOLBOX_PLUGIN_DIR', plugin_dir_path(TNCTOOLBOX_PLUGIN_FILE));

// Plugin Folder URL
define('TNCTOOLBOX_PLUGIN_URL', plugin_dir_url(TNCTOOLBOX_PLUGIN_FILE));

// Load required files
require_once TNCTOOLBOX_PLUGIN_DIR . 'core/core.php';
require_once TNCTOOLBOX_PLUGIN_DIR . 'core/settings.php';
require_once TNCTOOLBOX_PLUGIN_DIR . 'vendor/cpanel-uapi.php';
require_once TNCTOOLBOX_PLUGIN_DIR . 'vendor/cache-purge.php';

/**
 * Main plugin class for initialisation and hooks
 */
class TNC_Toolbox {
    /**
     * Core functionality handler
     * @var TNC_Core
     */
    public $core;

    /**
     * Settings handler
     * @var TNC_Settings
     */
    public $settings;

    /**
     * Plugin instance
     * @var TNC_Toolbox
     */
    private static $instance = null;

    /**
     * Get plugin instance
     * @return TNC_Toolbox
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Register activation and upgrade hooks
        register_activation_hook(TNCTOOLBOX_PLUGIN_FILE, array($this, 'handle_activation'));

        // Initialise core components after plugins are loaded
        add_action('plugins_loaded', array($this, 'init_components'));
    }

    /**
     * Initialise plugin components
     */
    public function init_components() {
        $this->core = new TNC_Core();
        $this->settings = new TNC_Settings();
    }

    /**
     * Handle plugin activation
     */
    public function handle_activation() {
        // Ensure settings are migrated if they exist
        if (is_dir(WP_CONTENT_DIR . '/tnc-toolbox-config/')) {
            $config_files = array(
                'cpanel-username' => 'username',
                'cpanel-api-key' => 'api_key',
                'server-hostname' => 'hostname'
            );

            // Load each config file's contents
            $config = array();
            foreach ($config_files as $file => $key) {
                $file_path = WP_CONTENT_DIR . '/tnc-toolbox-config/' . $file;
                if (is_readable($file_path)) {
                    $config[$key] = trim(file_get_contents($file_path));
                }
            }

            // If we found all files, store to DB
            if (count($config) === count($config_files)) {
                TNC_cPanel_UAPI::store_config(
                    $config['username'],
                    $config['api_key'],
                    $config['hostname']
                );
            }

            // Clean up old config files & directory
            foreach ($config_files as $file => $key) {
                $file_path = WP_CONTENT_DIR . '/tnc-toolbox-config/' . $file;
                if (is_readable($file_path)) {
                    unlink($file_path);
                }
            }
            rmdir(WP_CONTENT_DIR . '/tnc-toolbox-config/');
        }
    }

    /**
     * Handle version updates and migrations
     */
    public function handle_version_updates() {
        $stored_version = get_option('tnc_toolbox_version', '1.0.0');

        // If this is a pre-2.0.0 version and we have config files, migrate them
        if (version_compare($stored_version, '2.0.0', '<')) {
            $this->handle_activation();
        }

        // Update stored version if different
        if ($stored_version !== TNCTOOLBOX_VERSION) {
            update_option('tnc_toolbox_version', TNCTOOLBOX_VERSION);
        }
    }
}

/**
 * Main function to load plugin instance
 * @return TNC_Toolbox
 */
function TNCTOOLBOX() {
    return TNC_Toolbox::instance();
}

TNCTOOLBOX();

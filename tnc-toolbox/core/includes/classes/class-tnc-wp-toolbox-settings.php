<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Tnc_Wp_Toolbox_Settings
 *
 * This class contains all of the plugin settings.
 *
 * @package    TNCWPTBOX
 * @subpackage Classes/Tnc_Wp_Toolbox_Settings
 * @author     The Network Crew Pty Ltd
 */
class Tnc_Wp_Toolbox_Settings{

    /**
     * The plugin name
     * @var    string
     */
    private $plugin_name;

    /**
     * Our Tnc_Wp_Toolbox_Settings constructor 
     * to run the plugin logic.
     */
    function __construct(){
        $this->plugin_name = TNCWPTBOX_NAME;

        // Schedule a daily event to update the empty configs transient if not already scheduled.
        if (!wp_next_scheduled('tnc_update_empty_configs_transient')) {
            wp_schedule_event(time(), 'daily', 'tnc_update_empty_configs_transient');
        }
    }

    /**
     * Return the plugin name for use in GUI.
     * @return string The plugin name
     */
    public function get_plugin_name(){
    	return apply_filters('TNCWPTBOX/settings/get_plugin_name', $this->plugin_name);
    }

    // Function to register the restricted items, from above
    public function add_capability_dependent_settings() {
        if (current_user_can('update_core')) {
            add_action('tnc_update_empty_configs_transient', array($this, 'update_empty_configs_transient'));
            add_action('all_admin_notices', array($this, 'tnc_wp_toolbox_empty_configs_notice'));
            add_action('admin_menu', array($this, 'register_admin_menu'));
        }
    }

    /**
     * Register the WP Admin settings menu entry.
     */
    public function register_admin_menu() {
    	add_options_page(
    		'TNC Toolbox',
    		'TNC Toolbox',
    		'manage_options',
    		'tnc_toolbox',
    		array( $this, 'handle_settings_page' )
    	);
    }

    /**
     * Checks if any of the config files are empty
     * @access private
     * @return bool True if any config files are empty, False otherwise.
     */
    private function config_files_empty() {
        // Avoid Issue #13 by parsing file contents via new function, to return empty if empty.
        $api_key = $this->get_file_content( TNCWPTBOX_CONFIG_DIR . 'cpanel-api-key' );
        $username = $this->get_file_content( TNCWPTBOX_CONFIG_DIR . 'cpanel-username' );
        $hostname = $this->get_file_content( TNCWPTBOX_CONFIG_DIR . 'server-hostname' );
        // Now run the actual checks, with the returned values being proper/blank.
        if (empty($api_key) || empty($username) || empty($hostname)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Display a warning message for empty configuration files.
     */
    public function tnc_wp_toolbox_empty_configs_notice() {
        if (get_transient('tnc_wp_toolbox_empty_configs_warning')) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php _e('<b>Warning:</b> TNC Toolbox has been installed and activated but is missing config!', 'tnc-wp-toolbox'); ?></p>
                <p><?php _e('Please enter it on the <a href="options-general.php?page=tnc_toolbox">Settings page</a> for the toolbox to function properly. Thanks.', 'tnc-wp-toolbox'); ?></p>
            </div>
            <?php
            delete_transient('tnc_wp_toolbox_empty_configs_warning');
        }
    }

    /**
     * Update the empty configs transient based on the current state of the config files
     */
    public function update_empty_configs_transient() {
        if ($this->config_files_empty()) {
            set_transient('tnc_wp_toolbox_empty_configs_warning', true, 0);
        } else {
            delete_transient('tnc_wp_toolbox_empty_configs_warning');
        }
    }

    /**
     * Route settings page-loads; save/render.
     */
    public function handle_settings_page() {
        // Check if the form was submitted and the nonce is valid
    	if ( isset( $_POST['submit_tnc_toolbox_settings'] ) && wp_verify_nonce( $_POST['tnc_toolbox_settings_nonce'], 'tnc_toolbox_settings' ) ) {
    		$this->save_settings();
    	} else {
    		$this->render_settings_page();
    	}
    }

    /**
     * Save updated settings to disk
     */
    private function save_settings() {

        // Check if the config directory exists, create it if it doesn't
        if ( ! is_dir( TNCWPTBOX_CONFIG_DIR ) ) {
            wp_mkdir_p( TNCWPTBOX_CONFIG_DIR );
        }

        // Sanitize the API key, username, and hostname
    	$api_key = sanitize_text_field( $_POST['tnc_toolbox_api_key'] );
    	$username = sanitize_text_field( $_POST['tnc_toolbox_username'] );
    	$hostname = sanitize_text_field( $_POST['tnc_toolbox_server_hostname'] );

        // Save the API key to file
    	$api_key_file = TNCWPTBOX_CONFIG_DIR . 'cpanel-api-key';
    	if ( file_put_contents( $api_key_file, $api_key ) === false ) {
    		wp_die( 'TNC Toolbox: Unable to save API Key to file.' );
    	}
    	chmod( $api_key_file, 0600 );

        // Save the username to file
    	$username_file = TNCWPTBOX_CONFIG_DIR . 'cpanel-username';
    	if ( file_put_contents( $username_file, $username ) === false ) {
    		wp_die( 'TNC Toolbox: Unable to save Username to file.' );
    	}
    	chmod( $username_file, 0600 );

        // Save the hostname to file
    	$hostname_file = TNCWPTBOX_CONFIG_DIR . 'server-hostname';
    	if ( file_put_contents( $hostname_file, $hostname ) === false ) {
    		wp_die( 'TNC Toolbox: Unable to save Hostname to file.' );
    	}
    	chmod( $hostname_file, 0600 );

        // Redirect to the settings page
    	$this->render_settings_page();
    	exit;
    }

    /**
     * Render the plugin's settings page
     *
     * @access public
     * @since  1.1.2
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ) . " v" . TNCWPTBOX_VERSION; ?></h1>
            <h4>To communicate with the cPanel API (UAPI), we need your API Token, Username & Server Hostname.</h4>
            <form method="post">
                <input type="hidden" name="action" value="tnc_toolbox_settings" />
                <?php wp_nonce_field( 'tnc_toolbox_settings', 'tnc_toolbox_settings_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="tnc_toolbox_api_key">cPanel API Token</label><br><small>Key only, not the name. <a href="https://docs.cpanel.net/cpanel/security/manage-api-tokens-in-cpanel/" target="_blank">Docs</a>.</small></th>
                        <td><input type="text" id="tnc_toolbox_api_key" name="tnc_toolbox_api_key"  size="45" value="<?php echo esc_attr( $this->get_file_content( TNCWPTBOX_CONFIG_DIR . 'cpanel-api-key' ) ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tnc_toolbox_username">cPanel Username</label><br><small>Plain-text user, as used to log-in.</small></th>
                        <td><input type="text" id="tnc_toolbox_username" name="tnc_toolbox_username"  size="45" value="<?php echo esc_attr( $this->get_file_content( TNCWPTBOX_CONFIG_DIR . 'cpanel-username' ) ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tnc_toolbox_server_hostname">Server Hostname</label><br><small>FQDN of Server, no HTTPS etc.</small></th>
                        <td><input type="text" id="tnc_toolbox_server_hostname" name="tnc_toolbox_server_hostname"  size="45" value="<?php echo esc_attr( $this->get_file_content( TNCWPTBOX_CONFIG_DIR . 'server-hostname' ) ); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button( 'Save Settings' ); ?>
                <input type="hidden" name="submit_tnc_toolbox_settings" value="1">
            </form>
        </div>
        <?php
    }

    /**
     * Returns config file contents, if it exists, else return blank (empty)
     *
     * @access public
     * @since  1.3.4
     */
    private function get_file_content($file_path) {
        if (file_exists($file_path)) {
            return file_get_contents($file_path);
        }
        return '';
    }
}

<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Tnc_Wp_Toolbox_Settings
 *
 * This class contains all of the plugin settings.
 * Here you can configure the whole plugin data.
 *
 * @package    TNCWPTBOX
 * @subpackage Classes/Tnc_Wp_Toolbox_Settings
 * @author     The Network Crew Pty Ltd
 * @since      1.0.0
 */
class Tnc_Wp_Toolbox_Settings{

    /**
     * The plugin name
     *
     * @var    string
     * @since  1.0.0
     */
    private $plugin_name;

    /**
     * Our Tnc_Wp_Toolbox_Settings constructor 
     * to run the plugin logic.
     *
     * @since 1.0.0
     */
    function __construct(){

    	$this->plugin_name = TNCWPTBOX_NAME;

    	add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
    }

    /**
     * Return the plugin name for use in GUI.
     *
     * @access public
     * @since  1.0.0
     * @return string The plugin name
     */
    public function get_plugin_name(){
    	return apply_filters( 'TNCWPTBOX/settings/get_plugin_name', $this->plugin_name );
    }

    /**
     * Register the WP Admin settings menu entry.
     *
     * @access public
     * @since  1.1.2
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
     * Route settings page-loads; save/render.
     *
     * @access public
     * @since  1.1.2
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
     *
     * @access public
     * @since  1.1.2
     */
    private function save_settings() {
        // Sanitize the API key, username, and hostname
    	$api_key = sanitize_text_field( $_POST['tnc_toolbox_api_key'] );
    	$username = sanitize_text_field( $_POST['tnc_toolbox_username'] );
    	$hostname = sanitize_text_field( $_POST['tnc_toolbox_server_hostname'] );

    		// Save the API key to file
    	$api_key_file = TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-api-key';
    	if ( file_put_contents( $api_key_file, $api_key ) === false ) {
    		wp_die( 'Unable to save API key to file.' );
    	}
    	chmod( $api_key_file, 0600 );

    		// Save the username to file
    	$username_file = TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-username';
    	if ( file_put_contents( $username_file, $username ) === false ) {
    		wp_die( 'Unable to save username to file.' );
    	}
    	chmod( $username_file, 0600 );

    		// Save the hostname to file
    	$hostname_file = TNCWPTBOX_PLUGIN_DIR . 'config/server-hostname';
    	if ( file_put_contents( $hostname_file, $hostname ) === false ) {
    		wp_die( 'Unable to save hostname to file.' );
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
    		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    		<form method="post">
    			<input type="hidden" name="action" value="tnc_toolbox_settings" />
    			<?php wp_nonce_field( 'tnc_toolbox_settings', 'tnc_toolbox_settings_nonce' ); ?>
    			<table class="form-table">
    				<tr>
    					<th scope="row"><label for="tnc_toolbox_api_key">cPanel API Key</label><br><small>Key only, not the name. <a href="https://docs.cpanel.net/cpanel/security/manage-api-tokens-in-cpanel/" target="_blank">Docs</a>.</small></th>
    					<td><input type="text" id="tnc_toolbox_api_key" name="tnc_toolbox_api_key" value="<?php echo esc_attr( file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-api-key' ) ); ?>" /></td>
    				</tr>
    				<tr>
    					<th scope="row"><label for="tnc_toolbox_username">cPanel Username</label><br><small>Plain-text user, as used to log-in.</small></th>
    					<td><input type="text" id="tnc_toolbox_username" name="tnc_toolbox_username" value="<?php echo esc_attr( file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-username' ) ); ?>" /></td>
    				</tr>
    				<tr>
    					<th scope="row"><label for="tnc_toolbox_server_hostname">Server Hostname</label><br><small>FQDN of Server, no HTTPS etc.</small></th>
    					<td><input type="text" id="tnc_toolbox_server_hostname" name="tnc_toolbox_server_hostname" value="<?php echo esc_attr( file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/server-hostname' ) ); ?>" /></td>
    				</tr>
    			</table>
    			<?php submit_button( 'Save Settings' ); ?>
    			<input type="hidden" name="submit_tnc_toolbox_settings" value="1">
    		</form>
    	</div>
    	<?php
    }
}
<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HELPER COMMENT START
 * 
 * This class is used to bring your plugin to life. 
 * All the other registered classed bring features which are
 * controlled and managed by this class.
 * 
 * Within the add_hooks() function, you can register all of 
 * your WordPress related actions and filters as followed:
 * 
 * add_action( 'my_action_hook_to_call', array( $this, 'the_action_hook_callback', 10, 1 ) );
 * or
 * add_filter( 'my_filter_hook_to_call', array( $this, 'the_filter_hook_callback', 10, 1 ) );
 * or
 * add_shortcode( 'my_shortcode_tag', array( $this, 'the_shortcode_callback', 10 ) );
 * 
 * Once added, you can create the callback function, within this class, as followed: 
 * 
 * public function the_action_hook_callback( $some_variable ){}
 * or
 * public function the_filter_hook_callback( $some_variable ){}
 * or
 * public function the_shortcode_callback( $attributes = array(), $content = '' ){}
 * 
 * 
 * HELPER COMMENT END
 */

/**
 * Class Tnc_Wp_Toolbox_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		TNCWPTBOX
 * @subpackage	Classes/Tnc_Wp_Toolbox_Run
 * @author		The Network Crew Pty Ltd
 * @since		1.0.0
 */
class Tnc_Wp_Toolbox_Run{

	/**
	 * Our Tnc_Wp_Toolbox_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */
	
	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks(){
	
		add_action( 'plugin_action_links_' . TNCWPTBOX_PLUGIN_BASE, array( $this, 'add_plugin_action_link' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_custom_css' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_cache_purge_button' ), 100 );
		add_action( 'admin_post_nginx_cache_purge', array( $this, 'nginx_cache_purge' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_cache_off_button' ), 100 );
		add_action( 'admin_post_nginx_cache_off', array( $this, 'nginx_cache_off' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_cache_on_button' ), 100 );
		add_action( 'admin_post_nginx_cache_on', array( $this, 'nginx_cache_on' ) );
		add_action( 'admin_notices', array( $this, 'tnc_wp_toolbox_nginx_action_error_notice') );
		add_action( 'admin_notices', array( $this, 'tnc_wp_toolbox_nginx_action_success_notice') );
	
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	function tnc_wp_toolbox_nginx_action_error_notice() {
	    if ( $error_message = get_transient( 'tnc_wp_toolbox_nginx_action_error' ) ) {
		?>
		<div class="notice notice-error">
		    <p><?php echo esc_html( $error_message ); ?></p>
		</div>
		<?php
		delete_transient( 'tnc_wp_toolbox_nginx_action_error' );
	    }
	}

	function tnc_wp_toolbox_nginx_action_success_notice() {
	    if ( $success_message = get_transient( 'tnc_wp_toolbox_nginx_action_success' ) ) {
		?>
		<div class="notice notice-success">
		    <p><?php echo esc_html( $success_message ); ?></p>
		</div>
		<?php
		delete_transient( 'tnc_wp_toolbox_nginx_action_success' );
	    }
	}

	
	/**
	* Adds action links to the plugin list table
	*
	* @access	public
	* @since	1.0.0
	*
	* @param	array	$links An array of plugin action links.
	*
	* @return	array	An array of plugin action links.
	*/
	public function add_plugin_action_link( $links ) {

		$links['our_shop'] = sprintf( '<a href="%s" title="my.LEOPARD" style="font-weight:700;">%s</a>', 'https://my.leopard.host', __( 'my.LEOPARD', 'tnc-toolbox' ) );

		return $links;
	}

	/**
	 * Enqueue the custom CSS for plugin buttons
	 *
	 * @access  public
	 * @since   1.2.1
	 *
	 * @return  void
	 */
	public function enqueue_custom_css() {
	    wp_register_style( 'tnc_custom_css', false );
	    wp_enqueue_style( 'tnc_custom_css' );
	    $custom_css = "
	        .nginx-cache-btn.nginx-cache-off a { background-color: #d63638 !important; }
	        .nginx-cache-btn.nginx-cache-on a { background-color: green !important; }
	    ";
	    wp_add_inline_style( 'tnc_custom_css', $custom_css );
	}

	/**
	 * Add the menu items to the WordPress topbar
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @param	object $admin_bar The WP_Admin_Bar object
	 *
	 * @return	void
	 */
	public function add_cache_purge_button( $wp_admin_bar ) {
	    $args = array(
		'id'    => 'nginx_cache_purge',
		'title' => 'NGINX Cache: Purge',
		'href'  => admin_url( 'admin-post.php?action=nginx_cache_purge' ),
		'meta'  => array( 'class' => 'nginx-cache-purge' ),
	    );
	    $wp_admin_bar->add_node( $args );
	}

	public function add_cache_off_button( $wp_admin_bar ) {
	    $args = array(
	        'id'    => 'nginx_cache_off',
	        'title' => 'NC: Off',
	        'href'  => admin_url( 'admin-post.php?action=nginx_cache_off' ),
	        'meta'  => array( 'class' => 'nginx-cache-btn nginx-cache-off' ),
	    );
	    $wp_admin_bar->add_node( $args );
	}

	public function add_cache_on_button( $wp_admin_bar ) {
	    $args = array(
	        'id'    => 'nginx_cache_on',
	        'title' => 'NC: On',
	        'href'  => admin_url( 'admin-post.php?action=nginx_cache_on' ),
	        'meta'  => array( 'class' => 'nginx-cache-btn nginx-cache-on' ),
	    );
	    $wp_admin_bar->add_node( $args );
	}
	
	/**
	 * Function to handle the NGINX User Cache purging
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	Success/Failure
	 */
	function nginx_cache_purge() {
	    // Get the cPanel Username, exit if not present
	    if( is_readable( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-username' ) ) {
			$cpanel_username = file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-username' );
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'cPanel Username could not be read - please configure it in Settings');
			wp_safe_redirect(admin_url());
		exit;
	    }
	    // Get the API Token, exit if not present
	    if( is_readable( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-api-key' ) ) {
			$api_token = file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-api-key' );
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'cPanel API Token could not be read - please configure it in Settings');
			wp_safe_redirect(admin_url());
		exit;
	    }
	    // Get the hostname, exit is not present
	    if( is_readable( TNCWPTBOX_PLUGIN_DIR . 'config/server-hostname' ) ) {
			$server_hostname = file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/server-hostname' );
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'Server Hostname could not be read - please configure it in Settings');
			wp_safe_redirect(admin_url());
		exit;
	    }
	    // Build the headers for the request
	    $headers = array(
			'Authorization' => 'cpanel '. $cpanel_username . ':' . $api_token,
	    );
	    // Build the body for the request
	    $body = array(
			'parameter' => 'value',
	    );
	    // Build the URL for the request
	    $url = 'https://' . $server_hostname . ':2083/execute/NginxCaching/clear_cache';
	    // Make the request
	    $response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body' => $body,
	    ) );
	    // Prepare for redirection
	    $referer = wp_get_referer();
	    // Report the outcome
	    if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			set_transient( 'tnc_wp_toolbox_nginx_action_error', $error_message, 60 );
			wp_safe_redirect( $referer );
			exit;
	    } elseif ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			set_transient( 'tnc_wp_toolbox_nginx_action_success', 'NGINX User Cache has been successfully purged!', 60 );
			wp_safe_redirect( $referer );
			exit;
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'We hit a snag while purging the NGINX User Cache. If this continues, please contact us.', 60 );
			wp_safe_redirect( $referer );
			exit;
	    }	
	}


	/**
	 * Function to handle disabling the NGINX User Cache
	 *
	 * @access	public
	 * @since	1.1.0
	 *
	 * @return	Success/Failure
	 */
	function nginx_cache_off() {
	    // Get the cPanel Username, exit if not present
	    if( is_readable( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-username' ) ) {
			$cpanel_username = file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-username' );
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'cPanel Username could not be read - please configure it in Settings');
			wp_safe_redirect(admin_url());
		exit;
	    }
	    // Get the API Token, exit if not present
	    if( is_readable( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-api-key' ) ) {
			$api_token = file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-api-key' );
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'cPanel API Token could not be read - please configure it in Settings');
			wp_safe_redirect(admin_url());
		exit;
	    }
	    // Get the hostname, exit is not present
	    if( is_readable( TNCWPTBOX_PLUGIN_DIR . 'config/server-hostname' ) ) {
			$server_hostname = file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/server-hostname' );
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'Server Hostname could not be read - please configure it in Settings');
			wp_safe_redirect(admin_url());
		exit;
	    }
	    // Build the headers for the request
	    $headers = array(
			'Authorization' => 'cpanel '. $cpanel_username . ':' . $api_token,
	    );
	    // Build the body for the request
	    $body = array(
			'parameter' => 'value',
	    );
	    // Build the URL for the request
	    $url = 'https://' . $server_hostname . ':2083/execute/NginxCaching/disable_cache';
	    // Make the request
	    $response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body' => $body,
	    ) );
	    // Prepare for redirection
	    $referer = wp_get_referer();
	    // Report the outcome
	    if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			set_transient( 'tnc_wp_toolbox_nginx_action_error', $error_message, 60 );
			wp_safe_redirect( $referer );
			exit;
	    } elseif ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			set_transient( 'tnc_wp_toolbox_nginx_action_success', 'NGINX User Cache has been disabled!', 60 );
			wp_safe_redirect( $referer );
			exit;
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'We hit a snag while disabling the NGINX User Cache. If this continues, please contact us.', 60 );
			wp_safe_redirect( $referer );
			exit;
	    }	
	}

	/**
	 * Function to handle enabling the NGINX User Cache
	 *
	 * @access	public
	 * @since	1.1.0
	 *
	 * @return	Success/Failure
	 */
	function nginx_cache_on() {
	    // Get the cPanel Username, exit if not present
	    if( is_readable( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-username' ) ) {
			$cpanel_username = file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-username' );
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'cPanel Username could not be read - please configure it in Settings');
			wp_safe_redirect(admin_url());
		exit;
	    }
	    // Get the API Token, exit if not present
	    if( is_readable( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-api-key' ) ) {
			$api_token = file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/cpanel-api-key' );
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'cPanel API Token could not be read - please configure it in Settings');
			wp_safe_redirect(admin_url());
		exit;
	    }
	    // Get the hostname, exit is not present
	    if( is_readable( TNCWPTBOX_PLUGIN_DIR . 'config/server-hostname' ) ) {
			$server_hostname = file_get_contents( TNCWPTBOX_PLUGIN_DIR . 'config/server-hostname' );
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'Server Hostname could not be read - please configure it in Settings');
			wp_safe_redirect(admin_url());
		exit;
	    }
	    // Build the headers for the request
	    $headers = array(
			'Authorization' => 'cpanel '. $cpanel_username . ':' . $api_token,
	    );
	    // Build the body for the request
	    $body = array(
			'parameter' => 'value',
	    );
	    // Build the URL for the request
	    $url = 'https://' . $server_hostname . ':2083/execute/NginxCaching/enable_cache';
	    // Make the request
	    $response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body' => $body,
	    ) );
	    // Prepare for redirection
	    $referer = wp_get_referer();
	    // Report the outcome
	    if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			set_transient( 'tnc_wp_toolbox_nginx_action_error', $error_message, 60 );
			wp_safe_redirect( $referer );
			exit;
	    } elseif ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			set_transient( 'tnc_wp_toolbox_nginx_action_success', 'NGINX User Cache has been enabled!', 60 );
			wp_safe_redirect( $referer );
			exit;
	    } else {
			set_transient( 'tnc_wp_toolbox_nginx_action_error', 'We hit a snag while enabling the NGINX User Cache. If this continues, please contact us.', 60 );
			wp_safe_redirect( $referer );
			exit;
	    }	
	}
}
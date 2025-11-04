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
 * add_filter( 'my_filter_hook_to_call', array( $this, 'the_filter_hook_callback', 10, 1 ) );
 * add_shortcode( 'my_shortcode_tag', array( $this, 'the_shortcode_callback', 10 ) );
 * 
 * Once added, you can create the callback function, within this class, as followed: 
 * 
 * public function the_action_hook_callback( $some_variable ){}
 * public function the_filter_hook_callback( $some_variable ){}
 * public function the_shortcode_callback( $attributes = array(), $content = '' ){}
 * 
 * HELPER COMMENT END
 */

/**
 * Class Tnc_Wp_Toolbox_Run
 * Thats where we bring the plugin to life
 *
 * @package		TNCWPTBOX
 * @subpackage	Classes/Tnc_Wp_Toolbox_Run
 * @author		The Network Crew Pty Ltd
 * @since		1.0.0
 */
class Tnc_Wp_Toolbox_Run{
	// Our Tnc_Wp_Toolbox_Run constructor to run the plugin logic.
	function __construct(){
		$this->add_hooks();
	}

	/////////////////////
	// WORDPRESS HOOKS //
	/////////////////////
	
	private function add_hooks(){
		// Make sure WP-Admin > Plugins contains hyperlinks
		add_action( 'plugin_action_links_' . TNCWPTBOX_PLUGIN_BASE, array( $this, 'add_plugin_action_link' ), 20 );
		// Register CSS so we get custom colours in top menu
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_custom_css' ) );
		// Top-level menu item for WP-Admin area, register item
		add_action( 'admin_bar_menu', array( $this, 'add_parent_menu_entry' ), 99 );
		// Top-level child in menu, add cache purge (no ACL dep.)
		add_action( 'admin_bar_menu', array( $this, 'add_cache_purge_button' ), 100 );
		// Action for NGINX Cache Purge, register properly into WP
		add_action( 'admin_post_nginx_cache_purge', array( $this, 'nginx_cache_purge' ) );
		// Transient notice/error, register for both success & error
		add_action( 'admin_notices', array( $this, 'tnc_wp_toolbox_cpanel_action_error_notice') );
		add_action( 'admin_notices', array( $this, 'tnc_wp_toolbox_cpanel_action_success_notice') );
		// Scheduled purging including post_updated event handled
		add_action( 'tnc_scheduled_cache_purge', array( $this, 'nginx_cache_purge' ) );
		add_action( 'post_updated', array( $this, 'purge_cache_on_update' ), 10, 3 );
		// Purge the NGINX User Cache when WP Core is updated
		add_action( '_core_updated_successfully', array( $this, 'nginx_cache_purge' ) );
		add_action( '_core_updated_successfully', array( $this, 'config_inode_checks' ) );
	}

	// These are run from the parent class to ensure pluggable.php is ready
	public function add_capability_dependent_hooks() {
	    if (current_user_can('update_core')) {
		add_action('admin_bar_menu', array($this, 'add_cache_off_button'), 100);
		add_action('admin_post_nginx_cache_off', array($this, 'nginx_cache_off'));
		add_action('admin_bar_menu', array($this, 'add_cache_on_button'), 100);
		add_action('admin_post_nginx_cache_on', array($this, 'nginx_cache_on'));
	    }
	}

	//////////////////////////////
	// WORDPRESS HOOK CALLBACKS //
	//////////////////////////////

	function tnc_wp_toolbox_cpanel_action_error_notice() {
	    if ( $error_message = get_transient( 'tnc_wp_toolbox_cpanel_action_error' ) ) {
		?>
		<div class="notice notice-error">
		    <p><?php echo esc_html( $error_message ); ?></p>
		</div>
		<?php
		delete_transient( 'tnc_wp_toolbox_cpanel_action_error' );
	    }
	}

	function tnc_wp_toolbox_cpanel_action_success_notice() {
	    if ( $success_message = get_transient( 'tnc_wp_toolbox_cpanel_action_success' ) ) {
		?>
		<div class="notice notice-success">
		    <p><?php echo esc_html( $success_message ); ?></p>
		</div>
		<?php
		delete_transient( 'tnc_wp_toolbox_cpanel_action_success' );
	    }
	}

	///////////////////////////////
	// OTHER GENERAL PREP n WORK //
	///////////////////////////////
	
	// On the Plugins page, add link out and to the plugin config.
	public function add_plugin_action_link( $links ) {
	    $settings_link = '<a href="' . admin_url( 'options-general.php?page=tnc_toolbox' ) . '">' . __( 'Settings', 'tnc-toolbox' ) . '</a>';
	    $links['our_shop'] = sprintf( '<a href="%s" title="my.Merlot" style="font-weight:700;">%s</a>', 'https://my.merlot.digital', __( 'my.Merlot', 'tnc-toolbox' ) );

	    array_unshift( $links, $settings_link );

	    return $links;
	}

	// Add custom CSS and enqueue within WP Core, re: colours etc.
	public function enqueue_custom_css() {
	    wp_register_style( 'tnc_custom_css', false );
	    wp_enqueue_style( 'tnc_custom_css' );
	    $custom_css = "
	        .nginx-cache-btn.nginx-cache-off a { background-color: #d63638 !important; }
	        .nginx-cache-btn.nginx-cache-on a { background-color: green !important; }
	    ";
	    wp_add_inline_style( 'tnc_custom_css', $custom_css );
	}

	// WP-Admin: Add the top bar menu parent and entries
	public function add_parent_menu_entry( $wp_admin_bar ) {
		$args = array(
			'id' => 'tnc_parent_menu_entry',
			'title' => '<img src="' . plugins_url( 'tnc-toolbox/assets/tnc-icon.png' ) . '" style="height: 20px; padding-top: 6px;">',
			'href'  => admin_url( 'options-general.php?page=tnc_toolbox' ),
			'meta' => array( 'class' => 'tnc-parent-menu-entry' ),
		);
		$wp_admin_bar->add_node( $args );
	}

	// WP-Admin: Add CACHE PURGE (menu item)
	public function add_cache_purge_button( $wp_admin_bar ) {
	    $args = array(
		'id'    => 'nginx_cache_purge',
		'parent' => 'tnc_parent_menu_entry',
		'title' => 'NGINX Cache: Purge!',
		'href'  => admin_url( 'admin-post.php?action=nginx_cache_purge' ),
		'meta'  => array( 'class' => 'nginx-cache-btn nginx-cache-purge' ),
	    );
	    $wp_admin_bar->add_node( $args );
	}

	// WP-Admin: Add CACHE OFF (menu item)
	public function add_cache_off_button( $wp_admin_bar ) {
	    $args = array(
	        'id'    => 'nginx_cache_off',
	        'parent' => 'tnc_parent_menu_entry',
	        'title' => 'NGINX Cache: OFF',
	        'href'  => admin_url( 'admin-post.php?action=nginx_cache_off' ),
	        'meta'  => array( 'class' => 'nginx-cache-btn nginx-cache-off' ),
	    );
	    $wp_admin_bar->add_node( $args );
	}

	// WP-Admin: Add CACHE ON (menu item)
	public function add_cache_on_button( $wp_admin_bar ) {
	    $args = array(
	        'id'    => 'nginx_cache_on',
	        'parent' => 'tnc_parent_menu_entry',
	        'title' => 'NGINX Cache: ON',
	        'href'  => admin_url( 'admin-post.php?action=nginx_cache_on' ),
	        'meta'  => array( 'class' => 'nginx-cache-btn nginx-cache-on' ),
	    );
	    $wp_admin_bar->add_node( $args );
	}

	//////////////////////////////
	// DO A DEFINED THING (API) //
	// Called below > @endpoint //
	//////////////////////////////
	
	private function cpanel_api_request($endpoint, $success_message, $error_message) {
		// Read the config files (prepare)
		$config_items = ['cpanel-username', 'cpanel-api-key', 'server-hostname'];
		$config = [];
		foreach ($config_items as $item) {
			$file_path = TNCWPTBOX_CONFIG_DIR . $item;
			if (is_readable($file_path)) {
				$config[$item] = file_get_contents($file_path);
			} else {
				set_transient('tnc_wp_toolbox_cpanel_action_error', "TNC Toolbox: Problemo! {$item} config seems to be empty - please update it in Settings > TNC Toolbox. Cheers!", 60);
				wp_safe_redirect(admin_url());
				exit;
			}
		}

		// Build up the request structure
		$headers = ['Authorization' => 'cpanel ' . $config['cpanel-username'] . ':' . $config['cpanel-api-key']];
		$body = ['parameter' => 'value'];
		$url = 'https://' . $config['server-hostname'] . ':2083/execute/' . $endpoint;

		// Do the thing, prepare to handle
		$response = wp_remote_post($url, ['headers' => $headers, 'body' => $body]);
		$response_body = wp_remote_retrieve_body($response);
		$referer = wp_get_referer();
		
		// Match up conditions/errors, relay
		if (wp_remote_retrieve_response_code($response) == 200) {
			// Success relayed via green transient message.
			set_transient('tnc_wp_toolbox_cpanel_action_success', $success_message, 60);
		} elseif (!empty($response_body)) {
			// Truncate relayed error to first 200 chars, then relay.
			substr($response_body, 0, 200);
			set_transient('tnc_wp_toolbox_cpanel_action_error', 'TNC Toolbox: ' . $endpoint . ' hit a snag. The error we received is: ' . $response_body, 60);
		} else {
			// Failure (generic) relayed if there is no error returned from API.
			set_transient('tnc_wp_toolbox_cpanel_action_error', $error_message, 60);
		}

		// Off you go then, thanks for coming
		wp_safe_redirect($referer);
		exit;
	}

	////////////////////////////
	// FUNCTIONS TO DO THINGS //
	// Self-explanatory calls //
	////////////////////////////
	
	function nginx_cache_purge() {
		$this->cpanel_api_request('NginxCaching/clear_cache', 'TNC Toolbox: NGINX Cache has been Purged!', 'TNC Toolbox: NginxCaching/clear_cache hit an uncaught snag. If this continues, please contact your Hosting Support.');
	}
	
	function nginx_cache_off() {
		$this->cpanel_api_request('NginxCaching/disable_cache', 'TNC Toolbox: NGINX Cache has been Disabled.', 'TNC Toolbox: NginxCaching/disable_cache hit an uncaught snag. If this continues, please contact your Hosting Support.');
	}
	
	function nginx_cache_on() {
		$this->cpanel_api_request('NginxCaching/enable_cache', 'TNC Toolbox: NGINX Cache has been Enabled!', 'TNC Toolbox: NginxCaching/enable_cache hit an uncaught snag. If this continues, please contact your Hosting Support.');
	}

	/**
	 * Function to automatically purge the cache when a post or page is updated
	 *
	 * @param  int      $post_id   The ID of the post being updated
	 * @param  WP_Post  $post      The post object being updated
	 * @param  bool     $update    Whether this is an update to an existing post
	 * @return void
	 */
	public function purge_cache_on_update( $post_id, $post_after, $post_before ) {
	    // Check if the post is published or updated
	    if ( 'publish' === $post_after->post_status || ( $post_before->post_status === 'publish' && $post_after->post_status !== 'trash' ) ) {
	        // Schedule the cache purge to run after the current request
	        wp_schedule_single_event( time(), 'tnc_scheduled_cache_purge' );
	    }
	}
}
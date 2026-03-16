<?php
/**
 * Web Server Detection & Stack Management
 *
 * Handles detection of web server software and management of
 * the configured web stack type (NGINX/cPanel vs LiteSpeed).
 *
 * @package    TNCTOOLBOX
 * @subpackage Vendors
 * @author     The Network Crew Pty Ltd
 * @since      2.1.3
 * @license    GPLv3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Web Server Detection Handler
 *
 * Detects web server software and manages stack configuration.
 */
class TNC_Detection {

	/**
	 * Option name for web stack setting
	 */
	const WEB_STACK_KEY = 'tnc_web_stack';

	/**
	 * Web stack constants
	 */
	const STACK_NGINX = 'nginx';
	const STACK_LITESPEED = 'litespeed';

	/**
	 * Transient name for stack check cache
	 */
	const TRANSIENT_STACK_CHECK = 'tnc_web_stack_check';

	/**
	 * Get the configured web stack type
	 *
	 * @return string Stack type ('nginx' or 'litespeed')
	 */
	public static function get_web_stack() {
		return get_option( self::WEB_STACK_KEY, self::STACK_NGINX );
	}

	/**
	 * Set the web stack type
	 *
	 * @param string $stack Stack type ('nginx' or 'litespeed')
	 * @return bool True on success
	 */
	public static function set_web_stack( $stack ) {
		$valid_stacks = array( self::STACK_NGINX, self::STACK_LITESPEED );
		if ( ! in_array( $stack, $valid_stacks, true ) ) {
			$stack = self::STACK_NGINX;
		}
		return update_option( self::WEB_STACK_KEY, $stack );
	}

	/**
	 * Detect current web server software
	 *
	 * Checks multiple indicators for LiteSpeed since OpenLiteSpeed
	 * may report as Apache in SERVER_SOFTWARE while using LSAPI for PHP.
	 *
	 * @return string|false Returns 'litespeed', 'nginx', 'apache', or false if unknown
	 */
	public static function detect_web_server() {
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? strtolower( $_SERVER['SERVER_SOFTWARE'] ) : '';

		// Check SERVER_SOFTWARE for litespeed first
		if ( strpos( $server_software, 'litespeed' ) !== false ) {
			return 'litespeed';
		}

		// LiteSpeed SAPI check - php_sapi_name() returns 'litespeed' when using LSPHP/LSAPI
		// This is the most reliable check for OpenLiteSpeed
		$sapi_name = strtolower( php_sapi_name() );
		if ( strpos( $sapi_name, 'litespeed' ) !== false ) {
			return 'litespeed';
		}

		// Check for LITESPEED constant (set by LiteSpeed Cache plugin or LSAPI)
		if ( defined( 'LITESPEED' ) || defined( 'LSCACHE_ADV' ) ) {
			return 'litespeed';
		}

		// Check for LiteSpeed-specific server variables
		if ( isset( $_SERVER['X_LSCACHE'] ) || isset( $_SERVER['HTTP_X_LSCACHE'] ) ) {
			return 'litespeed';
		}

		// Now check other servers
		if ( strpos( $server_software, 'nginx' ) !== false ) {
			return 'nginx';
		}
		if ( strpos( $server_software, 'apache' ) !== false ) {
			return 'apache';
		}

		return false;
	}

	/**
	 * Auto-detect and update web stack if mismatched
	 *
	 * If server is LiteSpeed but config says nginx, auto-switch to litespeed.
	 * Credentials are preserved - not deleted.
	 *
	 * @return array|false Returns change info if stack was auto-switched, false otherwise
	 */
	public static function auto_detect_and_switch_stack() {
		$detected   = self::detect_web_server();
		$configured = self::get_web_stack();

		// If LiteSpeed detected but configured for nginx, auto-switch
		if ( $detected === 'litespeed' && $configured === self::STACK_NGINX ) {
			self::set_web_stack( self::STACK_LITESPEED );
			return array(
				'detected'    => $detected,
				'previous'    => $configured,
				'switched_to' => self::STACK_LITESPEED,
			);
		}

		return false;
	}

	/**
	 * Check if current stack is LiteSpeed
	 *
	 * @return bool True if web stack is litespeed
	 */
	public static function is_litespeed_stack() {
		return self::get_web_stack() === self::STACK_LITESPEED;
	}

	/**
	 * Check if current stack is NGINX (cPanel)
	 *
	 * @return bool True if web stack is nginx
	 */
	public static function is_nginx_stack() {
		return self::get_web_stack() === self::STACK_NGINX;
	}

	/**
	 * Get human-readable stack name
	 *
	 * @param string|null $stack Stack type, or null for current stack
	 * @return string Human-readable name
	 */
	public static function get_stack_name( $stack = null ) {
		if ( $stack === null ) {
			$stack = self::get_web_stack();
		}

		$names = array(
			self::STACK_NGINX     => 'ea-NGINX (cPanel/WHM)',
			self::STACK_LITESPEED => 'LiteSpeed (OpenLS/Enterprise)',
		);

		return isset( $names[ $stack ] ) ? $names[ $stack ] : $stack;
	}
}

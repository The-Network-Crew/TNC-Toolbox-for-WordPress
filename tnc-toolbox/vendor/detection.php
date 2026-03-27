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
	 * Option name to track if user has explicitly chosen a stack
	 */
	const STACK_SET_BY_USER_KEY = 'tnc_web_stack_user_set';

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
	 * @param string $stack    Stack type ('nginx' or 'litespeed')
	 * @param bool   $by_user  Whether this was explicitly set by the user via settings
	 * @return bool True on success
	 */
	public static function set_web_stack( $stack, $by_user = false ) {
		$valid_stacks = array( self::STACK_NGINX, self::STACK_LITESPEED );
		if ( ! in_array( $stack, $valid_stacks, true ) ) {
			$stack = self::STACK_NGINX;
		}
		if ( $by_user ) {
			update_option( self::STACK_SET_BY_USER_KEY, true );
		}
		return update_option( self::WEB_STACK_KEY, $stack );
	}

	/**
	 * Check whether the user has ever explicitly saved a stack choice
	 *
	 * @return bool True if the user has explicitly chosen a stack
	 */
	public static function is_user_configured() {
		return (bool) get_option( self::STACK_SET_BY_USER_KEY, false );
	}

	/**
	 * Detect current web server software
	 *
	 * Uses SERVER_SOFTWARE header only — this is a reliable indicator.
	 *
	 * - LiteSpeed/OpenLiteSpeed: SERVER_SOFTWARE contains "LiteSpeed"
	 * - ea-NGINX (cPanel): SERVER_SOFTWARE is "Apache" (PHP runs under
	 *   Apache behind the NGINX reverse proxy — this is expected)
	 *
	 * @return string|false Returns 'litespeed', 'nginx', 'apache', or false if unknown
	 */
	public static function detect_web_server() {
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? strtolower( $_SERVER['SERVER_SOFTWARE'] ) : '';

		// LiteSpeed/OpenLiteSpeed identify themselves in SERVER_SOFTWARE
		if ( strpos( $server_software, 'litespeed' ) !== false ) {
			return 'litespeed';
		}

		// NGINX as primary server (not ea-NGINX, which proxies to Apache)
		if ( strpos( $server_software, 'nginx' ) !== false ) {
			return 'nginx';
		}

		// Apache — this also covers ea-NGINX (cPanel) where PHP runs
		// under Apache behind the NGINX reverse proxy
		if ( strpos( $server_software, 'apache' ) !== false ) {
			return 'apache';
		}

		return false;
	}

	/**
	 * Auto-detect and correct web stack configuration
	 *
	 * Only switches automatically if the user has never explicitly saved
	 * a stack choice. Once a user saves settings, their choice is respected
	 * and auto-detection becomes advisory only (shown on the settings page).
	 *
	 * Handles both directions:
	 * - LiteSpeed detected + configured nginx → switch to litespeed
	 * - Non-LiteSpeed detected + configured litespeed → switch back to nginx
	 *   (fixes sites wrongly auto-switched by earlier buggy detection)
	 *
	 * @return array|false Returns change info if stack was auto-switched, false otherwise
	 */
	public static function auto_detect_and_switch_stack() {
		// Never override an explicit user choice
		if ( self::is_user_configured() ) {
			return false;
		}

		$detected   = self::detect_web_server();
		$configured = self::get_web_stack();

		// LiteSpeed detected but configured for nginx → switch to litespeed
		if ( $detected === 'litespeed' && $configured === self::STACK_NGINX ) {
			self::set_web_stack( self::STACK_LITESPEED, false );
			return array(
				'detected'    => $detected,
				'previous'    => $configured,
				'switched_to' => self::STACK_LITESPEED,
			);
		}

		// Not LiteSpeed but configured as litespeed → switch back to nginx
		// This corrects sites wrongly auto-switched by earlier detection logic
		if ( $detected !== 'litespeed' && $configured === self::STACK_LITESPEED ) {
			self::set_web_stack( self::STACK_NGINX, false );
			return array(
				'detected'    => $detected ? $detected : 'unknown',
				'previous'    => $configured,
				'switched_to' => self::STACK_NGINX,
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
	 * Check if the detected server matches the configured stack
	 *
	 * On ea-NGINX (cPanel), PHP runs under Apache behind the NGINX reverse
	 * proxy, so detection returns 'apache'. This is a known expected mismatch
	 * and should NOT trigger a warning.
	 *
	 * @return bool True if detection and config are compatible
	 */
	public static function is_detection_compatible() {
		$detected   = self::detect_web_server();
		$configured = self::get_web_stack();

		// No detection = no mismatch to warn about
		if ( ! $detected ) {
			return true;
		}

		// Direct match
		if ( $detected === $configured ) {
			return true;
		}

		// ea-NGINX: PHP reports Apache because it runs behind NGINX reverse proxy
		// This is expected and not a mismatch
		if ( $detected === 'apache' && $configured === self::STACK_NGINX ) {
			return true;
		}

		return false;
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

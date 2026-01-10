<?php
/**
 * NGINX Cache Purge Integration
 *
 * Provides selective URL purging using ea-nginx-cache-purge when available.
 * This allows purging only specific pages instead of the entire cache,
 * dramatically improving cache efficiency.
 *
 * For CloudLinux EA4 servers, install with:
 * dnf -y install ea-nginx-cache-purge
 *
 * @package    TNCTOOLBOX
 * @subpackage Vendors
 * @author     The Network Crew Pty Ltd
 * @since      2.1.0
 * @license    GPLv3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NGINX Cache Purge Handler
 *
 * Handles selective cache purging via HTTP PURGE requests when the
 * nginx-module-cache-purge module is available.
 */
class TNC_Cache_Purge {

	/**
	 * Option name for cache-purge availability status
	 */
	const OPTION_AVAILABLE = 'tnc_cache_purge_available';

	/**
	 * Option name for cache-purge enabled setting
	 */
	const OPTION_ENABLED = 'tnc_cache_purge_enabled';

	/**
	 * Option name for purge URL endpoint
	 */
	const OPTION_PURGE_URL = 'tnc_cache_purge_url';

	/**
	 * Transient name for availability check cache
	 */
	const TRANSIENT_CHECK = 'tnc_cache_purge_check';

	/**
	 * Check if cache-purge module is available.
	 *
	 * Tests by sending a PURGE request to a known URL and checking for
	 * the expected response from ngx_cache_purge module.
	 *
	 * @param bool $force Force a fresh check, ignoring cached result.
	 * @return bool True if cache-purge is available.
	 */
	public static function is_available( $force = false ) {
		// Check cached result first.
		if ( ! $force ) {
			$cached = get_transient( self::TRANSIENT_CHECK );
			if ( false !== $cached ) {
				return 'yes' === $cached;
			}
		}

		$available = self::test_purge_capability();

		// Cache result for 1 hour.
		set_transient( self::TRANSIENT_CHECK, $available ? 'yes' : 'no', HOUR_IN_SECONDS );
		update_option( self::OPTION_AVAILABLE, $available ? 'yes' : 'no' );

		return $available;
	}

	/**
	 * Test if PURGE method is accepted by the server.
	 *
	 * PURGE requests must be sent to localhost (127.0.0.1) with the Host header
	 * set to the actual domain. This is how ea-nginx-cache-purge is configured.
	 *
	 * The module returns specific signatures in the response body:
	 * - HTTP 200 with body containing "Successful purge" = module working.
	 * - HTTP 412 can occur when item not in cache, but we need body signature.
	 *
	 * Without the module, nginx may return 301/404/405 with generic HTML.
	 *
	 * @return bool True if PURGE is supported.
	 */
	private static function test_purge_capability() {
		$home_url = home_url( '/' );
		$host     = wp_parse_url( $home_url, PHP_URL_HOST );

		// PURGE must go to localhost with Host header.
		$test_url = 'http://127.0.0.1/';

		$args = array(
			'method'      => 'PURGE',
			'timeout'     => 5,
			'redirection' => 0,
			'sslverify'   => false,
			'headers'     => array(
				'Host' => $host,
			),
		);

		$response = wp_remote_request( $test_url, $args );

		if ( is_wp_error( $response ) ) {
			self::log( 'PURGE test failed: ' . $response->get_error_message() );
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		// ea-nginx-cache-purge module returns specific signatures:
		// - 200 OK with body "Successful purge" and "Key :" when purged.
		// - 412 Precondition Failed when item wasn't in cache.
		//
		// Without module: 301 redirect, 404 generic HTML, or 405 Method Not Allowed.
		//
		// We MUST check body for "Successful purge" signature to confirm module.
		if ( 200 === $code && stripos( $body, 'Successful purge' ) !== false ) {
			self::log( 'PURGE test successful: module detected (200 + signature)' );
			return true;
		}

		// 412 from module still indicates it's working (item just wasn't cached).
		// But generic nginx 412 doesn't have our signature, so check for module output.
		if ( 412 === $code && stripos( $body, 'Precondition Failed' ) !== false ) {
			// This is likely the module - generic nginx rarely returns 412 for PURGE.
			self::log( 'PURGE test successful: module detected (412 Precondition Failed)' );
			return true;
		}

		self::log( 'PURGE test: got HTTP ' . $code . ', module not detected' );
		return false;
	}

	/**
	 * Check if selective purging is enabled.
	 *
	 * Returns true if the user has enabled selective purging via checkbox.
	 * Module availability is checked separately during actual purge operations.
	 *
	 * @return bool True if enabled by user.
	 */
	public static function is_enabled() {
		return 'yes' === get_option( self::OPTION_ENABLED, 'no' );
	}

	/**
	 * Enable or disable selective purging.
	 *
	 * @param bool $enable True to enable, false to disable.
	 * @return bool True on success.
	 */
	public static function set_enabled( $enable ) {
		return update_option( self::OPTION_ENABLED, $enable ? 'yes' : 'no' );
	}

	/**
	 * Purge a specific URL from cache.
	 *
	 * PURGE requests must be sent to localhost (127.0.0.1) with the Host header
	 * set to the actual domain. This is how ea-nginx-cache-purge is configured
	 * for security (proxy_cache_purge PURGE from 127.0.0.1).
	 *
	 * URLs are appended with * wildcard to handle Vary: Accept-Encoding header
	 * which creates separate cache entries for different encodings (gzip, br, etc.).
	 *
	 * @param string $url URL to purge.
	 * @return array Result with 'success' and 'message' keys.
	 */
	public static function purge_url( $url ) {
		if ( empty( $url ) ) {
			return array(
				'success' => false,
				'message' => 'Empty URL provided',
			);
		}

		// Parse the URL to extract components.
		$parsed = wp_parse_url( $url );
		$host   = isset( $parsed['host'] ) ? $parsed['host'] : '';
		$path   = isset( $parsed['path'] ) ? $parsed['path'] : '/';
		$query  = isset( $parsed['query'] ) ? '?' . $parsed['query'] : '';

		// Append wildcard to handle Vary: Accept-Encoding cache variants.
		// This ensures gzip, brotli, and uncompressed versions are all purged.
		$path_with_wildcard = rtrim( $path, '/' ) . '/*';

		// Build localhost URL - PURGE must go to 127.0.0.1.
		$localhost_url = 'http://127.0.0.1' . $path_with_wildcard . $query;

		$args = array(
			'method'      => 'PURGE',
			'timeout'     => 10,
			'redirection' => 0,
			'sslverify'   => false,
			'headers'     => array(
				'Host' => $host,
			),
		);

		$response = wp_remote_request( $localhost_url, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		// ea-nginx-cache-purge module returns:
		// - 200 with "Successful purge" = item was in cache and purged.
		// - 412 Precondition Failed = item wasn't in cache (still success).
		//
		// Without module: 301/404/405 with generic nginx HTML (not a real purge).
		if ( 200 === $code && stripos( $body, 'Successful purge' ) !== false ) {
			self::log( 'Purged: ' . $url . ' (HTTP 200)' );
			return array(
				'success' => true,
				'message' => 'Purged successfully',
				'code'    => $code,
			);
		}

		// 412 means item wasn't cached - that's fine, nothing to purge.
		if ( 412 === $code ) {
			self::log( 'Not in cache: ' . $url . ' (HTTP 412)' );
			return array(
				'success' => true,
				'message' => 'Not in cache (already clean)',
				'code'    => $code,
			);
		}

		// Got a 200 but without cache-purge signature = module not working.
		if ( 200 === $code ) {
			self::log( 'Purge returned 200 but without module signature for ' . $url );
			return array(
				'success' => false,
				'message' => 'Cache purge module not responding correctly',
				'code'    => $code,
			);
		}

		// 301/404/405 = module not installed or not configured for this domain.
		$error_msg = 'Purge failed (HTTP ' . $code . ')';
		if ( 404 === $code || 301 === $code ) {
			$error_msg = 'Cache purge module not detected - install ea-nginx-cache-purge';
		} elseif ( 405 === $code ) {
			$error_msg = 'PURGE method not allowed - cache purge module not configured';
		}

		self::log( 'Purge failed for ' . $url . ': HTTP ' . $code );
		return array(
			'success' => false,
			'message' => $error_msg,
			'code'    => $code,
		);
	}

	/**
	 * Purge multiple URLs.
	 *
	 * @param array $urls Array of URLs to purge.
	 * @return array Results summary.
	 */
	public static function purge_urls( $urls ) {
		if ( empty( $urls ) ) {
			return array(
				'success'  => true,
				'message'  => 'No URLs to purge',
				'purged'   => 0,
				'failed'   => 0,
				'urls'     => array(),
			);
		}

		$urls    = array_unique( array_filter( (array) $urls ) );
		$results = array();
		$purged  = 0;
		$failed  = 0;

		foreach ( $urls as $url ) {
			$result           = self::purge_url( $url );
			$results[ $url ] = $result;
			if ( $result['success'] ) {
				$purged++;
			} else {
				$failed++;
			}
		}

		return array(
			'success'  => $failed === 0,
			'message'  => sprintf( 'Purged %d URLs, %d failed', $purged, $failed ),
			'purged'   => $purged,
			'failed'   => $failed,
			'urls'     => $results,
		);
	}

	/**
	 * Purge entire cache using wildcard.
	 *
	 * Uses PURGE /* to clear all cached content for this domain.
	 * Request goes to localhost with Host header set.
	 *
	 * @return array Result with 'success' and 'message' keys.
	 */
	public static function purge_all() {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );

		// Wildcard PURGE to localhost.
		$purge_url = 'http://127.0.0.1/*';

		$args = array(
			'method'      => 'PURGE',
			'timeout'     => 10,
			'redirection' => 0,
			'sslverify'   => false,
			'headers'     => array(
				'Host' => $host,
			),
		);

		$response = wp_remote_request( $purge_url, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $code ) {
			self::log( 'Purged all cache via wildcard PURGE' );
			return array(
				'success' => true,
				'message' => 'Cache purged successfully',
			);
		}

		return array(
			'success' => false,
			'message' => 'Unexpected response: HTTP ' . $code,
		);
	}

	/**
	 * Get URLs to purge for a given post.
	 *
	 * Generates a comprehensive list of URLs that should be purged when
	 * a post is updated, including the post itself, home page, archives, etc.
	 *
	 * @param int|WP_Post $post Post ID or object.
	 * @return array Array of URLs to purge.
	 */
	public static function get_post_purge_urls( $post ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return array();
		}

		$urls = array();

		// 1. The post itself.
		$permalink = get_permalink( $post );
		if ( $permalink ) {
			$urls[] = $permalink;
			// Also purge without trailing slash if applicable.
			$urls[] = untrailingslashit( $permalink );
		}

		// 2. Home page / front page.
		$urls[] = home_url( '/' );
		$urls[] = home_url();

		// 3. Blog/posts page (if different from home).
		$posts_page_id = get_option( 'page_for_posts' );
		if ( $posts_page_id ) {
			$posts_page_url = get_permalink( $posts_page_id );
			if ( $posts_page_url ) {
				$urls[] = $posts_page_url;
			}
		}

		// 4. Category archives.
		$categories = get_the_category( $post->ID );
		if ( $categories ) {
			foreach ( $categories as $category ) {
				$cat_link = get_category_link( $category->term_id );
				if ( $cat_link ) {
					$urls[] = $cat_link;
				}
			}
		}

		// 5. Tag archives.
		$tags = get_the_tags( $post->ID );
		if ( $tags ) {
			foreach ( $tags as $tag ) {
				$tag_link = get_tag_link( $tag->term_id );
				if ( $tag_link ) {
					$urls[] = $tag_link;
				}
			}
		}

		// 6. Author archive.
		$author_url = get_author_posts_url( (int) $post->post_author );
		if ( $author_url ) {
			$urls[] = $author_url;
		}

		// 7. Date archives (year, month, day).
		$post_date = strtotime( $post->post_date );
		if ( $post_date ) {
			$year  = (int) gmdate( 'Y', $post_date );
			$month = (int) gmdate( 'm', $post_date );
			$day   = (int) gmdate( 'd', $post_date );
			$urls[] = get_year_link( $year );
			$urls[] = get_month_link( $year, $month );
			$urls[] = get_day_link( $year, $month, $day );
		}

		// 8. Custom post type archive (if applicable).
		if ( 'post' !== $post->post_type && 'page' !== $post->post_type ) {
			$post_type_archive = get_post_type_archive_link( $post->post_type );
			if ( $post_type_archive ) {
				$urls[] = $post_type_archive;
			}
		}

		// 9. Custom taxonomies.
		$taxonomies = get_object_taxonomies( $post->post_type, 'names' );
		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
				continue; // Already handled above.
			}
			$terms = get_the_terms( $post->ID, $taxonomy );
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$term_link = get_term_link( $term );
					if ( $term_link && ! is_wp_error( $term_link ) ) {
						$urls[] = $term_link;
					}
				}
			}
		}

		// 10. Feed URLs.
		$urls[] = get_feed_link();
		$urls[] = get_feed_link( 'rss2' );

		// 11. REST API endpoints for this post.
		$rest_url = rest_url( 'wp/v2/' . $post->post_type . '/' . $post->ID );
		if ( $rest_url ) {
			$urls[] = $rest_url;
		}

		// Filter and deduplicate.
		$urls = array_unique( array_filter( $urls ) );

		/**
		 * Filter the URLs to purge for a post.
		 *
		 * @param array   $urls Array of URLs to purge.
		 * @param WP_Post $post The post being purged.
		 */
		return apply_filters( 'tnc_cache_purge_post_urls', $urls, $post );
	}

	/**
	 * Purge cache for a specific post.
	 *
	 * @param int|WP_Post $post Post ID or object.
	 * @return array Result summary.
	 */
	public static function purge_post( $post ) {
		$urls = self::get_post_purge_urls( $post );
		return self::purge_urls( $urls );
	}

	/**
	 * Log message if debugging is enabled.
	 *
	 * @param string $message Message to log.
	 */
	private static function log( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'TNC Cache Purge: ' . $message );
		}
	}

	/**
	 * Get availability status for display.
	 *
	 * Detection only runs on settings page (force_recheck=true) to avoid
	 * HTTP requests on every page load. Admin bar uses cached result.
	 *
	 * @param bool $force_recheck Force a fresh detection check (use on settings page).
	 * @return array Status info with 'available', 'enabled', 'message' keys.
	 */
	public static function get_status( $force_recheck = false ) {
		$enabled   = self::is_enabled();
		$available = get_option( self::OPTION_AVAILABLE, 'unknown' );

		// Only recheck on settings page (force_recheck=true).
		// This avoids HTTP requests on every page load.
		if ( $force_recheck && $enabled ) {
			$available = self::is_available( true ) ? 'yes' : 'no';
		}

		$message = '';
		if ( $enabled ) {
			if ( 'yes' === $available ) {
				$message = 'Selective purging active — only changed pages are purged for maximum efficiency';
			} elseif ( 'no' === $available ) {
				$message = 'Selective purging enabled but module not detected — purges may fail';
			} else {
				$message = 'Selective purging enabled — checking module availability...';
			}
		} else {
			$message = 'Selective purging disabled — using full cache purge via cPanel API';
		}

		return array(
			'available' => 'yes' === $available,
			'enabled'   => $enabled,
			'message'   => $message,
		);
	}

	/**
	 * Recheck availability (clears cache).
	 *
	 * @return bool True if available.
	 */
	public static function recheck() {
		delete_transient( self::TRANSIENT_CHECK );
		return self::is_available( true );
	}
}

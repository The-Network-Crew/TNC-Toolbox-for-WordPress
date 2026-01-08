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

		// ea-nginx-cache-purge returns:
		// - 200 OK with "Successful purge" if entry existed.
		// - 404 Not Found if entry didn't exist (but PURGE is supported).
		// - 412 Precondition Failed if entry wasn't in cache.
		// - 405 Method Not Allowed if PURGE is not configured.
		if ( 200 === $code || 404 === $code || 412 === $code ) {
			// Check for cache-purge signature in body.
			if ( stripos( $body, 'purge' ) !== false || stripos( $body, 'cache' ) !== false ) {
				self::log( 'PURGE test successful: module detected' );
				return true;
			}
			// Even without body, 200/404/412 on PURGE is a good sign.
			self::log( 'PURGE test: got ' . $code . ', assuming module available' );
			return true;
		}

		self::log( 'PURGE test: got ' . $code . ', module not available' );
		return false;
	}

	/**
	 * Check if selective purging is enabled.
	 *
	 * @return bool True if enabled and available.
	 */
	public static function is_enabled() {
		$enabled   = get_option( self::OPTION_ENABLED, 'yes' );
		$available = get_option( self::OPTION_AVAILABLE, 'unknown' );

		return 'yes' === $enabled && 'yes' === $available;
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

		// 200 = successfully purged, 404 = wasn't in cache (ok), 412 = wasn't in cache.
		if ( 200 === $code || 404 === $code || 412 === $code ) {
			self::log( 'Purged: ' . $url . ' (HTTP ' . $code . ')' );
			return array(
				'success' => true,
				'message' => 'Purged successfully',
				'code'    => $code,
			);
		}

		self::log( 'Purge failed for ' . $url . ': HTTP ' . $code );
		return array(
			'success' => false,
			'message' => 'Unexpected response: HTTP ' . $code,
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
	 * @return array Status info with 'available', 'enabled', 'message' keys.
	 */
	public static function get_status() {
		$available = get_option( self::OPTION_AVAILABLE, 'unknown' );
		$enabled   = get_option( self::OPTION_ENABLED, 'yes' );

		if ( 'unknown' === $available ) {
			$available = self::is_available() ? 'yes' : 'no';
		}

		$message = '';
		if ( 'yes' === $available && 'yes' === $enabled ) {
			$message = 'Selective purging active — only changed pages are purged for maximum efficiency';
		} elseif ( 'yes' === $available && 'no' === $enabled ) {
			$message = 'Selective purging available but disabled';
		} else {
			$message = 'nginx-module-cache-purge not detected — using full cache purge via cPanel API';
		}

		return array(
			'available' => 'yes' === $available,
			'enabled'   => 'yes' === $enabled,
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

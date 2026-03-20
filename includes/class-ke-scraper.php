<?php
/**
 * Kontentainment Events Scraper (Fetcher)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Scraper {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Fetch remote HTML content
	 *
	 * @param string $url The URL to fetch.
	 * @return array|WP_Error Array on success, WP_Error on failure.
	 */
	public function fetch( $url ) {
		// Basic validation
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'ke_invalid_url', 'The provided URL is invalid.' );
		}

		// Ensure it's a supported protocol
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		if ( ! in_array( $scheme, array( 'http', 'https' ) ) ) {
			return new WP_Error( 'ke_unsupported_protocol', 'The current scraper only supports HTTP and HTTPS URLs.' );
		}

		// Check domain lists from settings if available
		$settings = get_option( 'ke_import_settings', array() );
		$host = wp_parse_url( $url, PHP_URL_HOST );

		// 1. Check blocked domains
		if ( ! empty( $settings['blocked_domains'] ) ) {
			$blocked = array_map( 'trim', explode( "\n", $settings['blocked_domains'] ) );
			foreach ( $blocked as $domain ) {
				if ( ! empty( $domain ) && strpos( $host, $domain ) !== false ) {
					return new WP_Error( 'ke_domain_blocked', 'The domain is blocked by settings.' );
				}
			}
		}

		// 2. Check allowed domains (white list)
		if ( ! empty( $settings['allowed_domains'] ) ) {
			$allowed = array_map( 'trim', explode( "\n", $settings['allowed_domains'] ) );
			$is_allowed = false;
			foreach ( $allowed as $domain ) {
				if ( ! empty( $domain ) && strpos( $host, $domain ) !== false ) {
					$is_allowed = true;
					break;
				}
			}
			if ( ! $is_allowed ) {
				return new WP_Error( 'ke_domain_not_allowed', 'The domain is not in the allowed white list.' );
			}
		}

		// Configure request
		$args = array(
			'timeout'     => isset( $settings['timeout'] ) ? intval( $settings['timeout'] ) : 30,
			'redirection' => 5,
			'user-agent'  => isset( $settings['user_agent'] ) ? sanitize_text_field( $settings['user_agent'] ) : 'Mozilla/5.0 (WordPress/KontentainmentEvents)',
			'sslverify'   => false, // Optional: handle some SSL issues commonly encountered in local/test environments
		);

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return new WP_Error( 'ke_http_error', sprintf( 'Remote server returned HTTP code: %d', $status_code ) );
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return new WP_Error( 'ke_empty_body', 'The remote server returned an empty body.' );
		}

		// Success
		return array(
			'html'         => $body,
			'content_type' => wp_remote_retrieve_header( $response, 'content-type' ),
			'status_code'  => $status_code,
		);
	}
}
KE_Scraper::get_instance();

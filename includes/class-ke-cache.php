<?php
/**
 * Kontentainment Events Caching Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Cache {

	protected static $instance = null;
	private $prefix = 'ke_query_';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Get cached data by key
	 */
	public function get( $key ) {
		if ( ! $this->is_enabled() ) return false;
		return get_transient( $this->prefix . $key );
	}

	/**
	 * Set cached data
	 */
	public function set( $key, $data, $ttl = null ) {
		if ( ! $this->is_enabled() ) return false;
		
		if ( null === $ttl ) {
			$ttl = get_option( 'ke_cache_ttl', HOUR_IN_SECONDS );
		}
		
		return set_transient( $this->prefix . $key, $data, $ttl );
	}

	/**
	 * Generate a unique key for query args
	 */
	public function generate_key( $args ) {
		return md_hash( serialize( $args ) );
	}

	/**
	 * Flush all KE caches
	 */
	public function flush_all() {
		global $wpdb;
		// Use a more targeted delete if possible, but LIKE is standard for transients
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", '_transient_' . $this->prefix . '%' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", '_transient_timeout_' . $this->prefix . '%' ) );
		
		// Also flush ID cache
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", '_transient_ev_ids_%' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", '_transient_timeout_ev_ids_%' ) );
		
		return true;
	}

	/**
	 * Invalidate cache on post save/update
	 */
	public function invalidate_post_cache( $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( in_array( $post_type, [ 'event', 'venue' ] ) ) {
			$this->flush_all();
		}
	}

	private function is_enabled() {
		return '1' === (string) get_option( 'ke_enable_caching', '1' );
	}
}

// Add global hash helper if not exists
if ( ! function_exists('md_hash') ) {
	function md_hash( $data ) {
		return md5( is_string($data) ? $data : serialize($data) );
	}
}

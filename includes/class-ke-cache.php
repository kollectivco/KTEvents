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
	private $version = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->version = get_option( 'ke_cache_version', '1' );
	}

	/**
	 * Get cached data by key
	 */
	public function get( $key ) {
		if ( ! $this->is_enabled() ) return false;
		$versioned_key = $this->prefix . 'v' . $this->version . '_' . $key;
		return get_transient( $versioned_key );
	}

	/**
	 * Set cached data
	 */
	public function set( $key, $data, $ttl = null ) {
		if ( ! $this->is_enabled() ) return false;
		if ( null === $ttl ) {
			$ttl = get_option( 'ke_cache_ttl', HOUR_IN_SECONDS );
		}
		$versioned_key = $this->prefix . 'v' . $this->version . '_' . $key;
		return set_transient( $versioned_key, $data, $ttl );
	}

	/**
	 * Flush all KE caches (By incrementing version)
	 */
	public function flush_all() {
		$this->version = time();
		update_option( 'ke_cache_version', $this->version );
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

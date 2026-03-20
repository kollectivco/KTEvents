<?php
/**
 * Kontentainment Events Unique Post Tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Unique_Posts {

	protected static $instance = null;
	private $tracked_ids = [];

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Track a post ID
	 */
	public function track( $id ) {
		if ( ! is_numeric( $id ) ) return;
		if ( ! in_array( $id, $this->tracked_ids ) ) {
			$this->tracked_ids[] = (int) $id;
		}
	}

	/**
	 * Get all tracked IDs
	 */
	public function get_ids() {
		return $this->tracked_ids;
	}

	/**
	 * Filter IDs from a query result
	 */
	public function filter( $query_ids ) {
		return array_diff( (array) $query_ids, $this->tracked_ids );
	}
	
	/**
	 * Filter Query Results
	 * For compatibility with AJAX pagination
	 */
	public function clear() {
		$this->tracked_ids = [];
	}
}
// Init
KE_Unique_Posts::get_instance();

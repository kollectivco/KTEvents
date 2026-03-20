<?php
/**
 * Kontentainment Events Import Logs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Logs {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Create table hook
		add_action( 'admin_init', array( $this, 'maybe_create_table' ) );
	}

	/**
	 * Log an import event
	 */
	public function log( $data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ke_import_logs';

		$defaults = array(
			'timestamp'         => current_time( 'mysql' ),
			'source_url'        => '',
			'canonical_url'     => '',
			'source_name'       => '',
			'parser_used'       => '',
			'parser_confidence' => 0,
			'status'            => 'unknown',
			'post_id'           => 0,
			'message'           => '',
			'warnings'          => '',
			'errors'            => '',
		);

		$data = wp_parse_args( $data, $defaults );

		$wpdb->insert( $table_name, $data );
	}

	/**
	 * Get logs
	 */
	public function get_logs( $limit = 50, $offset = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ke_import_logs';
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT %d OFFSET %d", $limit, $offset ) );
	}

	/**
	 * Create the logs table if it doesn't exist
	 */
	public function maybe_create_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ke_import_logs';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			source_url text NOT NULL,
			canonical_url text DEFAULT '' NOT NULL,
			source_name varchar(255) DEFAULT '' NOT NULL,
			parser_used varchar(50) DEFAULT '' NOT NULL,
			parser_confidence int(3) DEFAULT 0 NOT NULL,
			status varchar(50) DEFAULT '' NOT NULL,
			post_id bigint(20) DEFAULT 0 NOT NULL,
			message text DEFAULT '' NOT NULL,
			warnings text DEFAULT '' NOT NULL,
			errors text DEFAULT '' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
KE_Logs::get_instance();

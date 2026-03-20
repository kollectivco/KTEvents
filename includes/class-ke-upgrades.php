<?php
/**
 * Kontentainment Events Upgrade & Lifecycle
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Upgrades {

	protected static $instance = null;
	private $db_version_option = 'ke_db_version';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_init', array( $this, 'check_upgrade' ) );
	}

	public function check_upgrade() {
		$current_db_version = get_option( $this->db_version_option, '0.1' );
		if ( version_compare( $current_db_version, KE_PLUGIN_VERSION, '<' ) ) {
			$this->run_upgrades( $current_db_version );
			update_option( $this->db_version_option, KE_PLUGIN_VERSION );
		}
	}

	private function run_upgrades( $old_version ) {
		// Example Migration Runners
		if ( version_compare( $old_version, '1.0.1', '<' ) ) {
			// Do something for 1.0.1, e.g. initialize new settings
			update_option( 'ke_enable_caching', '1' );
			update_option( 'ke_enable_schema', '1' );
		}
	}
}

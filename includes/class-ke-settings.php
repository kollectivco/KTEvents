<?php
/**
 * Kontentainment Events Import Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Settings {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings using WordPress Settings API
	 */
	public function register_settings() {
		register_setting( 'ke_import_settings_group', 'ke_import_settings', array( $this, 'sanitize' ) );

		add_settings_section(
			'ke_import_main_section',
			'General Import Settings',
			array( $this, 'section_callback' ),
			'ke-import-settings'
		);

		$fields = array(
			'timeout'              => 'Request Timeout (sec)',
			'user_agent'           => 'User Agent String',
			'auto_create_venue'    => 'Auto-create Venues',
			'auto_sideload_image'  => 'Auto-download Images',
			'allowed_domains'      => 'Allowed Domains (one per line)',
			'blocked_domains'      => 'Blocked Domains (one per line)',
			'debug_logging'        => 'Debug Logging',
		);

		foreach ( $fields as $id => $title ) {
			add_settings_field(
				$id,
				$title,
				array( $this, $id . '_callback' ),
				'ke-import-settings',
				'ke_import_main_section'
			);
		}
	}

	/**
	 * Settings Section Callback
	 */
	public function section_callback() {
		echo '<p>Configure the default behavior for the editorial import system.</p>';
	}

	/**
	 * Callback methods for settings fields
	 */
	public function timeout_callback() {
		$opts = get_option( 'ke_import_settings' );
		echo '<input type="number" name="ke_import_settings[timeout]" value="' . esc_attr( $opts['timeout'] ?? 30 ) . '" class="small-text"> seconds';
	}

	public function user_agent_callback() {
		$opts = get_option( 'ke_import_settings' );
		echo '<input type="text" name="ke_import_settings[user_agent]" value="' . esc_attr( $opts['user_agent'] ?? 'Mozilla/5.0 (WordPress/KontentainmentEvents)' ) . '" class="regular-text">';
	}

	public function auto_create_venue_callback() {
		$opts = get_option( 'ke_import_settings' );
		echo '<input type="checkbox" name="ke_import_settings[auto_create_venue]" value="1" ' . checked( $opts['auto_create_venue'] ?? 0, 1, false ) . '>';
	}

	public function auto_sideload_image_callback() {
		$opts = get_option( 'ke_import_settings' );
		echo '<input type="checkbox" name="ke_import_settings[auto_sideload_image]" value="1" ' . checked( $opts['auto_sideload_image'] ?? 0, 1, false ) . '>';
	}

	public function allowed_domains_callback() {
		$opts = get_option( 'ke_import_settings' );
		echo '<textarea name="ke_import_settings[allowed_domains]" rows="4" class="large-text">' . esc_textarea( $opts['allowed_domains'] ?? '' ) . '</textarea>';
	}

	public function blocked_domains_callback() {
		$opts = get_option( 'ke_import_settings' );
		echo '<textarea name="ke_import_settings[blocked_domains]" rows="4" class="large-text">' . esc_textarea( $opts['blocked_domains'] ?? '' ) . '</textarea>';
	}

	public function debug_logging_callback() {
		$opts = get_option( 'ke_import_settings' );
		echo '<input type="checkbox" name="ke_import_settings[debug_logging]" value="1" ' . checked( $opts['debug_logging'] ?? 0, 1, false ) . '>';
	}

	/**
	 * Sanitize Settings
	 */
	public function sanitize( $input ) {
		$new_input = array();
		$new_input['timeout']             = intval( $input['timeout'] ?? 30 );
		$new_input['user_agent']          = sanitize_text_field( $input['user_agent'] ?? '' );
		$new_input['auto_create_venue']   = isset( $input['auto_create_venue'] ) ? 1 : 0;
		$new_input['auto_sideload_image'] = isset( $input['auto_sideload_image'] ) ? 1 : 0;
		$new_input['allowed_domains']     = sanitize_textarea_field( $input['allowed_domains'] ?? '' );
		$new_input['blocked_domains']     = sanitize_textarea_field( $input['blocked_domains'] ?? '' );
		$new_input['debug_logging']       = isset( $input['debug_logging'] ) ? 1 : 0;
		return $new_input;
	}
}
KE_Settings::get_instance();

<?php
/**
 * Kontentainment Events Settings - Phase 6 Optimized
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
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings_page() {
		add_submenu_page(
			'edit.php?post_type=event',
			'KEA Settings',
			'Settings',
			'manage_options',
			'ke-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		// Production Settings
		register_setting( 'ke_settings_group', 'ke_enable_caching' );
		register_setting( 'ke_settings_group', 'ke_cache_ttl' );
		register_setting( 'ke_settings_group', 'ke_enable_schema' );
		register_setting( 'ke_settings_group', 'ke_delete_on_uninstall' );
		register_setting( 'ke_settings_group', 'ke_enable_import_logging' );

		// Import Logic Settings
		register_setting( 'ke_settings_group', 'ke_import_settings' );
	}

	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1>Kontentainment Events Settings</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'ke_settings_group' ); ?>
				<?php do_settings_sections( 'ke_settings_group' ); ?>

				<table class="form-table">
					<tr valign="top">
						<th scope="row">Performance / Caching</th>
						<td>
							<label>
								<input type="checkbox" name="ke_enable_caching" value="1" <?php checked( get_option('ke_enable_caching', '1'), '1' ); ?>>
								Enable Frontend Query Caching (Recommended)
							</label>
							<p class="description">Reduces server load by caching expensive database queries for events and venues.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Cache TTL (seconds)</th>
						<td>
							<input type="number" name="ke_cache_ttl" value="<?php echo esc_attr( get_option('ke_cache_ttl', HOUR_IN_SECONDS) ); ?>">
							<p class="description">How long should queries stay cached. Default is 3600 (1 hour).</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">SEO / Structured Data</th>
						<td>
							<label>
								<input type="checkbox" name="ke_enable_schema" value="1" <?php checked( get_option('ke_enable_schema', '1'), '1' ); ?>>
								Enable Event/Venue JSON-LD Schema
							</label>
							<p class="description">Helps search engines understand event details for Google events results.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Maintenance</th>
						<td>
							<label>
								<input type="checkbox" name="ke_delete_on_uninstall" value="1" <?php checked( get_option('ke_delete_on_uninstall'), '1' ); ?>>
								Delete KE data on uninstall (options only)
							</label>
							<p class="description">If checked, all settings will be removed when the plugin is deleted. CPT content will remain preserved.</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

<?php
/**
 * Kontentainment Events Diagnostics & Health
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Diagnostics {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_diagnostics_page' ) );
	}

	public function register_diagnostics_page() {
		add_submenu_page(
			'edit.php?post_type=event',
			'KEA Diagnostics',
			'Diagnostics & Health',
			'manage_options',
			'ke-diagnostics',
			array( $this, 'render_diagnostics_page' )
		);
	}

	public function render_diagnostics_page() {
		$event_count = wp_count_posts('event');
		$venue_count = wp_count_posts('venue');
		$elementor   = did_action( 'elementor/loaded' ) ? 'Active' : 'Not Detected';
		$foxiz       = ( did_action( 'foxiz_loaded' ) || did_action( 'foxiz_core_loaded' ) ) ? 'Active' : 'Not Detected';
		$cache       = get_option('ke_enable_caching', '1') === '1' ? 'Enabled' : 'Disabled';

		?>
		<div class="wrap ke-diagnostics-page">
			<h1>Kontentainment Events Diagnostics</h1>
			<p>Current health and environment status for troubleshooting.</p>

			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Value</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Plugin Version</td>
						<td><?php echo KE_PLUGIN_VERSION; ?></td>
						<td><span class="dashicons dashicons-yes-alt"></span></td>
					</tr>
					<tr>
						<td>Total Events</td>
						<td><?php echo $event_count->publish; ?> Published</td>
						<td><span class="dashicons dashicons-yes-alt"></span></td>
					</tr>
					<tr>
						<td>Total Venues</td>
						<td><?php echo $venue_count->publish; ?> Published</td>
						<td><span class="dashicons dashicons-yes-alt"></span></td>
					</tr>
					<tr>
						<td>Elementor Integration</td>
						<td><?php echo $elementor; ?></td>
						<td><span class="dashicons dashicons-admin-plugins"></span></td>
					</tr>
					<tr>
						<td>Foxiz Integration</td>
						<td><?php echo $foxiz; ?></td>
						<td><span class="dashicons dashicons-admin-appearance"></span></td>
					</tr>
					<tr>
						<td>Query Caching</td>
						<td><?php echo $cache; ?></td>
						<td><span class="dashicons dashicons-performance"></span></td>
					</tr>
					<tr>
						<td>Structured Data (Schema)</td>
						<td><?php echo (get_option('ke_enable_schema', '1') === '1' ? 'Enabled' : 'Disabled'); ?></td>
						<td><span class="dashicons dashicons-share-alt2"></span></td>
					</tr>
					<tr>
						<td>Active Registry Parsers</td>
						<td><?php echo count( KE_Parser_Registry::get_instance()->get_parsers() ); ?> Registered</td>
						<td><span class="dashicons dashicons-database-export"></span></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}

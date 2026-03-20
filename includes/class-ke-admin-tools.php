<?php
/**
 * Kontentainment Events Admin Maintenance Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Admin_Tools {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_tools_page' ) );
		add_action( 'admin_post_ke_run_tool', array( $this, 'run_tool_handler' ) );
	}

	public function register_tools_page() {
		add_submenu_page(
			'edit.php?post_type=event',
			'KEA Tools',
			'Maintenance Tools',
			'manage_options',
			'ke-tools',
			array( $this, 'render_tools_page' )
		);
	}

	public function render_tools_page() {
		?>
		<div class="wrap ke-admin-tools">
			<h1>Kontentainment Events Maintenance Tools</h1>
			<p>Use these tools to maintain data integrity and optimize performance.</p>

			<div class="ke-tool-card">
				<h3>Flush Caches</h3>
				<p>Clears all cached event/venue queries and transients.</p>
				<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
					<input type="hidden" name="action" value="ke_run_tool">
					<input type="hidden" name="tool_id" value="flush_cache">
					<?php wp_nonce_field( 'ke_tools_action' ); ?>
					<button type="submit" class="button button-primary">Flush Now</button>
				</form>
			</div>

			<div class="ke-tool-card">
				<h3>Sync Event Statuses</h3>
				<p>Recalculates "Upcoming" vs "Past" status based on today's date.</p>
				<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
					<input type="hidden" name="action" value="ke_run_tool">
					<input type="hidden" name="tool_id" value="sync_status">
					<?php wp_nonce_field( 'ke_tools_action' ); ?>
					<button type="submit" class="button button-primary">Sync Now</button>
				</form>
			</div>

			<div class="ke-tool-card">
				<h3>Recalculate Venue Event Counts</h3>
				<p>Rebuilds the cached counts of upcoming events for each venue.</p>
				<form method="get" action="<?php echo admin_url('admin-post.php'); ?>">
					<input type="hidden" name="action" value="ke_run_tool">
					<input type="hidden" name="tool_id" value="recalc_venue_counts">
					<?php wp_nonce_field( 'ke_tools_action' ); ?>
					<button type="submit" class="button button-primary">Recalculate</button>
				</form>
			</div>

			<div class="ke-tool-card">
				<h3>Updates / Diagnostics</h3>
				<p>Current Version: <strong><?php echo KE_PLUGIN_VERSION; ?></strong></p>
				<?php 
					$last_check = get_transient( 'ke_last_check_time' );
					$remote = get_transient( 'ke_github_update_data' );
				?>
				<p>Last Check: <?php echo $last_check ?: 'Never'; ?></p>
				<?php if ( $remote && ! empty($remote->tag_name) ) : ?>
					<p>Latest on GitHub: <a href="<?php echo esc_url($remote->html_url); ?>" target="_blank"><?php echo esc_html($remote->tag_name); ?></a></p>
				<?php endif; ?>

				<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
					<input type="hidden" name="action" value="ke_run_tool">
					<input type="hidden" name="tool_id" value="check_updates">
					<?php wp_nonce_field( 'ke_tools_action' ); ?>
					<button type="submit" class="button button-secondary">Check for Updates Now</button>
				</form>
			</div>
		</div>
		<style>
			.ke-tool-card {
				background: #fff; border: 1px solid #ccd0d4; padding: 20px;
				margin-bottom: 20px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,0.04);
			}
			.ke-tool-card h3 { margin-top: 0; }
		</style>
		<?php
	}

	public function run_tool_handler() {
		check_admin_referer( 'ke_tools_action' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die('Unauthorized');

		$tool_id = $_POST['tool_id'] ?? '';
		$message = 'Tool execution failed.';

		switch ( $tool_id ) {
			case 'flush_cache':
				KE_Cache::get_instance()->flush_all();
				$message = 'Cache flushed successfully.';
				break;
			case 'sync_status':
				$this->sync_all_statuses();
				$message = 'Event statuses synced successfully.';
				break;
			case 'recalc_venue_counts':
				$this->recalc_venue_counts();
				$message = 'Venue counts recalculated successfully.';
				break;
			case 'check_updates':
				$updater = new KE_Updater( KE_PLUGIN_DIR . 'kontentainment-events.php', 'https://github.com/kollectivco/KTEvents' );
				$remote = $updater->get_remote_data( true ); // Force refresh
				set_transient( 'ke_last_check_time', date_i18n( get_option('date_format') . ' H:i:s' ), DAY_IN_SECONDS );
				if ( $remote ) {
					$message = 'GitHub Release detected: ' . $remote->tag_name;
				} else {
					$message = 'Check completed. No GitHub Release found yet.';
				}
				break;
		}

		wp_safe_redirect( add_query_arg( [ 'page' => 'ke-tools', 'ke_msg' => urlencode($message) ], admin_url('edit.php?post_type=event&page=ke-tools') ) );
		exit;
	}

	private function sync_all_statuses() {
		$events = get_posts( [ 'post_type' => 'event', 'numberposts' => -1, 'post_status' => 'any' ] );
		$today = date('Y-m-d');
		foreach ( $events as $event ) {
			$date   = ke_get_event_meta( $event->ID, 'date' );
			$manual = get_post_meta( $event->ID, 'KE_event_status_manual', true );
			if ( '1' === $manual ) continue; // Preserve editorial overrides
			
			$status = ($date < $today) ? 'past' : 'upcoming';
			update_post_meta( $event->ID, 'KE_event_status', $status );
		}
	}

	private function recalc_venue_counts() {
		$venues = get_posts( [ 'post_type' => 'venue', 'numberposts' => -1 ] );
		foreach ( $venues as $venue ) {
			ke_count_venue_upcoming_events( $venue->ID ); // This helper already exists or should be optimized
		}
	}
}

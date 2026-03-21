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
				<p>Current Plugin Version: <strong><?php echo KE_PLUGIN_VERSION; ?></strong></p>
				<?php 
					$last_check = get_transient( 'ke_last_check_time' );
					$remote = get_transient( 'ke_github_update_data' );
				?>
				<p>Last Sync Check: <span class="ke-meta-value"><?php echo $last_check ?: 'Never'; ?></span></p>
				
				<?php if ( $remote && ! empty($remote->version) ) : ?>
					<div class="ke-update-info-plate">
						<p>Latest on GitHub: <span class="ke-tag-version"><?php echo esc_html($remote->version); ?></span></p>
						<?php if ( version_compare( KE_PLUGIN_VERSION, ltrim($remote->version, 'v'), '<' ) ) : ?>
							<p class="ke-notice-warning">Update Available! Refresh the Plugins page to see the notification.</p>
						<?php else : ?>
							<p class="ke-notice-info">You are running the latest version.</p>
						<?php endif; ?>
					</div>
				<?php elseif ( $remote === false ) : ?>
					<p class="ke-notice-warning">GitHub API check failed. Ensure the repo has at least one Tag or Release.</p>
				<?php else : ?>
					<p class="ke-notice-info">No update data cached. Click the button below to check.</p>
				<?php endif; ?>

				<form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="margin-top: 20px;">
					<input type="hidden" name="action" value="ke_run_tool">
					<input type="hidden" name="tool_id" value="check_updates">
					<?php wp_nonce_field( 'ke_tools_action' ); ?>
					<button type="submit" class="button button-secondary">Run Forced Version Check</button>
				</form>
			</div>
		</div>
		<style>
			.ke-tool-card {
				background: #fff; border: 1px solid #ccd0d4; padding: 20px;
				margin-bottom: 20px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,0.04);
			}
			.ke-tool-card h3 { margin-top: 0; }
			.ke-tag-version { background: #3b82f6; color: #fff; padding: 2px 8px; border-radius: 4px; font-weight: bold; text-decoration: none; }
			.ke-tag-version:hover { background: #1d4ed8; color: #fff; }
			.ke-notice-warning { color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; border-radius: 4px; }
			.ke-notice-info { color: #0c5460; background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 10px; border-radius: 4px; }
			.ke-meta-value { color: #111827; font-weight: 600; }
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
				$remote  = $updater->get_remote_data( true ); // Force refresh
				if ( $remote ) {
					$v_remote = ltrim($remote->version, 'v');
					if ( version_compare( KE_PLUGIN_VERSION, $v_remote, '<' ) ) {
						$message = "Update detected! GitHub Version: $v_remote. Visit Plugins to install.";
					} else {
						$message = "Check complete. Version $v_remote is the latest on GitHub.";
					}
				} else {
					$message = 'Check failed. No tagged versions found on GitHub.';
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

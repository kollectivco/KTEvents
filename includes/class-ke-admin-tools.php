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

		// Plugin Row Actions
		$plugin_file = KE_PLUGIN_DIR . 'kontentainment-events.php';
		$basename    = plugin_basename( $plugin_file );
		add_filter( "plugin_action_links_{$basename}", array( $this, 'add_plugin_row_action' ) );
		add_action( 'admin_post_ke_check_update', array( $this, 'handle_plugin_row_check' ) );

		// Admin Notices for Update Check
		add_action( 'admin_notices', array( $this, 'display_update_notices' ) );
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

	/**
	 * Add "Check for updates" link to Plugins screen
	 */
	public function add_plugin_row_action( $links ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=ke_check_update' ), 'ke_check_update_nonce' );
		$check_link = '<a href="' . esc_url( $url ) . '" style="color: #3b82f6; font-weight: 600;">Check for updates</a>';
		array_unshift( $links, $check_link );
		return $links;
	}

	/**
	 * Handle the click from the Plugins screen
	 */
	public function handle_plugin_row_check() {
		check_admin_referer( 'ke_check_update_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die('Unauthorized');

		$updater = new KE_Updater( KE_PLUGIN_DIR . 'kontentainment-events.php', 'https://github.com/kollectivco/KTEvents' );
		$remote = $updater->get_remote_data( true ); // Force refresh

		$result_code = 'no_update';
		if ( ! $remote || ! empty($remote->error) ) {
			$result_code = 'error';
			$error_msg   = $remote ? $remote->error : 'Unknown API Error';
		} else {
			$v_remote = ltrim($remote->version, 'v');
			if ( version_compare( KE_PLUGIN_VERSION, $v_remote, '<' ) ) {
				$result_code = 'update_found';
			}
		}

		wp_safe_redirect( add_query_arg( [ 
			'ke_update_result' => $result_code,
			'ke_error'         => ! empty($error_msg) ? urlencode($error_msg) : ''
		], admin_url( 'plugins.php' ) ) );
		exit;
	}

	/**
	 * Display the result notice on the Plugins screen
	 */
	public function display_update_notices() {
		if ( ! isset( $_GET['ke_update_result'] ) ) return;

		$result = $_GET['ke_update_result'];
		$error  = $_GET['ke_error'] ?? '';
		$class  = 'notice notice-info is-dismissible';
		$msg    = 'Update check complete.';

		$remote = get_transient( 'ke_github_update_data' );
		$v_remote = $remote ? ltrim($remote->version, 'v') : 'Unknown';

		switch ( $result ) {
			case 'update_found':
				$class = 'notice notice-warning is-dismissible';
				$msg = "<strong>Kontentainment Events Update Found!</strong> A new version ($v_remote) is available via " . ($remote->source ?? 'GitHub') . ".";
				break;
			case 'no_update':
				$msg = "Kontentainment Events is up to date. (Current: " . KE_PLUGIN_VERSION . ", Latest Remote: $v_remote)";
				break;
			case 'error':
				$class = 'notice notice-error is-dismissible';
				$msg = "<strong>Update Check Failed.</strong> " . ($error ? urldecode($error) : 'Please ensure at least one release or tag exists on GitHub.');
				break;
		}

		echo '<div class="' . esc_attr( $class ) . '"><p>' . wp_kses_post( $msg ) . '</p></div>';
	}

	public function render_tools_page() {
		// Existing tool page render logic...
		if ( isset( $_GET['ke_msg'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( urldecode( $_GET['ke_msg'] ) ) . '</p></div>';
		}
		?>
		<div class="wrap ke-admin-tools">
			<h1>Kontentainment Events Maintenance Tools</h1>
			<div class="ke-tool-card">
				<h3>Flush Caches</h3>
				<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
					<input type="hidden" name="action" value="ke_run_tool">
					<input type="hidden" name="tool_id" value="flush_cache">
					<?php wp_nonce_field( 'ke_tools_action' ); ?>
					<button type="submit" class="button button-primary">Flush Now</button>
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
						<p class="description">Source: <strong><?php echo esc_html($remote->source ?? 'Unknown'); ?></strong></p>
						<?php if ( ! empty($remote->error) ) : ?>
							<p class="ke-notice-warning">Issue detected: <?php echo esc_html($remote->error); ?></p>
						<?php elseif ( version_compare( KE_PLUGIN_VERSION, ltrim($remote->version, 'v'), '<' ) ) : ?>
							<p class="ke-notice-warning">Update Available! Visit the Plugins page to install.</p>
						<?php else : ?>
							<p class="ke-notice-info">You are running the latest version.</p>
						<?php endif; ?>
					</div>
				<?php elseif ( $remote && ! empty($remote->error) ) : ?>
					<p class="ke-notice-warning"><strong>Update Check Issue:</strong> <?php echo esc_html($remote->error); ?></p>
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
			.ke-tool-card { background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-bottom: 20px; border-radius: 4px; }
			.ke-tag-version { background: #3b82f6; color: #fff; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
			.ke-notice-warning { color: #856404; background-color: #fff3cd; padding: 10px; border-radius: 4px; border: 1px solid #ffeeba; }
			.ke-notice-info { color: #0c5460; background-color: #d1ecf1; padding: 10px; border-radius: 4px; border: 1px solid #bee5eb; }
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
			case 'check_updates':
				$updater = new KE_Updater( KE_PLUGIN_DIR . 'kontentainment-events.php', 'https://github.com/kollectivco/KTEvents' );
				$remote  = $updater->get_remote_data( true );
				if ( $remote ) {
					$v_remote = ltrim($remote->version, 'v');
					$message = ( version_compare( KE_PLUGIN_VERSION, $v_remote, '<' ) ) ? "Update detected: $v_remote." : "Plugin is up to date.";
				} else {
					$message = 'Check failed.';
				}
				break;
		}

		wp_safe_redirect( add_query_arg( [ 'page' => 'ke-tools', 'ke_msg' => urlencode($message) ], admin_url('edit.php?post_type=event&page=ke-tools') ) );
		exit;
	}
}

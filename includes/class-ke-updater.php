<?php
/**
 * Kontentainment Events GitHub Updater - Phase 6.1 Refined
 * Native WordPress update notifications and ZIP handling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Updater {

	private $plugin_slug; // e.g. kontentainment-events/kontentainment-events.php
	private $base_slug;   // e.g. kontentainment-events
	private $plugin_file;
	private $github_repo; // e.g. kollectivco/KTEvents
	private $github_api_url;
	private $access_token = '';

	public function __construct( $plugin_file, $github_url ) {
		$this->plugin_file = $plugin_file;
		$this->plugin_slug = plugin_basename( $plugin_file );
		$this->base_slug   = dirname( $this->plugin_slug );
		
		// Parse repo path
		$path = trim( wp_parse_url( $github_url, PHP_URL_PATH ), '/' );
		$this->github_repo = $path;
		$this->github_api_url = "https://api.github.com/repos/{$this->github_repo}/releases/latest";

		// Support for private repo token (optional)
		if ( defined( 'KE_GITHUB_TOKEN' ) ) {
			$this->access_token = KE_GITHUB_TOKEN;
		} else {
			$this->access_token = get_option( 'ke_github_token', '' );
		}

		// WP Hooks for native updates
		add_filter( 'site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder' ), 10, 3 );
	}

	/**
	 * Check if a newer version is available on GitHub
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote = $this->get_remote_data();
		if ( ! $remote || is_wp_error( $remote ) || empty($remote->tag_name) ) {
			return $transient;
		}

		$current_version = KE_PLUGIN_VERSION;
		$new_version     = ltrim( $remote->tag_name, 'v' );

		if ( version_compare( $current_version, $new_version, '<' ) ) {
			$res = new stdClass();
			$res->slug     = $this->base_slug;
			$res->plugin   = $this->plugin_slug;
			$res->new_version = $new_version;
			$res->url      = "https://github.com/{$this->github_repo}";
			$res->package  = $this->get_asset_url( $remote );

			$transient->response[ $this->plugin_slug ] = $res;
		}

		return $transient;
	}

	/**
	 * Provide plugin information for the "View Details" modal
	 */
	public function plugin_info( $res, $action, $args ) {
		if ( 'plugin_information' !== $action || $args->slug !== $this->base_slug ) {
			return $res;
		}

		$remote = $this->get_remote_data();
		if ( ! $remote || is_wp_error( $remote ) || empty($remote->tag_name) ) {
			return $res;
		}

		$res = new stdClass();
		$res->name        = 'Kontentainment Events';
		$res->slug        = $this->base_slug;
		$res->version     = ltrim( $remote->tag_name, 'v' );
		$res->author      = 'Antigravity';
		$res->homepage    = "https://github.com/{$this->github_repo}";
		$res->download_link = $this->get_asset_url( $remote );
		$res->sections = array(
			'description' => 'A standalone editorial events directory for magazine websites.',
			'changelog'   => wp_kses_post( wpautop( $remote->body ) ),
		);

		return $res;
	}

	/**
	 * Fix folder name issue if Github source ZIP is used (fallback)
	 */
	public function fix_source_folder( $source, $remote_source, $upgrader ) {
		global $wp_filesystem;
		
		if ( strpos( $source, $this->base_slug ) !== false ) {
			return $source;
		}

		// Rename root folder to kontentainment-events if it's GitHub's auto-name
		$new_source = trailingslashit( $remote_source ) . $this->base_slug . '/';
		$wp_filesystem->move( $source, $new_source );
		return $new_source;
	}

	/**
	 * Fetch remote data from GitHub API
	 */
	public function get_remote_data( $force = false ) {
		$remote = get_transient( 'ke_github_update_data' );
		if ( false !== $remote && ! $force ) {
			return $remote;
		}

		$args = array(
			'headers' => array(
				'Accept' => 'application/vnd.github.v3+json',
			),
		);

		if ( ! empty( $this->access_token ) ) {
			$args['headers']['Authorization'] = 'token ' . $this->access_token;
		}

		$response = wp_remote_get( $this->github_api_url, $args );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$remote = json_decode( wp_remote_retrieve_body( $response ) );
		set_transient( 'ke_github_update_data', $remote, HOUR_IN_SECONDS );

		return $remote;
	}

	/**
	 * Find the built ZIP asset if available, otherwise fallback to source ZIP
	 */
	private function get_asset_url( $remote ) {
		if ( ! empty( $remote->assets ) ) {
			foreach ( $remote->assets as $asset ) {
				if ( strpos( $asset->name, $this->base_slug . '.zip' ) !== false ) {
					return $asset->browser_download_url;
				}
			}
		}
		return $remote->zipball_url; // Fallback to GitHub source zip
	}
}

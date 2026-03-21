<?php
/**
 * Kontentainment Events GitHub Updater - Phase 6.1 AUDITED & REFINED
 * Native WordPress update notifications, Auto-update support, and View Details Modal
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

		// Token support
		$this->access_token = defined( 'KE_GITHUB_TOKEN' ) ? KE_GITHUB_TOKEN : get_option( 'ke_github_token', '' );

		// Hook into WP Core Update System
		add_filter( 'site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder' ), 10, 3 );

		// Explicit Auto-Update Support
		add_filter( 'auto_update_plugin', array( $this, 'maybe_auto_update' ), 20, 2 );
	}

	/**
	 * Inject update info into the native site_transient_update_plugins
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
			$res->tested   = '6.4.3';
			
			$transient->response[ $this->plugin_slug ] = $res;
		} else {
			// No update available, but we still want to expose metadata for auto-update checks
			$res = new stdClass();
			$res->slug     = $this->base_slug;
			$res->plugin   = $this->plugin_slug;
			$res->new_version = $current_version;
			$res->package  = '';
			$transient->no_update[ $this->plugin_slug ] = $res;
		}

		return $transient;
	}

	/**
	 * Provide professional plugin information for the "View Details" modal
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
		$res->name           = 'Kontentainment Events';
		$res->slug           = $this->base_slug;
		$res->version        = ltrim( $remote->tag_name, 'v' );
		$res->author         = '<a href="https://github.com/kollectivco">Kollectiv</a>';
		$res->homepage       = "https://github.com/{$this->github_repo}";
		$res->download_link  = $this->get_asset_url( $remote );
		$res->last_updated   = $remote->published_at;
		$res->requires       = '6.0';
		$res->tested         = '6.4.3';
		$res->requires_php   = '7.4';

		$body = $remote->body;
		$res->sections = array(
			'description' => 'A professional editorial events and directory plugin for WordPress magazine websites.',
			'changelog'   => wp_kses_post( wpautop( $body ) ),
		);

		// Premium visual assets
		$res->icons = array(
			'1x' => 'https://raw.githubusercontent.com/' . $this->github_repo . '/main/assets/images/icon-128x128.png',
			'2x' => 'https://raw.githubusercontent.com/' . $this->github_repo . '/main/assets/images/icon-256x256.png',
		);
		
		$res->banners = array(
			'low'  => 'https://raw.githubusercontent.com/' . $this->github_repo . '/main/assets/images/banner-772x250.png',
			'high' => 'https://raw.githubusercontent.com/' . $this->github_repo . '/main/assets/images/banner-1544x500.png',
		);

		return $res;
	}

	/**
	 * Support for native WordPress auto-updates
	 */
	public function maybe_auto_update( $update, $item ) {
		if ( $item->slug === $this->base_slug ) {
			return true; // Allow WordPress to manage auto-updates for this plugin if enabled by user
		}
		return $update;
	}

	/**
	 * Renames the root folder to kontentainment-events if Github uses a mismatched name
	 */
	public function fix_source_folder( $source, $remote_source, $upgrader ) {
		if ( strpos( $source, $this->base_slug ) !== false ) {
			return $source;
		}

		global $wp_filesystem;
		$new_source = trailingslashit( $remote_source ) . $this->base_slug . '/';
		$wp_filesystem->move( $source, $new_source );
		return $new_source;
	}

	/**
	 * Fetch remote data from GitHub API with caching
	 */
	public function get_remote_data( $force = false ) {
		$remote = get_transient( 'ke_github_update_data' );
		if ( false !== $remote && ! $force ) {
			return $remote;
		}

		$args = array(
			'timeout' => 15,
			'headers' => array(
				'Accept'     => 'application/vnd.github.v3+json',
				'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
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
		set_transient( 'ke_github_update_data', $remote, HOUR_IN_SECONDS * 6 );
		set_transient( 'ke_last_check_time', date_i18n( get_option('date_format') . ' H:i:s' ), DAY_IN_SECONDS );

		return $remote;
	}

	/**
	 * Pull ZIP asset or source zip fallback
	 */
	private function get_asset_url( $remote ) {
		if ( ! empty( $remote->assets ) ) {
			foreach ( $remote->assets as $asset ) {
				// Match against distributable ZIP name
				if ( false !== strpos( $asset->name, $this->base_slug ) && strpos( $asset->name, '.zip' ) !== false ) {
					return $asset->browser_download_url;
				}
			}
		}
		return $remote->zipball_url;
	}
}

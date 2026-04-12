<?php
/**
 * Kontentainment Events GitHub Updater - v1.3.59 (OPTIMIZED - NO RATE LIMITS)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Updater {

	private $plugin_slug; 
	private $base_slug;   
	private $plugin_file;
	private $github_repo; 
	private $access_token = '';

	public function __construct( $plugin_file, $github_url ) {
		$this->plugin_file = $plugin_file;
		$this->plugin_slug = plugin_basename( $plugin_file );
		$this->base_slug   = dirname( $this->plugin_slug );
		
		$path = trim( wp_parse_url( $github_url, PHP_URL_PATH ), '/' );
		$this->github_repo = $path;

		$this->access_token = defined( 'KE_GITHUB_TOKEN' ) ? KE_GITHUB_TOKEN : get_option( 'ke_github_token', '' );

		if ( ! empty( $this->plugin_slug ) && ! empty( $this->base_slug ) ) {
			add_filter( 'site_transient_update_plugins', array( $this, 'check_update' ) );
			add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
			add_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder' ), 10, 4 );
			add_filter( 'auto_update_plugin', array( $this, 'maybe_auto_update' ), 20, 2 );
		}
	}

	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) return $transient;

		$remote = $this->get_remote_data();
		if ( ! $remote || empty( $remote->version ) || '0.0.0' === $remote->version ) {
			return $transient;
		}

		$new_version = ltrim( $remote->version, 'v' );
		if ( version_compare( KE_PLUGIN_VERSION, $new_version, '<' ) ) {
			$res = new stdClass();
			$res->slug     = $this->base_slug;
			$res->plugin   = $this->plugin_slug;
			$res->new_version = $new_version;
			$res->url      = "https://github.com/{$this->github_repo}";
			$res->package  = $remote->package;
			$res->tested   = '6.4.3';
			
			$transient->response[ $this->plugin_slug ] = $res;
		}
		return $transient;
	}

	public function plugin_info( $res, $action, $args ) {
		if ( 'plugin_information' !== $action || $args->slug !== $this->base_slug ) return $res;

		$remote = $this->get_remote_data();
		if ( ! $remote || is_wp_error( $remote ) ) return $res;

		$res = new stdClass();
		$res->name           = 'Kontentainment Events';
		$res->slug           = $this->base_slug;
		$res->version        = ltrim( $remote->version, 'v' );
		$res->author         = '<a href="https://github.com/kollectivco">Kollectiv</a>';
		$res->homepage       = "https://github.com/{$this->github_repo}";
		$res->download_link  = $remote->package;
		$res->last_updated   = $remote->last_updated;
		$res->sections       = array(
			'description' => 'A professional editorial events directory for magazine websites.',
			'changelog'   => wp_kses_post( wpautop( $remote->changelog ) ),
		);
		return $res;
	}

	public function maybe_auto_update( $update, $item ) {
		if ( isset($item->slug) && $item->slug === $this->base_slug ) return true;
		return $update;
	}

	public function fix_source_folder( $source, $remote_source, $upgrader, $hook_extra ) {
		if ( ! $source || ! $this->base_slug ) return $source;
		
		if ( strpos( basename($source), $this->base_slug ) === 0 && basename($source) === $this->base_slug ) {
			return $source;
		}

		$target_item = ( isset( $upgrader->skin->plugin ) ) ? $upgrader->skin->plugin : ( $hook_extra['plugin'] ?? '' );
		if ( $target_item !== $this->plugin_slug ) {
			return $source;
		}

		global $wp_filesystem;
		$new_source = trailingslashit( $remote_source ) . $this->base_slug . '/';
		if ( $wp_filesystem->move( $source, $new_source ) ) return $new_source;

		return $source;
	}

	/**
	 * ULTRA ROBUST REMOTE DATA FETCH
	 * Tries GitHub API, then falls back to GitHub Raw to bypass 403 Forbidden/Rate limits.
	 */
	public function get_remote_data( $force = false ) {
		$cache_key = 'ke_gh_' . md5($this->github_repo) . '_data_v2';
		$cached = get_transient( $cache_key );
		if ( false !== $cached && ! $force ) return $cached;

		$remote = new stdClass();
		$remote->version = '0.0.0';
		$remote->package = '';
		$remote->last_updated = current_time('mysql');
		$remote->changelog = 'New updates are available on GitHub.';
		$remote->error = '';
		$remote->source = 'none';

		// Step 1: Try GitHub API (Releases)
		$release_url = "https://api.github.com/repos/{$this->github_repo}/releases/latest";
		$response    = $this->api_get( $release_url );
		$code        = wp_remote_retrieve_response_code( $response );

		if ( ! is_wp_error( $response ) && 200 === $code ) {
			$data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $data->tag_name ) ) {
				$remote->version = $data->tag_name;
				$remote->package = $this->get_asset_url( $data );
				$remote->last_updated = $data->published_at;
				$remote->changelog = $data->body ?: 'New updates available.';
				$remote->source = 'GitHub API (Release)';
				$this->cache_remote( $remote );
				return $remote;
			}
		}

		// Step 2: FALLBACK TO RAW (Bypasses 403 / Rate Limits)
		// We fetch the main plugin file from GitHub Raw and parse the version
		$raw_url  = "https://raw.githubusercontent.com/{$this->github_repo}/main/kontentainment-events.php";
		$response = wp_remote_get( $raw_url, [ 'timeout' => 10, 'user-agent' => 'KTEvents-Updater' ] );
		$code     = wp_remote_retrieve_response_code( $response );

		if ( ! is_wp_error( $response ) && 200 === $code ) {
			$body = wp_remote_retrieve_body( $response );
			if ( preg_match( '/Version:\s*([0-9\.]+)/i', $body, $matches ) ) {
				$remote->version = trim( $matches[1] );
				// For Raw check, we assume the ZIP is at the main branch or we use the latest tag logic
				$remote->package = "https://github.com/{$this->github_repo}/archive/refs/tags/v{$remote->version}.zip";
				$remote->source  = 'GitHub Raw (bypass)';
				$remote->error   = ''; // Clear 403 if raw worked
				
				$this->cache_remote( $remote );
				return $remote;
			}
		}

		// Step 3: Catch Error if everything failed
		if ( is_wp_error( $response ) ) {
			$remote->error = $response->get_error_message();
		} elseif ( 403 === $code ) {
			$remote->error = "GitHub Rate Limit Exceeded (403). Waiting for cooldown or use a Token in KEA Tools.";
		} else {
			$remote->error = "Update server returned code: $code";
		}

		return $remote;
	}

	private function api_get( $url ) {
		$args = array(
			'timeout' => 15,
			'headers' => array(
				'Accept'     => 'application/vnd.github.v3+json',
				'User-Agent' => 'KTEvents-Updater/1.0; ' . get_bloginfo( 'url' ),
			),
		);
		if ( $this->access_token ) {
			$args['headers']['Authorization'] = 'token ' . $this->access_token;
		}
		return wp_remote_get( $url, $args );
	}

	private function cache_remote( $remote ) {
		$cache_key = 'ke_gh_' . md5($this->github_repo) . '_data_v2';
		set_transient( $cache_key, $remote, HOUR_IN_SECONDS * 12 ); // Cache for 12 hours
		set_transient( 'ke_github_update_data', $remote, HOUR_IN_SECONDS * 12 ); 
		set_transient( 'ke_last_check_time', date_i18n( get_option('date_format') . ' H:i:s' ), DAY_IN_SECONDS );
	}

	private function get_asset_url( $data ) {
		if ( ! empty( $data->assets ) ) {
			foreach ( $data->assets as $asset ) {
				if ( strpos( strtolower($asset->name), '.zip' ) !== false ) {
					return $asset->browser_download_url;
				}
			}
		}
		return $data->zipball_url;
	}
}

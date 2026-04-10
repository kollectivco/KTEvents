<?php
/**
 * Kontentainment Events GitHub Updater - v1.3.19 (FIXED - ISOLATED IDENTITY)
 * Orchestrates plugin updates specifically for this plugin only.
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
		$this->plugin_slug = plugin_basename( $plugin_file ); // kontentainment-events/kontentainment-events.php
		$this->base_slug   = dirname( $this->plugin_slug );  // kontentainment-events
		
		// Parse repo path
		$path = trim( wp_parse_url( $github_url, PHP_URL_PATH ), '/' );
		$this->github_repo = $path;

		// Token support (for private repo testing)
		$this->access_token = defined( 'KE_GITHUB_TOKEN' ) ? KE_GITHUB_TOKEN : get_option( 'ke_github_token', '' );

		// Only register hooks if we have valid slugs
		if ( ! empty( $this->plugin_slug ) && ! empty( $this->base_slug ) ) {
			add_filter( 'site_transient_update_plugins', array( $this, 'check_update' ) );
			add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
			add_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder' ), 10, 4 );
			add_filter( 'auto_update_plugin', array( $this, 'maybe_auto_update' ), 20, 2 );
		}
	}

	/**
	 * Inject our plugin into the update transient
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) return $transient;

		$remote = $this->get_remote_data();
		if ( ! $remote || is_wp_error( $remote ) || empty( $remote->version ) || '0.0.0' === $remote->version ) {
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

	/**
	 * Provide plugin information for the "View Details" modal
	 */
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
			'description' => 'A professional editorial events and directory plugin for WordPress magazine websites.',
			'changelog'   => wp_kses_post( wpautop( $remote->changelog ) ),
		);
		return $res;
	}

	public function maybe_auto_update( $update, $item ) {
		if ( isset($item->slug) && $item->slug === $this->base_slug ) return true;
		return $update;
	}

	/**
	 * CRITICAL FIX: Ensure the source folder selection ONLY targets this plugin.
	 * Hijacking unrelated zip uploads was caused by lack of scope checking here.
	 */
	public function fix_source_folder( $source, $remote_source, $upgrader, $hook_extra ) {
		if ( ! $source || ! $this->base_slug ) return $source;
		
		// 1. Isolate to plugin skip if it already contains our slug correctly
		if ( strpos( basename($source), $this->base_slug ) === 0 && basename($source) === $this->base_slug ) {
			return $source;
		}

		// 2. STRICTOR CHECK: Is this upgrader actually targeting OUR plugin?
		// Check skin->plugin (standard for single updates)
		$target_item = '';
		if ( isset( $upgrader->skin->plugin ) ) {
			$target_item = $upgrader->skin->plugin;
		} elseif ( isset( $hook_extra['plugin'] ) ) {
			$target_item = $hook_extra['plugin'];
		}

		// If we can't confirm this is our plugin, bail immediately.
		if ( $target_item !== $this->plugin_slug ) {
			return $source;
		}

		// 3. Perform the rename only for our plugin
		global $wp_filesystem;
		$new_source = trailingslashit( $remote_source ) . $this->base_slug . '/';
		
		if ( $wp_filesystem->move( $source, $new_source ) ) {
			return $new_source;
		}

		return $source;
	}

	/**
	 * Fetch remote data with granular error tracking
	 */
	public function get_remote_data( $force = false ) {
		$cache_key = 'ke_gh_' . md5($this->github_repo) . '_data';
		$cached = get_transient( $cache_key );
		if ( false !== $cached && ! $force ) return $cached;

		$remote = new stdClass();
		$remote->version = '0.0.0';
		$remote->package = '';
		$remote->last_updated = '';
		$remote->changelog = '';
		$remote->error = '';
		$remote->source = 'none';

		// Step 1: Handshake with Releases
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
				$remote->source = 'GitHub Release';
				
				$this->cache_remote( $remote );
				return $remote;
			}
		} elseif ( ! is_wp_error( $response ) && 404 !== $code ) {
			$remote->error = "GitHub API Error: $code " . wp_remote_retrieve_response_message( $response );
			return $remote;
		}

		// Step 2: Fallback to Tags
		$tags_url = "https://api.github.com/repos/{$this->github_repo}/tags";
		$response = $this->api_get( $tags_url );
		$code     = wp_remote_retrieve_response_code( $response );

		if ( ! is_wp_error( $response ) && 200 === $code ) {
			$data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $data ) && is_array( $data ) ) {
				$latest = $data[0];
				$remote->version = $latest->name;
				$remote->package = $latest->zipball_url;
				$remote->source  = 'Git Tag';
				$remote->changelog = 'Release notes available on GitHub Tags.';
				
				$this->cache_remote( $remote );
				return $remote;
			} else {
				$remote->error = "No versions found in repository.";
			}
		} else {
			$reason = is_wp_error( $response ) ? $response->get_error_message() : "HTTP $code";
			$remote->error = "Connection failed: $reason";
		}

		return $remote;
	}

	private function api_get( $url ) {
		$args = array(
			'timeout' => 20,
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
		$cache_key = 'ke_gh_' . md5($this->github_repo) . '_data';
		set_transient( $cache_key, $remote, HOUR_IN_SECONDS * 6 );
		set_transient( 'ke_github_update_data', $remote, HOUR_IN_SECONDS * 6 ); // Static key for Admin Tools
		set_transient( 'ke_last_check_time', date_i18n( get_option('date_format') . ' H:i:s' ), DAY_IN_SECONDS );
	}

	private function get_asset_url( $data ) {
		if ( ! empty( $data->assets ) ) {
			foreach ( $data->assets as $asset ) {
				// Match any ZIP file in the release assets
				if ( strpos( strtolower($asset->name), '.zip' ) !== false ) {
					return $asset->browser_download_url;
				}
			}
		}
		return $data->zipball_url;
	}
}

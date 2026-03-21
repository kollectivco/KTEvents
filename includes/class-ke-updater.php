<?php
/**
 * Kontentainment Events GitHub Updater - v6.2 (AUDITED & RECOVERED)
 * Native WordPress update notifications with Release/Tag fallback
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
		
		// Parse repo path
		$path = trim( wp_parse_url( $github_url, PHP_URL_PATH ), '/' );
		$this->github_repo = $path;

		// Token support
		$this->access_token = defined( 'KE_GITHUB_TOKEN' ) ? KE_GITHUB_TOKEN : get_option( 'ke_github_token', '' );

		// Hook into WP
		add_filter( 'site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder' ), 10, 3 );
		add_filter( 'auto_update_plugin', array( $this, 'maybe_auto_update' ), 20, 2 );
	}

	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote = $this->get_remote_data();
		if ( ! $remote || is_wp_error( $remote ) ) {
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
		if ( 'plugin_information' !== $action || $args->slug !== $this->base_slug ) {
			return $res;
		}

		$remote = $this->get_remote_data();
		if ( ! $remote || is_wp_error( $remote ) ) {
			return $res;
		}

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
		if ( $item->slug === $this->base_slug ) return true;
		return $update;
	}

	public function fix_source_folder( $source, $remote_source, $upgrader ) {
		if ( strpos( $source, $this->base_slug ) !== false ) return $source;
		global $wp_filesystem;
		$new_source = trailingslashit( $remote_source ) . $this->base_slug . '/';
		$wp_filesystem->move( $source, $new_source );
		return $new_source;
	}

	/**
	 * Fetch remote data with Release -> Tag fallback
	 */
	public function get_remote_data( $force = false ) {
		$cached = get_transient( 'ke_github_update_data' );
		if ( false !== $cached && ! $force ) return $cached;

		$remote = new stdClass();
		$remote->version = '0.0.0';
		$remote->changelog = 'Check GitHub for release notes.';
		$remote->package = '';
		$remote->last_updated = '';

		// 1. Try Releases
		$release_url = "https://api.github.com/repos/{$this->github_repo}/releases/latest";
		$response = $this->api_get( $release_url );

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $data->tag_name ) ) {
				$remote->version = $data->tag_name;
				$remote->changelog = $data->body;
				$remote->last_updated = $data->published_at;
				$remote->package = $this->get_asset_url( $data );
				
				set_transient( 'ke_github_update_data', $remote, HOUR_IN_SECONDS * 6 );
				set_transient( 'ke_last_check_time', date_i18n( get_option('date_format') . ' H:i:s' ), DAY_IN_SECONDS );
				return $remote;
			}
		}

		// 2. Fallback to Tags
		$tags_url = "https://api.github.com/repos/{$this->github_repo}/tags";
		$response = $this->api_get( $tags_url );

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $data ) && is_array( $data ) ) {
				$latest_tag = $data[0];
				$remote->version = $latest_tag->name;
				$remote->package = $latest_tag->zipball_url;
				
				set_transient( 'ke_github_update_data', $remote, HOUR_IN_SECONDS * 6 );
				set_transient( 'ke_last_check_time', date_i18n( get_option('date_format') . ' H:i:s' ), DAY_IN_SECONDS );
				return $remote;
			}
		}

		return false;
	}

	private function api_get( $url ) {
		$args = array(
			'timeout' => 15,
			'headers' => array(
				'Accept'     => 'application/vnd.github.v3+json',
				'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ),
			),
		);
		if ( ! empty( $this->access_token ) ) {
			$args['headers']['Authorization'] = 'token ' . $this->access_token;
		}
		return wp_remote_get( $url, $args );
	}

	private function get_asset_url( $data ) {
		if ( ! empty( $data->assets ) ) {
			foreach ( $data->assets as $asset ) {
				if ( false !== strpos( $asset->name, $this->base_slug ) && strpos( $asset->name, '.zip' ) !== false ) {
					return $asset->browser_download_url;
				}
			}
		}
		return $data->zipball_url;
	}
}

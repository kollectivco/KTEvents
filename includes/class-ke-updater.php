<?php
/**
 * Kontentainment Events GitHub Updater
 * Simple logic to check for updates from GitHub
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Updater {

	private $plugin_slug;
	private $plugin_file;
	private $github_url;
	private $github_api_url;

	public function __construct( $plugin_file, $github_url ) {
		$this->plugin_file = $plugin_file;
		$this->plugin_slug = plugin_basename( $plugin_file );
		$this->github_url  = $github_url;
		
		// Convert URL to API URL
		// Example: https://github.com/kollectivco/KTEvents -> https://api.github.com/repos/kollectivco/KTEvents/releases/latest
		$path = trim( wp_parse_url( $github_url, PHP_URL_PATH ), '/' );
		$this->github_api_url = "https://api.github.com/repos/{$path}/releases/latest";

		add_filter( 'site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
	}

	/**
	 * Check if a newer version is available on GitHub
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote = $this->get_remote_data();
		if ( ! $remote || is_wp_error( $remote ) ) {
			return $transient;
		}

		// Check if the remote version is newer
		$current_version = KE_PLUGIN_VERSION;
		$new_version     = ltrim( $remote->tag_name, 'v' );

		if ( version_compare( $current_version, $new_version, '<' ) ) {
			$res = new stdClass();
			$res->slug = 'kontentainment-events';
			$res->plugin = $this->plugin_slug;
			$res->new_version = $new_version;
			$res->tested = get_bloginfo( 'version' );
			$res->package = $remote->zipball_url;
			$res->url = $this->github_url;

			$transient->response[ $this->plugin_slug ] = $res;
		}

		return $transient;
	}

	/**
	 * Provide plugin information for the "View Details" modal
	 */
	public function plugin_info( $res, $action, $args ) {
		if ( 'plugin_information' !== $action || $args->slug !== 'kontentainment-events' ) {
			return $res;
		}

		$remote = $this->get_remote_data();
		if ( ! $remote || is_wp_error( $remote ) ) {
			return $res;
		}

		$res = new stdClass();
		$res->name = 'Kontentainment Events';
		$res->slug = 'kontentainment-events';
		$res->version = ltrim( $remote->tag_name, 'v' );
		$res->author = 'Antigravity';
		$res->homepage = $this->github_url;
		$res->download_link = $remote->zipball_url;
		$res->sections = array(
			'description' => $remote->body ?: 'A standalone editorial events directory for magazine websites.',
			'changelog'   => 'See GitHub for details.',
		);

		return $res;
	}

	/**
	 * Fetch remote data from GitHub API
	 */
	private function get_remote_data() {
		$remote = get_transient( 'ke_github_update_data' );
		if ( false !== $remote ) {
			// return $remote;
		}

		$response = wp_remote_get( $this->github_api_url, array(
			'headers' => array(
				'Accept' => 'application/vnd.github.v3+json',
			),
		) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$remote = json_decode( wp_remote_retrieve_body( $response ) );
		set_transient( 'ke_github_update_data', $remote, HOUR_IN_SECONDS );

		return $remote;
	}
}

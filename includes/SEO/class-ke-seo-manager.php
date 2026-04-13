<?php
/**
 * Kontentainment Events SEO Manager
 * Orchestrates SEO features and handles compatibility with major SEO plugins.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_SEO_Manager {

	protected static $instance = null;

	/**
	 * Return an instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// If a major SEO plugin is active, we completely back off to avoid duplication/confusion
		if ( self::is_seo_plugin_active() ) {
			return;
		}

		$this->includes();
		$this->init_submodules();
		add_action( 'init', array( $this, 'register_sitemaps' ) );
	}

	/**
	 * Register native WP sitemaps for events and venues
	 */
	public function register_sitemaps() {
		if ( function_exists( 'wp_register_sitemap_provider' ) ) {
			// WordPress 5.5+ native sitemaps are enabled by default for public post types.
		}
	}

	/**
	 * Include required SEO sub-modules
	 */
	private function includes() {
		require_once KE_PLUGIN_DIR . 'includes/SEO/class-ke-seo-fields.php';
		require_once KE_PLUGIN_DIR . 'includes/SEO/class-ke-schema-manager.php';
		require_once KE_PLUGIN_DIR . 'includes/SEO/class-ke-seo-frontend.php';
	}

	/**
	 * Initialize sub-modules
	 */
	private function init_submodules() {
		KE_SEO_Fields::get_instance();
		KE_Schema_Manager::get_instance();
		KE_SEO_Frontend::get_instance();
	}

	/**
	 * Check if any major SEO plugin is active
	 */
	public static function is_seo_plugin_active() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		// Also check for network active plugins
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
		}

		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',          // Yoast SEO
			'seo-by-rank-math/rank-math.php',    // Rank Math
			'all-in-one-seo-pack/all_in_one_seo_pack.php', // AIOSEO
		);

		foreach ( $seo_plugins as $plugin ) {
			if ( in_array( $plugin, $active_plugins ) ) {
				return true;
			}
		}

		// Fallback detection using classes
		if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || class_exists( 'AIOSEO_Base_Plugin' ) ) {
			return true;
		}

		return false;
	}
}

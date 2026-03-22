<?php
/**
 * Plugin Name: Kontentainment Events
 * Plugin URI:  https://github.com/kollectivco/KTEvents
 * Description: A professional editorial events directory for magazine websites.
 * Version:     1.3.3
 * Author:      Kollectiv
 * Author URI:  https://github.com/kollectivco
 * Text Domain: kontentainment-events
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/kollectivco/KTEvents
 * Primary Branch: main
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define( 'KE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'KE_PLUGIN_VERSION', '1.3.3' );

/**
 * Main Kontentainment Events Class
 */
class KE_Events {

	/**
	 * Instance of this class.
	 *
	 * @var KE_Events
	 */
	protected static $instance = null;

	/**
	 * Return an instance of this class.
	 *
	 * @return KE_Events
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
		
		// Initialize GitHub Updater
		new KE_Updater( __FILE__, 'https://github.com/kollectivco/KTEvents' );
		
		// Phase 6: Lifecycle & Schema
		KE_Upgrades::get_instance();
		KE_Schema::get_instance();
		KE_Admin_Tools::get_instance();
	}

	/**
	 * Include required files
	 */
	private function includes() {
		require_once KE_PLUGIN_DIR . 'includes/helpers.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-updater.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-elementor-descriptions.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-unique-posts.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-sidebars.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-taxonomies.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-post-types.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-meta-boxes.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-save.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-admin.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-templates.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-ajax.php';
		
		// Phase 3 & 4
		require_once KE_PLUGIN_DIR . 'includes/class-ke-scraper.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-parser-interface.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-parser-generic.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-parser-scenenow.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-parser-cairojazzclub.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-parser-registry.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-duplicates.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-logs.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-settings.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-import-url.php';

		// Phase 6 Production Hardening
		require_once KE_PLUGIN_DIR . 'includes/class-ke-cache.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-query.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-egypt-locations.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-location-matcher.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-schema.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-admin-tools.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-diagnostics.php';
		require_once KE_PLUGIN_DIR . 'includes/class-ke-upgrades.php';

		// Elementor integration is now deferred to init hook for reliability
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		
		// Version Check & Seeding
		add_action( 'admin_init', array( $this, 'check_version' ) );

		// Delayed Elementor Load
		add_action( 'init', array( $this, 'init_elementor' ) );
		
		// Cache Invalidation Hook
		add_action( 'save_post', array( KE_Cache::get_instance(), 'invalidate_post_cache' ) );
	}

	/**
	 * Check version for potential seeding/upgrades
	 */
	public function check_version() {
		$installed_version = get_option( 'ke_plugin_version' );
		if ( $installed_version !== KE_PLUGIN_VERSION ) {
			// Ensure Taxonomies are registered before seeding
			KE_Taxonomies::get_instance()->register();
			KE_Post_Types::get_instance()->register();
			
			KE_Egypt_Locations::seed_categories();
			KE_Egypt_Locations::seed_locations();
			
			update_option( 'ke_plugin_version', KE_PLUGIN_VERSION );
		}
	}

	/**
	 * Safe Elementor Loader
	 */
	public function init_elementor() {
		if ( did_action( 'elementor/loaded' ) ) {
			require_once KE_PLUGIN_DIR . 'includes/class-ke-elementor.php';
			KE_Elementor::get_instance();
		}
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		// Only load if relevant template or widget is active
		wp_enqueue_style( 'ke-frontend', KE_PLUGIN_URL . 'assets/css/ke-frontend.css', array(), KE_PLUGIN_VERSION );
		wp_enqueue_script( 'ke-frontend', KE_PLUGIN_URL . 'assets/js/ke-frontend.js', array( 'jquery' ), KE_PLUGIN_VERSION, true );
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on KE admin screens
		if ( strpos( $hook, 'ke-' ) !== false || strpos( $hook, 'event' ) !== false || strpos( $hook, 'venue' ) !== false ) {
			wp_enqueue_style( 'ke-admin', KE_PLUGIN_URL . 'assets/css/ke-admin.css', array(), KE_PLUGIN_VERSION );
			wp_enqueue_script( 'ke-admin', KE_PLUGIN_URL . 'assets/js/ke-admin.js', array( 'jquery' ), KE_PLUGIN_VERSION, true );
			
			// Inject Egypt Location Data
			wp_add_inline_script( 'ke-admin', 'const keEgyptData = ' . json_encode( KE_Egypt_Locations::get_locations_bridge() ) . ';', 'before' );
		}
	}

	/**
	 * Activation hook
	 */
	public function activate() {
		KE_Taxonomies::get_instance()->register();
		KE_Post_Types::get_instance()->register();
		
		// Seed Egypt Data
		KE_Egypt_Locations::seed_categories();
		KE_Egypt_Locations::seed_locations();
		
		flush_rewrite_rules();

		// Set initial DB version
		add_option( 'ke_db_version', KE_PLUGIN_VERSION );
	}
}

// Initialize the plugin
function KE_Init() {
	return KE_Events::get_instance();
}
KE_Init();

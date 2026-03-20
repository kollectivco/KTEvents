<?php
/**
 * Kontentainment Events Elementor Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Elementor {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Register widgets
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		// Register widget categories
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_categories' ) );
	}

	/**
	 * Register KE Category
	 */
	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'ke-events',
			array(
				'title' => 'KE Events',
				'icon'  => 'fa fa-calendar',
			)
		);
	}

	/**
	 * Register Widgets
	 */
	public function register_widgets( $widgets_manager ) {
		// Base / Helpers
		require_once KE_PLUGIN_DIR . 'includes/class-ke-elementor-options.php';
		require_once KE_PLUGIN_DIR . 'elementor/class-ke-widget-base.php';

		require_once KE_PLUGIN_DIR . 'elementor/widgets/class-ke-widget-events-grid.php';
		require_once KE_PLUGIN_DIR . 'elementor/widgets/class-ke-widget-featured-events.php';
		require_once KE_PLUGIN_DIR . 'elementor/widgets/class-ke-widget-upcoming-events.php';
		require_once KE_PLUGIN_DIR . 'elementor/widgets/class-ke-widget-venues-grid.php';
		require_once KE_PLUGIN_DIR . 'elementor/widgets/class-ke-widget-venue-events.php';
		require_once KE_PLUGIN_DIR . 'elementor/widgets/class-ke-widget-events-by-category.php';

		$widgets_manager->register( new KE_Widget_Events_Grid() );
		$widgets_manager->register( new KE_Widget_Featured_Events() );
		$widgets_manager->register( new KE_Widget_Upcoming_Events() );
		$widgets_manager->register( new KE_Widget_Venues_Grid() );
		$widgets_manager->register( new KE_Widget_Venue_Events() );
		$widgets_manager->register( new KE_Widget_Events_By_Category() );
	}
}
// Initialization happens in main plugin file if Elementor exists.

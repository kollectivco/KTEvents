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
		// Enqueue Swiper when Elementor frontend scripts load
		add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'enqueue_swiper' ) );
	}

	/**
	 * Enqueue Swiper (reuse Elementor's bundled version)
	 */
	public function enqueue_swiper() {
		// Elementor bundles Swiper. We just need to make sure the handle is enqueued.
		if ( wp_script_is( 'swiper', 'registered' ) ) {
			wp_enqueue_script( 'swiper' );
		}
		if ( wp_style_is( 'swiper', 'registered' ) ) {
			wp_enqueue_style( 'swiper' );
		}
	}

	/**
	 * Register KE Category
	 */
	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'ke-events',
			array(
				'title' => 'KT Events',
				'icon'  => 'eicon-calendar',
			),
			1 // High priority if supported
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
		require_once KE_PLUGIN_DIR . 'elementor/widgets/class-ke-widget-events-grid-carousel.php';
		require_once KE_PLUGIN_DIR . 'elementor/widgets/class-ke-widget-events-list.php';
		require_once KE_PLUGIN_DIR . 'elementor/widgets/class-ke-widget-featured-events.php';
		require_once KE_PLUGIN_DIR . 'elementor/widgets/class-ke-widget-upcoming-events.php';
		require_once KE_PLUGIN_DIR . 'elementor/widgets/class-ke-widget-events-by-category.php';

		$widgets_manager->register( new KE_Widget_Events_Grid() );
		$widgets_manager->register( new KE_Widget_Events_Grid_Carousel() );
		$widgets_manager->register( new KE_Widget_Events_List() );
		$widgets_manager->register( new KE_Widget_Featured_Events() );
		$widgets_manager->register( new KE_Widget_Upcoming_Events() );
		$widgets_manager->register( new KE_Widget_Events_By_Category() );
	}
}
// Initialization happens in main plugin file if Elementor exists.

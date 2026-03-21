<?php
/**
 * Kontentainment Events Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Sidebars {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
	}

	/**
	 * Register sidebars
	 */
	public function register_sidebars() {
		register_sidebar( array(
			'name'          => 'Events Sidebar',
			'id'            => 'ke-events-sidebar',
			'description'   => 'Dedicated sidebar for event pages.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}
}

KE_Sidebars::get_instance();

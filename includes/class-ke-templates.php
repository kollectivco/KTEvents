<?php
/**
 * Kontentainment Events Template Loader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Templates {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'template_include', array( $this, 'template_loader' ) );
	}

	/**
	 * Template Loader
	 */
	public function template_loader( $template ) {
		$post_type = get_query_var( 'post_type' );

		if ( is_post_type_archive( 'event' ) || is_tax( array( 'event_category', 'event_city', 'event_area' ) ) ) {
			$file = 'archive-event.php';
			$find[] = $file;
			$find[] = 'kontentainment-events/' . $file;
		} elseif ( is_singular( 'event' ) ) {
			$file = 'single-event.php';
			$find[] = $file;
			$find[] = 'kontentainment-events/' . $file;
		} elseif ( is_post_type_archive( 'venue' ) ) {
			$file = 'archive-venue.php';
			$find[] = $file;
			$find[] = 'kontentainment-events/' . $file;
		} elseif ( is_singular( 'venue' ) ) {
			$file = 'single-venue.php';
			$find[] = $file;
			$find[] = 'kontentainment-events/' . $file;
		}

		if ( isset( $file ) ) {
			$template = locate_template( array_reverse( $find ) );
			if ( ! $template ) {
				$template = KE_PLUGIN_DIR . 'templates/' . $file;
			}
		}

		return $template;
	}
}
KE_Templates::get_instance();

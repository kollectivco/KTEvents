<?php
/**
 * Kontentainment Events AJAX Handlers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_AJAX {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// AJAX for filtering
		add_action( 'wp_ajax_ke_filter_archive', array( $this, 'filter_archive' ) );
		add_action( 'wp_ajax_nopriv_ke_filter_archive', array( $this, 'filter_archive' ) );

		// Localize script for AJAX URL and nonces
		add_action( 'wp_enqueue_scripts', array( $this, 'localize_ajax' ), 20 );
	}

	/**
	 * Localize AJAX URL and Nonce
	 */
	public function localize_ajax() {
		wp_localize_script( 'ke-frontend', 'ke_ajax_obj', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'ke_ajax_nonce' ),
		) );
	}

	/**
	 * Handle Archive Filtering using AJAX
	 */
	public function filter_archive() {
		check_ajax_referer( 'ke_ajax_nonce', 'nonce' );

		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'event';
		$is_load_more = isset( $_GET['is_load_more'] ) && '1' === $_GET['is_load_more'];

		if ( 'venue' === $post_type ) {
			$query = KE_Query::get_instance()->get_venues();
			$html = KE_Query::get_instance()->render_venues_loop( $query );
		} else {
			$query = KE_Query::get_instance()->get_events();
			$html = KE_Query::get_instance()->render_events_loop( $query );
		}

		wp_send_json_success( array(
			'html'         => $html,
			'found_posts'  => $query->found_posts,
			'max_num_pages' => $query->max_num_pages,
			'current_page' => $query->query_vars['paged'],
		) );
	}
}
KE_AJAX::get_instance();

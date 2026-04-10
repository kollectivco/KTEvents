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

		// AJAX for single page related loading
		add_action( 'wp_ajax_ke_load_related', array( $this, 'load_related_events' ) );
		add_action( 'wp_ajax_nopriv_ke_load_related', array( $this, 'load_related_events' ) );

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

	/**
	 * Lazy load related events for single page
	 */
	public function load_related_events() {
		check_ajax_referer( 'ke_ajax_nonce', 'nonce' );

		$type = $_GET['type'] ?? '';
		$exclude = isset($_GET['exclude']) ? [intval($_GET['exclude'])] : [];
		$html = '';
		$title = '';

		switch ( $type ) {
			case 'venue':
				$venue_id = intval($_GET['venue_id']);
				if ( ! $venue_id ) wp_send_json_error();
				$title = 'More at this Venue';
				$args = [ 'posts_per_page' => 3, 'post__not_in' => $exclude, 'venue_id' => $venue_id, 'no_found_rows' => true ];
				break;
			case 'category':
				$cat_id = intval($_GET['cat_id']);
				if ( ! $cat_id ) wp_send_json_error();
				$title = 'More in this Category';
				$args = [ 'posts_per_page' => 3, 'post__not_in' => $exclude, 'ke_category' => $cat_id, 'no_found_rows' => true ];
				break;
			case 'recommended':
				$title = 'Recommended Events';
				$args = [ 'posts_per_page' => 3, 'post__not_in' => $exclude, 'ke_sort' => 'date_desc', 'no_found_rows' => true ];
				break;
			default:
				wp_send_json_error();
		}

		$query = KE_Query::get_instance()->get_events( $args );
		
		if ( $query->have_posts() ) {
			ob_start();
			?>
			<div class="ke-supporting-block">
				<h2 class="ke-foxiz-section-title"><?php echo esc_html($title); ?></h2>
				<?php echo KE_Query::get_instance()->render_events_loop( $query, [ 'columns' => 3 ] ); ?>
			</div>
			<?php
			$html = ob_get_clean();
		}

		wp_send_json_success( [ 'html' => $html ] );
	}
}
KE_AJAX::get_instance();

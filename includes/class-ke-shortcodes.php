<?php
/**
 * Kontentainment Events Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Shortcodes {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'ke_events_archive', array( $this, 'render_events_archive' ) );
	}

	/**
	 * Render Events Archive Shortcode
	 */
	public function render_events_archive( $atts ) {
		$atts = shortcode_atts( array(
			'columns' => 3,
			'limit'   => 12,
			'preset'  => 'classic'
		), $atts, 'ke_events_archive' );

		ob_start();
		?>
		<div class="ke-isolated-wrap ke-events-directory">
			<div class="ke-page-header">
				<h1 class="ke-page-title">Events</h1>
				<div class="ke-title-line"></div>
			</div>

			<div class="ke-discovery-filter">
				<?php $this->render_discovery_filter(); ?>
			</div>

			<div id="ke-archive-loop" class="ke-archive-loop-container">
				<?php
				$query = KE_Query::get_instance()->get_events( array(
					'posts_per_page' => $atts['limit'],
					'query_mode'     => 'standard'
				) );
				
				// Important: We pass a flag to tell the renderer NOT to double-wrap in .ke-isolated-wrap
				echo KE_Query::get_instance()->render_events_loop( $query, array(
					'columns'       => $atts['columns'],
					'layout_preset' => $atts['preset'],
					'pagination'    => 'load_more',
					'skip_isolated_wrap' => true,
					'extra_classes' => 'ke-mobile-2-up',
					'columns_mobile' => 2
				) );
				?>
			</div>

			<div id="ke-loading-overlay" class="ke-loading-overlay">
				<div class="ke-spinner"></div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	private function render_discovery_filter() {
		?>
		<form id="ke-filter-form" method="get" action="">
			<!-- Layer 1: Time/Mode Discovery Tabs -->
			<div class="ke-filter-layer ke-layer-tabs">
				<div class="ke-filter-label">Quick Choice</div>
				<div class="ke-tabs-scroll-wrapper">
					<div class="ke-nav-scroll">
						<button type="button" class="ke-nav-item active" data-range="">Upcoming</button>
						<button type="button" class="ke-nav-item" data-meta="ke_recommended">Recommended</button>
						<button type="button" class="ke-nav-item" data-range="today">Today</button>
						<button type="button" class="ke-nav-item" data-range="weekend">Weekend</button>
						<button type="button" class="ke-nav-item" data-range="week">This Week</button>
					</div>
					<button type="button" class="ke-refine-toggle-btn" id="ke-toggle-advanced">
						<span>Get Specific</span>
					</button>
				</div>
			</div>

			<!-- Layer 2: Location Discovery Pills -->
			<div class="ke-filter-layer ke-layer-locations">
				<div class="ke-filter-label">Quick Location</div>
				<div class="ke-pills-scroll">
					<button type="button" class="ke-pill-item active" data-city="">All Events</button>
					<?php 
					$cities = get_terms( array( 'taxonomy' => 'event_city', 'hide_empty' => true ) );
					if ( ! is_wp_error( $cities ) ) :
						foreach ( $cities as $city ) : ?>
							<button type="button" class="ke-pill-item" data-city="<?php echo esc_attr($city->slug); ?>">
								<?php echo esc_html($city->name); ?>
							</button>
						<?php endforeach;
					endif; ?>
				</div>
			</div>

			<input type="hidden" name="ke_range" id="ke-input-range" value="">
			<input type="hidden" name="ke_recommended" id="ke-input-recommended" value="">
			<input type="hidden" name="ke_city" id="ke-input-city" value="">

			<div id="ke-advanced-filters" class="ke-refinement-area" style="display: none;">
				<div class="ke-refinement-grid">
					<div class="ke-refine-node ke-node-search">
						<label>Topic / Keywords</label>
						<input type="text" name="ke_search" placeholder="Search events..." value="">
					</div>
					<div class="ke-refine-node">
						<label>Sort By</label>
						<select name="ke_sort" class="ke-styled-select">
							<option value="upcoming">Upcoming First</option>
							<option value="latest">Just Added</option>
						</select>
					</div>
					<div class="ke-refine-node ke-node-submit">
						<button type="submit" class="ke-apply-btn button-primary">Apply Filters</button>
					</div>
				</div>
			</div>
		</form>
		<?php
	}
}

KE_Shortcodes::get_instance();

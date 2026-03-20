<?php
/**
 * Kontentainment Events Query Logic - Phase 5.2 Updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Query {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Get events with expanded filters and unique post support
	 */
	public function get_events( $overrides = array() ) {
		$args = $this->get_filtered_events_args( $overrides );
		$query = new WP_Query( $args );

		// Track IDs for Unique Posts
		if ( (isset($overrides['unique_post']) && 'yes' === $overrides['unique_post']) || isset($overrides['is_widget']) ) {
			if ( $query->have_posts() ) {
				foreach ( $query->posts as $p ) {
					KE_Unique_Posts::get_instance()->track( $p->ID );
				}
			}
		}

		return $query;
	}

	/**
	 * Build Event Query Args
	 */
	public function get_filtered_events_args( $overrides = array() ) {
		$input = empty( $overrides ) || (isset( $overrides['paged'] ) && !isset($overrides['is_widget'])) ? $_GET : $overrides;
		
		$paged = isset( $input['ke_paged'] ) ? intval( $input['ke_paged'] ) : ( isset( $overrides['paged'] ) ? $overrides['paged'] : 1 );
		
		$defaults = array(
			'post_type'      => 'event',
			'posts_per_page' => 12,
			'paged'          => $paged,
			'post_status'    => 'publish',
			'meta_query'     => array( 'relation' => 'AND' ),
			'tax_query'      => array( 'relation' => 'AND' ),
			'offset'         => 0,
			'post__in'       => array(),
			'post__not_in'   => array(),
		);

		$args = wp_parse_args( $overrides, $defaults );

		// 1. Query Mode Logic
		$mode = $input['query_mode'] ?? 'standard';
		
		if ( 'current' === $mode ) {
			// Dynamic Context
			if ( is_single() && get_post_type() === 'venue' ) {
				$args['meta_query'][] = array( 'key' => 'KE_event_venue_id', 'value' => get_the_ID(), 'compare' => '=' );
			} elseif ( is_tax() ) {
				$queried_object = get_queried_object();
				$args['tax_query'][] = array( 'taxonomy' => $queried_object->taxonomy, 'field' => 'term_id', 'terms' => $queried_object->term_id );
			}
		} elseif ( 'standard' === $mode ) {
			// Manual Selections
			if ( ! empty( $input['include_ids'] ) ) $args['post__in'] = array_map( 'intval', explode( ',', $input['include_ids'] ) );
			if ( ! empty( $input['exclude_ids'] ) ) $args['post__not_in'] = array_map( 'intval', explode( ',', $input['exclude_ids'] ) );
			if ( ! empty( $input['offset'] ) ) $args['offset'] = intval( $input['offset'] );
		}

		// 2. Unique Post Filtering
		if ( isset($input['unique_post']) && 'yes' === $input['unique_post'] ) {
			$tracked_ids = KE_Unique_Posts::get_instance()->get_ids();
			if ( ! empty( $tracked_ids ) ) {
				$args['post__not_in'] = array_merge( (array) $args['post__not_in'], $tracked_ids );
			}
		}

		// Handle Offset + Pagination conflict
		if ( $args['offset'] > 0 && $args['paged'] > 1 ) {
			$args['offset'] = intval( $args['offset'] ) + ( ( $args['paged'] - 1 ) * $args['posts_per_page'] );
		}

		// Taxonomies
		$tax_map = array( 'event_category' => 'event_category', 'event_city' => 'event_city', 'event_area' => 'event_area' );
		foreach ( $tax_map as $key => $taxonomy ) {
			$val = $input[ $key ] ?? '';
			if ( ! empty( $val ) ) {
				$args['tax_query'][] = array( 'taxonomy' => $taxonomy, 'field' => is_numeric($val) ? 'term_id' : 'slug', 'terms' => (array) $val );
			}
		}

		// Event Specific Metadata Filters
		$status = $input['status'] ?? '';
		if ( ! empty( $status ) ) {
			if ( 'all_exc_past' === $status ) {
				$args['meta_query'][] = array( 'key' => 'KE_event_status', 'value' => 'past', 'compare' => '!=' );
			} else {
				$args['meta_query'][] = array( 'key' => 'KE_event_status', 'value' => $status, 'compare' => '=' );
			}
		}

		$meta_toggles = array( 'featured' => 'KE_event_featured', 'editor_pick' => 'KE_event_editor_pick' );
		foreach ( $meta_toggles as $key => $meta_key ) {
			$val = $input[ $key ] ?? '';
			if ( '1' === (string)$val || true === $val || 'yes' === $val ) {
				$args['meta_query'][] = array( 'key' => $meta_key, 'value' => '1', 'compare' => '=' );
			}
		}

		// Venue filter
		$venue_id = $input['venue_id'] ?? '';
		if ( ! empty( $venue_id ) ) {
			$args['meta_query'][] = array( 'key' => 'KE_event_venue_id', 'value' => $venue_id, 'compare' => '=' );
		}

		// Sorting
		$sort = $input['orderby_custom'] ?? 'upcoming';
		switch ( $sort ) {
			case 'latest': $args['orderby'] = 'date'; $args['order'] = 'DESC'; break;
			case 'title_asc': $args['orderby'] = 'title'; $args['order'] = 'ASC'; break;
			case 'title_desc': $args['orderby'] = 'title'; $args['order'] = 'DESC'; break;
			case 'date_asc': $args['meta_key'] = 'KE_event_date'; $args['orderby'] = 'meta_value'; $args['order'] = 'ASC'; break;
			case 'date_desc': $args['meta_key'] = 'KE_event_date'; $args['orderby'] = 'meta_value'; $args['order'] = 'DESC'; break;
			case 'rand': $args['orderby'] = 'rand'; break;
			default:
				$args['meta_query']['status_clause'] = array( 'key' => 'KE_event_status', 'compare' => 'EXISTS' );
				$args['meta_query']['date_clause'] = array( 'key' => 'KE_event_date', 'compare' => 'EXISTS', 'type' => 'DATE' );
				$args['orderby'] = array( 'date_clause' => 'ASC' );
				break;
		}

		return $args;
	}

	/**
	 * Render Events Loop with Presets and horizontal scroll detection
	 */
	public function render_events_loop( $query, $settings = array() ) {
		$display = wp_parse_args( $settings, array(
			'layout_preset' => 'classic',
			'columns'       => 3,
			'columns_tablet' => 2,
			'columns_mobile' => 1,
		) );

		ob_start();
		if ( $query->have_posts() ) {
			$classes = array( 'ke-loop-wrapper', 'ke-preset-' . esc_attr($display['layout_preset']), 'ke-columns-' . esc_attr($display['columns']) );
			if ( isset($display['horizontal_scroll']) && 'yes' === $display['horizontal_scroll'] ) $classes[] = 'ke-horizontal-scroll';
			if ( isset($display['is_boxed']) && 'yes' === $display['is_boxed'] ) $classes[] = 'ke-boxed';
			
			// Foxiz dark mode classes
			if ( isset($display['color_scheme']) && 'dark' === $display['color_scheme'] ) $classes[] = 'ke-scheme-dark';

			echo '<div class="' . implode(' ', $classes) . '">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$preset = $display['layout_preset'];
				$partial = KE_PLUGIN_DIR . 'templates/partials/widgets/event-card-' . $preset . '.php';
				if ( file_exists( $partial ) ) include $partial;
				else include KE_PLUGIN_DIR . 'templates/partials/loop-event-card.php';
			}
			echo '</div>';

			// Render Pagination
			if ( ! empty( $display['pagination'] ) ) $this->render_pagination( $query, $display );

			wp_reset_postdata();
		} else {
			include KE_PLUGIN_DIR . 'templates/partials/archive-empty-state.php';
		}
		return ob_get_clean();
	}

	/**
	 * Render Pagination
	 */
	private function render_pagination( $query, $settings ) {
		if ( $query->max_num_pages <= 1 ) return;
		$type = $settings['pagination'];
		$style = $settings['pagination_style'] ?? 'standard';
		$label = $settings['pagination_label'] ?? 'LOAD MORE';

		echo '<div class="ke-pagination-wrapper ke-pagination-' . esc_attr($type) . ' ke-pagination-style-' . esc_attr($style) . '">';
		if ( 'load_more' === $type ) {
			echo '<a href="#" class="ke-load-more-btn" data-max-pages="' . $query->max_num_pages . '">' . esc_html($label) . '</a>';
		}
		echo '</div>';
	}
}
KE_Query::get_instance();

<?php
/**
 * Kontentainment Events Query Logic - Phase 6 Optimized
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

		// 1. Check Cache
		$cache_key   = md_hash( json_encode( $args ) );
		$cached_query = KE_Cache::get_instance()->get( 'event_q_' . $cache_key );
		if ( $cached_query ) return $cached_query;

		// 2. Fetch Query
		$query = new WP_Query( $args );

		// 3. Set Cache
		KE_Cache::get_instance()->set( 'event_q_' . $cache_key, $query );

		// Track IDs for Unique Posts (on real query)
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
	 * Get filtered venues with caching
	 */
	public function get_venues( $overrides = array() ) {
		$args = $this->get_filtered_venues_args( $overrides );
		
		$cache_key = md_hash( json_encode( $args ) );
		$cached = KE_Cache::get_instance()->get( 'venue_q_' . $cache_key );
		if ( $cached ) return $cached;

		$query = new WP_Query( $args );
		KE_Cache::get_instance()->set( 'venue_q_' . $cache_key, $query );
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
			'ignore_sticky_posts' => true, // Production optimization
		);

		$args = wp_parse_args( $overrides, $defaults );

		$mode = $input['query_mode'] ?? 'standard';
		
		// 1. Mandatory Upcoming Filter (Global Frontend Rule)
		// We hide past events by default unless explicitly allowed (e.g. in some specific archive mode if ever needed)
		if ( ! ( isset($overrides['show_past']) && $overrides['show_past'] ) ) {
			$args['meta_query'][] = array(
				'key'     => 'KE_event_date',
				'value'   => current_time( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE'
			);
		}

		if ( 'current' === $mode ) {
			if ( is_single() && get_post_type() === 'venue' ) {
				$args['meta_query'][] = array( 'key' => 'KE_event_venue_id', 'value' => get_the_ID(), 'compare' => '=' );
			} elseif ( is_tax() ) {
				$queried_object = get_queried_object();
				$args['tax_query'][] = array( 'taxonomy' => $queried_object->taxonomy, 'field' => 'term_id', 'terms' => $queried_object->term_id );
			}
		} elseif ( 'standard' === $mode ) {
			if ( ! empty( $input['include_ids'] ) ) $args['post__in'] = array_map( 'intval', explode( ',', $input['include_ids'] ) );
			if ( ! empty( $input['exclude_ids'] ) ) $args['post__not_in'] = array_map( 'intval', explode( ',', $input['exclude_ids'] ) );
			if ( ! empty( $input['offset'] ) ) $args['offset'] = intval( $input['offset'] );
		}

		if ( isset($input['unique_post']) && 'yes' === $input['unique_post'] ) {
			$tracked_ids = KE_Unique_Posts::get_instance()->get_ids();
			if ( ! empty( $tracked_ids ) ) $args['post__not_in'] = array_merge( (array) $args['post__not_in'], $tracked_ids );
		}

		if ( $args['offset'] > 0 && $args['paged'] > 1 ) {
			$args['offset'] = intval( $args['offset'] ) + ( ( $args['paged'] - 1 ) * $args['posts_per_page'] );
		}

		$tax_map = array( 
			'ke_category' => 'event_category', 
			'ke_city'     => 'event_city', 
			'ke_gov'      => 'event_governorate',
			'event_category' => 'event_category', // Compatibility
			'event_city'     => 'event_city'      // Compatibility
		);
		foreach ( $tax_map as $key => $taxonomy ) {
			$val = $input[ $key ] ?? '';
			if ( ! empty( $val ) ) {
				$args['tax_query'][] = array( 'taxonomy' => $taxonomy, 'field' => is_numeric($val) ? 'term_id' : 'slug', 'terms' => (array) $val );
			}
		}

		// Quick Dynamic Range Filter
		$range = $input['ke_range'] ?? '';
		if ( ! empty( $range ) ) {
			$start = current_time( 'Y-m-d' );
			$end   = $start;
			
			switch ( $range ) {
				case 'today':
					$end = $start;
					break;
				case 'weekend':
					$end = date( 'Y-m-d', strtotime( 'next Sunday' ) );
					$start = date( 'Y-m-d', strtotime( 'next Friday' ) );
					break;
				case 'week':
					$end = date( 'Y-m-d', strtotime( 'next Sunday' ) );
					break;
			}

			// Redefine the date filter for quick ranges
			// We remove the mandatory generic one if it was added
			foreach ( $args['meta_query'] as $k => $q ) {
				if ( is_array($q) && isset($q['key']) && 'KE_event_date' === $q['key'] ) {
					unset( $args['meta_query'][$k] );
				}
			}

			$args['meta_query'][] = array(
				'key'     => 'KE_event_date',
				'value'   => array( $start, $end ),
				'compare' => 'BETWEEN',
				'type'    => 'DATE'
			);
		}

		$status = $input['status'] ?? '';
		if ( ! empty( $status ) ) {
			if ( 'all_exc_past' === $status ) {
				$args['meta_query'][] = array( 'key' => 'KE_event_status', 'value' => 'past', 'compare' => '!=' );
			} else {
				$args['meta_query'][] = array( 'key' => 'KE_event_status', 'value' => $status, 'compare' => '=' );
			}
		}

		$meta_toggles = array( 
			'featured'      => 'KE_event_featured', 
			'editor_pick'   => 'KE_event_editor_pick',
			'ke_recommended' => 'KE_event_featured' // Map recommended to featured
		);
		foreach ( $meta_toggles as $key => $meta_key ) {
			$val = $input[ $key ] ?? '';
			if ( in_array((string)$val, [ '1', 'yes', 'true' ]) ) {
				$args['meta_query'][] = array( 'key' => $meta_key, 'value' => '1', 'compare' => '=' );
			}
		}

		$venue_id = $input['venue_id'] ?? '';
		if ( ! empty( $venue_id ) ) {
			$args['meta_query'][] = array( 'key' => 'KE_event_venue_id', 'value' => $venue_id, 'compare' => '=' );
		}

		$sort = $input['ke_sort'] ?? $input['orderby_custom'] ?? 'upcoming';
		switch ( $sort ) {
			case 'latest': $args['orderby'] = 'date'; $args['order'] = 'DESC'; break;
			case 'title_asc': $args['orderby'] = 'title'; $args['order'] = 'ASC'; break;
			case 'title_desc': $args['orderby'] = 'title'; $args['order'] = 'DESC'; break;
			case 'date_asc': $args['meta_key'] = 'KE_event_date'; $args['orderby'] = 'meta_value'; $args['order'] = 'ASC'; break;
			case 'date_desc': $args['meta_key'] = 'KE_event_date'; $args['orderby'] = 'meta_value'; $args['order'] = 'DESC'; break;
			case 'rand': $args['orderby'] = 'rand'; break;
			default:
				// If upcoming or default
				$args['meta_key'] = 'KE_event_date';
				$args['orderby']  = 'meta_value'; 
				$args['order']    = 'ASC';
				break;
		}

		return $args;
	}

	/**
	 * Optimized Render Events Loop
	 */
	public function render_events_loop( $query, $settings = array() ) {
		$display = wp_parse_args( $settings, array( 
			'layout_preset'  => 'classic', 
			'columns'        => 3, 
			'columns_tablet' => 2, 
			'columns_mobile' => 1,
			'gap'            => 'medium' 
		) );
		ob_start();
		if ( $query->have_posts() ) {
			$classes = array( 
				'ke-loop-wrapper', 
				'ke-preset-' . esc_attr($display['layout_preset']), 
				'ke-columns-' . esc_attr($display['columns']),
				'ke-columns-tablet-' . esc_attr($display['columns_tablet']),
				'ke-columns-mobile-' . esc_attr($display['columns_mobile'])
			);
			if ( ! empty( $display['gap'] ) ) $classes[] = 'ke-gap-' . esc_attr( $display['gap'] );
			if ( isset($display['horizontal_scroll']) && 'yes' === $display['horizontal_scroll'] ) $classes[] = 'ke-horizontal-scroll';
			if ( isset($display['is_boxed']) && 'yes' === $display['is_boxed'] ) $classes[] = 'ke-boxed';
			if ( isset($display['color_scheme']) && 'dark' === $display['color_scheme'] ) $classes[] = 'ke-scheme-dark';

			$count = 0;
			$max_initial = $display['max_initial'] ?? 0;

			echo '<div class="' . implode(' ', $classes) . '">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$count++;
				$preset = $display['layout_preset'];
				$item_classes = array();
				if ( $max_initial > 0 && $count > $max_initial ) {
					$item_classes[] = 'ke-hidden-item';
				}

				$partial = KE_PLUGIN_DIR . 'templates/partials/widgets/event-card-' . $preset . '.php';
				
				// Standardize item wrapper for hidden state support
				if ( ! empty( $item_classes ) ) echo '<div class="' . implode(' ', $item_classes) . '">';
				
				if ( file_exists( $partial ) ) include $partial;
				else include KE_PLUGIN_DIR . 'templates/partials/loop-event-card.php';
				
				if ( ! empty( $item_classes ) ) echo '</div>';
			}
			echo '</div>';

			if ( $max_initial > 0 && $query->post_count > $max_initial ) {
				echo '<div class="ke-show-more-wrapper">';
				echo '<button type="button" class="ke-show-more-btn" data-target="next">' . esc_html__( 'Show More', 'kontentainment-events' ) . '</button>';
				echo '</div>';
			}

			if ( ! empty( $display['pagination'] ) ) $this->render_pagination( $query, $display );
			wp_reset_postdata();
		} else {
			include KE_PLUGIN_DIR . 'templates/partials/archive-empty-state.php';
		}
		return ob_get_clean();
	}

	private function render_pagination( $query, $settings ) {
		if ( $query->max_num_pages <= 1 ) return;
		$type = $settings['pagination'];
		$style = $settings['pagination_style'] ?? 'standard';
		$label = $settings['pagination_label'] ?? 'LOAD MORE';
		echo '<div class="ke-pagination-wrapper ke-pagination-' . esc_attr($type) . ' ke-pagination-style-' . esc_attr($style) . '">';
		if ( 'load_more' === $type ) echo '<a href="#" class="ke-load-more-btn" data-max-pages="' . $query->max_num_pages . '">' . esc_html($label) . '</a>';
		echo '</div>';
	}

	public function get_filtered_venues_args( $overrides = array() ) {
		$input = empty( $overrides ) || (isset( $overrides['paged'] ) && !isset($overrides['is_widget'])) ? $_GET : $overrides;
		$defaults = array( 'post_type' => 'venue', 'posts_per_page' => 12, 'paged' => $input['ke_paged'] ?? 1, 'post_status' => 'publish', 'ignore_sticky_posts' => true );
		$args = wp_parse_args( $overrides, $defaults );
		if ( ! empty( $input['ke_search'] ) ) $args['s'] = sanitize_text_field( $input['ke_search'] );
		$tax_map = array( 'event_governorate' => 'event_governorate', 'event_city' => 'event_city' );
		foreach ( $tax_map as $key => $taxonomy ) {
			$val = $input[ $key ] ?? '';
			if ( ! empty( $val ) ) $args['tax_query'][] = array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => (array) $val );
		}
		return $args;
	}

	public function render_venues_loop( $query, $settings = array() ) {
		$display = wp_parse_args( $settings, array( 'layout_preset' => 'grid', 'columns' => 3 ) );
		ob_start();
		if ( $query->have_posts() ) {
			echo '<div class="ke-loop-wrapper ke-venues-preset-' . esc_attr($display['layout_preset']) . ' ke-columns-' . esc_attr($display['columns']) . '">';
			while ( $query->have_posts() ) { $query->the_post(); include KE_PLUGIN_DIR . 'templates/partials/loop-venue-card.php'; }
			echo '</div>';
			wp_reset_postdata();
		} else { include KE_PLUGIN_DIR . 'templates/partials/archive-empty-state.php'; }
		return ob_get_clean();
	}
}
KE_Query::get_instance();

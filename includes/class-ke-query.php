<?php
/**
 * Kontentainment Events Query Logic - Phase 6 Optimized
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Query {

	protected static $instance = null;
	private static $page_query_cache = []; // Request-level static cache

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

		// 1. Static Request-level Cache (Absolute fastest)
		$static_key = md5( wp_json_encode( $args ) );
		if ( isset( self::$page_query_cache[ $static_key ] ) ) {
			return self::$page_query_cache[ $static_key ];
		}

		// Performance: Pagination optimization
		if ( ! isset( $args['paged'] ) || $args['posts_per_page'] === -1 ) {
			$args['no_found_rows'] = true;
		}
		
		// Performance: Warm up caches for events themselves
		$args['update_post_meta_cache'] = true;
		$args['update_post_term_cache'] = true;

		// 2. Check Persistent Cache (IDs only)
		$cache_args = $args;
		unset($cache_args['post__not_in']); 
		
		$cache_key    = 'ev_v2_' . md5( wp_json_encode( $cache_args ) );
		$cache_handler = KE_Cache::get_instance();
		$cached_ids   = $cache_handler->get( $cache_key );

		if ( false !== $cached_ids ) {
			// Respect original posts_per_page when fetching from cache
			$limit = isset($args['posts_per_page']) ? intval($args['posts_per_page']) : -1;
			$fetch_ids = ( $limit > 0 ) ? array_slice( (array) $cached_ids, 0, $limit ) : $cached_ids;
			
			$query = new WP_Query( array(
				'post_type' => 'event',
				'post__in'  => ! empty( $fetch_ids ) ? $fetch_ids : array( 0 ),
				'orderby'   => 'post__in',
				'posts_per_page' => -1,
				'no_found_rows' => true,
				'fields' => 'all'
			) );
			self::$page_query_cache[ $static_key ] = $query;
			return $query;
		}

		// 3. Fetch Real Query
		$query = new WP_Query( $args );

		// 4. Set Persistent Cache
		if ( $query->have_posts() ) {
			$ids = wp_list_pluck( $query->posts, 'ID' );
			$cache_handler->set( $cache_key, $ids, HOUR_IN_SECONDS );
		} else {
			$cache_handler->set( $cache_key, array(), HOUR_IN_SECONDS );
		}

		// Unique Post Tracking
		if ( (isset($overrides['unique_post']) && 'yes' === $overrides['unique_post']) || isset($overrides['is_widget']) ) {
			if ( $query->have_posts() ) {
				foreach ( $query->posts as $p ) {
					KE_Unique_Posts::get_instance()->track( $p->ID );
				}
			}
		}

		self::$page_query_cache[ $static_key ] = $query;
		return $query;
	}

	/**
	 * Get filtered venues with caching
	 */
	public function get_venues( $overrides = array() ) {
		$args = $this->get_filtered_venues_args( $overrides );
		
		// Performance Flags
		if ( ! isset( $args['paged'] ) || $args['posts_per_page'] === -1 ) {
			$args['no_found_rows'] = true;
		}
		$args['update_post_meta_cache'] = true;
		$args['update_post_term_cache'] = true;

		$cache_key = md_hash( wp_json_encode( $args ) );
		$cache_handler = KE_Cache::get_instance();
		$cached_ids = $cache_handler->get( 'vn_ids_' . $cache_key );
		
		if ( false !== $cached_ids ) {
			return new WP_Query( array(
				'post_type' => 'venue',
				'post__in'  => ! empty( $cached_ids ) ? $cached_ids : array( 0 ),
				'orderby'   => 'post__in',
				'posts_per_page' => -1,
				'no_found_rows' => true
			) );
		}

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			$ids = wp_list_pluck( $query->posts, 'ID' );
			$cache_handler->set( 'vn_ids_' . $cache_key, $ids, HOUR_IN_SECONDS );
		} else {
			$cache_handler->set( 'vn_ids_' . $cache_key, array(), HOUR_IN_SECONDS );
		}

		return $query;
	}

	/**
	 * Build Event Query Args
	 */
	/**
	 * Get upcoming or past events for a venue
	 */
	public function get_venue_events( $venue_id, $type = 'upcoming', $limit = -1 ) {
		$args = array(
			'venue_id'       => $venue_id,
			'posts_per_page' => $limit,
			'show_past'      => ( 'past' === $type )
		);
		
		if ( 'past' === $type ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'KE_event_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '<',
					'type'    => 'DATE'
				)
			);
			$args['ke_sort'] = 'date_desc';
		} else {
			$args['ke_sort'] = 'date_asc';
		}

		return $this->get_events( $args );
	}

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
			if ( ! empty( $tracked_ids ) ) {
				// Capping to avoid massive SQL queries that "hang"
				$capped_ids = array_slice( (array) $tracked_ids, -50 ); 
				$args['post__not_in'] = array_merge( (array) $args['post__not_in'], $capped_ids );
			}
		}

		if ( $args['offset'] > 0 && $args['paged'] > 1 ) {
			$args['offset'] = intval( $args['offset'] ) + ( ( $args['paged'] - 1 ) * $args['posts_per_page'] );
		}

		$tax_map = array( 
			'ke_category' => 'event_category', 
			'ke_city'     => 'event_city', 
			'ke_gov'      => 'event_governorate',
			'event_category' => 'event_category', // Compatibility
			'event_city'     => 'event_city',      // Compatibility
			'event_area'     => 'event_area'       // Elementor Advanced Filter
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
			case 'rand': 
				$args['orderby'] = 'rand'; 
				// Optimization: random order should never use Found Rows
				$args['no_found_rows'] = true;
				break;
			default:
				// If upcoming or default
				$args['meta_key'] = 'KE_event_date';
				$args['orderby']  = 'meta_value'; 
				$args['order']    = 'ASC';
				break;
		}

		// Prevent N+1 on metadata and taxonomies
		$args['update_post_meta_cache'] = true;
		$args['update_post_term_cache'] = true;

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
			
			$is_carousel = isset($display['carousel']) && 'yes' === $display['carousel'];
			$carousel_uid = uniqid('ke_carousel_');
			
			$classes = array( 
				'ke-isolated-wrap',
				'ke-loop-wrapper', 
				'ke-preset-' . esc_attr($display['layout_preset'])
			);

			if ( ! empty( $display['is_widget'] ) ) {
				$classes[] = 'ke-elementor-widget';
			}

			if ( ! empty( $display['extra_classes'] ) ) {
				if ( is_array( $display['extra_classes'] ) ) {
					$classes = array_merge( $classes, $display['extra_classes'] );
				} else {
					$classes[] = $display['extra_classes'];
				}
			}

			if ( ! empty( $display['image_ratio'] ) ) {
				$classes[] = 'ke-ratio-' . esc_attr( $display['image_ratio'] );
			}
			
			// Prevent grid column CSS leakage when acting as a carousel
			if ( ! $is_carousel ) {
				if ( isset($display['columns']) ) $classes[] = 'ke-columns-' . esc_attr($display['columns']);
				if ( isset($display['columns_tablet']) ) $classes[] = 'ke-columns-tablet-' . esc_attr($display['columns_tablet']);
				if ( isset($display['columns_mobile']) ) $classes[] = 'ke-columns-mobile-' . esc_attr($display['columns_mobile']);
			}
			
			$gap = ! empty( $display['gap'] ) ? $display['gap'] : ( ! empty( $display['gap_preset'] ) ? $display['gap_preset'] : '' );
			if ( ! empty( $gap ) && ! $is_carousel ) $classes[] = 'ke-gap-' . esc_attr( $gap );
			if ( isset($display['horizontal_scroll']) && 'yes' === $display['horizontal_scroll'] ) $classes[] = 'ke-horizontal-scroll';
			if ( isset($display['is_boxed']) && 'yes' === $display['is_boxed'] ) $classes[] = 'ke-boxed';
			if ( isset($display['color_scheme']) && 'dark' === $display['color_scheme'] ) $classes[] = 'ke-scheme-dark';

			$count = 0;
			$max_initial = $display['max_initial'] ?? 0;

			// Carousel Wrapper Open
			if ( $is_carousel ) {
				$classes[] = 'swiper-wrapper'; // Internal wrapper acts as swiper wrapper
				
				$items_mob = isset($display['carousel_items_mobile']) && is_numeric($display['carousel_items_mobile']) ? floatval($display['carousel_items_mobile']) : 1;
				$items_tab = isset($display['carousel_items_tablet']) && is_numeric($display['carousel_items_tablet']) ? floatval($display['carousel_items_tablet']) : 2;
				$items_des = isset($display['carousel_items']) && is_numeric($display['carousel_items']) ? floatval($display['carousel_items']) : 3;
				
				$gap = isset($display['carousel_gap']) && is_numeric($display['carousel_gap']) ? intval($display['carousel_gap']) : 24;
				$speed = isset($display['carousel_speed']) && is_numeric($display['carousel_speed']) ? intval($display['carousel_speed']) : 400;

				$c_settings = array(
					'slidesPerView' => $items_mob,
					'spaceBetween'  => $gap,
					'grabCursor'    => true,
					'watchSlidesProgress' => true,
					'observer'      => true,
					'observeParents'=> true,
					'loop'          => (isset($display['carousel_loop']) && 'yes' === $display['carousel_loop'] && $query->post_count > 1),
					'init'          => true,
					'centeredSlides'=> isset($display['carousel_center']) && 'yes' === $display['carousel_center'],
					'freeMode'      => (isset($display['carousel_free_scroll']) && 'yes' === $display['carousel_free_scroll']) ? array('enabled' => true) : false,
					'speed'         => $speed,
					'breakpoints'   => array(
						768  => array( 'slidesPerView' => $items_tab, 'spaceBetween' => $gap ),
						1024 => array( 'slidesPerView' => $items_des, 'spaceBetween' => $gap ),
					)
				);

				if ( isset($display['carousel_autoplay']) && 'yes' === $display['carousel_autoplay'] && $query->post_count > 1 ) {
					$delay = isset($display['carousel_autoplay_speed']) && is_numeric($display['carousel_autoplay_speed']) ? intval($display['carousel_autoplay_speed']) : 5000;
					$c_settings['autoplay'] = array(
						'delay' => $delay,
						'disableOnInteraction' => false,
						'pauseOnMouseEnter' => isset($display['carousel_pause_hover']) && 'yes' === $display['carousel_pause_hover']
					);
				}
				
				$show_arrows = isset($display['carousel_arrows']) && 'yes' === $display['carousel_arrows'];
				$show_dots   = isset($display['carousel_dots']) && 'yes' === $display['carousel_dots'];
				$hide_arrows_mob = isset($display['carousel_hide_arrows_mobile']) && 'yes' === $display['carousel_hide_arrows_mobile'];
				
				if ( $show_arrows ) {
					$c_settings['navigation'] = array(
						'nextEl' => ".{$carousel_uid}-next",
						'prevEl' => ".{$carousel_uid}-prev"
					);
				}
				if ( $show_dots ) {
					$c_settings['pagination'] = array(
						'el' => ".{$carousel_uid}-pagination",
						'clickable' => true
					);
				}

				echo '<div class="ke-carousel-container ' . ($hide_arrows_mob ? 'ke-hide-arrows-mobile' : '') . '" id="' . $carousel_uid . '">';
				echo '<div class="swiper ke-swiper" data-swiper-settings=\'' . esc_attr( wp_json_encode( $c_settings ) ) . '\'>';
			}

			echo '<div class="' . implode(' ', $classes) . '">';
			
			// Performance: Bulk prime caches (Eliminates N+1 per-card)
			$prime_ids = [];
			foreach ( $query->posts as $p ) {
				// Collect Venues
				$vid = get_post_meta( $p->ID, 'KE_event_venue_id', true );
				if ( $vid ) $prime_ids[] = intval( $vid );
				
				// Collect Thumbnails
				$tid = get_post_thumbnail_id( $p->ID );
				if ( $tid ) $prime_ids[] = intval( $tid );
			}
			if ( ! empty( $prime_ids ) ) {
				_prime_post_caches( array_unique( $prime_ids ), false, true );
			}

			// Pre-determine partial to avoid repeated file_exists calls inside the loop
			$preset  = $display['layout_preset'];
			$partial = KE_PLUGIN_DIR . 'templates/partials/widgets/event-card-' . $preset . '.php';
			if ( ! file_exists( $partial ) ) {
				$partial = KE_PLUGIN_DIR . 'templates/partials/loop-event-card.php';
			}

			// LOOP
			while ( $query->have_posts() ) {
				$query->the_post();
				$count++;
				$item_classes = array();
				
				if ( $max_initial > 0 && $count > $max_initial ) {
					$item_classes[] = 'ke-hidden-item';
				}
				if ( $is_carousel ) {
					$item_classes[] = 'swiper-slide';
				}

				// Standardize item wrapper for hidden state / slider support
				if ( ! empty( $item_classes ) ) {
					echo '<div class="' . implode(' ', $item_classes) . '">';
				}
				
				include $partial;
				
				if ( ! empty( $item_classes ) ) {
					echo '</div>';
				}
			}
			
			echo '</div>'; // close loop wrapper
			
			// Show More Button (Initial reveal for hidden items)
			if ( $max_initial > 0 && $query->post_count > $max_initial && ! $is_carousel ) {
				echo '<div class="ke-show-more-wrapper">';
				echo '<button type="button" class="ke-show-more-btn">' . esc_html__( 'Show More', 'kontentainment-events' ) . '</button>';
				echo '</div>';
			}
			
			// Carousel Navigation Close
			if ( $is_carousel ) {
				echo '</div>'; // close swiper block
				
				if ( isset($display['carousel_arrows']) && 'yes' === $display['carousel_arrows'] ) {
					echo '<div class="swiper-button-prev ke-carousel-prev ' . $carousel_uid . '-prev"></div>';
					echo '<div class="swiper-button-next ke-carousel-next ' . $carousel_uid . '-next"></div>';
				}
				if ( isset($display['carousel_dots']) && 'yes' === $display['carousel_dots'] ) {
					echo '<div class="swiper-pagination ke-carousel-pagination ' . $carousel_uid . '-pagination"></div>';
				}
				
				echo '</div>'; // close ke-carousel-container
				
				// initKECarousels() in ke-frontend.js handles the heavy lifting. 
				// This inline call just triggers it immediately for this specific element.
				echo '<script>if(typeof initKECarousels === "function") initKECarousels();</script>';
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

<?php
/**
 * Kontentainment Events Query Logic - Phase 5 Updated
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
	 * Get events with filters
	 */
	public function get_events( $overrides = array() ) {
		$args = $this->get_filtered_events_args( $overrides );
		return new WP_Query( $args );
	}

	/**
	 * Build Event Query Args
	 * Supports both $_GET for archives and direct $overrides for widgets
	 */
	public function get_filtered_events_args( $overrides = array() ) {
		// Detect if we are in an AJAX filter context or using direct args (widgets)
		$input = empty( $overrides ) || isset( $overrides['paged'] ) ? $_GET : $overrides;
		
		$paged = isset( $input['ke_paged'] ) ? intval( $input['ke_paged'] ) : ( isset( $overrides['paged'] ) ? $overrides['paged'] : 1 );
		
		$defaults = array(
			'post_type'      => 'event',
			'posts_per_page' => 12,
			'paged'          => $paged,
			'post_status'    => 'publish',
			'meta_query'     => array( 'relation' => 'AND' ),
			'tax_query'      => array( 'relation' => 'AND' ),
		);

		$args = wp_parse_args( $overrides, $defaults );

		// Keyword search
		if ( ! empty( $input['ke_search'] ) ) {
			$args['s'] = sanitize_text_field( $input['ke_search'] );
		}

		// Taxonomies
		$tax_map = array(
			'ke_category' => 'event_category',
			'ke_city'     => 'event_city',
			'ke_area'     => 'event_area',
		);

		foreach ( $tax_map as $key => $taxonomy ) {
			$val = $input[ $key ] ?? ( $overrides[ $taxonomy ] ?? '' );
			if ( ! empty( $val ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => is_numeric($val) ? 'term_id' : 'slug',
					'terms'    => $val,
				);
			}
		}

		// Status filter
		$status = $input['ke_status'] ?? ( $overrides['status'] ?? '' );
		if ( ! empty( $status ) ) {
			if ( 'all_exc_past' === $status ) {
				$args['meta_query'][] = array(
					'key'     => 'KE_event_status',
					'value'   => 'past',
					'compare' => '!=',
				);
			} else {
				$args['meta_query'][] = array(
					'key'     => 'KE_event_status',
					'value'   => $status,
					'compare' => '=',
				);
			}
		}

		// Featured filter
		$featured = $input['ke_featured'] ?? ( $overrides['featured'] ?? '' );
		if ( '1' === (string)$featured || true === $featured ) {
			$args['meta_query'][] = array(
				'key'     => 'KE_event_featured',
				'value'   => '1',
				'compare' => '=',
			);
		}

		// Editor Pick filter
		$editor_pick = $input['ke_editor_pick'] ?? ( $overrides['editor_pick'] ?? '' );
		if ( '1' === (string)$editor_pick || true === $editor_pick ) {
			$args['meta_query'][] = array(
				'key'     => 'KE_event_editor_pick',
				'value'   => '1',
				'compare' => '=',
			);
		}

		// Venue filter
		$venue_id = $input['ke_venue'] ?? ( $overrides['venue_id'] ?? '' );
		if ( ! empty( $venue_id ) ) {
			$args['meta_query'][] = array(
				'key'     => 'KE_event_venue_id',
				'value'   => $venue_id,
				'compare' => '=',
			);
		}

		// Sorting
		$sort = $input['ke_sort'] ?? ( $overrides['orderby_custom'] ?? 'upcoming' );
		
		switch ( $sort ) {
			case 'latest':
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
				break;
			case 'title_asc':
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
			case 'title_desc':
				$args['orderby'] = 'title';
				$args['order']   = 'DESC';
				break;
			case 'date_asc':
				$args['meta_key'] = 'KE_event_date';
				$args['orderby']  = 'meta_value';
				$args['order']    = 'ASC';
				break;
			case 'date_desc':
				$args['meta_key'] = 'KE_event_date';
				$args['orderby']  = 'meta_value';
				$args['order']    = 'DESC';
				break;
			case 'upcoming':
			default:
				$args['meta_query']['status_clause'] = array(
					'key'     => 'KE_event_status',
					'compare' => 'EXISTS',
				);
				$args['meta_query']['date_clause'] = array(
					'key'     => 'KE_event_date',
					'compare' => 'EXISTS',
					'type'    => 'DATE',
				);
				$args['orderby'] = array(
					'date_clause' => 'ASC',
				);
				break;
		}

		return $args;
	}

	/**
	 * Get venues with filters
	 */
	public function get_venues( $overrides = array() ) {
		$args = $this->get_filtered_venues_args( $overrides );
		return new WP_Query( $args );
	}

	/**
	 * Build Venue Query Args
	 */
	public function get_filtered_venues_args( $overrides = array() ) {
		$input = empty( $overrides ) || isset( $overrides['paged'] ) ? $_GET : $overrides;
		$paged = isset( $input['ke_paged'] ) ? intval( $input['ke_paged'] ) : ( isset( $overrides['paged'] ) ? $overrides['paged'] : 1 );

		$defaults = array(
			'post_type'      => 'venue',
			'posts_per_page' => 12,
			'paged'          => $paged,
			'post_status'    => 'publish',
			'tax_query'      => array(),
		);

		$args = wp_parse_args( $overrides, $defaults );

		// Keyword search
		if ( ! empty( $input['ke_search'] ) ) {
			$args['s'] = sanitize_text_field( $input['ke_search'] );
		}

		// Taxonomies
		$tax_map = array(
			'ke_city' => 'event_city',
			'ke_area' => 'event_area',
		);

		foreach ( $tax_map as $key => $taxonomy ) {
			$val = $input[ $key ] ?? ( $overrides[ $taxonomy ] ?? '' );
			if ( ! empty( $val ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => is_numeric($val) ? 'term_id' : 'slug',
					'terms'    => $val,
				);
			}
		}

		// Sorting
		$sort = $input['ke_sort'] ?? ( $overrides['orderby_custom'] ?? 'title_asc' );

		switch ( $sort ) {
			case 'latest':
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
				break;
			case 'title_desc':
				$args['orderby'] = 'title';
				$args['order']   = 'DESC';
				break;
			case 'title_asc':
			default:
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
		}

		return $args;
	}

	/**
	 * Render Events Loop
	 * @param WP_Query $query
	 * @param array $settings Display settings (for widgets)
	 */
	public function render_events_loop( $query, $settings = array() ) {
		// Define default display settings if not provided
		$display = wp_parse_args( $settings, array(
			'show_image'    => 'yes',
			'show_excerpt'  => 'yes',
			'show_date'     => 'yes',
			'show_time'     => 'yes',
			'show_venue'    => 'yes',
			'show_meta'     => 'yes',
			'show_badge'    => 'yes',
			'excerpt_length' => 20,
			'columns'       => 3,
			'columns_tablet' => 2,
			'columns_mobile' => 1,
		) );

		ob_start();
		if ( $query->have_posts() ) {
			echo '<div class="ke-loop-wrapper ke-columns-' . esc_attr($display['columns']) . ' ke-columns-tablet-' . esc_attr($display['columns_tablet']) . ' ke-columns-mobile-' . esc_attr($display['columns_mobile']) . '">';
			while ( $query->have_posts() ) {
				$query->the_post();
				// Use the settings in the partial
				include KE_PLUGIN_DIR . 'templates/partials/loop-event-card.php';
			}
			echo '</div>';
			wp_reset_postdata();
		} else {
			include KE_PLUGIN_DIR . 'templates/partials/archive-empty-state.php';
		}
		return ob_get_clean();
	}

	/**
	 * Render Venues Loop
	 */
	public function render_venues_loop( $query, $settings = array() ) {
		$display = wp_parse_args( $settings, array(
			'show_image'     => 'yes',
			'show_excerpt'   => 'yes',
			'show_meta'      => 'yes',
			'columns'        => 3,
			'columns_tablet' => 2,
			'columns_mobile' => 1,
		) );

		ob_start();
		if ( $query->have_posts() ) {
			echo '<div class="ke-loop-wrapper ke-columns-' . esc_attr($display['columns']) . ' ke-columns-tablet-' . esc_attr($display['columns_tablet']) . ' ke-columns-mobile-' . esc_attr($display['columns_mobile']) . '">';
			while ( $query->have_posts() ) {
				$query->the_post();
				include KE_PLUGIN_DIR . 'templates/partials/loop-venue-card.php';
			}
			echo '</div>';
			wp_reset_postdata();
		} else {
			include KE_PLUGIN_DIR . 'templates/partials/archive-empty-state.php';
		}
		return ob_get_clean();
	}

	/**
	 * Get related events
	 */
	public function get_related_events( $post_id, $limit = 4 ) {
		$categories = wp_get_post_terms( $post_id, 'event_category', array( 'fields' => 'ids' ) );
		return $this->get_events( array(
			'posts_per_page' => $limit,
			'post__not_in'   => array( $post_id ),
			'event_category' => $categories,
			'orderby_custom' => 'upcoming',
		) );
	}
}
KE_Query::get_instance();

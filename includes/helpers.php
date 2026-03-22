<?php
/**
 * Kontentainment Events Helper Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get event meta
 */
function ke_get_event_meta( $post_id, $key = '', $single = true ) {
	return get_post_meta( $post_id, 'KE_event_' . $key, $single );
}

/**
 * Get venue meta
 */
function ke_get_venue_meta( $post_id, $key = '', $single = true ) {
	return get_post_meta( $post_id, 'KE_venue_' . $key, $single );
}

/**
 * Get event date display
 */
function ke_get_event_date_display( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$date = ke_get_event_meta( $post_id, 'date' );
	if ( ! $date ) {
		return '';
	}

	return date_i18n( get_option( 'date_format' ), strtotime( $date ) );
}

/**
 * Get event status label
 */
function ke_get_event_status_label( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$status = ke_get_event_meta( $post_id, 'status' );
	return ucfirst( $status ?: 'upcoming' );
}

/**
 * Count upcoming events at a venue
 */
function ke_count_venue_upcoming_events( $venue_id ) {
	$cache_key = 'venue_count_' . $venue_id;
	$cached    = KE_Cache::get_instance()->get( $cache_key );
	
	if ( false !== $cached ) {
		return $cached;
	}

	$args = array(
		'post_type'      => 'event',
		'posts_per_page' => -1,
		'fields'         => 'ids', // Performance
		'no_found_rows'  => false,
		'meta_query'     => array(
			array(
				'key'     => 'KE_event_venue_id',
				'value'   => $venue_id,
				'compare' => '=',
			),
			array(
				'key'     => 'KE_event_status',
				'value'   => 'upcoming',
				'compare' => '=',
			),
		),
	);

	$query = new WP_Query( $args );
	$count = $query->found_posts;

	// Cache for 24 hours unless invalidated
	KE_Cache::get_instance()->set( $cache_key, $count, DAY_IN_SECONDS );

	return $count;
}

/**
 * Get full location display for a venue (Address, City, Gov)
 */
function ke_get_venue_location_display( $venue_id ) {
	if ( ! $venue_id ) {
		return '';
	}

	$address = get_post_meta( $venue_id, 'KE_venue_address', true );
	$cities  = wp_get_object_terms( $venue_id, 'event_city' );
	$govs    = wp_get_object_terms( $venue_id, 'event_governorate' );

	$parts = array();
	if ( $address ) {
		$parts[] = $address;
	}
	if ( ! empty( $cities ) && ! is_wp_error( $cities ) ) {
		$parts[] = $cities[0]->name;
	}
	if ( ! empty( $govs ) && ! is_wp_error( $govs ) ) {
		$parts[] = $govs[0]->name;
	}

	return implode( ', ', $parts );
}

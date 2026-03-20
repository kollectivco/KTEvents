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
	$query = new WP_Query( array(
		'post_type'      => 'event',
		'posts_per_page' => -1,
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
	) );

	return $query->found_posts;
}

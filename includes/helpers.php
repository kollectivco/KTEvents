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

	$formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $date ) );
	return is_rtl() ? ke_convert_to_arabic_numbers( $formatted_date ) : $formatted_date;
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
		'fields'         => 'ids', 
		'no_found_rows'  => true, // -1 already fetches all, no need for calc_found_rows
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
	$count = count( $query->posts );

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
	$cities  = get_the_terms( $venue_id, 'event_city' );
	$govs    = get_the_terms( $venue_id, 'event_governorate' );

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

/**
 * Get SVG Icons for the UI
 */
function ke_get_svg_icon( $name = '' ) {
	$icons = array(
		'calendar' => '<svg class="ke-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
		'map-pin'  => '<svg class="ke-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
		'phone'    => '<svg class="ke-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>',
	);

	if ( isset( $icons[ $name ] ) ) {
		echo $icons[ $name ];
	}
}

/**
 * Convert numbers inside a string to Eastern Arabic numerals (١, ٢, ٣...)
 */
function ke_convert_to_arabic_numbers( $str ) {
	$western = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$eastern = array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' );
	return str_replace( $western, $eastern, $str );
}

/**
 * Format event time string (HH:MM) to 12-hour format with AM/PM (Arabic or English)
 */
function ke_format_event_time( $time_str ) {
	if ( ! $time_str ) {
		return '';
	}

	$timestamp = strtotime( $time_str );
	if ( ! $timestamp ) {
		return $time_str;
	}

	$formatted_time = date( 'h:i A', $timestamp );
	
	if ( is_rtl() ) {
		$formatted_time = str_replace( array( 'AM', 'PM' ), array( 'صباحاً', 'مساءً' ), $formatted_time );
		$formatted_time = ke_convert_to_arabic_numbers( $formatted_time );
	}

	return $formatted_time;
}

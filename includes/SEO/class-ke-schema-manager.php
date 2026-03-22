<?php
/**
 * Kontentainment Events Schema Manager
 * Generates JSON-LD schema for Events and Venues.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Schema_Manager {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_head', array( $this, 'output_schema' ), 20 );
	}

	/**
	 * Output JSON-LD Schema
	 */
	public function output_schema() {
		if ( is_singular( 'event' ) ) {
			$this->render_event_schema();
		} elseif ( is_singular( 'venue' ) ) {
			$this->render_venue_schema();
		}
	}

	/**
	 * Render Event Schema
	 */
	private function render_event_schema() {
		$event_id = get_the_ID();
		
		$start_date = ke_get_event_meta( $event_id, 'date' );
		$end_date   = ke_get_event_meta( $event_id, 'end_date' );
		$start_time = ke_get_event_meta( $event_id, 'time' );
		$end_time   = ke_get_event_meta( $event_id, 'end_time' );

		if ( ! $start_date ) return;

		// Format dates for Schema (ISO 8601)
		$iso_start = $start_date . ( $start_time ? 'T' . $start_time : 'T00:00' );
		$iso_end   = ( $end_date ?: $start_date ) . ( $end_time ? 'T' . $end_time : ( $start_time ? 'T23:59' : 'T23:59' ) );

		$venue_id   = ke_get_event_meta( $event_id, 'venue_id' );
		$venue_name = $venue_id ? get_the_title( $venue_id ) : get_post_meta( $event_id, 'KE_event_address', true );
		
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Event',
			'name'        => get_the_title( $event_id ),
			'description' => get_the_excerpt( $event_id ) ?: wp_trim_words( get_post_field( 'post_content', $event_id ), 50 ),
			'startDate'   => $iso_start,
			'endDate'     => $iso_end,
			'image'       => array( get_the_post_thumbnail_url( $event_id, 'large' ) ),
			'eventStatus' => 'https://schema.org/EventScheduled',
			'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
		);

		// Location
		if ( $venue_id ) {
			$schema['location'] = array(
				'@type'   => 'Place',
				'name'    => $venue_name,
				'address' => array(
					'@type'           => 'PostalAddress',
					'streetAddress'   => get_post_meta( $venue_id, 'KE_venue_address', true ),
					'addressLocality' => '', // Could pull from city term
				)
			);
		}

		// Offers (if ticket URL exists)
		$ticket_url = ke_get_event_meta( $event_id, 'official_url' );
		if ( $ticket_url ) {
			$schema['offers'] = array(
				'@type' => 'Offer',
				'url' => esc_url( $ticket_url ),
				'availability' => 'https://schema.org/InStock'
			);
		}

		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
	}

	/**
	 * Render Venue Schema
	 */
	private function render_venue_schema() {
		$venue_id = get_the_ID();
		
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'LocalBusiness',
			'name'        => get_the_title( $venue_id ),
			'description' => get_post_meta( $venue_id, 'KE_venue_short_description', true ) ?: get_the_excerpt( $venue_id ),
			'image'       => array( get_the_post_thumbnail_url( $venue_id, 'large' ) ),
			'address'     => array(
				'@type' => 'PostalAddress',
				'streetAddress' => get_post_meta( $venue_id, 'KE_venue_address', true )
			),
			'telephone'   => get_post_meta( $venue_id, 'KE_venue_phone', true ),
			'url'         => get_permalink( $venue_id )
		);

		$lat = get_post_meta( $venue_id, 'KE_venue_lat', true );
		$lng = get_post_meta( $venue_id, 'KE_venue_lng', true );
		if ( $lat && $lng ) {
			$schema['geo'] = array(
				'@type' => 'GeoCoordinates',
				'latitude' => $lat,
				'longitude' => $lng
			);
		}

		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
	}
}

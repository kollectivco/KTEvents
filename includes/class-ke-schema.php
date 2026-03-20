<?php
/**
 * Kontentainment Events SEO / Structured Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Schema {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_head', array( $this, 'output_schema' ) );
	}

	public function output_schema() {
		if ( '1' !== (string) get_option( 'ke_enable_schema', '1' ) ) return;

		if ( is_singular( 'event' ) ) {
			$this->render_event_schema( get_the_ID() );
		} elseif ( is_singular( 'venue' ) ) {
			$this->render_venue_schema( get_the_ID() );
		}
	}

	private function render_event_schema( $post_id ) {
		$event_date  = ke_get_event_meta( $post_id, 'date' );
		$event_time  = ke_get_event_meta( $post_id, 'time' );
		$venue_id    = ke_get_event_meta( $post_id, 'venue_id' );
		$status      = ke_get_event_meta( $post_id, 'status' );
		
		$schema = [
			'@context' => 'https://schema.org',
			'@type'    => 'Event',
			'name'     => get_the_title( $post_id ),
			'description' => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
			'url'      => get_permalink( $post_id ),
			'startDate' => $event_date . ($event_time ? 'T' . $event_time : ''),
			'eventStatus' => $this->map_status_to_schema( $status ),
		];

		if ( has_post_thumbnail( $post_id ) ) {
			$schema['image'] = get_the_post_thumbnail_url( $post_id, 'full' );
		}

		if ( $venue_id ) {
			$schema['location'] = [
				'@type' => 'Place',
				'name'  => get_the_title( $venue_id ),
				'address' => ke_get_venue_meta( $venue_id, 'address' ),
			];
		}

		echo "\n" . '<script type="application/ld+json">' . json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}

	private function render_venue_schema( $post_id ) {
		$schema = [
			'@context' => 'https://schema.org',
			'@type'    => 'LocalBusiness',
			'name'     => get_the_title( $post_id ),
			'description' => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
			'url'      => get_permalink( $post_id ),
			'address'  => ke_get_venue_meta( $post_id, 'address' ),
			'telephone' => ke_get_venue_meta( $post_id, 'phone' ),
		];
		
		if ( has_post_thumbnail( $post_id ) ) {
			$schema['image'] = get_the_post_thumbnail_url( $post_id, 'full' );
		}

		echo "\n" . '<script type="application/ld+json">' . json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}

	private function map_status_to_schema( $status ) {
		$map = [
			'upcoming' => 'EventScheduled',
			'ongoing'  => 'EventScheduled',
			'cancelled' => 'EventCancelled',
			'past'      => 'EventMovedOnline', // past is ambiguous in schema
			'postponed' => 'EventPostponed',
		];
		$val = $map[ $status ] ?? 'EventScheduled';
		return 'https://schema.org/' . $val;
	}
}

<?php
/**
 * Kontentainment Events Duplicate Detection
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Duplicates {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Check if an event already exists
	 *
	 * @param array $fields The normalized fields extracted from the parser.
	 * @return array Possible duplicate info.
	 */
	public function check( $fields ) {
		$duplicates = array(
			'exact'    => array(),
			'possible' => array(),
		);

		// 1. Check by Source URL
		if ( ! empty( $fields['source_url'] ) ) {
			$found = $this->query_by_meta( 'KE_event_source_url', $fields['source_url'] );
			if ( $found ) {
				$duplicates['exact'][] = $found;
			}
		}

		// 2. Check by Canonical URL
		if ( ! empty( $fields['canonical_url'] ) ) {
			$found = $this->query_by_meta( 'KE_event_canonical_url', $fields['canonical_url'] );
			if ( $found ) {
				$duplicates['exact'][] = $found;
			}
		}

		// 3. Check by Title + Date + Venue Hash
		if ( ! empty( $fields['title'] ) && ! empty( $fields['event_date'] ) ) {
			$hash = $this->generate_hash( $fields );
			$found_by_hash = $this->query_by_meta( 'KE_event_import_hash', $hash );
			if ( $found_by_hash ) {
				$duplicates['possible'][] = $found_by_hash;
			}
		}

		return $duplicates;
	}

	/**
	 * Generate a unique hash for title/date/venue
	 */
	public function generate_hash( $fields ) {
		$title = sanitize_title( $fields['title'] ?? '' );
		$date  = $fields['event_date'] ?? '';
		$venue = sanitize_title( $fields['venue_name'] ?? '' );
		return md5( $title . '|' . $date . '|' . $venue );
	}

	private function query_by_meta( $key, $value ) {
		$query = new WP_Query( array(
			'post_type'      => 'event',
			'meta_key'       => $key,
			'meta_value'     => $value,
			'posts_per_page' => 1,
			'post_status'    => array( 'publish', 'draft', 'pending' ),
		) );

		if ( $query->have_posts() ) {
			return $query->posts[0];
		}
		return null;
	}
}
KE_Duplicates::get_instance();

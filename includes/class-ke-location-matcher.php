<?php
/**
 * Kontentainment Events Location Auto-Detection Logic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Location_Matcher {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Scrub detected locations from address to keep it clean
	 */
	public function scrub_location_from_address( $address, $gov_name, $city_name ) {
		if ( empty( $address ) ) return '';

		$clean = $address;

		// Normalize comparison
		$gov_q = preg_quote( $gov_name, '/' );
		$city_q = preg_quote( $city_name, '/' );

		// 1. Remove Gov if it appears at the end or as a segment
		if ( ! empty( $gov_name ) ) {
			// Try trailing segment: "Somabay, Hurghada, Red Sea" -> "Somabay, Hurghada"
			$clean = preg_replace( '/,\s*' . $gov_q . '\s*$/i', '', $clean );
			// Try space: "Cairo Egypt"
			$clean = preg_replace( '/\s+' . $gov_q . '\s*$/i', '', $clean );
		}

		// 2. Remove City
		if ( ! empty( $city_name ) ) {
			$clean = preg_replace( '/,\s*' . $city_q . '\s*$/i', '', $clean );
			$clean = preg_replace( '/\s+' . $city_q . '\s*$/i', '', $clean );
		}

		// Final trim
		$clean = rtrim( trim( $clean ), ',' );

		return $clean;
	}

	/**
	 * Detect Governorate and City from an address string
	 * 
	 * @param string $address The raw address string
	 * @return array Detected IDs and names
	 */
	public function detect( $address ) {
		if ( empty( $address ) ) {
			return array(
				'gov_id'          => 0,
				'gov_name'        => '',
				'city_id'         => 0,
				'city_name'       => '',
				'confidence'      => 0,
				'cleaned_address' => ''
			);
		}

		// ... (keep normalization logic)
		$clean_address = html_entity_decode( $address, ENT_QUOTES, 'UTF-8' );
		$clean_address = preg_replace( '/\s+/', ' ', $clean_address );
		$clean_address = preg_replace( '/\b\d{5}\b/', '', $clean_address );
		
		$parts = array_map( 'trim', explode( ',', $clean_address ) );
		$parts = array_unique( $parts );
		$search_pool = strtolower( implode( ' ', $parts ) );

		$governorates = get_terms( array(
			'taxonomy'   => 'event_governorate',
			'hide_empty' => false,
		) );

		$detected_gov = null;
		$detected_city = null;

		$gov_hits = array();
		foreach ( $governorates as $gov ) {
			$name = strtolower( $gov->name );
			if ( preg_match( '/\b' . preg_quote( $name, '/' ) . '\b/i', $search_pool ) ) {
				$gov_hits[] = $gov;
			}
		}

		if ( ! empty( $gov_hits ) ) {
			usort( $gov_hits, function($a, $b) {
				return strlen($b->name) - strlen($a->name);
			});
			$detected_gov = $gov_hits[0];
		}

		if ( $detected_gov ) {
			$cities = get_terms( array(
				'taxonomy'   => 'event_city',
				'hide_empty' => false,
				'meta_query' => array(
					array(
						'key'     => 'parent_governorate_id',
						'value'   => $detected_gov->term_id,
						'compare' => '='
					)
				)
			) );

			foreach ( $cities as $city ) {
				$name = strtolower( $city->name );
				if ( preg_match( '/\b' . preg_quote( $name, '/' ) . '\b/i', $search_pool ) ) {
					$detected_city = $city;
					break;
				}
			}
		}

		if ( ! $detected_gov ) {
			$all_cities = get_terms( array(
				'taxonomy'   => 'event_city',
				'hide_empty' => false,
			) );

			foreach ( $all_cities as $city ) {
				$name = strtolower( $city->name );
				if ( preg_match( '/\b' . preg_quote( $name, '/' ) . '\b/i', $search_pool ) ) {
					$detected_city = $city;
					$gov_id = get_term_meta( $city->term_id, 'parent_governorate_id', true );
					if ( $gov_id ) {
						$detected_gov = get_term( $gov_id, 'event_governorate' );
					}
					break;
				}
			}
		}

		$confidence = 0;
		if ( $detected_gov && $detected_city ) $confidence = 100;
		elseif ( $detected_gov ) $confidence = 60;
		elseif ( $detected_city ) $confidence = 40;

		$gov_name = $detected_gov ? $detected_gov->name : '';
		$city_name = $detected_city ? $detected_city->name : '';

		return array(
			'gov_id'          => $detected_gov ? $detected_gov->term_id : 0,
			'gov_name'        => $gov_name,
			'city_id'         => $detected_city ? $detected_city->term_id : 0,
			'city_name'       => $city_name,
			'confidence'      => $confidence,
			'source'          => 'address_parsing',
			'cleaned_address' => $this->scrub_location_from_address( $address, $gov_name, $city_name )
		);
	}
}

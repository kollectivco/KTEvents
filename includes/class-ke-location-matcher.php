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
	 * Detect Governorate and City from an address string
	 * 
	 * @param string $address The raw address string
	 * @return array Detected IDs and names
	 */
	public function detect( $address ) {
		if ( empty( $address ) ) {
			return array(
				'gov_id'     => 0,
				'gov_name'   => '',
				'city_id'    => 0,
				'city_name'  => '',
				'confidence' => 0
			);
		}

		// 1. Normalize address
		$clean_address = html_entity_decode( $address, ENT_QUOTES, 'UTF-8' );
		$clean_address = preg_replace( '/\s+/', ' ', $clean_address );
		
		// Strip postal codes (Egypt usually 5 digits)
		$clean_address = preg_replace( '/\b\d{5}\b/', '', $clean_address );
		
		// Strip repeat words (often happens in messy imports)
		$parts = array_map( 'trim', explode( ',', $clean_address ) );
		$parts = array_unique( $parts );
		$clean_address = implode( ' ', $parts );
		$clean_address = strtolower( $clean_address );

		$governorates = get_terms( array(
			'taxonomy'   => 'event_governorate',
			'hide_empty' => false,
		) );

		$detected_gov = null;
		$detected_city = null;

		// 2. Map of "Noise" vs "Signal"
		// If address contains "Red Sea", prioritize it over "Cairo" if both appear
		// (Generic logic: longer names or specific regions might have higher signal)
		$gov_hits = array();
		foreach ( $governorates as $gov ) {
			$name = strtolower( $gov->name );
			if ( preg_match( '/\b' . preg_quote( $name, '/' ) . '\b/i', $clean_address ) ) {
				$gov_hits[] = $gov;
			}
		}

		// Heuristic: If multiple Govs found, pick the most specific/longer name? 
		// Or if "Red Sea" and "Cairo" both exist, and Hurghada is there, Red Sea is the winner.
		if ( ! empty( $gov_hits ) ) {
			// Sort by length desc: "South Sinai" before "Sinai"
			usort( $gov_hits, function($a, $b) {
				return strlen($b->name) - strlen($a->name);
			});
			$detected_gov = $gov_hits[0];
		}

		// 3. Find City
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
				if ( preg_match( '/\b' . preg_quote( $name, '/' ) . '\b/i', $clean_address ) ) {
					$detected_city = $city;
					break;
				}
			}
		}

		// 4. Backtrack: If no Gov found, but City found
		if ( ! $detected_gov ) {
			$all_cities = get_terms( array(
				'taxonomy'   => 'event_city',
				'hide_empty' => false,
			) );

			foreach ( $all_cities as $city ) {
				$name = strtolower( $city->name );
				if ( preg_match( '/\b' . preg_quote( $name, '/' ) . '\b/i', $clean_address ) ) {
					$detected_city = $city;
					$gov_id = get_term_meta( $city->term_id, 'parent_governorate_id', true );
					if ( $gov_id ) {
						$detected_gov = get_term( $gov_id, 'event_governorate' );
					}
					break;
				}
			}
		}

		// 5. Final result and confidence
		$confidence = 0;
		if ( $detected_gov && $detected_city ) $confidence = 100;
		elseif ( $detected_gov ) $confidence = 60;
		elseif ( $detected_city ) $confidence = 40;

		return array(
			'gov_id'     => $detected_gov ? $detected_gov->term_id : 0,
			'gov_name'   => $detected_gov ? $detected_gov->name : '',
			'city_id'    => $detected_city ? $detected_city->term_id : 0,
			'city_name'  => $detected_city ? $detected_city->name : '',
			'confidence' => $confidence,
			'source'     => 'address_parsing'
		);
	}
}

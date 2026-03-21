<?php
/**
 * Kontentainment Events Egypt Location Dataset
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Egypt_Locations {

	/**
	 * Get structured governorates and cities
	 */
	public static function get_governorates() {
		return array(
			'Cairo' => array(
				'Nasr City', 'Heliopolis', 'New Cairo', 'Badr City', 'El Shorouk', 
				'Obour', 'Zamalek', 'Garden City', 'Downtown', 'Maadi', 'Old Cairo'
			),
			'Giza' => array(
				'Giza', 'Dokki', 'Mohandessin', 'Agouza', 'Sheikh Zayed', 
				'6th of October', 'New Giza'
			),
			'Alexandria' => array( 'Alexandria City', 'North Coast', 'Montaza' ),
			'Red Sea' => array(
				'Hurghada', 'El Gouna', 'Soma Bay', 'Makadi Bay', 
				'Sahl Hasheesh', 'Hurghada City'
			),
			'Fayoum' => array( 'Fayoum City' ),
			'Ismailia' => array( 'Ismailia City' ),
			'Suez' => array( 'Suez City' ),
			'Aswan' => array( 'Aswan City' ),
			'Port Said' => array( 'Port Said City' ),
			'South Sinai' => array( 'Sharm El Sheikh', 'Dahab', 'Nuweiba' ),
			'Matrouh' => array( 'Marsa MatrouhCity', 'Siwa' ),
			'Luxor' => array( 'Luxor City' ),
			'Qena' => array( 'Qena City' ),
			'North Sinai' => array( 'Arish' ),
		);
	}

	/**
	 * Seed taxonomies with Egypt data (Governorates and Cities)
	 */
	public static function seed_locations() {
		$data = self::get_governorates();

		foreach ( $data as $gov_name => $cities ) {
			// Add/Get Governorate
			$gov_term = term_exists( $gov_name, 'event_governorate' );
			if ( ! $gov_term ) {
				$gov_term = wp_insert_term( $gov_name, 'event_governorate' );
			}
			$gov_id = ! is_wp_error( $gov_term ) ? ( is_array( $gov_term ) ? $gov_term['term_id'] : $gov_term ) : 0;

			if ( ! $gov_id ) continue;

			foreach ( $cities as $city_name ) {
				// Add/Get City
				$city_term = term_exists( $city_name, 'event_city' );
				if ( ! $city_term ) {
					$city_term = wp_insert_term( $city_name, 'event_city' );
				}
				$city_id = ! is_wp_error( $city_term ) ? ( is_array( $city_term ) ? $city_term['term_id'] : $city_term ) : 0;
				
				if ( $city_id ) {
					// Link City to Governorate via term meta
					update_term_meta( $city_id, 'parent_governorate_id', $gov_id );
				}
			}
		}
	}

	/**
	 * Seed categories
	 */
	public static function seed_categories() {
		$categories = array(
			'Events', 'Screening', 'Concerts', 'Conference', 'Exhibitions', 
			'Festival', 'Nightlife', 'Sports', 'Summit', 'Tent', 'Theatre', 'Workshop'
		);

		foreach ( $categories as $cat ) {
			if ( ! term_exists( $cat, 'event_category' ) ) {
				wp_insert_term( $cat, 'event_category' );
			}
		}
	}

	/**
	 * Get locations bridge for JS
	 */
	public static function get_locations_bridge() {
		$governorates = get_terms( 'event_governorate', array( 'hide_empty' => false ) );
		$bridge = array();

		if ( ! is_wp_error( $governorates ) && ! empty( $governorates ) ) {
			foreach ( $governorates as $gov ) {
				$cities = get_terms( 'event_city', array(
					'hide_empty' => false,
					'meta_query' => array(
						array(
							'key'     => 'parent_governorate_id',
							'value'   => $gov->term_id,
							'compare' => '='
						)
					)
				) );

				$city_items = array();
				if ( ! is_wp_error( $cities ) ) {
					foreach ( $cities as $city ) {
						$city_items[] = array(
							'id'   => $city->term_id,
							'name' => $city->name
						);
					}
				}

				$bridge[ $gov->term_id ] = array(
					'name'   => $gov->name,
					'cities' => $city_items
				);
			}
		}

		return $bridge;
	}
}

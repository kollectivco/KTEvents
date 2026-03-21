<?php
/**
 * Kontentainment Events Egypt Location Dataset
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Egypt_Locations {

	/**
	 * Get all governorates
	 */
	public static function get_governorates() {
		return array(
			'Cairo' => array( 'New Cairo', 'Maadi', 'Zamalek', 'Heliopolis', 'Nasr City', 'Downtown', 'Shorouk', 'Madinaty' ),
			'Giza' => array( '6th of October', 'Sheikh Zayed', 'Dokki', 'Mohandessin', 'Pyramids', 'Haram' ),
			'Alexandria' => array( 'Montaza', 'Smoha', 'Stanley', 'Agami', 'Sidi Gaber' ),
			'Red Sea' => array( 'Hurghada', 'El Gouna', 'Sahl Hasheesh', 'Marsa Alam' ),
			'South Sinai' => array( 'Sharm El Sheikh', 'Dahab', 'Nuweiba' ),
			'Matrouh' => array( 'North Coast', 'Marsa Matrouh', 'Siwa' ),
			'Dakahlia' => array( 'Mansoura' ),
			'Gharbia' => array( 'Tanta' ),
			'Sharqia' => array( 'Zagazig' ),
			'Qalyubia' => array( 'Banha' ),
			'Minya' => array( 'Minya' ),
			'Assiut' => array( 'Assiut' ),
			'Sohag' => array( 'Sohag' ),
			'Qena' => array( 'Qena' ),
			'Luxor' => array( 'Luxor' ),
			'Aswan' => array( 'Aswan' ),
			'Port Said' => array( 'Port Said' ),
			'Ismailia' => array( 'Ismailia' ),
			'Suez' => array( 'Suez' ),
			'Damietta' => array( 'Damietta' ),
			'Fayoum' => array( 'Fayoum' ),
			'Beni Suef' => array( 'Beni Suef' ),
			'Menofia' => array( 'Shebin El Kom' ),
			'Beheira' => array( 'Damanhour' ),
			'Kafr El Sheikh' => array( 'Kafr El Sheikh' ),
			'North Sinai' => array( 'Arish' ),
			'New Valley' => array( 'Kharga' ),
		);
	}

	/**
	 * Seed taxonomies with Egypt data (Governorates and Cities)
	 */
	public static function seed_locations() {
		$data = self::get_governorates();

		foreach ( $data as $gov_name => $cities ) {
			// Add Governorate
			$gov_term = wp_insert_term( $gov_name, 'event_governorate' );
			$gov_id = ! is_wp_error( $gov_term ) ? $gov_term['term_id'] : ( term_exists( $gov_name, 'event_governorate' ) ?: 0 );

			foreach ( $cities as $city_name ) {
				// Add City
				wp_insert_term( $city_name, 'event_city' );
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
			wp_insert_term( $cat, 'event_category' );
		}
	}
}

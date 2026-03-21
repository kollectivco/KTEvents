<?php
/**
 * Kontentainment Events Taxonomies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Taxonomies {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register Custom Taxonomies
	 */
	public function register() {
		// Event Categories
		register_taxonomy( 'event_category', 'event', array(
			'labels'            => array(
				'name'          => 'Event Categories',
				'singular_name' => 'Event Category',
				'search_items'  => 'Search Event Categories',
				'all_items'     => 'All Event Categories',
				'parent_item'   => 'Parent Event Category',
				'parent_item_colon' => 'Parent Event Category:',
				'edit_item'     => 'Edit Event Category',
				'update_item'   => 'Update Event Category',
				'add_new_item'  => 'Add New Event Category',
				'new_item_name' => 'New Event Category Name',
				'menu_name'     => 'Categories',
			),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'event-category' ),
			'show_in_rest'      => true,
		) );

		// Event Governorates (Shared)
		register_taxonomy( 'event_governorate', array( 'event', 'venue' ), array(
			'labels'            => array(
				'name'          => 'Governorates',
				'singular_name' => 'Governorate',
				'search_items'  => 'Search Governorates',
				'all_items'     => 'All Governorates',
				'parent_item'   => 'Parent Governorate',
				'parent_item_colon' => 'Parent Governorate:',
				'edit_item'     => 'Edit Governorate',
				'update_item'   => 'Update Governorate',
				'add_new_item'  => 'Add New Governorate',
				'new_item_name' => 'New Governorate Name',
				'menu_name'     => 'Governorates',
			),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'event-governorate' ),
			'show_in_rest'      => true,
		) );

		// Event Cities (Shared)
		register_taxonomy( 'event_city', array( 'event', 'venue' ), array(
			'labels'            => array(
				'name'          => 'Cities',
				'singular_name' => 'City',
				'search_items'  => 'Search Cities',
				'all_items'     => 'All Cities',
				'parent_item'   => 'Parent City',
				'parent_item_colon' => 'Parent City:',
				'edit_item'     => 'Edit City',
				'update_item'   => 'Update City',
				'add_new_item'  => 'Add New City',
				'new_item_name' => 'New City Name',
				'menu_name'     => 'Cities',
			),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'event-city' ),
			'show_in_rest'      => true,
		) );

	}
}
KE_Taxonomies::get_instance();

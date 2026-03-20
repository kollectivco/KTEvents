<?php
/**
 * Kontentainment Events Post Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Post_Types {

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
	 * Register Custom Post Types
	 */
	public function register() {
		// Event CPT
		register_post_type( 'event', array(
			'labels'              => array(
				'name'               => 'Events',
				'singular_name'      => 'Event',
				'menu_name'          => 'KE Events',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New Event',
				'edit_item'          => 'Edit Event',
				'new_item'           => 'New Event',
				'view_item'          => 'View Event',
				'search_items'       => 'Search Events',
				'not_found'          => 'No events found',
				'not_found_in_trash' => 'No events found in Trash',
			),
			'description'         => 'Editorial events listing',
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-calendar-alt',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'events' ),
			'show_in_rest'        => true,
			'taxonomies'          => array( 'event_category', 'event_city', 'event_area' ),
		) );

		// Venue CPT
		register_post_type( 'venue', array(
			'labels'              => array(
				'name'               => 'Venues',
				'singular_name'      => 'Venue',
				'menu_name'          => 'KE Venues',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New Venue',
				'edit_item'          => 'Edit Venue',
				'new_item'           => 'New Venue',
				'view_item'          => 'View Venue',
				'search_items'       => 'Search Venues',
				'not_found'          => 'No venues found',
				'not_found_in_trash' => 'No venues found in Trash',
			),
			'description'         => 'Editorial venues listing',
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=event', // Nest under Events menu
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-location',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'venues' ),
			'show_in_rest'        => true,
			'taxonomies'          => array( 'event_city', 'event_area' ),
		) );
	}
}
KE_Post_Types::get_instance();

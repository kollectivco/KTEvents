<?php
/**
 * Kontentainment Events Save Meta
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Save {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'save_post_event', array( $this, 'save_event_meta' ) );
		add_action( 'save_post_venue', array( $this, 'save_venue_meta' ) );
	}

	/**
	 * Save Event Meta
	 */
	public function save_event_meta( $post_id ) {
		if ( ! $this->can_save( 'ke_meta_nonce', 'ke_save_meta', $post_id ) ) {
			return;
		}

		$fields = array(
			'KE_event_venue_id'      => 'sanitize_text_field',
			'KE_event_date'          => 'sanitize_text_field',
			'KE_event_end_date'      => 'sanitize_text_field',
			'KE_event_time'          => 'sanitize_text_field',
			'KE_event_end_time'      => 'sanitize_text_field',
			'KE_event_status'        => 'sanitize_text_field',
			'KE_event_organizer_name' => 'sanitize_text_field',
			'KE_event_address'       => 'sanitize_text_field',
			'KE_event_phone'         => 'sanitize_text_field',
			'KE_event_official_url'  => 'esc_url_raw',
			'KE_event_source_url'    => 'esc_url_raw',
			'KE_event_featured'      => 'sanitize_text_field',
			'KE_event_editor_pick'   => 'sanitize_text_field',
			'KE_event_last_verified' => 'sanitize_text_field',
			'KE_event_internal_notes' => 'sanitize_textarea_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, $field, call_user_func( $sanitize_callback, $_POST[ $field ] ) );
			} else {
				// Handle checkboxes if not present in $_POST
				if ( in_array( $field, array( 'KE_event_featured', 'KE_event_editor_pick' ) ) ) {
					update_post_meta( $post_id, $field, '0' );
				}
			}
		}
	}

	/**
	 * Save Venue Meta
	 */
	public function save_venue_meta( $post_id ) {
		if ( ! $this->can_save( 'ke_meta_nonce', 'ke_save_meta', $post_id ) ) {
			return;
		}

		$fields = array(
			'KE_venue_arabic_name'       => 'sanitize_text_field',
			'KE_venue_english_name'      => 'sanitize_text_field',
			'KE_venue_address'           => 'sanitize_text_field',
			'KE_venue_phone'             => 'sanitize_text_field',
			'KE_venue_website'           => 'esc_url_raw',
			'KE_venue_instagram'         => 'esc_url_raw',
			'KE_venue_map_url'           => 'esc_url_raw',
			'KE_venue_lat'               => 'sanitize_text_field',
			'KE_venue_lng'               => 'sanitize_text_field',
			'KE_venue_short_description' => 'sanitize_textarea_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, $field, call_user_func( $sanitize_callback, $_POST[ $field ] ) );
			}
		}

		// Handle Taxonomies from Meta Box
		if ( isset( $_POST['ke_venue_governorate'] ) ) {
			wp_set_object_terms( $post_id, intval( $_POST['ke_venue_governorate'] ), 'event_governorate' );
		}
		if ( isset( $_POST['ke_venue_city'] ) ) {
			wp_set_object_terms( $post_id, intval( $_POST['ke_venue_city'] ), 'event_city' );
		}
	}

	/**
	 * Check if the post can be saved
	 */
	private function can_save( $nonce_name, $nonce_action, $post_id ) {
		if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) ) {
			return false;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return true;
	}
}
KE_Save::get_instance();

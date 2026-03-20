<?php
/**
 * Kontentainment Events Import from URL Controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Import_URL {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_ke_fetch_event_preview', array( $this, 'ajax_fetch_preview' ) );
		add_action( 'admin_post_ke_save_imported_event', array( $this, 'handle_save_import' ) );
	}

	/**
	 * AJAX Fetch Preview
	 */
	public function ajax_fetch_preview() {
		check_ajax_referer( 'ke_import_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
		}

		$url = isset( $_POST['source_url'] ) ? esc_url_raw( $_POST['source_url'] ) : '';
		if ( empty( $url ) ) {
			wp_send_json_error( array( 'message' => 'URL is required.' ) );
		}

		// 1. Fetch HTML
		$fetch_result = KE_Scraper::get_instance()->fetch( $url );
		if ( is_wp_error( $fetch_result ) ) {
			wp_send_json_error( array( 'message' => $fetch_result->get_error_message() ) );
		}

		// 2. Select Parser
		$parser = KE_Parser_Registry::get_instance()->get_parser_for_url( $url );
		if ( ! $parser ) {
			wp_send_json_error( array( 'message' => 'No suitable parser found for this URL.' ) );
		}

		// 3. Parse Data
		$parsed_data = $parser->parse( $fetch_result['html'], $url );

		// 4. Duplicate Check
		$duplicates = KE_Duplicates::get_instance()->check( $parsed_data['fields'] );

		// 5. Venue Match
		$venue_id = 0;
		if ( ! empty( $parsed_data['fields']['venue_name'] ) ) {
			$venue = get_page_by_title( $parsed_data['fields']['venue_name'], OBJECT, 'venue' );
			if ( $venue ) {
				$venue_id = $venue->ID;
			}
		}

		// Log fetching/parsing
		KE_Logs::get_instance()->log( array(
			'source_url'        => $url,
			'canonical_url'     => $parsed_data['canonical_url'],
			'source_name'       => $parsed_data['source_name'],
			'parser_used'       => $parsed_data['parser_name'],
			'parser_confidence' => $parsed_data['parser_confidence'],
			'status'            => 'previewed',
			'message'           => 'Event successfully fetched and parsed for preview.',
		) );

		wp_send_json_success( array(
			'data'        => $parsed_data,
			'duplicates'  => $duplicates,
			'matched_venue_id' => $venue_id,
		) );
	}

	/**
	 * Handle Final Save/Update
	 */
	public function handle_save_import() {
		if ( ! isset( $_POST['ke_import_save_nonce'] ) || ! wp_verify_nonce( $_POST['ke_import_save_nonce'], 'ke_save_import' ) ) {
			wp_die( 'Security check failed.' );
		}

		$action = sanitize_text_field( $_POST['ke_import_action'] ?? 'create' ); // create / update
		$post_id = intval( $_POST['ke_existing_post_id'] ?? 0 );

		$fields = array(
			'title'          => sanitize_text_field( $_POST['title'] ),
			'description'    => wp_kses_post( $_POST['description'] ),
			'excerpt'        => sanitize_textarea_field( $_POST['excerpt'] ?? '' ),
			'status'         => sanitize_text_field( $_POST['status'] ?? 'upcoming' ),
			'event_date'     => sanitize_text_field( $_POST['event_date'] ),
			'event_end_date' => sanitize_text_field( $_POST['event_end_date'] ),
			'event_time'     => sanitize_text_field( $_POST['event_time'] ),
			'event_end_time' => sanitize_text_field( $_POST['event_end_time'] ),
			'venue_name'     => sanitize_text_field( $_POST['venue_name'] ),
			'venue_id'       => intval( $_POST['venue_id'] ),
			'organizer_name' => sanitize_text_field( $_POST['organizer_name'] ),
			'address'        => sanitize_text_field( $_POST['address'] ),
			'phone'          => sanitize_text_field( $_POST['phone'] ),
			'official_url'   => esc_url_raw( $_POST['official_url'] ),
			'source_url'     => esc_url_raw( $_POST['source_url'] ),
			'image_url'      => esc_url_raw( $_POST['image_url'] ),
			'category'       => intval( $_POST['category_id'] ),
			'city'           => intval( $_POST['city_id'] ),
			'area'           => intval( $_POST['area_id'] ),
		);

		// Handle Venue Creation if needed
		$final_venue_id = $fields['venue_id'];
		if ( ! $final_venue_id && ! empty( $fields['venue_name'] ) && isset( $_POST['auto_create_venue'] ) ) {
			$final_venue_id = wp_insert_post( array(
				'post_type'   => 'venue',
				'post_title'  => $fields['venue_name'],
				'post_status' => 'publish',
			) );

			if ( $final_venue_id && ! is_wp_error( $final_venue_id ) ) {
				update_post_meta( $final_venue_id, 'KE_venue_address', $fields['address'] );
				update_post_meta( $final_venue_id, 'KE_venue_phone', $fields['phone'] );
				
				// Assign city/area to venue too
				if ( $fields['city'] ) wp_set_object_terms( $final_venue_id, $fields['city'], 'event_city' );
				if ( $fields['area'] ) wp_set_object_terms( $final_venue_id, $fields['area'], 'event_area' );
			}
		}

		// Create or Update Event
		$post_data = array(
			'post_type'    => 'event',
			'post_title'   => $fields['title'],
			'post_content' => $fields['description'],
			'post_excerpt' => $fields['excerpt'],
			'post_status'  => 'publish',
		);

		if ( 'update' === $action && $post_id ) {
			$post_data['ID'] = $post_id;
			$event_id = wp_update_post( $post_data );
		} else {
			$event_id = wp_insert_post( $post_data );
		}

		if ( is_wp_error( $event_id ) || ! $event_id ) {
			wp_die( 'Failed to save event.' );
		}

		// Save Meta
		update_post_meta( $event_id, 'KE_event_status', $fields['status'] );
		update_post_meta( $event_id, 'KE_event_date', $fields['event_date'] );
		update_post_meta( $event_id, 'KE_event_end_date', $fields['event_end_date'] );
		update_post_meta( $event_id, 'KE_event_time', $fields['event_time'] );
		update_post_meta( $event_id, 'KE_event_end_time', $fields['event_end_time'] );
		update_post_meta( $event_id, 'KE_event_venue_id', $final_venue_id );
		update_post_meta( $event_id, 'KE_event_organizer_name', $fields['organizer_name'] );
		update_post_meta( $event_id, 'KE_event_address', $fields['address'] );
		update_post_meta( $event_id, 'KE_event_phone', $fields['phone'] );
		update_post_meta( $event_id, 'KE_event_official_url', $fields['official_url'] );
		update_post_meta( $event_id, 'KE_event_source_url', $fields['source_url'] );
		
		// Phase 4 Metadata
		update_post_meta( $event_id, 'KE_event_source_name', sanitize_text_field( $_POST['source_name'] ?? '' ) );
		update_post_meta( $event_id, 'KE_event_canonical_url', esc_url_raw( $_POST['canonical_url'] ?? '' ) );
		update_post_meta( $event_id, 'KE_event_import_parser', sanitize_text_field( $_POST['parser_name'] ?? '' ) );
		update_post_meta( $event_id, 'KE_event_import_confidence', intval( $_POST['parser_confidence'] ?? 0 ) );
		update_post_meta( $event_id, 'KE_event_raw_date_text', sanitize_text_field( $_POST['raw_date_text'] ?? '' ) );
		update_post_meta( $event_id, 'KE_event_raw_location_text', sanitize_text_field( $_POST['raw_location_text'] ?? '' ) );
		
		update_post_meta( $event_id, 'KE_event_last_verified', current_time( 'Y-m-d' ) );
		update_post_meta( $event_id, 'KE_event_last_imported_at', current_time( 'mysql' ) );

		// Save hash for duplication detection
		$hash = KE_Duplicates::get_instance()->generate_hash( array(
			'title'      => $fields['title'],
			'event_date' => $fields['event_date'],
			'venue_name' => $fields['venue_name'],
		) );
		update_post_meta( $event_id, 'KE_event_import_hash', $hash );

		// Assign Taxonomies
		if ( $fields['category'] ) wp_set_object_terms( $event_id, $fields['category'], 'event_category' );
		if ( $fields['city'] ) wp_set_object_terms( $event_id, $fields['city'], 'event_city' );
		if ( $fields['area'] ) wp_set_object_terms( $event_id, $fields['area'], 'event_area' );

		// Image Sideloading
		if ( ! empty( $fields['image_url'] ) && ( isset( $_POST['sideload_image'] ) || get_option('ke_import_settings')['auto_sideload_image'] ) ) {
			$this->sideload_image( $fields['image_url'], $event_id, $fields['title'] );
		}

		// Log final result
		KE_Logs::get_instance()->log( array(
			'source_url' => $fields['source_url'],
			'post_id'    => $event_id,
			'status'     => ( 'update' === $action ) ? 'updated' : 'created',
			'message'    => sprintf( 'Event %s successfully.', ( 'update' === $action ) ? 'updated' : 'created' ),
		) );

		// Redirect to edit screen
		wp_redirect( admin_url( 'post.php?post=' . $event_id . '&action=edit' ) );
		exit;
	}

	/**
	 * Sideload image to media library and set as featured
	 */
	private function sideload_image( $file_url, $post_id, $title ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$att_id = media_sideload_image( $file_url, $post_id, $title, 'id' );
		if ( ! is_wp_error( $att_id ) ) {
			set_post_thumbnail( $post_id, $att_id );
		}
	}
}
KE_Import_URL::get_instance();

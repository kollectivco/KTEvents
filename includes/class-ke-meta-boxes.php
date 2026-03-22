<?php
/**
 * Kontentainment Events Meta Boxes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Meta_Boxes {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	/**
	 * Add Meta Boxes
	 */
	public function add_meta_boxes() {
		// Event Meta Boxes
		add_meta_box(
			'ke_event_details',
			'Event Details',
			array( $this, 'render_event_details' ),
			'event',
			'normal',
			'high'
		);

		add_meta_box(
			'ke_event_status_featured',
			'Status & Featured',
			array( $this, 'render_event_status_featured' ),
			'event',
			'side',
			'default'
		);

		// Venue Meta Boxes
		add_meta_box(
			'ke_venue_details',
			'Venue Details',
			array( $this, 'render_venue_details' ),
			'venue',
			'normal',
			'high'
		);
	}

	/**
	 * Render Event Details Meta Box
	 */
	public function render_event_details( $post ) {
		wp_nonce_field( 'ke_save_meta', 'ke_meta_nonce' );

		$values = get_post_custom( $post->ID );
		$venue_id = isset( $values['KE_event_venue_id'] ) ? $values['KE_event_venue_id'][0] : '';
		$event_date = isset( $values['KE_event_date'] ) ? $values['KE_event_date'][0] : '';
		$event_end_date = isset( $values['KE_event_end_date'] ) ? $values['KE_event_end_date'][0] : '';
		$event_time = isset( $values['KE_event_time'] ) ? $values['KE_event_time'][0] : '';
		$event_end_time = isset( $values['KE_event_end_time'] ) ? $values['KE_event_end_time'][0] : '';
		$organizer = isset( $values['KE_event_organizer_name'] ) ? $values['KE_event_organizer_name'][0] : '';
		$address = isset( $values['KE_event_address'] ) ? $values['KE_event_address'][0] : '';
		$phone = isset( $values['KE_event_phone'] ) ? $values['KE_event_phone'][0] : '';
		$official_url = isset( $values['KE_event_official_url'] ) ? $values['KE_event_official_url'][0] : '';
		$source_url = isset( $values['KE_event_source_url'] ) ? $values['KE_event_source_url'][0] : '';
		$last_verified = isset( $values['KE_event_last_verified'] ) ? $values['KE_event_last_verified'][0] : '';
		$internal_notes = isset( $values['KE_event_internal_notes'] ) ? $values['KE_event_internal_notes'][0] : '';

		// Get all venues for dropdown
		$venues = get_posts( array( 'post_type' => 'venue', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		?>
		<div class="ke-admin-form">
			<p>
				<label for="KE_event_venue_id"><strong>Venue</strong></label><br>
				<select name="KE_event_venue_id" id="KE_event_venue_id" class="widefat">
					<option value="">Select a Venue</option>
					<?php foreach ( $venues as $venue ) : ?>
						<option value="<?php echo $venue->ID; ?>" <?php selected( $venue_id, $venue->ID ); ?>><?php echo esc_html( $venue->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<div style="display: flex; gap: 20px;">
				<p style="flex: 1;">
					<label for="KE_event_date"><strong>Start Date</strong></label><br>
					<input type="date" name="KE_event_date" id="KE_event_date" value="<?php echo esc_attr( $event_date ); ?>" class="widefat">
				</p>
				<p style="flex: 1;">
					<label for="KE_event_end_date"><strong>End Date</strong></label><br>
					<input type="date" name="KE_event_end_date" id="KE_event_end_date" value="<?php echo esc_attr( $event_end_date ); ?>" class="widefat">
				</p>
			</div>

			<div style="display: flex; gap: 20px;">
				<p style="flex: 1;">
					<label for="KE_event_time"><strong>Start Time</strong></label><br>
					<input type="time" name="KE_event_time" id="KE_event_time" value="<?php echo esc_attr( $event_time ); ?>" class="widefat">
				</p>
				<p style="flex: 1;">
					<label for="KE_event_end_time"><strong>End Time</strong></label><br>
					<input type="time" name="KE_event_end_time" id="KE_event_end_time" value="<?php echo esc_attr( $event_end_time ); ?>" class="widefat">
				</p>
			</div>

			<p>
				<label for="KE_event_organizer_name"><strong>Organizer Name</strong></label><br>
				<input type="text" name="KE_event_organizer_name" id="KE_event_organizer_name" value="<?php echo esc_attr( $organizer ); ?>" class="widefat">
			</p>

			<p>
				<label for="KE_event_address"><strong>Address</strong></label><br>
				<input type="text" name="KE_event_address" id="KE_event_address" value="<?php echo esc_attr( $address ); ?>" class="widefat">
			</p>

			<p>
				<label for="KE_event_phone"><strong>Phone</strong></label><br>
				<input type="text" name="KE_event_phone" id="KE_event_phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat">
			</p>

			<p>
				<label for="KE_event_official_url"><strong>Official URL</strong></label><br>
				<input type="url" name="KE_event_official_url" id="KE_event_official_url" value="<?php echo esc_attr( $official_url ); ?>" class="widefat">
			</p>

			<p>
				<label for="KE_event_source_url"><strong>Source URL</strong></label><br>
				<input type="url" name="KE_event_source_url" id="KE_event_source_url" value="<?php echo esc_attr( $source_url ); ?>" class="widefat">
			</p>

			<p>
				<label for="KE_event_last_verified"><strong>Last Verified Date</strong></label><br>
				<input type="date" name="KE_event_last_verified" id="KE_event_last_verified" value="<?php echo esc_attr( $last_verified ); ?>" class="widefat">
			</p>

			<p>
				<label for="KE_event_internal_notes"><strong>Internal Notes (Admin Only)</strong></label><br>
				<textarea name="KE_event_internal_notes" id="KE_event_internal_notes" rows="4" class="widefat"><?php echo esc_textarea( $internal_notes ); ?></textarea>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Event Status & Featured Meta Box
	 */
	public function render_event_status_featured( $post ) {
		$values = get_post_custom( $post->ID );
		$status = isset( $values['KE_event_status'] ) ? $values['KE_event_status'][0] : 'upcoming';
		$featured = isset( $values['KE_event_featured'] ) ? (bool) $values['KE_event_featured'][0] : false;
		$editor_pick = isset( $values['KE_event_editor_pick'] ) ? (bool) $values['KE_event_editor_pick'][0] : false;
		?>
		<p>
			<label for="KE_event_status"><strong>Event Status</strong></label><br>
			<select name="KE_event_status" id="KE_event_status" class="widefat">
				<option value="upcoming" <?php selected( $status, 'upcoming' ); ?>>Upcoming</option>
				<option value="ongoing" <?php selected( $status, 'ongoing' ); ?>>Ongoing</option>
				<option value="past" <?php selected( $status, 'past' ); ?>>Past</option>
				<option value="cancelled" <?php selected( $status, 'cancelled' ); ?>>Cancelled</option>
				<option value="postponed" <?php selected( $status, 'postponed' ); ?>>Postponed</option>
			</select>
		</p>
		<hr>
		<p>
			<label>
				<input type="checkbox" name="KE_event_featured" value="1" <?php checked( $featured, true ); ?>>
				<strong>Featured Event</strong>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" name="KE_event_editor_pick" value="1" <?php checked( $editor_pick, true ); ?>>
				<strong>Editor's Pick</strong>
			</label>
		</p>
		<?php
	}

	/**
	 * Render Venue Details Meta Box
	 */
	public function render_venue_details( $post ) {
		wp_nonce_field( 'ke_save_meta', 'ke_meta_nonce' );

		$values = get_post_custom( $post->ID );
		$arabic_name = isset( $values['KE_venue_arabic_name'] ) ? $values['KE_venue_arabic_name'][0] : '';
		$english_name = isset( $values['KE_venue_english_name'] ) ? $values['KE_venue_english_name'][0] : '';
		$address = isset( $values['KE_venue_address'] ) ? $values['KE_venue_address'][0] : '';
		$phone = isset( $values['KE_venue_phone'] ) ? $values['KE_venue_phone'][0] : '';
		$website = isset( $values['KE_venue_website'] ) ? $values['KE_venue_website'][0] : '';
		$instagram = isset( $values['KE_venue_instagram'] ) ? $values['KE_venue_instagram'][0] : '';
		$map_url = isset( $values['KE_venue_map_url'] ) ? $values['KE_venue_map_url'][0] : '';
		$lat = isset( $values['KE_venue_lat'] ) ? $values['KE_venue_lat'][0] : '';
		$lng = isset( $values['KE_venue_lng'] ) ? $values['KE_venue_lng'][0] : '';
		$short_desc = isset( $values['KE_venue_short_description'] ) ? $values['KE_venue_short_description'][0] : '';

		// Get terms for the venue
		$assigned_gov = wp_get_object_terms( $post->ID, 'event_governorate', array( 'fields' => 'ids' ) );
		$assigned_city = wp_get_object_terms( $post->ID, 'event_city', array( 'fields' => 'ids' ) );
		
		$governorates = get_terms( 'event_governorate', array( 'hide_empty' => false ) );
		$cities = get_terms( 'event_city', array( 'hide_empty' => false ) );
		?>
		<div class="ke-admin-form">
			<div style="display: flex; gap: 20px;">
				<p style="flex: 1;">
					<label for="KE_venue_arabic_name"><strong>Arabic Name</strong></label><br>
					<input type="text" name="KE_venue_arabic_name" id="KE_venue_arabic_name" value="<?php echo esc_attr( $arabic_name ); ?>" class="widefat">
				</p>
				<p style="flex: 1;">
					<label for="KE_venue_english_name"><strong>English Name</strong></label><br>
					<input type="text" name="KE_venue_english_name" id="KE_venue_english_name" value="<?php echo esc_attr( $english_name ); ?>" class="widefat">
				</p>
			</div>

			<div style="display: flex; gap: 20px;">
				<p style="flex: 1;">
					<label for="ke_venue_governorate"><strong>Governorate (Egypt)</strong></label><br>
					<select name="ke_venue_governorate" id="ke_venue_governorate" class="widefat ke-governorate-select">
						<option value="">-- Select Governorate --</option>
						<?php foreach ( $governorates as $gov ) : ?>
							<option value="<?php echo $gov->term_id; ?>" <?php selected( in_array( $gov->term_id, $assigned_gov ), true ); ?>><?php echo esc_html( $gov->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p style="flex: 1;">
					<label for="ke_venue_city"><strong>City / Region</strong></label><br>
					<select name="ke_venue_city" id="ke_venue_city" class="widefat ke-city-select">
						<option value="">-- Select City --</option>
						<?php foreach ( $cities as $city ) : ?>
							<option value="<?php echo $city->term_id; ?>" <?php selected( in_array( $city->term_id, $assigned_city ), true ); ?>><?php echo esc_html( $city->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			</div>

			<p>
				<label for="KE_venue_short_description"><strong>Short Description</strong></label><br>
				<textarea name="KE_venue_short_description" id="KE_venue_short_description" rows="2" class="widefat"><?php echo esc_textarea( $short_desc ); ?></textarea>
			</p>

			<p>
				<label for="KE_venue_address"><strong>Address</strong></label><br>
				<input type="text" name="KE_venue_address" id="KE_venue_address" value="<?php echo esc_attr( $address ); ?>" class="widefat">
			</p>

			<p>
				<label for="KE_venue_phone"><strong>Phone</strong></label><br>
				<input type="text" name="KE_venue_phone" id="KE_venue_phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat">
			</p>

			<p>
				<label for="KE_venue_website"><strong>Website URL</strong></label><br>
				<input type="url" name="KE_venue_website" id="KE_venue_website" value="<?php echo esc_attr( $website ); ?>" class="widefat">
			</p>

			<p>
				<label for="KE_venue_instagram"><strong>Instagram URL</strong></label><br>
				<input type="url" name="KE_venue_instagram" id="KE_venue_instagram" value="<?php echo esc_attr( $instagram ); ?>" class="widefat">
			</p>

			<p>
				<label for="KE_venue_map_url"><strong>Google Maps URL</strong></label><br>
				<input type="url" name="KE_venue_map_url" id="KE_venue_map_url" value="<?php echo esc_attr( $map_url ); ?>" class="widefat">
			</p>

			<div style="display: flex; gap: 20px;">
				<p style="flex: 1;">
					<label for="KE_venue_lat"><strong>Latitude</strong></label><br>
					<input type="text" name="KE_venue_lat" id="KE_venue_lat" value="<?php echo esc_attr( $lat ); ?>" class="widefat">
				</p>
				<p style="flex: 1;">
					<label for="KE_venue_lng"><strong>Longitude</strong></label><br>
					<input type="text" name="KE_venue_lng" id="KE_venue_lng" value="<?php echo esc_attr( $lng ); ?>" class="widefat">
				</p>
			</div>
		</div>
		<?php
	}
}
KE_Meta_Boxes::get_instance();

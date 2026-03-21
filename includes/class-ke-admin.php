<?php
/**
 * Kontentainment Events Admin UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Admin {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Event Columns
		add_filter( 'manage_event_posts_columns', array( $this, 'event_columns' ) );
		add_action( 'manage_event_posts_custom_column', array( $this, 'event_column_content' ), 10, 2 );

		// Venue Columns
		add_filter( 'manage_venue_posts_columns', array( $this, 'venue_columns' ) );
		add_action( 'manage_venue_posts_custom_column', array( $this, 'venue_column_content' ), 10, 2 );

		// Menu Items
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
	}

	/**
	 * Add Menu Pages
	 */
	public function add_menu_pages() {
		add_submenu_page(
			'edit.php?post_type=event',
			'Import from URL',
			'Import from URL',
			'edit_posts',
			'ke-import-url',
			array( $this, 'render_import_page' )
		);

		add_submenu_page(
			'edit.php?post_type=event',
			'Import Logs',
			'Import Logs',
			'edit_posts',
			'ke-import-logs',
			array( $this, 'render_logs_page' )
		);

		add_submenu_page(
			'edit.php?post_type=event',
			'Import Settings',
			'Import Settings',
			'manage_options',
			'ke-import-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render Import from URL Page
	 */
	public function render_import_page() {
		?>
		<div class="wrap ke-admin-wrapper">
			<h1>Import Event from URL</h1>
			<p>Paste a source URL (e.g., from an event site) to fetch and preview event data before importing.</p>

			<div class="ke-import-container">
				<form id="ke-fetch-preview-form">
					<?php wp_nonce_field( 'ke_import_nonce', 'ke_import_nonce' ); ?>
					<table class="form-table">
						<tr>
							<th scope="row"><label for="source_url">Source URL</label></th>
							<td>
								<input type="url" id="source_url" name="source_url" class="large-text" placeholder="https://example.com/events/concert-2026" required>
								<p class="description">Paste the full URL of the event page.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Import Logic</th>
							<td>
								<div class="ke-inline-fields">
									<select name="default_category_id">
										<option value="">Default Category</option>
										<?php foreach ( get_terms('event_category', array('hide_empty' => false)) as $term ) : ?>
											<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
										<?php endforeach; ?>
									</select>
									<select name="default_city_id">
										<option value="">Default City</option>
										<?php foreach ( get_terms('event_city', array('hide_empty' => false)) as $term ) : ?>
											<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
										<?php endforeach; ?>
									</select>
									<label style="margin-left:15px;"><input type="checkbox" name="auto_create_venue" value="1" checked> Auto-create missing venues</label>
								</div>
							</td>
						</tr>
					</table>
					<p class="submit">
						<button type="submit" class="button button-primary" id="ke-fetch-btn">Fetch & Preview</button>
						<span class="spinner" id="ke-fetch-spinner"></span>
					</p>
				</form>

				<div id="ke-import-error" class="notice notice-error" style="display:none;"></div>

				<div id="ke-import-preview-wrapper" style="display:none;">
					<hr>
					<div class="ke-preview-header">
						<h2 id="ke-preview-heading">Import Preview</h2>
						<div id="ke-duplicate-notice" class="notice notice-warning inline" style="display:none;"></div>
					</div>

					<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
						<?php wp_nonce_field( 'ke_save_import', 'ke_import_save_nonce' ); ?>
						<input type="hidden" name="action" value="ke_save_imported_event">
						<input type="hidden" name="source_url" id="preview_source_url">
						<input type="hidden" name="source_name" id="preview_source_name">
						<input type="hidden" name="parser_name" id="preview_parser_name">
						<input type="hidden" name="parser_confidence" id="preview_parser_confidence_field">
						<input type="hidden" name="canonical_url" id="preview_canonical_url">
						<input type="hidden" name="image_url" id="preview_image_url">
						<input type="hidden" name="raw_date_text" id="preview_raw_date_text">
						<input type="hidden" name="raw_location_text" id="preview_raw_location_text">
						<input type="hidden" name="ke_import_action" id="ke_import_action" value="create">
						<input type="hidden" name="ke_existing_post_id" id="ke_existing_post_id" value="">

						<div class="ke-preview-grid">
							<!-- Main Content Area -->
							<div class="ke-preview-main">
								<div class="ke-card-box">
									<h3>Event Details</h3>
									<div class="ke-field-group">
										<label>Event Title</label>
										<input type="text" name="title" id="preview_title" class="large-text" required>
									</div>

									<div class="ke-field-group">
										<label>Description</label>
										<?php wp_editor( '', 'preview_description', array( 'textarea_name' => 'description', 'textarea_rows' => 12, 'media_buttons' => false ) ); ?>
									</div>

									<div class="ke-field-group">
										<label>Short Summary</label>
										<textarea name="excerpt" id="preview_excerpt" rows="2" class="large-text"></textarea>
									</div>

									<div class="ke-field-row">
										<div class="ke-field-item">
											<label>Status</label>
											<select name="status" id="preview_status" class="widefat">
												<option value="upcoming">Upcoming</option>
												<option value="ongoing">Ongoing</option>
												<option value="past">Past</option>
												<option value="cancelled">Cancelled</option>
											</select>
										</div>
										<div class="ke-field-item">
											<label>Event Category</label>
											<select name="category_id" id="preview_category_id" class="widefat">
												<option value="">Select Category</option>
												<?php foreach ( get_terms('event_category', array('hide_empty' => false)) as $term ) : ?>
													<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
								</div>

								<div class="ke-card-box">
									<h3>Schedule / Timing</h3>
									<div class="ke-field-row">
										<div class="ke-field-item">
											<label>Start Date</label>
											<input type="date" name="event_date" id="preview_event_date" class="widefat">
										</div>
										<div class="ke-field-item">
											<label>Start Time</label>
											<input type="time" name="event_time" id="preview_event_time" class="widefat">
										</div>
									</div>
									<div class="ke-field-row">
										<div class="ke-field-item">
											<label>End Date</label>
											<input type="date" name="event_end_date" id="preview_event_end_date" class="widefat">
										</div>
										<div class="ke-field-item">
											<label>End Time</label>
											<input type="time" name="event_end_time" id="preview_event_end_time" class="widefat">
										</div>
									</div>
									<p class="description" id="ke-raw-date-suggestion"></p>
								</div>
							</div>

							<!-- Sidebar Area -->
							<div class="ke-preview-sidebar">
								<div class="ke-card-box">
									<h3>Featured Image</h3>
									<div class="ke-image-preview-area">
										<div id="ke-preview-image-wrap">
											<img id="ke-preview-img-src" src="" style="width:100%; border-radius:4px; display:none;">
											<div id="ke-no-image" class="ke-placeholder-box">No image found</div>
										</div>
										<div style="margin-top:10px;">
											<label><input type="checkbox" name="sideload_image" value="1" checked> Sideload this image</label>
											<input type="url" name="manual_image_url" id="preview_manual_image_url" placeholder="Paste custom image URL..." class="widefat" style="margin-top:5px; font-size:11px;">
										</div>
									</div>
								</div>

								<div class="ke-card-box">
									<h3>Venue Information</h3>
									<div class="ke-field-group">
										<label>Venue Name</label>
										<input type="text" name="venue_name" id="preview_venue_name" class="widefat">
										<input type="hidden" name="venue_id" id="preview_venue_id">
										<div id="ke-venue-status" class="ke-mini-notice"></div>
									</div>

									<div class="ke-field-group">
										<label>City</label>
										<select name="city_id" id="preview_city_id" class="widefat">
											<option value="">Select City</option>
											<?php foreach ( get_terms('event_city', array('hide_empty' => false)) as $term ) : ?>
												<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
											<?php endforeach; ?>
										</select>
									</div>

									<div class="ke-field-group">
										<label>Area</label>
										<select name="area_id" id="preview_area_id" class="widefat">
											<option value="">Select Area</option>
											<?php foreach ( get_terms('event_area', array('hide_empty' => false)) as $term ) : ?>
												<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
											<?php endforeach; ?>
										</select>
									</div>

									<div class="ke-field-group">
										<label>Address</label>
										<input type="text" name="address" id="preview_address" class="widefat">
									</div>
								</div>

								<div class="ke-card-box">
									<h3>Additional Info</h3>
									<div class="ke-field-group">
										<label>Organizer</label>
										<input type="text" name="organizer_name" id="preview_organizer_name" class="widefat">
									</div>
									<div class="ke-field-group">
										<label>Phone / Contact</label>
										<input type="text" name="phone" id="preview_phone" class="widefat">
									</div>
									<div class="ke-field-group">
										<label>Official URL</label>
										<input type="url" name="official_url" id="preview_official_url" class="widefat">
									</div>
								</div>

								<div class="ke-card-box ke-diagnostics-box">
									<h3>Diagnostics</h3>
									<p><strong>Parser:</strong> <span id="ke-parser-name">-</span></p>
									<div class="ke-confidence-wrap">
										<label>Confidence Score</label>
										<div class="ke-confidence-bar"><div id="ke-confidence-fill" style="width: 0%"></div></div>
										<span id="ke-parser-confidence">0%</span>
									</div>
									<div id="ke-parser-warnings" class="ke-warnings-list"></div>
								</div>

								<div class="ke-actions-box">
									<button type="submit" class="button button-primary button-hero widefat" id="ke-save-import-btn">Finalize Import</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Logs Page
	 */
	public function render_logs_page() {
		$logs = KE_Logs::get_instance()->get_logs( 50 );
		?>
		<div class="wrap">
			<h1>Import Logs</h1>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Timestamp</th>
						<th>Source URL</th>
						<th>Parser</th>
						<th>Confidence</th>
						<th>Status</th>
						<th>Result</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $logs ) : foreach ( $logs as $log ) : ?>
						<tr>
							<td><?php echo esc_html( $log->timestamp ); ?></td>
							<td><a href="<?php echo esc_url( $log->source_url ); ?>" target="_blank"><?php echo esc_html( wp_trim_words( $log->source_url, 10 ) ); ?></a></td>
							<td><?php echo esc_html( $log->parser_used ); ?></td>
							<td><?php echo esc_html( $log->parser_confidence ); ?>%</td>
							<td><span class="ke-status-tag ke-status-<?php echo esc_attr($log->status); ?>"><?php echo esc_html( $log->status ); ?></span></td>
							<td>
								<?php if ( $log->post_id ) : ?>
									<a href="<?php echo get_edit_post_link( $log->post_id ); ?>">View Event</a>
								<?php else : ?>
									-
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; else : ?>
						<tr><td colspan="6">No logs found.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<style>
			.ke-status-tag { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
			.ke-status-created { background: #dcfce7; color: #166534; }
			.ke-status-updated { background: #dbeafe; color: #1e40af; }
			.ke-status-failed { background: #fee2e2; color: #991b1b; }
			.ke-status-previewed { background: #f3f4f6; color: #374151; }
		</style>
		<?php
	}

	/**
	 * Render Settings Page
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1>Import Settings</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'ke_import_settings_group' );
				do_settings_sections( 'ke-import-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Columns Logic (Existing from Phase 1)
	 */
	public function event_columns( $columns ) {
		$new_columns = array(
			'cb'         => $columns['cb'],
			'title'      => $columns['title'],
			'ke_date'    => 'Event Date',
			'ke_status'  => 'Status',
			'ke_venue'   => 'Venue',
			'ke_city'    => 'City',
			'ke_area'    => 'Area',
			'ke_featured' => 'Featured',
			'date'       => $columns['date'],
		);
		return $new_columns;
	}

	public function event_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'ke_date':
				$date = get_post_meta( $post_id, 'KE_event_date', true );
				echo esc_html( $date ? date_i18n( get_option( 'date_format' ), strtotime( $date ) ) : '—' );
				break;
			case 'ke_status':
				$status = get_post_meta( $post_id, 'KE_event_status', true );
				echo esc_html( ucfirst( $status ?: 'Upcoming' ) );
				break;
			case 'ke_venue':
				$venue_id = get_post_meta( $post_id, 'KE_event_venue_id', true );
				if ( $venue_id ) {
					echo '<a href="' . get_edit_post_link( $venue_id ) . '">' . esc_html( get_the_title( $venue_id ) ) . '</a>';
				} else {
					echo '—';
				}
				break;
			case 'ke_city':
				$cities = get_the_term_list( $post_id, 'event_city', '', ', ', '' );
				echo $cities ?: '—';
				break;
			case 'ke_area':
				$areas = get_the_term_list( $post_id, 'event_area', '', ', ', '' );
				echo $areas ?: '—';
				break;
			case 'ke_featured':
				$featured = get_post_meta( $post_id, 'KE_event_featured', true );
				echo $featured ? '<span class="dashicons dashicons-star-filled" style="color: #ffb900;"></span>' : '—';
				break;
		}
	}

	public function venue_columns( $columns ) {
		$new_columns = array(
			'cb'        => $columns['cb'],
			'title'     => $columns['title'],
			'ke_city'   => 'City',
			'ke_area'   => 'Area',
			'ke_phone'  => 'Phone',
			'ke_website' => 'Website',
			'date'      => $columns['date'],
		);
		return $new_columns;
	}

	public function venue_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'ke_city':
				echo get_the_term_list( $post_id, 'event_city', '', ', ', '' ) ?: '—';
				break;
			case 'ke_area':
				echo get_the_term_list( $post_id, 'event_area', '', ', ', '' ) ?: '—';
				break;
			case 'ke_phone':
				echo esc_html( get_post_meta( $post_id, 'KE_venue_phone', true ) ?: '—' );
				break;
			case 'ke_website':
				$website = get_post_meta( $post_id, 'KE_venue_website', true );
				if ( $website ) {
					echo '<a href="' . esc_url( $website ) . '" target="_blank">Visit Site</a>';
				} else {
					echo '—';
				}
				break;
		}
	}
}
KE_Admin::get_instance();

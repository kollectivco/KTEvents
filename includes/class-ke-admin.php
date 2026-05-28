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

		// Translation AJAX Actions
		add_action( 'wp_ajax_ke_save_translations', array( $this, 'ajax_save_translations' ) );
		add_action( 'wp_ajax_ke_auto_translate', array( $this, 'ajax_auto_translate' ) );
		add_action( 'wp_ajax_ke_reset_translations', array( $this, 'ajax_reset_translations' ) );

		// Global high-performance Translation override
		add_filter( 'gettext', array( __CLASS__, 'translate_override' ), 20, 3 );
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

		add_submenu_page(
			'edit.php?post_type=event',
			'Quick Translation',
			'Quick Translation',
			'manage_options',
			'ke-translation',
			array( $this, 'render_translation_page' )
		);
	}

	/**
	 * Render Import from URL Page
	 */
	public function render_import_page() {
		$categories = get_terms('event_category', array('hide_empty' => false));
		$governorates = get_terms('event_governorate', array('hide_empty' => false));
		$venues = get_posts(array('post_type' => 'venue', 'posts_per_page' => -1, 'post_status' => 'publish'));
		?>
		<div class="wrap ke-admin-wrapper">
			<div class="ke-admin-header">
				<h1>Import Event from URL</h1>
				<p class="description">Paste a source URL to audit and import event data into your library.</p>
			</div>

			<!-- STEP 1: URL Input Card -->
			<div class="ke-card ke-card-full">
				<form id="ke-fetch-preview-form" class="ke-fetch-form">
					<?php wp_nonce_field( 'ke_import_nonce', 'ke_import_nonce' ); ?>
					<div class="ke-fetch-input-wrap">
						<div class="ke-input-group">
							<label for="source_url">Source Event URL</label>
							<div class="ke-input-with-button">
								<input type="url" id="source_url" name="source_url" placeholder="https://scenenow.com/Events/Detail/..." required>
								<button type="submit" class="button button-primary button-hero" id="ke-fetch-btn">Fetch & Audit Data</button>
							</div>
							<p class="description">We support SceneNow, Cairo Jazz Club, and generic schema.org sources.</p>
						</div>
					</div>
					
					<div class="ke-fetch-options">
						<div class="ke-mini-field">
							<label>Default Category</label>
							<select name="default_category_id">
								<option value="">Auto-detect</option>
								<?php foreach ( $categories as $term ) : ?>
									<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="ke-mini-field">
							<label><input type="checkbox" name="auto_create_venue" value="1" checked> Auto-create missing venues</label>
						</div>
						<div class="spinner" id="ke-fetch-spinner"></div>
					</div>
				</form>
			</div>

			<div id="ke-import-error" class="notice notice-error" style="display:none; margin: 20px 0;"></div>

			<!-- STEP 2: Preview Area (Card Based) -->
			<div id="ke-import-preview-wrapper" style="display:none;" class="ke-preview-container">
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

					<div class="ke-dashboard-layout">
						<!-- Main Content Column -->
						<div class="ke-main-col">
							
							<!-- Event Content Card -->
							<div class="ke-card">
								<div class="ke-card-header">
									<h2><span class="dashicons dashicons-edit"></span> Event Details</h2>
									<div id="ke-duplicate-notice" class="ke-badge-notice" style="display:none;"></div>
								</div>
								<div class="ke-card-body">
									<div class="ke-form-row">
										<label>Event Title</label>
										<input type="text" name="title" id="preview_title" class="large-text" required>
									</div>

									<div class="ke-form-row">
										<label>Description</label>
										<?php wp_editor( '', 'preview_description', array( 'textarea_name' => 'description', 'textarea_rows' => 10, 'media_buttons' => false ) ); ?>
									</div>

									<div class="ke-form-row">
										<label>Extended Summary (Excerpt)</label>
										<textarea name="excerpt" id="preview_excerpt" rows="2" class="large-text"></textarea>
									</div>
								</div>
							</div>

							<!-- Schedule Card -->
							<div class="ke-card">
								<div class="ke-card-header">
									<h2><span class="dashicons dashicons-calendar-alt"></span> Schedule & Timing</h2>
								</div>
								<div class="ke-card-body">
									<div class="ke-grid-2">
										<div class="ke-form-row">
											<label>Start Date</label>
											<input type="date" name="event_date" id="preview_event_date" class="widefat">
										</div>
										<div class="ke-form-row">
											<label>Start Time</label>
											<input type="time" name="event_time" id="preview_event_time" class="widefat">
										</div>
										<div class="ke-form-row">
											<label>End Date</label>
											<input type="date" name="event_end_date" id="preview_event_end_date" class="widefat">
										</div>
										<div class="ke-form-row">
											<label>End Time</label>
											<input type="time" name="event_end_time" id="preview_event_end_time" class="widefat">
										</div>
									</div>
									<div id="ke-date-source-alert" class="ke-info-note"></div>
								</div>
							</div>

							<!-- Venue Card: SELECT OR CREATE -->
							<div class="ke-card ke-venue-card">
								<div class="ke-card-header">
									<h2><span class="dashicons dashicons-location"></span> Venue Assignment</h2>
								</div>
								<div class="ke-card-body">
									<div class="ke-grid-2">
										<div class="ke-form-row">
											<label>Governorate</label>
											<select name="governorate_id" id="preview_governorate_id" class="widefat">
												<option value="">-- Choose Governorate --</option>
												<?php foreach ($governorates as $term) : ?>
													<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="ke-form-row">
											<label>City / Town</label>
											<select name="city_id" id="preview_city_id" class="widefat">
												<option value="">-- Choose City --</option>
												<?php foreach (get_terms('event_city', array('hide_empty' => false)) as $term) : ?>
													<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
									<div id="ke-location-match-status"></div>

									<hr>

									<div class="ke-venue-toggle-wrap">
										<label class="ke-radio-label">
											<input type="radio" name="venue_mode" value="existing" checked> 
											<span>Use Existing Venue</span>
										</label>
										<label class="ke-radio-label">
											<input type="radio" name="venue_mode" value="new"> 
											<span>Create New Venue Inline</span>
										</label>
									</div>

									<!-- Mode: Existing -->
									<div id="ke-venue-mode-existing" class="ke-venue-mode-content">
										<div class="ke-form-row">
											<label>Select Venue from Library</label>
											<select name="venue_id" id="preview_venue_id" class="widefat ke-select2">
												<option value="">-- Search for a venue --</option>
												<?php foreach ($venues as $v) : ?>
													<option value="<?php echo $v->ID; ?>"><?php echo esc_html($v->post_title); ?></option>
												<?php endforeach; ?>
											</select>
											<div id="ke-venue-match-status"></div>
										</div>
									</div>

									<!-- Mode: New -->
									<div id="ke-venue-mode-new" class="ke-venue-mode-content" style="display:none;">
										<div class="ke-grid-2">
											<div class="ke-form-row">
												<label>Venue Name</label>
												<input type="text" name="new_venue_name" id="preview_venue_name" class="widefat">
											</div>
											<div class="ke-form-row">
												<label>Phone / Contact</label>
												<input type="text" name="phone" id="preview_phone" class="widefat">
											</div>
										</div>
										<div class="ke-form-row">
											<label>Address</label>
											<input type="text" name="address" id="preview_address" class="widefat">
										</div>
										<div class="ke-form-row">
											<label>Google Maps Link</label>
											<input type="url" name="official_url" id="preview_official_url" placeholder="https://maps.google.com/..." class="widefat">
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Sidebar Column -->
						<div class="ke-sidebar-col">
							
							<!-- Action Card -->
							<div class="ke-card ke-card-primary">
								<div class="ke-card-body">
									<button type="submit" class="button button-primary button-hero widefat" id="ke-save-import-btn">Finalize & Import Event</button>
									<p class="ke-safe-note"><span class="dashicons dashicons-shield"></span> All fields can be edited after import.</p>
								</div>
							</div>

							<!-- Image Card -->
							<div class="ke-card">
								<div class="ke-card-header"><h3>Featured Image</h3></div>
								<div class="ke-card-body">
									<div class="ke-image-preview">
										<img id="ke-preview-img-src" src="" style="display:none;">
										<div id="ke-no-image" class="ke-img-placeholder">No image detected</div>
									</div>
									<div class="ke-image-controls">
										<label><input type="checkbox" name="sideload_image" value="1" checked> Sideload Image</label>
										<input type="url" name="manual_image_url" id="preview_manual_image_url" placeholder="Paste custom URL..." class="widefat">
									</div>
								</div>
							</div>

							<!-- Location & Category Card -->
							<div class="ke-card">
								<div class="ke-card-header"><h3>Event Settings</h3></div>
								<div class="ke-card-body">
									<div class="ke-form-row">
										<label>Event Status</label>
										<select name="status" id="preview_status" class="widefat">
											<option value="upcoming" selected>Upcoming</option>
											<option value="ongoing">Ongoing</option>
											<option value="past">Past</option>
											<option value="cancelled">Cancelled</option>
										</select>
									</div>

									<div class="ke-form-row" style="display:flex; gap:15px; margin-bottom:15px;">
										<label class="ke-radio-label">
											<input type="checkbox" name="featured" value="1"> Featured
										</label>
										<label class="ke-radio-label">
											<input type="checkbox" name="editor_pick" value="1"> Editor Pick
										</label>
									</div>

									<div class="ke-form-row">
										<label>Category</label>
										<select name="category_id" id="preview_category_id" class="widefat">
											<option value="">-- Choose Category --</option>
											<?php foreach ($categories as $term) : ?>
												<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
							</div>

							<!-- Diagnostics Card -->
							<div class="ke-card ke-diagnostics-card">
								<div class="ke-card-header"><h3>Diagnostics</h3></div>
								<div class="ke-card-body">
									<div class="ke-diag-meta">
										<span>Parser: <strong id="ke-parser-name">-</strong></span>
									</div>
									<div class="ke-confidence-section">
										<div class="ke-confidence-label">Confidence: <span id="ke-parser-confidence">0%</span></div>
										<div class="ke-confidence-bar"><div id="ke-confidence-fill"></div></div>
									</div>
									<div id="ke-parser-warnings" class="ke-warnings-list"></div>
								</div>
							</div>

						</div>
					</div>
				</form>
			</div>
		</div>

		<!-- Egypt Data Bridge -->
		<script id="ke-egypt-data" type="application/json">
			<?php echo json_encode(KE_Egypt_Locations::get_governorates()); ?>
		</script>
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
			'ke_governorate' => 'Gov.',
			'ke_city'        => 'City',
			'ke_featured' => 'Featured',
			'date'       => $columns['date'],
		);
		return $new_columns;
	}

	public function event_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'ke_date':
				$date = get_post_meta( $post_id, 'KE_event_date', true );
				if ( is_array( $date ) ) $date = reset( $date );
				echo esc_html( ( $date && is_string( $date ) ) ? date_i18n( get_option( 'date_format' ), strtotime( $date ) ) : '—' );
				break;
			case 'ke_status':
				$status = get_post_meta( $post_id, 'KE_event_status', true );
				if ( is_array( $status ) ) $status = reset( $status );
				echo esc_html( ucfirst( (string)$status ?: 'Upcoming' ) );
				break;
			case 'ke_venue':
				$venue_id = get_post_meta( $post_id, 'KE_event_venue_id', true );
				if ( is_array( $venue_id ) ) $venue_id = reset( $venue_id );
				if ( $venue_id && get_post_status( $venue_id ) ) {
					echo '<a href="' . get_edit_post_link( $venue_id ) . '">' . esc_html( get_the_title( $venue_id ) ) . '</a>';
				} else {
					echo '—';
				}
				break;
			case 'ke_city':
				$cities = get_the_term_list( $post_id, 'event_city', '', ', ', '' );
				echo ( ! is_wp_error( $cities ) && ! empty( $cities ) ) ? $cities : '—';
				break;
			case 'ke_governorate':
				$govs = get_the_term_list( $post_id, 'event_governorate', '', ', ', '' );
				echo ( ! is_wp_error( $govs ) && ! empty( $govs ) ) ? $govs : '—';
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
				$terms = get_the_term_list( $post_id, 'event_city', '', ', ', '' );
				echo ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms : '—';
				break;
			case 'ke_area':
				$terms = get_the_term_list( $post_id, 'event_area', '', ', ', '' );
				echo ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms : '—';
				break;
			case 'ke_phone':
				$phone = get_post_meta( $post_id, 'KE_venue_phone', true );
				if ( is_array( $phone ) ) $phone = reset( $phone );
				echo esc_html( (string)$phone ?: '—' );
				break;
			case 'ke_website':
				$website = get_post_meta( $post_id, 'KE_venue_website', true );
				if ( is_array( $website ) ) $website = reset( $website );
				if ( $website && is_string( $website ) ) {
					echo '<a href="' . esc_url( $website ) . '" target="_blank">Visit Site</a>';
				} else {
					echo '—';
				}
				break;
		}
	}

	/**
	 * Dynamically scan plugin files for translatable strings matching 'kontentainment-events'
	 */
	public static function scan_plugin_strings() {
		$strings = array();
		$plugin_dir = plugin_dir_path( dirname( __FILE__ ) );
		
		// Recursively scan directories: templates, includes, and elementor
		$dirs_to_scan = array(
			$plugin_dir . 'templates',
			$plugin_dir . 'includes',
			$plugin_dir . 'elementor',
		);
		
		$files = array( $plugin_dir . 'kontentainment-events.php' );
		
		foreach ( $dirs_to_scan as $dir ) {
			if ( is_dir( $dir ) ) {
				$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) );
				foreach ( $iterator as $file ) {
					if ( $file->isFile() && 'php' === $file->getExtension() ) {
						$files[] = $file->getPathname();
					}
				}
			}
		}
		
		// Regex pattern to find localization strings using the 'kontentainment-events' text domain
		// Matches: __(), _e(), esc_html__(), esc_html_e(), esc_attr__(), esc_attr_e()
		$pattern = '/(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*([\'"])(.*?)\1\s*,\s*[\'"]kontentainment-events[\'"]\s*\)/s';
		
		foreach ( $files as $filepath ) {
			if ( ! file_exists( $filepath ) ) {
				continue;
			}
			$content = file_get_contents( $filepath );
			if ( preg_match_all( $pattern, $content, $matches ) ) {
				if ( ! empty( $matches[2] ) ) {
					foreach ( $matches[2] as $str ) {
						// Clean backslashes if any escaped quotes
						$str = stripslashes( $str );
						if ( ! empty( $str ) ) {
							$strings[ $str ] = '';
						}
					}
				}
			}
		}
		
		// Sort strings alphabetically
		ksort( $strings );
		return $strings;
	}

	/**
	 * Get Default Translations
	 */
	public static function get_default_translations() {
		// Exact list of localized strings used in our frontend templates
		$defaults = array(
			'LOAD MORE' => 'عرض المزيد',
			'Events' => 'الايفنتات',
			'Quick Choice' => 'اختيار سريع',
			'Upcoming' => 'اللي جاية',
			'Our Recommendations' => 'ترشيحاتنا',
			'Today' => 'النهارده',
			'Weekend' => 'الويك إند',
			'This week' => 'الأسبوع ده',
			'Filter more' => 'حدد أكتر',
			'Quick Location' => 'مكان سريع',
			'All events' => 'كل الايفنتات',
			'Topic / Search words' => 'الموضوع / كلمات البحث',
			'Search for events...' => 'ابحث عن ايفنتات...',
			'Sort by' => 'ترتيب حسب',
			'Upcoming first' => 'القادمة أولاً',
			'Recently added' => 'المضافة حديثاً',
			'Apply filters' => 'تطبيق الفلاتر',
			'No matching results' => 'مفيش نتائج مطابقة',
			'Try changing filters or search terms to find what you are looking for.' => 'جرب تغير الفلاتر أو كلمات البحث عشان تلاقي اللي بتدور عليه.',
			'Clear all filters' => 'مسح كل الفلاتر',
			'Upcoming Events' => 'الايفنتات القادمة',
			'Date' => 'التاريخ',
			'TBA' => 'يحدد لاحقاً',
			'Time' => 'الوقت',
			'Venue' => 'المكان',
			'Phone' => 'التليفون',
			'More in this venue' => 'المزيد في المكان ده',
			'More in this section' => 'المزيد في القسم ده',
			'Recommended events for you' => 'ايفنتات مقترحة ليك',
			'Address' => 'العنوان',
			'Website' => 'الموقع الإلكتروني',
			'Official Website' => 'الموقع الرسمي',
			'Get Directions' => 'احصل على الاتجاهات',
			'About Venue' => 'عن المكان',
			'No upcoming events scheduled at this venue currently.' => 'مفيش ايفنتات قادمة مجدولة في المكان ده حالياً.',
			'Recent Past Events' => 'الايفنتات السابقة مؤخراً',
		);

		// Dynamically scan for any extra strings in the plugin files to be fully future-proof and robust!
		$scanned = self::scan_plugin_strings();
		
		// Merge them, keeping defaults if already present
		foreach ( $scanned as $key => $val ) {
			if ( ! isset( $defaults[ $key ] ) ) {
				$defaults[ $key ] = '';
			}
		}

		return $defaults;
	}

	/**
	 * High-Performance Translation Override Filter
	 */
	public static function translate_override( $translated_text, $text, $domain ) {
		static $translations = null;
		if ( null === $translations ) {
			$translations = get_option( 'ke_quick_translations', array() );
			$defaults = self::get_default_translations();
			$translations = wp_parse_args( $translations, $defaults );
			
			// Automatically migrate existing saved database translations to use "ايفنتات" on the fly
			foreach ( $translations as $key => $val ) {
				if ( is_string( $val ) ) {
					$val = str_replace( 'الفعاليات', 'الايفنتات', $val );
					$val = str_replace( 'فعاليات', 'ايفنتات', $val );
					$translations[ $key ] = $val;
				}
			}
		}

		if ( 'kontentainment-events' === $domain ) {
			if ( isset( $translations[ $text ] ) && ! empty( $translations[ $text ] ) ) {
				return $translations[ $text ];
			}
		}

		return $translated_text;
	}

	/**
	 * AJAX Save Translations
	 */
	public function ajax_save_translations() {
		check_ajax_referer( 'ke_ajax_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$translations = isset( $_POST['translations'] ) ? (array) $_POST['translations'] : array();
		$sanitized = array();

		foreach ( $translations as $key => $val ) {
			$sanitized[ sanitize_text_field( $key ) ] = sanitize_text_field( $val );
		}

		update_option( 'ke_quick_translations', $sanitized );
		wp_send_json_success( 'Translations saved successfully!' );
	}

	/**
	 * AJAX Auto Translate using Google Translate client API
	 */
	public function ajax_auto_translate() {
		check_ajax_referer( 'ke_ajax_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$strings = isset( $_POST['strings'] ) ? (array) $_POST['strings'] : array();
		$translations = array();

		foreach ( $strings as $string ) {
			$string = sanitize_text_field( $string );
			if ( empty( $string ) ) {
				continue;
			}

			// Call free public Google Translate API (client gtx)
			$url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=ar&dt=t&q=' . rawurlencode( $string );
			$response = wp_remote_get( $url );

			if ( ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body );
				if ( is_array( $data ) && isset( $data[0][0][0] ) ) {
					$translations[ $string ] = $data[0][0][0];
				}
			}
		}

		wp_send_json_success( $translations );
	}

	/**
	 * AJAX Reset to Default translations
	 */
	public function ajax_reset_translations() {
		check_ajax_referer( 'ke_ajax_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$defaults = self::get_default_translations();
		update_option( 'ke_quick_translations', $defaults );
		wp_send_json_success( $defaults );
	}

	/**
	 * Render Quick Translation Dashboard Page
	 */
	public function render_translation_page() {
		$saved = get_option( 'ke_quick_translations', array() );
		$defaults = self::get_default_translations();
		$strings = wp_parse_args( $saved, $defaults );
		?>
		<div class="wrap ke-translation-container">
			<div class="ke-loading-mask">
				<div class="ke-spinner-dual"></div>
			</div>

			<div class="ke-translation-header">
				<div class="ke-translation-title-area">
					<h1><span class="dashicons dashicons-translation"></span> Quick Translation</h1>
					<p class="description">Allows you to quickly translate front-end strings to your language.</p>
				</div>
				<div class="ke-translation-actions">
					<button type="button" class="ke-translation-btn ke-btn-auto-translate" id="ke-auto-translate-btn">
						<span class="dashicons dashicons-admin-site"></span> Auto Translation
					</button>
					<div class="ke-translation-btn ke-btn-quick-tools" id="ke-quick-tools-btn">
						<span class="dashicons dashicons-cloud"></span> Quick Tools
						<div class="ke-quick-tools-dropdown" id="ke-quick-tools-menu">
							<button type="button" id="ke-export-translations-btn">
								<span class="dashicons dashicons-download"></span> Export JSON
							</button>
							<label for="ke-import-translations-file">
								<span class="dashicons dashicons-upload"></span> Import JSON
								<input type="file" id="ke-import-translations-file" accept=".json" style="display: none;">
							</label>
							<button type="button" id="ke-reset-translations-btn" style="color: #ef4444;">
								<span class="dashicons dashicons-trash" style="color: #ef4444;"></span> Reset to Defaults
							</button>
						</div>
					</div>
				</div>
			</div>

			<div class="ke-translation-notice">
				<div class="dashicons dashicons-info"></div>
				<div>
					<strong>PLEASE NOTE:</strong> Please keep "%s" as it is in the translated text if the string contains this variable. Incorrect formatting can cause fatal errors in PHP code and prevent the site from loading correctly.
				</div>
			</div>

			<div class="ke-translation-card">
				<div class="ke-translation-list-header">
					<div class="ke-translation-header-col">
						<span class="dashicons dashicons-admin-site"></span> Source String - English
					</div>
					<div class="ke-translation-header-col">
						<span class="dashicons dashicons-translation"></span> Translation
					</div>
				</div>
				<div class="ke-translation-list-body">
					<?php foreach ( $defaults as $english => $fallback ) : 
						$current_val = isset( $saved[ $english ] ) ? $saved[ $english ] : '';
						?>
						<div class="ke-translation-row">
							<div class="ke-translation-source"><?php echo esc_html( $english ); ?></div>
							<div class="ke-translation-input-wrap">
								<input type="text" 
									class="ke-translation-input" 
									data-key="<?php echo esc_attr( $english ); ?>" 
									value="<?php echo esc_attr( $current_val ); ?>" 
									placeholder="<?php echo esc_attr( $fallback ); ?>">
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="ke-translation-footer">
				<div class="ke-translation-search-wrap">
					<span class="dashicons dashicons-search"></span>
					<input type="text" id="ke-search-strings" placeholder="Search source string...">
				</div>
				<button type="button" class="ke-btn-save-translations" id="ke-save-translations-btn">Save Changes</button>
			</div>
		</div>
		<?php
	}
}
KE_Admin::get_instance();

<?php
/**
 * Kontentainment Events SEO Fields
 * Handles adding SEO-specific fields for Event and Venue post types.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_SEO_Fields {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_seo_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_seo_fields' ) );
	}

	/**
	 * Add custom SEO meta box for Event and Venue
	 */
	public function add_seo_meta_box() {
		$screens = array( 'event', 'venue' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				'ke_seo_metabox',
				'SEO Settings (KT Events)',
				array( $this, 'render_seo_metabox' ),
				$screen,
				'normal',
				'default'
			);
		}
	}

	/**
	 * Render the SEO meta box
	 */
	public function render_seo_metabox( $post ) {
		wp_nonce_field( 'ke_save_seo_data', 'ke_seo_nonce' );

		$seo_title       = get_post_meta( $post->ID, '_ke_seo_title', true );
		$seo_desc        = get_post_meta( $post->ID, '_ke_seo_description', true );
		$focus_keyword   = get_post_meta( $post->ID, '_ke_seo_focus_keyword', true );
		$canonical       = get_post_meta( $post->ID, '_ke_seo_canonical_url', true );
		$og_title        = get_post_meta( $post->ID, '_ke_og_title', true );
		$og_desc         = get_post_meta( $post->ID, '_ke_og_description', true );
		$og_image        = get_post_meta( $post->ID, '_ke_og_image', true );
		$twitter_title   = get_post_meta( $post->ID, '_ke_twitter_title', true );
		$twitter_desc    = get_post_meta( $post->ID, '_ke_twitter_description', true );
		$twitter_image   = get_post_meta( $post->ID, '_ke_twitter_image', true );
		$robots          = get_post_meta( $post->ID, '_ke_seo_robots', true ) ?: 'index, follow';

		?>
		<style>
			.ke-seo-field-group { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0; }
			.ke-seo-field-group:last-child { border-bottom: none; }
			.ke-seo-label { display: block; font-weight: bold; margin-bottom: 5px; }
			.ke-seo-help { display: block; color: #777; font-size: 13px; margin-top: 5px; }
			.ke-seo-image-preview { display: block; max-width: 200px; margin-top: 10px; border: 1px solid #ddd; }
		</style>
		<div class="ke-admin-seo-form">
			<div class="ke-seo-field-group">
				<label class="ke-seo-label" for="ke_seo_title">SEO Title</label>
				<input type="text" name="ke_seo_title" id="ke_seo_title" value="<?php echo esc_attr( $seo_title ); ?>" class="widefat">
				<span class="ke-seo-help">Overrides the main page title in search engine results. Fallback: %title% - %sitename%</span>
			</div>

			<div class="ke-seo-field-group">
				<label class="ke-seo-label" for="ke_seo_description">SEO Meta Description</label>
				<textarea name="ke_seo_description" id="ke_seo_description" rows="3" class="widefat"><?php echo esc_textarea( $seo_desc ); ?></textarea>
				<span class="ke-seo-help">A brief summary (150-160 characters) to show in search snippets.</span>
			</div>

			<div class="ke-seo-field-group">
				<label class="ke-seo-label" for="ke_seo_focus_keyword">Focus Keyword</label>
				<input type="text" name="ke_seo_focus_keyword" id="ke_seo_focus_keyword" value="<?php echo esc_attr( $focus_keyword ); ?>" class="widefat">
				<span class="ke-seo-help">The primary keyword you want this page to rank for.</span>
			</div>

			<div class="ke-seo-field-group">
				<label class="ke-seo-label" for="ke_seo_canonical_url">Canonical URL Override</label>
				<input type="url" name="ke_seo_canonical_url" id="ke_seo_canonical_url" value="<?php echo esc_attr( $canonical ); ?>" class="widefat">
				<span class="ke-seo-help">Specify a different canonical URL if this page should point to another source.</span>
			</div>

			<div class="ke-seo-field-group">
				<label class="ke-seo-label">Social Media Meta (Open Graph & Twitter)</label>
				<div style="margin-top: 10px; padding: 15px; background: #f9fafb; border: 1px solid #eef2f7; border-radius: 8px;">
					<p>
						<label for="ke_og_title">Facebook/WhatsApp Title</label><br>
						<input type="text" name="ke_og_title" id="ke_og_title" value="<?php echo esc_attr( $og_title ); ?>" class="widefat">
					</p>
					<p>
						<label for="ke_og_description">Facebook/WhatsApp Description</label><br>
						<textarea name="ke_og_description" id="ke_og_description" rows="2" class="widefat"><?php echo esc_textarea( $og_desc ); ?></textarea>
					</p>
					<p>
						<label for="ke_og_image">Social Share Image URL</label><br>
						<input type="url" name="ke_og_image" id="ke_og_image" value="<?php echo esc_attr( $og_image ); ?>" class="widefat">
					</p>
					<hr>
					<p><strong>Twitter Specific (Optional)</strong></p>
					<p>
						<label for="ke_twitter_title">Twitter Title</label><br>
						<input type="text" name="ke_twitter_title" id="ke_twitter_title" value="<?php echo esc_attr( $twitter_title ); ?>" class="widefat">
					</p>
					<p>
						<label for="ke_twitter_description">Twitter Description</label><br>
						<textarea name="ke_twitter_description" id="ke_twitter_description" rows="2" class="widefat"><?php echo esc_textarea( $twitter_desc ); ?></textarea>
					</p>
				</div>
			</div>

			<div class="ke-seo-field-group">
				<label class="ke-seo-label" for="ke_seo_robots">Robots Setting</label>
				<select name="ke_seo_robots" id="ke_seo_robots" class="widefat">
					<option value="index, follow" <?php selected( $robots, 'index, follow' ); ?>>Index, Follow</option>
					<option value="noindex, follow" <?php selected( $robots, 'noindex, follow' ); ?>>No-Index, Follow</option>
					<option value="index, nofollow" <?php selected( $robots, 'index, nofollow' ); ?>>Index, No-Follow</option>
					<option value="noindex, nofollow" <?php selected( $robots, 'noindex, nofollow' ); ?>>No-Index, No-Follow</option>
				</select>
			</div>
		</div>
		<?php
	}

	/**
	 * Save SEO field data
	 */
	public function save_seo_fields( $post_id ) {
		if ( ! isset( $_POST['ke_seo_nonce'] ) || ! wp_verify_nonce( $_POST['ke_seo_nonce'], 'ke_save_seo_data' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'ke_seo_title'          => '_ke_seo_title',
			'ke_seo_description'    => '_ke_seo_description',
			'ke_seo_focus_keyword'  => '_ke_seo_focus_keyword',
			'ke_seo_canonical_url'  => '_ke_seo_canonical_url',
			'ke_og_title'           => '_ke_og_title',
			'ke_og_description'     => '_ke_og_description',
			'ke_og_image'           => '_ke_og_image',
			'ke_twitter_title'      => '_ke_twitter_title',
			'ke_twitter_description' => '_ke_twitter_description',
			'ke_twitter_image'       => '_ke_twitter_image',
			'ke_seo_robots'         => '_ke_seo_robots',
		);

		foreach ( $fields as $post_key => $meta_key ) {
			if ( isset( $_POST[ $post_key ] ) ) {
				$val = sanitize_text_field( $_POST[ $post_key ] );
				if ( strpos( $post_key, 'url' ) !== false || strpos( $post_key, 'image' ) !== false ) {
					$val = esc_url_raw( $val );
				}
				update_post_meta( $post_id, $meta_key, $val );
			}
		}
	}
}

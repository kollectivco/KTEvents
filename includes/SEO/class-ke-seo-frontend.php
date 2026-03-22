<?php
/**
 * Kontentainment Events SEO Frontend
 * Handles meta tag output on the frontend when no major SEO plugin is active.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_SEO_Frontend {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Only run if NO SEO plugin is active
		if ( ! KE_SEO_Manager::is_seo_plugin_active() ) {
			add_action( 'wp_head', array( $this, 'output_meta_tags' ), 1 );
			add_filter( 'pre_get_document_title', array( $this, 'filter_page_title' ), 15 );
		}
	}

	/**
	 * Filter Document Title
	 */
	public function filter_page_title( $title ) {
		if ( ! is_singular( array( 'event', 'venue' ) ) && ! is_post_type_archive( array( 'event', 'venue' ) ) && ! is_tax( array( 'event_category', 'event_city', 'event_area' ) ) ) {
			return $title;
		}

		$post_id = get_queried_object_id();
		$custom_title = get_post_meta( $post_id, '_ke_seo_title', true );

		if ( $custom_title ) {
			return $custom_title;
		}

		// Intelligent fallback
		$site_name = get_bloginfo( 'name' );
		if ( is_singular() ) {
			return get_the_title( $post_id ) . ' | ' . $site_name;
		}

		if ( is_post_type_archive( 'event' ) ) {
			return 'Events Calendar | ' . $site_name;
		}

		if ( is_post_type_archive( 'venue' ) ) {
			return 'Venues Directory | ' . $site_name;
		}

		return $title;
	}

	/**
	 * Output SEO Meta Tags in <head>
	 */
	public function output_meta_tags() {
		if ( ! is_singular( array( 'event', 'venue' ) ) && ! is_post_type_archive( array( 'event', 'venue' ) ) && ! is_tax( array( 'event_category', 'event_city', 'event_area' ) ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		$desc    = get_post_meta( $post_id, '_ke_seo_description', true );
		if ( ! $desc && is_singular() ) {
			$desc = get_the_excerpt( $post_id );
			if ( ! $desc ) {
				$content = get_post_field( 'post_content', $post_id );
				$desc = wp_trim_words( $content, 25 );
			}
		}

		$canonical = get_post_meta( $post_id, '_ke_seo_canonical_url', true ) ?: get_permalink( $post_id );
		$robots    = get_post_meta( $post_id, '_ke_seo_robots', true ) ?: 'index, follow';

		// OG Data
		$og_title = get_post_meta( $post_id, '_ke_og_title', true ) ?: get_the_title( $post_id );
		$og_desc  = get_post_meta( $post_id, '_ke_og_description', true ) ?: $desc;
		$og_img   = get_post_meta( $post_id, '_ke_og_image', true ) ?: get_the_post_thumbnail_url( $post_id, 'large' );

		echo "\n<!-- Kontentainment Events SEO Fallback -->\n";
		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
		}
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
		echo '<meta name="robots" content="' . esc_attr( $robots ) . '" />' . "\n";

		// OG Tags
		echo '<meta property="og:type" content="article" />' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '" />' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $og_desc ) . '" />' . "\n";
		echo '<meta property="og:url" content="' . esc_url( get_permalink( $post_id ) ) . '" />' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";
		if ( $og_img ) {
			echo '<meta property="og:image" content="' . esc_url( $og_img ) . '" />' . "\n";
		}

		// Twitter Tags
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $og_desc ) . '" />' . "\n";
		if ( $og_img ) {
			echo '<meta name="twitter:image" content="' . esc_url( $og_img ) . '" />' . "\n";
		}
		echo "<!-- End Kontentainment Events SEO Fallback -->\n\n";
	}
}

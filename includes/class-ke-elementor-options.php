<?php
/**
 * Kontentainment Events Elementor Options Helper - Phase 5.2 Expanded
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Elementor_Options {

	/**
	 * Get Query Modes
	 */
	public static function get_query_modes() {
		return [
			'standard' => 'Standard Query Settings',
			'current'  => 'Current Context (Dynamic)',
			'global'   => 'Global Query (Template Builder)',
			'advanced' => 'Advanced Post Type & Taxonomies',
		];
	}

	/**
	 * Get layout presets (enhanced labels)
	 */
	public static function get_event_layout_presets() {
		return [
			'classic'        => 'Classic Grid',
			'grid_1'         => 'Grid 1 (Clean)',
			'grid_2'         => 'Grid 2 (Boxed)',
			'minimal_grid'   => 'Minimal Grid',
			'editorial_grid' => 'Editorial Grid',
			'list_1'         => 'List 1 (Horizontal)',
			'list_2'         => 'List 2 (Compact)',
			'flex_overlay'   => 'Flex Overlay',
			'small_list'     => 'Small List / Minimal',
			'highlight'      => 'Highlight / Hero',
		];
	}

	/**
	 * Get AJAX pagination styles
	 */
	public static function get_pagination_styles() {
		return [
			'standard' => 'Standard Button',
			'boxed'    => 'Boxed Outlined',
			'link'     => 'Plain Link',
			'circle'   => 'Circular Icon Only',
		];
	}

	/**
	 * Get Color Schemes
	 */
	public static function get_color_schemes() {
		return [
			'default' => 'Default (Inherit)',
			'light'   => 'Light Mode Scheme',
			'dark'    => 'Dark Mode Scheme',
		];
	}

	/**
	 * Get Sorting Options
	 */
	public static function get_sort_options() {
		return [
			'upcoming'   => 'Nearest Upcoming First',
			'latest'     => 'Latest Added First',
			'date_asc'   => 'Event Date (ASC)',
			'date_desc'  => 'Event Date (DESC)',
			'title_asc'  => 'Title (A-Z)',
			'title_desc' => 'Title (Z-A)',
			'rand'       => 'Random Order',
		];
	}

	/**
	 * Get Gap Presets
	 */
	public static function get_gap_options() {
		return [
			'default' => 'Default (32px)',
			'small'   => 'Small (15px)',
			'large'   => 'Large (50px)',
			'none'    => 'No Gap',
			'custom'  => 'Custom Value...',
		];
	}

	/**
	 * Get Image Ratio Options
	 */
	public static function get_image_ratios() {
		return [
			'1-1'   => '1:1 Square',
			'4-3'   => '4:3 Standard',
			'16-9'  => '16:9 Wide',
			'3-4'   => '3:4 Portrait',
			'2-1'   => '2:1 Banner',
			'custom' => 'Custom Ratio...',
		];
	}
}

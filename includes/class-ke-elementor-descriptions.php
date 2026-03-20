<?php
/**
 * Kontentainment Events Elementor Descriptions Helper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KE_Elementor_Descriptions {

	public static function get( $id ) {
		$desc = [
			'query_mode'   => 'Select how you want to fetch events. Standard uses your selections below. Current Context uses the page/archive the user is viewing.',
			'offset'       => 'Number of posts to skip. Useful for avoiding duplicates or starting a grid from the 2nd post.',
			'unique_post'  => 'Enable this to avoid repeating posts that have already appeared in other KE widgets on this page.',
			'offset_info'  => 'Note: Using Offset may interfere with AJAX pagination in some WordPress versions.',
			'unique_info'  => 'Note: Unique Post tracking is request-based and helps avoid duplicates on a single page.',
			'scroll_info'  => 'Note: Horizontal Scroll is generally incompatible with AJAX pagination.',
			'image_ratio'  => 'Select a preset ratio or choose Custom to define your own.',
			'excerpt_info' => 'If the post has a manual excerpt, it will be used. Otherwise, it will be generated from the content.',
		];

		return $desc[ $id ] ?? '';
	}
}

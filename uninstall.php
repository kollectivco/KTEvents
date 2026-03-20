<?php
/**
 * Kontentainment Events Uninstall
 * 
 * Triggered on plugin deletion.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Only delete if the setting is specifically checked
$ke_delete_on_uninstall = get_option( 'ke_delete_on_uninstall' );

if ( '1' === (string) $ke_delete_on_uninstall ) {
	global $wpdb;

	// 1. Delete Options
	$options_to_delete = [
		'ke_db_version',
		'ke_enable_caching',
		'ke_enable_schema',
		'ke_cache_ttl',
		'ke_delete_on_uninstall',
		'ke_import_settings',
	];

	foreach ( $options_to_delete as $option ) {
		delete_option( $option );
	}

	// 2. Delete Transients
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_ke_query_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_ke_query_%'" );

	// 3. Optional: Delete CPTs (Extremely Destructive - only if user is warned)
	/*
	$posts = get_posts( [ 'post_type' => [ 'event', 'venue' ], 'numberposts' => -1, 'post_status' => 'any' ] );
	foreach ( $posts as $post ) wp_delete_post( $post->ID, true );
	*/
}

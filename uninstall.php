<?php
/**
 * Translation Tools uninstall file to clean all settings and transient data from the database.
 *
 * @package Translation Tools
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}


// Check if it is a multisite uninstall - if so, run the uninstall function for each blog id.
if ( is_multisite() ) {
	global $wpdb;
	foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) as $ttools_blog ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		switch_to_blog( $ttools_blog );
		ttools_uninstall();
	}
	restore_current_blog();
} else {
	ttools_uninstall();
}


/**
 * Removes ALL plugin data if set in the settings.
 *
 * @since 1.0.0
 */
function ttools_uninstall() {
	$option = get_option( 'ttools_settings' );
	// Check if Delete Data on Uninstall is set.
	if ( empty( $option['delete_data_on_uninstall'] ) ) {
		return;
	} else {
		if ( is_multisite() ) {
			// Delete option in Multisite.
			delete_site_option( 'ttools_settings' );
		} else {
			// Delete option.
			delete_option( 'ttools_settings' );
		}
		// Delete transients.
		ttools_uninstall_delete_transients( 'translation_tools_' );
	}
}


/**
 * Removes ALL transiantes on uninstall.
 *
 * @since 1.0.0
 *
 * @param string $search  Transient search term.
 */
function ttools_uninstall_delete_transients( $search ) {
	global $wpdb;

	$ttools_transients = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"SELECT option_name AS name FROM $wpdb->options WHERE option_name LIKE %s",
			'%_transient_' . $search . '%'
		)
	);
	$ttools_transients = array_map(
		function( $object ) {
			return $object->name;
		},
		$ttools_transients
	);
	if ( is_array( $ttools_transients ) ) {
		foreach ( $ttools_transients as $ttools_transient ) {
			if ( is_multisite() ) {
				// Delete transients in Multisite.
				delete_site_transient( substr( $ttools_transient, strlen( '_transient_' ) ) );
			} else {
				// Delete transients.
				delete_transient( substr( $ttools_transient, strlen( '_transient_' ) ) );
			}
		}
	}
}

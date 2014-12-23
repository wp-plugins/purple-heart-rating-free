<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( ! WP_UNINSTALL_PLUGIN ) {
	exit();
}

/**
 * @var wpdb $wpdb
 */
global $wpdb;

if ( is_a( $wpdb, 'wpdb' ) ) {
	// Deletes user meta stuff (like closed meta boxes, etc.)
	$wpdb->prepare( 'DELETE FROM ' . $wpdb->usermeta . ' WHERE meta_key LIKE %s', '%wpbph%' );
}

delete_option( 'wpbph' );
delete_option( 'wpb_plugin_purple-heart-rating-free_version' );


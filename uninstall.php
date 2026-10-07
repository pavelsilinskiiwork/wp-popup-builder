<?php
/**
 * Removes all plugin data on uninstall.
 *
 * @package PavelSilinskiiPopupBuilder
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// The post type isn't registered during uninstall, so query IDs directly.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$pspb_popup_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s", 'pspb_popup' ) );

foreach ( $pspb_popup_ids as $pspb_popup_id ) {
	wp_delete_post( (int) $pspb_popup_id, true );
}

// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pspb_impressions" );

delete_option( 'pspb_version' );

<?php
/**
 * Activation routine.
 *
 * @package PavelSilinskiiPopupBuilder
 */

namespace PavelSilinskii\PopupBuilder;

defined( 'ABSPATH' ) || exit;

/**
 * Creates the impressions table and records the installed version.
 */
class Installer {

	/**
	 * Runs on plugin activation (and on version change).
	 */
	public static function activate(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = $wpdb->prefix . 'pspb_impressions';
		$charset_collate = $wpdb->get_charset_collate();

		// dbDelta is whitespace-sensitive: two spaces after PRIMARY KEY, one field per line.
		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			popup_id bigint(20) unsigned NOT NULL,
			visitor_key varchar(64) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'shown',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY popup_visitor (popup_id,visitor_key)
		) {$charset_collate};";

		dbDelta( $sql );

		update_option( 'pspb_version', PSPB_VERSION );
	}
}

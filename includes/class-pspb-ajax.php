<?php
/**
 * AJAX: records popup impressions, dismissals and conversions.
 *
 * @package PavelSilinskiiPopupBuilder
 */

namespace PavelSilinskii\PopupBuilder;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the pspb_dismiss AJAX action for guests and logged-in users.
 */
class Ajax {

	private const STATUSES = array( 'shown', 'dismissed', 'converted' );

	/**
	 * Hooks registration.
	 */
	public function __construct() {
		add_action( 'wp_ajax_pspb_dismiss', array( $this, 'handle_dismiss' ) );
		add_action( 'wp_ajax_nopriv_pspb_dismiss', array( $this, 'handle_dismiss' ) );
	}

	/**
	 * Records one impression row.
	 */
	public function handle_dismiss(): void {
		check_ajax_referer( 'pspb_nonce', 'nonce' );

		$popup_id    = absint( $_POST['popup_id'] ?? 0 );
		$action_type = sanitize_key( wp_unslash( $_POST['action_type'] ?? 'dismissed' ) );

		if ( ! in_array( $action_type, self::STATUSES, true ) ) {
			$action_type = 'dismissed';
		}

		// Only accept IDs of real, published popups so the table can't be filled with junk.
		if ( 0 === $popup_id || PostType::POST_TYPE !== get_post_type( $popup_id ) || 'publish' !== get_post_status( $popup_id ) ) {
			wp_send_json_error( 'Invalid popup ID', 400 );
		}

		$ip         = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );

		// Salted (HMAC) rather than plain md5, so the key can't be reversed by brute-forcing the IPv4 space.
		$visitor_key = wp_hash( $ip . $user_agent );

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$wpdb->prefix . 'pspb_impressions',
			array(
				'popup_id'    => $popup_id,
				'visitor_key' => $visitor_key,
				'status'      => $action_type,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s' )
		);

		wp_send_json_success( array( 'recorded' => true ) );
	}

	/**
	 * Number of times each popup was shown.
	 *
	 * @param int[] $popup_ids Popup post IDs.
	 * @return array<int, int> Popup ID => count.
	 */
	public static function get_impression_counts( array $popup_ids ): array {
		global $wpdb;

		$popup_ids = array_filter( array_map( 'absint', $popup_ids ) );

		if ( empty( $popup_ids ) ) {
			return array();
		}

		$placeholders = implode( ',', array_fill( 0, count( $popup_ids ), '%d' ) );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT popup_id, COUNT(*) AS total FROM {$wpdb->prefix}pspb_impressions WHERE status = 'shown' AND popup_id IN ({$placeholders}) GROUP BY popup_id",
				...$popup_ids
			)
		);
		// phpcs:enable

		$counts = array_fill_keys( $popup_ids, 0 );

		foreach ( (array) $rows as $row ) {
			$counts[ (int) $row->popup_id ] = (int) $row->total;
		}

		return $counts;
	}
}

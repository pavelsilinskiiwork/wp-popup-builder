<?php
/**
 * The pspb_popup custom post type and its settings.
 *
 * @package PavelSilinskiiPopupBuilder
 */

namespace PavelSilinskii\PopupBuilder;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the post type and reads popup settings with defaults applied.
 */
class PostType {

	public const POST_TYPE = 'pspb_popup';

	/**
	 * Allowed values for the select/radio settings.
	 */
	public const TRIGGERS    = array( 'delay', 'scroll', 'exit_intent', 'click' );
	public const FREQUENCIES = array( 'once', 'session', 'always' );
	public const PAGE_RULES  = array( 'all', 'front_page', 'specific' );
	public const ANIMATIONS  = array( 'fade', 'slide', 'zoom' );

	/**
	 * Hooks registration.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Registers the pspb_popup post type.
	 */
	public function register(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'          => array(
					'name'               => __( 'Popups', 'pavel-silinskii-popup-builder' ),
					'singular_name'      => __( 'Popup', 'pavel-silinskii-popup-builder' ),
					'add_new'            => __( 'Add New Popup', 'pavel-silinskii-popup-builder' ),
					'add_new_item'       => __( 'Add New Popup', 'pavel-silinskii-popup-builder' ),
					'edit_item'          => __( 'Edit Popup', 'pavel-silinskii-popup-builder' ),
					'new_item'           => __( 'New Popup', 'pavel-silinskii-popup-builder' ),
					'view_item'          => __( 'View Popup', 'pavel-silinskii-popup-builder' ),
					'search_items'       => __( 'Search Popups', 'pavel-silinskii-popup-builder' ),
					'not_found'          => __( 'No popups found.', 'pavel-silinskii-popup-builder' ),
					'not_found_in_trash' => __( 'No popups found in Trash.', 'pavel-silinskii-popup-builder' ),
					'all_items'          => __( 'All Popups', 'pavel-silinskii-popup-builder' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => false,
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);
	}

	/**
	 * Default value for every popup setting.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'_pspb_enabled'           => true,
			'_pspb_trigger'           => 'delay',
			'_pspb_trigger_delay'     => 3,
			'_pspb_trigger_scroll'    => 50,
			'_pspb_trigger_selector'  => '',
			'_pspb_display_frequency' => 'once',
			'_pspb_display_pages'     => 'all',
			'_pspb_specific_pages'    => array(),
			'_pspb_overlay'           => true,
			'_pspb_close_on_overlay'  => true,
			'_pspb_width'             => 600,
			'_pspb_bg_color'          => '#ffffff',
			'_pspb_text_color'        => '#333333',
			'_pspb_button_color'      => '#2271b1',
			'_pspb_close_button'      => true,
			'_pspb_animation'         => 'fade',
		);
	}

	/**
	 * Reads all settings for a popup, falling back to defaults for keys never saved.
	 *
	 * @param int $popup_id Popup post ID.
	 * @return array<string, mixed>
	 */
	public static function get_meta( int $popup_id ): array {
		$meta = array();

		foreach ( self::defaults() as $key => $default ) {
			if ( ! metadata_exists( 'post', $popup_id, $key ) ) {
				$meta[ $key ] = $default;
				continue;
			}

			$value = get_post_meta( $popup_id, $key, true );

			if ( is_bool( $default ) ) {
				$value = '1' === (string) $value;
			} elseif ( is_int( $default ) ) {
				$value = absint( $value );
			} elseif ( is_array( $default ) ) {
				$value = array_map( 'absint', (array) $value );
			} else {
				$value = (string) $value;
			}

			$meta[ $key ] = $value;
		}

		return $meta;
	}
}

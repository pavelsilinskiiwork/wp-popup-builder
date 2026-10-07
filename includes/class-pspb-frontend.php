<?php
/**
 * Frontend: enqueues assets and renders popup markup.
 *
 * @package PavelSilinskiiPopupBuilder
 */

namespace PavelSilinskii\PopupBuilder;

defined( 'ABSPATH' ) || exit;

/**
 * Outputs active popups matching the current request.
 */
class Frontend {

	/**
	 * Popups for the current request, computed once.
	 *
	 * @var \WP_Post[]|null
	 */
	private ?array $popups = null;

	/**
	 * Hooks registration.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_footer', array( $this, 'render_popups' ) );
	}

	/**
	 * Whether this request can show popups at all.
	 */
	public function should_render(): bool {
		return ! ( is_admin() || wp_doing_ajax() || defined( 'REST_REQUEST' ) || is_feed() || is_embed() );
	}

	/**
	 * Enqueues assets only when at least one popup will render.
	 */
	public function enqueue(): void {
		if ( empty( $this->get_active_popups() ) ) {
			return;
		}

		wp_enqueue_style( 'pspb-frontend', PSPB_PLUGIN_URL . 'assets/css/pspb-frontend.css', array(), PSPB_VERSION );
		wp_enqueue_script( 'pspb-frontend', PSPB_PLUGIN_URL . 'assets/js/pspb-frontend.js', array(), PSPB_VERSION, true );
		wp_localize_script(
			'pspb-frontend',
			'pspbData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'pspb_nonce' ),
				'popups'  => $this->get_popups_data(),
			)
		);
	}

	/**
	 * Published, enabled popups whose page rules match the current request.
	 *
	 * @return \WP_Post[]
	 */
	public function get_active_popups(): array {
		if ( null !== $this->popups ) {
			return $this->popups;
		}

		if ( ! $this->should_render() ) {
			$this->popups = array();
			return $this->popups;
		}

		$popups = get_posts(
			array(
				'post_type'   => PostType::POST_TYPE,
				'post_status' => 'publish',
				'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_pspb_enabled',
						'value' => '1',
					),
				),
				'numberposts' => 10,
			)
		);

		$this->popups = array_values( array_filter( $popups, array( $this, 'matches_page_rules' ) ) );

		return $this->popups;
	}

	/**
	 * Checks a popup's "Pages" display rule against the current request.
	 *
	 * @param \WP_Post $popup Popup post.
	 */
	private function matches_page_rules( \WP_Post $popup ): bool {
		$meta = PostType::get_meta( $popup->ID );

		switch ( $meta['_pspb_display_pages'] ) {
			case 'front_page':
				return is_front_page();
			case 'specific':
				$current = get_queried_object_id();
				return $current > 0 && in_array( $current, $meta['_pspb_specific_pages'], true );
			default:
				return true;
		}
	}

	/**
	 * Popup settings passed to the frontend script.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_popups_data(): array {
		$data = array();

		foreach ( $this->get_active_popups() as $popup ) {
			$meta = PostType::get_meta( $popup->ID );

			$data[] = array(
				'id'             => $popup->ID,
				'trigger'        => $meta['_pspb_trigger'],
				'delay'          => $meta['_pspb_trigger_delay'],
				'scroll'         => $meta['_pspb_trigger_scroll'],
				'selector'       => sanitize_text_field( $meta['_pspb_trigger_selector'] ),
				'frequency'      => $meta['_pspb_display_frequency'],
				'animation'      => $meta['_pspb_animation'],
				'overlay'        => $meta['_pspb_overlay'],
				'closeOnOverlay' => $meta['_pspb_close_on_overlay'],
				'closeButton'    => $meta['_pspb_close_button'],
			);
		}

		return $data;
	}

	/**
	 * Prints popup markup in the footer.
	 */
	public function render_popups(): void {
		if ( is_admin() ) {
			return;
		}

		$popups = $this->get_active_popups();

		if ( empty( $popups ) ) {
			return;
		}

		include PSPB_PLUGIN_DIR . 'templates/popup.php';
	}

	/**
	 * Post-content HTML plus form elements, so newsletter-signup shortcodes survive escaping.
	 *
	 * @return array<string, array<string, bool>>
	 */
	public static function allowed_content_html(): array {
		$allowed = wp_kses_allowed_html( 'post' );
		$common  = array(
			'id'          => true,
			'class'       => true,
			'name'        => true,
			'style'       => true,
			'aria-label'  => true,
			'disabled'    => true,
			'required'    => true,
			'placeholder' => true,
			'value'       => true,
			'type'        => true,
		);

		$allowed['form']     = array_merge( $common, array( 'action' => true, 'method' => true, 'target' => true, 'novalidate' => true ) );
		$allowed['input']    = array_merge( $common, array( 'checked' => true, 'autocomplete' => true, 'min' => true, 'max' => true, 'pattern' => true ) );
		$allowed['select']   = array_merge( $common, array( 'multiple' => true ) );
		$allowed['option']   = array( 'value' => true, 'selected' => true );
		$allowed['textarea'] = array_merge( $common, array( 'rows' => true, 'cols' => true ) );
		$allowed['button']   = array_merge( $allowed['button'] ?? array(), $common );
		$allowed['label']    = array_merge( $allowed['label'] ?? array(), array( 'for' => true, 'class' => true ) );

		return $allowed;
	}
}

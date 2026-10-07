<?php
/**
 * Meta boxes on the popup edit screen.
 *
 * @package PavelSilinskiiPopupBuilder
 */

namespace PavelSilinskii\PopupBuilder;

defined( 'ABSPATH' ) || exit;

/**
 * Renders and saves popup settings.
 */
class MetaBoxes {

	/**
	 * Hooks registration.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register' ) );
		add_action( 'save_post_pspb_popup', array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Adds the four settings meta boxes.
	 */
	public function register(): void {
		$screen = PostType::POST_TYPE;

		add_meta_box( 'pspb-trigger', __( 'Trigger Settings', 'pavel-silinskii-popup-builder' ), array( $this, 'render_trigger' ), $screen, 'normal', 'high' );
		add_meta_box( 'pspb-display', __( 'Display Rules', 'pavel-silinskii-popup-builder' ), array( $this, 'render_display' ), $screen, 'normal', 'high' );
		add_meta_box( 'pspb-appearance', __( 'Appearance', 'pavel-silinskii-popup-builder' ), array( $this, 'render_appearance' ), $screen, 'normal', 'default' );
		add_meta_box( 'pspb-status', __( 'Status', 'pavel-silinskii-popup-builder' ), array( $this, 'render_status' ), $screen, 'side', 'high' );
	}

	/**
	 * Trigger Settings box.
	 *
	 * @param \WP_Post $post Current popup.
	 */
	public function render_trigger( $post ): void {
		$meta     = PostType::get_meta( $post->ID );
		$triggers = array(
			'delay'       => __( 'On Delay', 'pavel-silinskii-popup-builder' ),
			'scroll'      => __( 'On Scroll', 'pavel-silinskii-popup-builder' ),
			'exit_intent' => __( 'Exit Intent', 'pavel-silinskii-popup-builder' ),
			'click'       => __( 'On Click', 'pavel-silinskii-popup-builder' ),
		);

		wp_nonce_field( 'pspb_save_meta', 'pspb_nonce_meta' );
		?>
		<div class="pspb-meta-row">
			<span class="pspb-meta-label"><?php esc_html_e( 'Trigger type', 'pavel-silinskii-popup-builder' ); ?></span>
			<fieldset class="pspb-trigger-options">
				<legend class="screen-reader-text"><?php esc_html_e( 'Trigger type', 'pavel-silinskii-popup-builder' ); ?></legend>
				<?php foreach ( $triggers as $value => $label ) : ?>
					<label>
						<input type="radio" name="_pspb_trigger" value="<?php echo esc_attr( $value ); ?>" <?php checked( $meta['_pspb_trigger'], $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</fieldset>
		</div>

		<div class="pspb-meta-row pspb-field-delay">
			<label for="pspb_trigger_delay"><?php esc_html_e( 'Delay (seconds)', 'pavel-silinskii-popup-builder' ); ?></label>
			<input type="number" id="pspb_trigger_delay" name="_pspb_trigger_delay" min="0" step="1" class="small-text" value="<?php echo esc_attr( $meta['_pspb_trigger_delay'] ); ?>">
		</div>

		<div class="pspb-meta-row pspb-field-scroll">
			<label for="pspb_trigger_scroll"><?php esc_html_e( 'Scroll percentage', 'pavel-silinskii-popup-builder' ); ?></label>
			<input type="number" id="pspb_trigger_scroll" name="_pspb_trigger_scroll" min="0" max="100" step="1" class="small-text" value="<?php echo esc_attr( $meta['_pspb_trigger_scroll'] ); ?>"> %
		</div>

		<div class="pspb-meta-row pspb-field-selector">
			<label for="pspb_trigger_selector"><?php esc_html_e( 'Click selector', 'pavel-silinskii-popup-builder' ); ?></label>
			<input type="text" id="pspb_trigger_selector" name="_pspb_trigger_selector" class="regular-text" placeholder=".open-popup, #promo-link" value="<?php echo esc_attr( $meta['_pspb_trigger_selector'] ); ?>">
		</div>
		<?php
	}

	/**
	 * Display Rules box.
	 *
	 * @param \WP_Post $post Current popup.
	 */
	public function render_display( $post ): void {
		$meta        = PostType::get_meta( $post->ID );
		$frequencies = array(
			'once'    => __( 'Once per visitor', 'pavel-silinskii-popup-builder' ),
			'session' => __( 'Once per session', 'pavel-silinskii-popup-builder' ),
			'always'  => __( 'Every visit', 'pavel-silinskii-popup-builder' ),
		);
		$page_rules  = array(
			'all'        => __( 'All pages', 'pavel-silinskii-popup-builder' ),
			'front_page' => __( 'Front page only', 'pavel-silinskii-popup-builder' ),
			'specific'   => __( 'Specific pages', 'pavel-silinskii-popup-builder' ),
		);
		?>
		<div class="pspb-meta-row">
			<label for="pspb_display_frequency"><?php esc_html_e( 'Frequency', 'pavel-silinskii-popup-builder' ); ?></label>
			<select id="pspb_display_frequency" name="_pspb_display_frequency">
				<?php foreach ( $frequencies as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $meta['_pspb_display_frequency'], $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="pspb-meta-row">
			<label for="pspb_display_pages"><?php esc_html_e( 'Pages', 'pavel-silinskii-popup-builder' ); ?></label>
			<select id="pspb_display_pages" name="_pspb_display_pages">
				<?php foreach ( $page_rules as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $meta['_pspb_display_pages'], $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="pspb-meta-row pspb-field-specific-pages">
			<label for="pspb_specific_pages"><?php esc_html_e( 'Page / post IDs', 'pavel-silinskii-popup-builder' ); ?></label>
			<input type="text" id="pspb_specific_pages" name="_pspb_specific_pages" class="regular-text" placeholder="12, 34, 56" value="<?php echo esc_attr( implode( ', ', $meta['_pspb_specific_pages'] ) ); ?>">
			<p class="description"><?php esc_html_e( 'Comma-separated IDs.', 'pavel-silinskii-popup-builder' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Appearance box.
	 *
	 * @param \WP_Post $post Current popup.
	 */
	public function render_appearance( $post ): void {
		$meta       = PostType::get_meta( $post->ID );
		$defaults   = PostType::defaults();
		$animations = array(
			'fade'  => __( 'Fade', 'pavel-silinskii-popup-builder' ),
			'slide' => __( 'Slide up', 'pavel-silinskii-popup-builder' ),
			'zoom'  => __( 'Zoom', 'pavel-silinskii-popup-builder' ),
		);
		$colors     = array(
			'_pspb_bg_color'     => __( 'Background color', 'pavel-silinskii-popup-builder' ),
			'_pspb_text_color'   => __( 'Text color', 'pavel-silinskii-popup-builder' ),
			'_pspb_button_color' => __( 'Button color', 'pavel-silinskii-popup-builder' ),
		);
		$checkboxes = array(
			'_pspb_overlay'          => __( 'Show overlay', 'pavel-silinskii-popup-builder' ),
			'_pspb_close_on_overlay' => __( 'Close on overlay click', 'pavel-silinskii-popup-builder' ),
			'_pspb_close_button'     => __( 'Show close button', 'pavel-silinskii-popup-builder' ),
		);
		?>
		<div class="pspb-meta-row">
			<label for="pspb_width"><?php esc_html_e( 'Width (px)', 'pavel-silinskii-popup-builder' ); ?></label>
			<input type="number" id="pspb_width" name="_pspb_width" min="200" max="2000" step="10" class="small-text" value="<?php echo esc_attr( $meta['_pspb_width'] ); ?>">
		</div>

		<?php foreach ( $colors as $key => $label ) : ?>
			<div class="pspb-meta-row">
				<label for="<?php echo esc_attr( ltrim( $key, '_' ) ); ?>"><?php echo esc_html( $label ); ?></label>
				<input type="text" id="<?php echo esc_attr( ltrim( $key, '_' ) ); ?>" name="<?php echo esc_attr( $key ); ?>" class="pspb-color-field" data-default-color="<?php echo esc_attr( $defaults[ $key ] ); ?>" value="<?php echo esc_attr( $meta[ $key ] ); ?>">
			</div>
		<?php endforeach; ?>

		<div class="pspb-meta-row">
			<label for="pspb_animation"><?php esc_html_e( 'Animation', 'pavel-silinskii-popup-builder' ); ?></label>
			<select id="pspb_animation" name="_pspb_animation">
				<?php foreach ( $animations as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $meta['_pspb_animation'], $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<?php foreach ( $checkboxes as $key => $label ) : ?>
			<div class="pspb-meta-row">
				<span class="pspb-meta-label"><?php echo esc_html( $label ); ?></span>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $meta[ $key ] ); ?>>
					<?php esc_html_e( 'Yes', 'pavel-silinskii-popup-builder' ); ?>
				</label>
			</div>
		<?php endforeach; ?>
		<?php
	}

	/**
	 * Status box.
	 *
	 * @param \WP_Post $post Current popup.
	 */
	public function render_status( $post ): void {
		$meta   = PostType::get_meta( $post->ID );
		$counts = Ajax::get_impression_counts( array( $post->ID ) );
		?>
		<p>
			<label>
				<input type="checkbox" name="_pspb_enabled" value="1" <?php checked( $meta['_pspb_enabled'] ); ?>>
				<?php esc_html_e( 'Enabled', 'pavel-silinskii-popup-builder' ); ?>
			</label>
		</p>
		<p>
			<?php esc_html_e( 'Impressions:', 'pavel-silinskii-popup-builder' ); ?>
			<span class="pspb-impressions-count"><?php echo esc_html( number_format_i18n( $counts[ $post->ID ] ?? 0 ) ); ?></span>
		</p>
		<?php
	}

	/**
	 * Saves all settings.
	 *
	 * @param int      $post_id Popup ID.
	 * @param \WP_Post $post    Popup post.
	 */
	public function save( $post_id, $post ): void {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Quick Edit and bulk edit don't include our fields; leave meta untouched.
		if ( ! isset( $_POST['pspb_nonce_meta'] ) ) {
			return;
		}

		check_admin_referer( 'pspb_save_meta', 'pspb_nonce_meta' );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$defaults = PostType::defaults();

		// Booleans: stored as '1' / '0' so "unchecked" differs from "never saved".
		foreach ( array( '_pspb_enabled', '_pspb_overlay', '_pspb_close_on_overlay', '_pspb_close_button' ) as $key ) {
			update_post_meta( $post_id, $key, empty( $_POST[ $key ] ) ? '0' : '1' );
		}

		// Integers.
		update_post_meta( $post_id, '_pspb_trigger_delay', absint( $_POST['_pspb_trigger_delay'] ?? $defaults['_pspb_trigger_delay'] ) );
		update_post_meta( $post_id, '_pspb_trigger_scroll', min( 100, absint( $_POST['_pspb_trigger_scroll'] ?? $defaults['_pspb_trigger_scroll'] ) ) );
		$width = absint( $_POST['_pspb_width'] ?? 0 );
		update_post_meta( $post_id, '_pspb_width', $width > 0 ? $width : $defaults['_pspb_width'] );

		// Whitelisted choices.
		$choices = array(
			'_pspb_trigger'           => PostType::TRIGGERS,
			'_pspb_display_frequency' => PostType::FREQUENCIES,
			'_pspb_display_pages'     => PostType::PAGE_RULES,
			'_pspb_animation'         => PostType::ANIMATIONS,
		);
		foreach ( $choices as $key => $allowed ) {
			$value = sanitize_key( wp_unslash( $_POST[ $key ] ?? '' ) );
			update_post_meta( $post_id, $key, in_array( $value, $allowed, true ) ? $value : $defaults[ $key ] );
		}

		// Colors.
		foreach ( array( '_pspb_bg_color', '_pspb_text_color', '_pspb_button_color' ) as $key ) {
			$color = sanitize_hex_color( wp_unslash( $_POST[ $key ] ?? '' ) );
			update_post_meta( $post_id, $key, $color ? $color : $defaults[ $key ] );
		}

		// Text.
		update_post_meta( $post_id, '_pspb_trigger_selector', sanitize_text_field( wp_unslash( $_POST['_pspb_trigger_selector'] ?? '' ) ) );

		// Comma-separated IDs → array of unique positive ints.
		$raw_ids = sanitize_text_field( wp_unslash( $_POST['_pspb_specific_pages'] ?? '' ) );
		$ids     = array_values( array_unique( array_filter( array_map( 'absint', explode( ',', $raw_ids ) ) ) ) );
		update_post_meta( $post_id, '_pspb_specific_pages', $ids );
	}
}

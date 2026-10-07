<?php
/**
 * Popup markup, printed in wp_footer.
 *
 * Included from Frontend::render_popups(), which provides $popups (WP_Post[]).
 * Dismiss controls use data-pspb-dismiss; pspb-frontend.js handles their clicks.
 *
 * @package PavelSilinskiiPopupBuilder
 */

use PavelSilinskii\PopupBuilder\Frontend;
use PavelSilinskii\PopupBuilder\PostType;

defined( 'ABSPATH' ) || exit;

$pspb_allowed_html = Frontend::allowed_content_html();

foreach ( $popups as $pspb_popup ) :
	$pspb_id   = absint( $pspb_popup->ID );
	$pspb_meta = PostType::get_meta( $pspb_id );

	if ( ! $pspb_meta['_pspb_enabled'] ) {
		continue;
	}

	$pspb_style = sprintf(
		'width:%dpx;background:%s;color:%s;--pspb-button-color:%s;',
		absint( $pspb_meta['_pspb_width'] ),
		sanitize_hex_color( $pspb_meta['_pspb_bg_color'] ),
		sanitize_hex_color( $pspb_meta['_pspb_text_color'] ),
		sanitize_hex_color( $pspb_meta['_pspb_button_color'] )
	);
	?>
	<?php if ( $pspb_meta['_pspb_overlay'] ) : ?>
	<div id="pspb-overlay-<?php echo esc_attr( $pspb_id ); ?>"
		class="pspb-overlay"
		<?php if ( $pspb_meta['_pspb_close_on_overlay'] ) : ?>
		data-pspb-dismiss="<?php echo esc_attr( $pspb_id ); ?>"
		<?php endif; ?>></div>
	<?php endif; ?>

	<div id="pspb-popup-<?php echo esc_attr( $pspb_id ); ?>"
		class="pspb-popup pspb-anim-<?php echo esc_attr( $pspb_meta['_pspb_animation'] ); ?>"
		style="<?php echo esc_attr( $pspb_style ); ?>"
		role="dialog"
		aria-modal="true"
		aria-label="<?php echo esc_attr( get_the_title( $pspb_id ) ); ?>"
		tabindex="-1">

		<?php if ( $pspb_meta['_pspb_close_button'] ) : ?>
		<button type="button" class="pspb-close"
			data-pspb-dismiss="<?php echo esc_attr( $pspb_id ); ?>"
			aria-label="<?php esc_attr_e( 'Close', 'pavel-silinskii-popup-builder' ); ?>">&times;</button>
		<?php endif; ?>

		<div class="pspb-content">
			<?php
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter.
			echo wp_kses( apply_filters( 'the_content', $pspb_popup->post_content ), $pspb_allowed_html );
			?>
		</div>
	</div>
<?php endforeach; ?>

<?php
/**
 * Admin: Settings → Popup Builder page and editor assets.
 *
 * @package PavelSilinskiiPopupBuilder
 */

namespace PavelSilinskii\PopupBuilder;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the admin page listing popups.
 */
class Admin {

	public const MENU_SLUG = 'pspb-popups';

	/**
	 * Hooks registration.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'parent_file', array( $this, 'highlight_parent_menu' ) );
		add_filter( 'submenu_file', array( $this, 'highlight_submenu' ) );
	}

	/**
	 * Adds Settings → Popup Builder.
	 */
	public function add_menu(): void {
		add_options_page(
			__( 'Popup Builder', 'pavel-silinskii-popup-builder' ),
			__( 'Popup Builder', 'pavel-silinskii-popup-builder' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Whether the current screen is a pspb_popup editor or list.
	 */
	private function is_popup_screen(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && PostType::POST_TYPE === $screen->post_type;
	}

	/**
	 * Keeps Settings expanded while editing a popup.
	 *
	 * @param string $parent_file Current parent menu file.
	 */
	public function highlight_parent_menu( $parent_file ) {
		return $this->is_popup_screen() ? 'options-general.php' : $parent_file;
	}

	/**
	 * Highlights Settings → Popup Builder while editing a popup.
	 *
	 * @param string|null $submenu_file Current submenu file.
	 */
	public function highlight_submenu( $submenu_file ) {
		return $this->is_popup_screen() ? self::MENU_SLUG : $submenu_file;
	}

	/**
	 * Renders the popup list page.
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$popups = get_posts(
			array(
				'post_type'   => PostType::POST_TYPE,
				'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'numberposts' => -1,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);

		$counts      = Ajax::get_impression_counts( wp_list_pluck( $popups, 'ID' ) );
		$add_new_url = admin_url( 'post-new.php?post_type=' . PostType::POST_TYPE );
		?>
		<div class="wrap pspb-admin-page">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Popup Builder', 'pavel-silinskii-popup-builder' ); ?></h1>
			<a href="<?php echo esc_url( $add_new_url ); ?>" class="page-title-action"><?php esc_html_e( 'Add New Popup', 'pavel-silinskii-popup-builder' ); ?></a>
			<hr class="wp-header-end">

			<table class="wp-list-table widefat fixed striped pspb-admin-table">
				<thead>
					<tr>
						<th scope="col" class="column-id"><?php esc_html_e( 'ID', 'pavel-silinskii-popup-builder' ); ?></th>
						<th scope="col" class="column-primary"><?php esc_html_e( 'Title', 'pavel-silinskii-popup-builder' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Status', 'pavel-silinskii-popup-builder' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Impressions', 'pavel-silinskii-popup-builder' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Actions', 'pavel-silinskii-popup-builder' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $popups ) ) : ?>
					<tr>
						<td colspan="5">
							<?php esc_html_e( 'No popups yet.', 'pavel-silinskii-popup-builder' ); ?>
							<a href="<?php echo esc_url( $add_new_url ); ?>"><?php esc_html_e( 'Create your first popup', 'pavel-silinskii-popup-builder' ); ?></a>
						</td>
					</tr>
				<?php else : ?>
					<?php
					foreach ( $popups as $popup ) :
						$meta        = PostType::get_meta( $popup->ID );
						$status_obj  = get_post_status_object( $popup->post_status );
						$status      = $status_obj ? $status_obj->label : $popup->post_status;
						$edit_link   = get_edit_post_link( $popup->ID );
						$delete_link = get_delete_post_link( $popup->ID );
						$title       = '' !== $popup->post_title ? $popup->post_title : __( '(no title)', 'pavel-silinskii-popup-builder' );
						?>
					<tr>
						<td class="column-id"><?php echo absint( $popup->ID ); ?></td>
						<td class="column-primary">
							<strong><a href="<?php echo esc_url( $edit_link ); ?>"><?php echo esc_html( $title ); ?></a></strong>
						</td>
						<td>
							<?php echo esc_html( $status ); ?>
							<?php if ( ! $meta['_pspb_enabled'] ) : ?>
								<span class="pspb-disabled-badge"><?php esc_html_e( 'Disabled', 'pavel-silinskii-popup-builder' ); ?></span>
							<?php endif; ?>
						</td>
						<td><span class="pspb-impressions-count"><?php echo esc_html( number_format_i18n( $counts[ $popup->ID ] ?? 0 ) ); ?></span></td>
						<td>
							<a href="<?php echo esc_url( $edit_link ); ?>"><?php esc_html_e( 'Edit', 'pavel-silinskii-popup-builder' ); ?></a>
							<?php if ( $delete_link ) : ?>
								| <a href="<?php echo esc_url( $delete_link ); ?>" class="submitdelete"><?php esc_html_e( 'Trash', 'pavel-silinskii-popup-builder' ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Loads assets on our list page and on pspb_popup edit screens only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue( $hook ): void {
		if ( 'settings_page_' . self::MENU_SLUG === $hook ) {
			wp_enqueue_style( 'pspb-admin', PSPB_PLUGIN_URL . 'assets/css/pspb-admin.css', array(), PSPB_VERSION );
			return;
		}

		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! $this->is_popup_screen() ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'pspb-admin', PSPB_PLUGIN_URL . 'assets/css/pspb-admin.css', array(), PSPB_VERSION );
		wp_enqueue_script( 'pspb-admin', PSPB_PLUGIN_URL . 'assets/js/pspb-admin.js', array( 'jquery', 'wp-color-picker' ), PSPB_VERSION, true );
	}
}

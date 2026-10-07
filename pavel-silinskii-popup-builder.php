<?php
/**
 * Plugin Name:       Pavel Silinskii Popup Builder
 * Plugin URI:        https://github.com/pavelsilinskiiwork/pavel-silinskii-popup-builder
 * Description:       Lightweight popup builder for WordPress. Create popups for promotions, newsletter signups, and announcements with flexible trigger and display rules.
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      8.0
 * Author:            Pavel Silinskii
 * Author URI:        https://profiles.wordpress.org/pavelsilinskii/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pavel-silinskii-popup-builder
 * Domain Path:       /languages
 *
 * @package PavelSilinskiiPopupBuilder
 */

namespace PavelSilinskii\PopupBuilder;

defined( 'ABSPATH' ) || exit;

define( 'PSPB_VERSION', '1.0.0' );
define( 'PSPB_PLUGIN_FILE', __FILE__ );
define( 'PSPB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PSPB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once PSPB_PLUGIN_DIR . 'includes/class-pspb-installer.php';
require_once PSPB_PLUGIN_DIR . 'includes/class-pspb-post-type.php';
require_once PSPB_PLUGIN_DIR . 'includes/class-pspb-frontend.php';
require_once PSPB_PLUGIN_DIR . 'includes/class-pspb-ajax.php';
require_once PSPB_PLUGIN_DIR . 'admin/class-pspb-admin.php';
require_once PSPB_PLUGIN_DIR . 'admin/class-pspb-meta-boxes.php';

register_activation_hook( __FILE__, array( Installer::class, 'activate' ) );

add_action(
	'plugins_loaded',
	function () {
		// Activation hooks don't fire on plugin updates, so re-run the installer when the stored version lags.
		if ( get_option( 'pspb_version' ) !== PSPB_VERSION ) {
			Installer::activate();
		}

		new PostType();
		new Frontend();
		new Ajax();

		if ( is_admin() ) {
			new Admin();
			new MetaBoxes();
		}
	}
);

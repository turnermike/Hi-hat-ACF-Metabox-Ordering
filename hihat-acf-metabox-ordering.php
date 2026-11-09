<?php
/**
 * Plugin Name: Hi-hat ACF Metabox Ordering
 * Plugin URI: https://github.com/turnermike/Hi-hat-ACF-Metabox-Ordering
 * Description: Enforce a consistent ACF field order across your entire site — set it once as an admin, and it applies to every user, on the post types you choose.
 * Version: 1.2.0
 * Author: Mike Turner - Hi-hat Consulting
 * Author URI: https://hi-hatconsulting.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: hi-hat-acf-metabox-ordering
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Network: true
 * ACF: 5.0.0
 *
 * @package HihatAcfMetaboxOrdering
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check for ACF dependency on admin_init.
add_action( 'admin_init', function () {
	if ( ! class_exists( 'acf' ) ) {
		$plugin = plugin_basename( __FILE__ );
		if ( is_plugin_active( $plugin ) ) {
			deactivate_plugins( $plugin, true, is_plugin_active_for_network( $plugin ) );
			// Suppress WordPress's activation success notice after deactivating.
			unset( $_GET['activate'], $_GET['activated'] );
		}
	}
} );

// Display admin notice if ACF is not installed/activated.
add_action( 'all_admin_notices', function () {
	if ( ! class_exists( 'acf' ) ) {
		echo '<div class="notice notice-warning"><p>';
		esc_html_e( 'Hi-hat ACF Metabox Ordering has been deactivated because it requires Advanced Custom Fields. Install and activate Advanced Custom Fields, then activate Hi-hat ACF Metabox Ordering again.', 'hi-hat-acf-metabox-ordering' );
		echo '</p></div>';
	}
} );

// Only load plugin if ACF is active.
if ( ! class_exists( 'acf' ) ) {
	return;
}

define( 'HIHAT_METABOX_ORDERING_DIR', __DIR__ );
define( 'HIHAT_METABOX_ORDERING_OPTION_KEY_SETTINGS', 'hihat_enabled_post_types' );
define( 'HIHAT_METABOX_ORDERING_OPTION_KEY_ORDERS', 'hihat_acf_metabox_order' );
define( 'HIHAT_METABOX_ORDERING_OPTION_KEY_ACTIVATED', 'hihat_show_activation_notice' );

// Flag the site so an admin notice prompts the user to enable ordering per post type.
register_activation_hook( __FILE__, function () {
	update_option( HIHAT_METABOX_ORDERING_OPTION_KEY_ACTIVATED, 1 );
} );

require_once HIHAT_METABOX_ORDERING_DIR . '/includes/functions.php';
require_once HIHAT_METABOX_ORDERING_DIR . '/includes/class-plugin.php';

/**
 * Initialize the plugin.
 */
add_action( 'plugins_loaded', function () {
	HihatAcfMetaboxOrdering\Plugin::instance()->init();
}, 10 );

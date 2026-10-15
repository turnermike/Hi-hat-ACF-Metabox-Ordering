<?php
/**
 * Plugin Name: Hi-hat ACF Metabox Ordering
 * Plugin URI: https://github.com/turnermike/Hi-hat-ACF-Metabox-Ordering
 * Description: Enforce a consistent ACF field order across your entire site — set it once as an admin, and it applies to every user, on the post types you choose.
 * Version: 1.1.0
 * Author: Mike Turner - Hi-hat Consulting
 * Author URI: https://hi-hatconsulting.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: hi-hat-acf-metabox-ordering
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Network: true
 *
 * @package HihatAcfMetaboxOrdering
 */


namespace HihatAcfMetaboxOrdering;

defined( 'ABSPATH' ) || exit;

const PLUGIN_DIR = __DIR__;
const OPTION_KEY_SETTINGS = 'hihat_enabled_post_types';
const OPTION_KEY_ORDERS = 'hihat_acf_metabox_order';

require_once PLUGIN_DIR . '/includes/functions.php';
require_once PLUGIN_DIR . '/includes/class-plugin.php';

/**
 * Initialize the plugin.
 */
add_action( 'plugins_loaded', function () {
	Plugin::instance()->init();
}, 10 );

<?php
/**
 * Main plugin orchestrator class.
 *
 * @package HihatAcfMetaboxOrdering
 */

namespace HihatAcfMetaboxOrdering;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin class.
 */
class Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Metabox order handler.
	 *
	 * @var Metabox_Order_Handler|null
	 */
	private $handler = null;

	/**
	 * Settings page handler.
	 *
	 * @var Settings|null
	 */
	private $settings = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function init() {
		$this->load_classes();
		$this->register_hooks();
	}

	/**
	 * Load required classes.
	 *
	 * @return void
	 */
	private function load_classes() {
		require_once PLUGIN_DIR . '/includes/class-metabox-order-handler.php';
		require_once PLUGIN_DIR . '/includes/class-settings.php';

		$this->handler  = new Metabox_Order_Handler();
		$this->settings = new Settings();
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	private function register_hooks() {
		// Initialize settings page.
		if ( is_admin() ) {
			$this->settings->init();
		}

		// Intercept when metabox order is saved (happens during AJAX meta-box-order action).
		add_filter( 'update_user_metadata', [ $this->handler, 'intercept_metabox_order_save' ], 10, 5 );
		// Override per-user metabox order with global order when retrieving.
		add_filter( 'get_user_metadata', [ $this->handler, 'override_metabox_order_retrieval' ], 10, 5 );
	}
}

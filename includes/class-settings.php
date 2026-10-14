<?php
/**
 * Settings page for the Hi-hat ACF Metabox Ordering plugin.
 *
 * @package HihatAcfMetaboxOrdering
 */

namespace HihatAcfMetaboxOrdering;

defined( 'ABSPATH' ) || exit;

/**
 * Settings class.
 */
class Settings {
	/**
	 * Settings page hook.
	 *
	 * @var string
	 */
	private $page_hook = null;

	/**
	 * Initialize settings.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_notices', [ $this, 'display_notices' ] );
	}

	/**
	 * Register the settings menu.
	 *
	 * @return void
	 */
	public function register_menu() {
		$this->page_hook = add_submenu_page(
			'tools.php',
			__( 'Metabox Ordering', 'hi-hat-acf-metabox-ordering' ),
			__( 'Metabox Ordering', 'hi-hat-acf-metabox-ordering' ),
			'manage_options',
			'hihat-metabox-ordering',
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		// In multisite, use network options, in single site use site options
		$option_group = 'hihat_metabox_ordering_settings';
		
		// For multisite, we use network settings (global to the network)
		// For single site, we use regular options
		register_setting(
			$option_group,
			OPTION_KEY_SETTINGS,
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
				'show_in_rest'      => false,
			]
		);

		add_settings_section(
			'hihat_metabox_ordering_section',
			__( 'Enable Metabox Ordering', 'hi-hat-acf-metabox-ordering' ),
			[ $this, 'render_section_description' ],
			$option_group
		);

		// Get all post types.
		$post_types = $this->get_post_types();

		foreach ( $post_types as $post_type ) {
			add_settings_field(
				'hihat_metabox_ordering_' . $post_type,
				$this->get_post_type_label( $post_type ),
				[ $this, 'render_checkbox' ],
				$option_group,
				'hihat_metabox_ordering_section',
				[ 'post_type' => $post_type ]
			);
		}
	}

	/**
	 * Get all available post types.
	 *
	 * @return array
	 */
	private function get_post_types() {
		$post_types = get_post_types( [ 'public' => true ], 'names' );

		// Remove built-in post types that typically don't need custom metabox ordering.
		$exclude = [ 'attachment', 'wp_block', 'wp_template', 'wp_template_part' ];
		$post_types = array_diff( $post_types, $exclude );

		return array_values( array_unique( $post_types ) );
	}

	/**
	 * Get the label for a post type.
	 *
	 * @param string $post_type Post type name.
	 * @return string
	 */
	private function get_post_type_label( $post_type ) {
		$post_type_obj = get_post_type_object( $post_type );
		return $post_type_obj ? $post_type_obj->label : ucfirst( $post_type );
	}

	/**
	 * Render the section description.
	 *
	 * @return void
	 */
	public function render_section_description() {
		echo wp_kses_post(
			wpautop(
				__( 'Select which post types should use the global metabox ordering. When enabled, all users will see ACF fields in the order you set, regardless of their individual preferences.', 'hi-hat-acf-metabox-ordering' )
			)
		);
	}

	/**
	 * Render a checkbox field.
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_checkbox( $args ) {
		$post_type = $args['post_type'];
		$options   = get_option( OPTION_KEY_SETTINGS, [] );
		$checked   = ! empty( $options[ $post_type ] );

		?>
		<input
			type="checkbox"
			id="hihat_metabox_ordering_<?php echo esc_attr( $post_type ); ?>"
			name="<?php echo esc_attr( OPTION_KEY_SETTINGS ); ?>[<?php echo esc_attr( $post_type ); ?>]"
			value="1"
			<?php checked( $checked ); ?>
		/>
		<label for="hihat_metabox_ordering_<?php echo esc_attr( $post_type ); ?>">
			<?php esc_html_e( 'Enable ordering for this post type', 'hi-hat-acf-metabox-ordering' ); ?>
		</label>
		<?php
	}

	/**
	 * Sanitize settings.
	 *
	 * @param mixed $value Settings value.
	 * @return array
	 */
	public function sanitize_settings( $value ) {
		if ( ! is_array( $value ) ) {
			return [];
		}

		$sanitized = [];

		foreach ( $value as $key => $val ) {
			if ( '1' === $val || 1 === $val || true === $val ) {
				$sanitized[ sanitize_key( $key ) ] = 1;
			}
		}

		return $sanitized;
	}

	/**
	 * Display admin notices.
	 *
	 * @return void
	 */
	public function display_notices() {
		// Check if we're on the settings page.
		if ( ! isset( $_GET['page'] ) || 'hihat-metabox-ordering' !== $_GET['page'] ) {
			return;
		}

		// Check if settings were saved.
		if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php esc_html_e( 'Metabox ordering settings saved successfully.', 'hi-hat-acf-metabox-ordering' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hi-hat-acf-metabox-ordering' ) );
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Metabox Ordering Settings', 'hi-hat-acf-metabox-ordering' ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'hihat_metabox_ordering_settings' );
				do_settings_sections( 'hihat_metabox_ordering_settings' );
				submit_button();
				?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'How It Works', 'hi-hat-acf-metabox-ordering' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Enable this plugin for the post types you want to manage.', 'hi-hat-acf-metabox-ordering' ); ?></li>
				<li><?php esc_html_e( 'Edit a post of that type and arrange the ACF metaboxes in your desired order.', 'hi-hat-acf-metabox-ordering' ); ?></li>
				<li><?php esc_html_e( 'Your ordering will be automatically saved and applied to all users for that post type.', 'hi-hat-acf-metabox-ordering' ); ?></li>
			</ol>
		</div>
		<?php
	}
}

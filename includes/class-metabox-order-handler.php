<?php
/**
 * Metabox order handler — captures and stores admin's metabox ordering.
 *
 * @package HihatAcfMetaboxOrdering
 */

namespace HihatAcfMetaboxOrdering;

defined( 'ABSPATH' ) || exit;

/**
 * Metabox order handler class.
 */
class Metabox_Order_Handler {

	/**
	 * Intercept update_user_metadata filter for metabox ordering.
	 *
	 * This filter fires when WordPress is about to update user metadata.
	 * When an admin rearranges metaboxes, WordPress calls update_user_meta()
	 * with a key like 'meta-box-order_page'. We hook the filter,
	 * store the order globally, and continue normally.
	 *
	 * @param null   $null Unused.
	 * @param int    $user_id The user ID.
	 * @param string $meta_key The meta key.
	 * @param mixed  $meta_value The meta value.
	 * @param mixed  $prev_value The previous value.
	 * @return null Must return null to allow WordPress to continue normally.
	 */
	public function intercept_metabox_order_save( $null, $user_id, $meta_key, $meta_value, $prev_value ) {
		// Only intercept metabox order keys.
		if ( strpos( $meta_key, 'meta-box-order_' ) !== 0 ) {
			return $null;
		}

		// Only admins can set the global order.
		// Check the user being updated, not the current user.
		$user = get_userdata( $user_id );
		if ( ! $user || ! user_can( $user_id, 'manage_options' ) ) {
			return $null;
		}

		// Extract post type from meta key (e.g., 'meta-box-order_page' -> 'page').
		$post_type = str_replace( 'meta-box-order_', '', $meta_key );

		// Check if this post type has ordering enabled in settings.
		if ( ! is_post_type_enabled( $post_type ) ) {
			return $null;
		}

		// The order is saved per post, so the edit screen's post ID is required.
		$post_id = $this->get_post_id_from_referer();
		if ( ! $post_id || get_post_type( $post_id ) !== $post_type ) {
			return $null;
		}

		$global_order = get_option( OPTION_KEY_ORDERS, [] );
		if ( ! is_array( $global_order ) ) {
			$global_order = [];
		}

		$global_order[ $this->get_order_key( $meta_key, $post_id ) ] = $meta_value;

		update_option( OPTION_KEY_ORDERS, $global_order );

		// Must return $null to allow WordPress to continue normally.
		return $null;
	}

	/**
	 * Override per-user metabox order with global order when retrieving.
	 *
	 * This filter ensures that when WordPress retrieves user metadata for
	 * metabox ordering, we return the global order instead of per-user preferences.
	 *
	 * The filter must return:
	 * - An array with one element if $single is true: [ 'value' ]
	 * - A plain value if $single is false
	 * - null to allow WordPress to continue normally
	 *
	 * @param null   $null Unused (will be null when filter fires).
	 * @param int    $object_id The user ID.
	 * @param string $meta_key The meta key.
	 * @param bool   $single Whether meta is fetched as single or array.
	 * @param string $meta_type The meta type ('user' in this case).
	 * @return mixed|null The global metabox order or null to continue normally.
	 */
	public function override_metabox_order_retrieval( $null, $object_id, $meta_key, $single, $meta_type ) {
		// Only override metabox order keys.
		if ( strpos( $meta_key, 'meta-box-order_' ) !== 0 ) {
			return null;
		}

		// Extract post type from meta key (e.g., 'meta-box-order_page' -> 'page').
		$post_type = str_replace( 'meta-box-order_', '', $meta_key );

		// Check if this post type has ordering enabled in settings.
		if ( ! is_post_type_enabled( $post_type ) ) {
			return null;
		}

		// Only the post currently being edited can have a saved order.
		$post = $GLOBALS['post'] ?? null;
		if ( ! $post instanceof \WP_Post || $post->post_type !== $post_type ) {
			return null;
		}

		$global_order = get_option( OPTION_KEY_ORDERS, [] );
		if ( empty( $global_order ) || ! is_array( $global_order ) ) {
			return null;
		}

		$order_key = $this->get_order_key( $meta_key, $post->ID );
		if ( isset( $global_order[ $order_key ] ) ) {
			$order_value = $global_order[ $order_key ];

			// WordPress expects an array when $single is true (it will extract [0]).
			if ( $single ) {
				return [ $order_value ];
			}

			return $order_value;
		}

		return null;
	}

	/**
	 * Build the option array key for a metabox order on a single post.
	 *
	 * @param string $meta_key The meta key, e.g. 'meta-box-order_page'.
	 * @param int    $post_id  The post ID.
	 * @return string
	 */
	private function get_order_key( $meta_key, $post_id ) {
		return $meta_key . '_' . absint( $post_id );
	}

	/**
	 * Read the post ID of the edit screen that sent the request.
	 *
	 * The metabox ordering AJAX request has no post ID, but the browser sends
	 * the edit URL (post.php?post=ID&action=edit) as the referer.
	 *
	 * @return int The post ID, or 0 if it can't be determined.
	 */
	private function get_post_id_from_referer() {
		$referer = wp_get_referer();
		if ( ! $referer ) {
			return 0;
		}

		$query = wp_parse_url( $referer, PHP_URL_QUERY );
		if ( ! $query ) {
			return 0;
		}

		parse_str( $query, $args );

		return isset( $args['post'] ) ? absint( $args['post'] ) : 0;
	}
}


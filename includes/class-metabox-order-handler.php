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
	 * @return null|bool Null to let WordPress save user meta normally, or true once the value is stored per post.
	 */
	public function intercept_metabox_order_save( $null, $user_id, $meta_key, $meta_value, $prev_value ) {
		file_put_contents( __DIR__ . '/../hihat-debug.log', date( 'H:i:s' ) . ' ANY-SAVE key=' . $meta_key . ' user=' . $user_id . ' ajax=' . ( wp_doing_ajax() ? 1 : 0 ) . ' action=' . ( $_REQUEST['action'] ?? '' ) . "\n", FILE_APPEND ); // TEMP
		$parsed = $this->parse_meta_key( $meta_key );
		if ( ! $parsed ) {
			return $null;
		}

		list( $type, $post_type ) = $parsed;

		if ( ! is_post_type_enabled( $post_type ) ) {
			return $null;
		}

		// The state is saved per post, so the edit screen's post ID is required.
		$post_id = $this->get_post_id_from_referer();
		if ( ! $post_id || get_post_type( $post_id ) !== $post_type ) {
			return $null;
		}

		// Only admins can set the global order. Any user who can edit the post
		// can set the shared open/closed state.
		$allowed = 'order' === $type
			? user_can( $user_id, 'manage_options' )
			: user_can( $user_id, 'edit_post', $post_id );

		if ( ! $allowed ) {
			return $null;
		}

		update_post_meta( $post_id, $this->get_storage_key( $meta_key ), $meta_value );
		file_put_contents( __DIR__ . '/../hihat-debug.log', date( 'H:i:s' ) . ' SAVE post=' . $post_id . ' key=' . $meta_key . ' value=' . wp_json_encode( $meta_value ) . "\n", FILE_APPEND ); // TEMP

		// A non-null return stops WordPress from also writing the shared user meta,
		// which would otherwise be read back by posts without their own saved state.
		return true;
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
		$parsed = $this->parse_meta_key( $meta_key );
		if ( ! $parsed ) {
			return null;
		}

		list( , $post_type ) = $parsed;

		if ( ! is_post_type_enabled( $post_type ) ) {
			return null;
		}

		// Only the post currently being edited can have saved state.
		$post = $GLOBALS['post'] ?? null;
		if ( ! $post instanceof \WP_Post || $post->post_type !== $post_type ) {
			return null;
		}

		$storage_key = $this->get_storage_key( $meta_key );
		file_put_contents( __DIR__ . '/../hihat-debug.log', date( 'H:i:s' ) . ' READ post=' . $post->ID . ' key=' . $meta_key . ' exists=' . (int) metadata_exists( 'post', $post->ID, $storage_key ) . "\n", FILE_APPEND ); // TEMP
		if ( metadata_exists( 'post', $post->ID, $storage_key ) ) {
			$order_value = get_post_meta( $post->ID, $storage_key, true );

			// WordPress expects an array when $single is true (it will extract [0]).
			if ( $single ) {
				return [ $order_value ];
			}

			return $order_value;
		}

		return null;
	}

	/**
	 * Split a managed user meta key into its type and post type.
	 *
	 * @param string $meta_key The meta key, e.g. 'meta-box-order_page' or 'closedpostboxes_page'.
	 * @return array|null [ 'order' or 'closed', post type ], or null if the key isn't managed.
	 */
	private function parse_meta_key( $meta_key ) {
		$prefixes = [
			'order'  => 'meta-box-order_',
			'closed' => 'closedpostboxes_',
		];

		foreach ( $prefixes as $type => $prefix ) {
			if ( strpos( $meta_key, $prefix ) === 0 ) {
				return [ $type, substr( $meta_key, strlen( $prefix ) ) ];
			}
		}

		return null;
	}

	/**
	 * Build the post meta key that stores saved state for a post.
	 *
	 * @param string $meta_key The user meta key, e.g. 'meta-box-order_page'.
	 * @return string
	 */
	private function get_storage_key( $meta_key ) {
		return '_hihat_' . $meta_key;
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


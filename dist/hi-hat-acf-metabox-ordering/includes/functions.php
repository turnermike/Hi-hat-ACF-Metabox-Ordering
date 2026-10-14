<?php
/**
 * Utility functions for Hi-hat ACF Metabox Ordering plugin.
 *
 * @package HihatAcfMetaboxOrdering
 */

namespace HihatAcfMetaboxOrdering;

defined( 'ABSPATH' ) || exit;

/**
 * Get enabled post types for metabox ordering.
 *
 * @return array Array of post type names.
 */
function get_enabled_post_types() {
	$options = get_option( OPTION_KEY_SETTINGS, [] );

	if ( empty( $options ) ) {
		return [];
	}

	// Return post types that have a truthy value.
	return array_keys( array_filter( $options ) );
}

/**
 * Check if a post type has metabox ordering enabled.
 *
 * @param string $post_type Post type name.
 * @return bool
 */
function is_post_type_enabled( $post_type ) {
	$enabled = get_enabled_post_types();
	return in_array( $post_type, $enabled, true );
}

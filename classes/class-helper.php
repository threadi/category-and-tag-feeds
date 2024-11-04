<?php
/**
 * File for helper-functions for this plugin.
 *
 * @package category-and-tag-feeds
 */

namespace lwCf;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Define helper object.
 */
class Helper {

	/**
	 * Convert attribute-array to usable by own functions.
	 *
	 * @param array $attributes List of attributes.
	 * @return array
	 */
	public static function prepare_attributes( array $attributes ): array {
		// set default rssType if none is set.
		if ( empty( $attributes ) ) {
			$attributes['rssType'] = 'rss';
		}

		// convert lowercase rssType to uppercase.
		if ( ! empty( $attributes ) && isset( $attributes['rsstype'] ) ) {
			$attributes['rssType'] = $attributes['rsstype'];
			unset( $attributes['rsstype'] );
		}

		// convert sub-array in string.
		if ( ! empty( $attributes ) && is_array( $attributes['rssType'] ) ) {
			$attributes['rssType'] = $attributes['rssType'][0];
		}

		// return resulting array.
		return $attributes;
	}
}

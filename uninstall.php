<?php
/**
 * File for Uninstall-handling.
 *
 * @package category-and-tag-feeds
 */

// if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// prevent also other direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// set field-name for meta-field to enable rss on category-taxonomy.
const LW_CF_CAT_META = 'lw_cf_rssfeed';

// remove all marker on categories where rss-feed-marker exist.
$query      = array(
	'taxonomy'   => 'category',
	'hide_empty' => '0',
	'meta_query' => array(
		array(
			'key'     => LW_CF_CAT_META,
			'value'   => '1',
			'compare' => '=',
		),
	),
);
$categories = get_categories( $query );
foreach ( $categories as $category ) {
	// -> remove marker.
	delete_term_meta( $category->term_id, LW_CF_CAT_META );
}

// remove all marker post-categories where rss-feed-marker exist.
$query     = array(
	'taxonomy'   => 'post_tag',
	'hide_empty' => '0',
	'meta_query' => array(
		array(
			'key'     => LW_CF_CAT_META,
			'value'   => '1',
			'compare' => '=',
		),
	),
);
$tag_array = get_categories( $query );
foreach ( $tag_array as $my_tag ) {
	// -> remove marker.
	delete_term_meta( $my_tag->term_id, LW_CF_CAT_META );
}

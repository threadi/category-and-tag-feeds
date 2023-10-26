<?php
/**
 * Plugin Name:       Category- and Tag-Feeds
 * Description:       Control feed-URLs in category- and tag-listing.
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Version:           @@VersionNumber@@
 * Author:            laOlaWeb
 * Author URI:        https://laolaweb.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       category-and-tag-feeds
 *
 * @package category-and-tag-feeds
 */

use lwCf\Helper;
use Elementor\Plugin;

// save plugin-path.
const LW_CF_PLUGIN = __FILE__;
// set field-name for meta-field to enable rss on category-taxonomy.
const LW_CF_CAT_META = 'lw_cf_rssfeed';

// embed admin-functions only in wp-admin.
if ( is_admin() ) {
	include_once 'inc/admin.php';
}

/**
 * Register an old-fashion Wordpress-widget only if Block-widgets are disabled.
 *
 * @return       void
 * @noinspection PhpUnused
 */
function lw_cf_register_widget(): void {
	if ( ! wp_use_widgets_block_editor() ) {
		include_once 'classes/class-helper.php';
		include_once 'classes/class-widget-categories.php';
		include_once 'classes/class-widget-tags.php';
		register_widget( 'LwCf\Widget_Categories' );
		register_widget( 'LwCf\Widget_Tags' );
	}
}
add_action( 'widgets_init', 'lw_cf_register_widget' );

/**
 * Add own CSS and JS for frontend.
 *
 * @return       void
 * @noinspection PhpUnused
 */
function lw_cf_frontend_enqueue(): void {
	wp_enqueue_style(
		'lw-cf-css',
		plugin_dir_url( LW_CF_PLUGIN ) . '/css/style.css',
		array(),
		filemtime( plugin_dir_path( LW_CF_PLUGIN ) . '/css/style.css' ),
	);
}
add_action( 'wp_enqueue_scripts', 'lw_cf_frontend_enqueue', PHP_INT_MAX );
add_action( 'admin_enqueue_scripts', 'lw_cf_frontend_enqueue', PHP_INT_MAX );

/**
 * Register the Gutenberg-Blocks with all necessary settings.
 *
 * @return       void
 * @noinspection PhpUnused
 */
function lw_cf_add_blocks(): void {
	// include Blocks only if Gutenberg is available.
	if ( function_exists( 'register_block_type' ) ) {
		register_block_type(
			plugin_dir_path( LW_CF_PLUGIN ) . 'blocks/categories/',
			array(
				'render_callback' => 'lw_cf_get_categories',
				'attributes'      => array(
					'rssType' => array(
						'type'    => 'array',
						'default' => array( 'rss' ),
					),
				),
			)
		);
		register_block_type(
			plugin_dir_path( LW_CF_PLUGIN ) . 'blocks/tags/',
			array(
				'render_callback' => 'lw_cf_get_tags',
				'attributes'      => array(
					'rssType' => array(
						'type'    => 'array',
						'default' => array( 'rss' ),
					),
				),
			)
		);
		wp_set_script_translations( 'lwcf-categories-editor-script', 'category-and-tag-feeds', trailingslashit( plugin_dir_path( LW_CF_PLUGIN ) ) . 'languages/' );
		wp_set_script_translations( 'lwcf-tags-editor-script', 'category-and-tag-feeds', trailingslashit( plugin_dir_path( LW_CF_PLUGIN ) ) . 'languages/' );
	}
}
add_action( 'init', 'lw_cf_add_blocks' );

/**
 * Output Tag-list.
 *
 * @param  array $attributes List of attributes.
 * @return string
 */
function lw_cf_get_tags( array $attributes ): string {
	// prepare attributes.
	include_once 'classes/class-helper.php';
	$attributes = Helper::prepare_attributes( $attributes );

	// get all RSS-feeds which are marked as published.
	$query = array(
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
	$tags  = get_tags( $query );
	if ( ! empty( $tags ) ) {

		// return list of tags.
		ob_start();
		echo '<ul class="lw-cf-rss-list">';
		foreach ( $tags as $tag ) {
			$link = get_tag_feed_link( $tag->term_id, $attributes['rssType'] );
			echo '<li><a href="' . esc_url( $link ) . '"><span class="dashicons dashicons-rss"></span> ' . esc_html( $tag->name ) . '</a></li>';
		}
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	// otherwise return empty string.
	return '';
}

/**
 * Output category-list.
 *
 * @param  array $attributes List of attributes.
 * @return string
 */
function lw_cf_get_categories( array $attributes ): string {
	// prepare attributes.
	include_once 'classes/class-helper.php';
	$attributes = Helper::prepare_attributes( $attributes );

	// get all RSS-feeds which are marked as published.
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
	if ( ! empty( $categories ) ) {

		// return list of categories.
		ob_start();
		echo '<ul class="lw-cf-rss-list">';
		foreach ( $categories as $category ) {
			$link = get_category_feed_link( $category->term_id, $attributes['rssType'] );
			echo '<li><a href="' . esc_url( $link ) . '"><span class="dashicons dashicons-rss"></span> ' . esc_html( $category->name ) . '</a></li>';
		}
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	// otherwise return hint in Gutenberg.
	return '';
}

/**
 * Add endpoint for requests from our own Block.
 *
 * @return       void
 * @noinspection PhpUnused
 */
function lw_cf_add_rest_api(): void {
	register_rest_route(
		'lwcf/v1',
		'/rssTypes/',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'lw_cf_api_return_rss_types',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'rest_api_init', 'lw_cf_add_rest_api' );

/**
 * Return available rssTypes.
 *
 * @param        WP_REST_Request $request The request-object.
 * @return       string[]
 * @noinspection PhpUnused
 * @noinspection PhpUnusedParameterInspection
 */
function lw_cf_api_return_rss_types( WP_REST_Request $request ): array {
	return lw_cf_get_rss_types();
}

/**
 * Get available rss-types incl. their translations
 *
 * @return array
 */
function lw_cf_get_rss_types(): array {
	global $wp_rewrite;

	// set labels.
	$labels = array(
		'feed' => __( 'Feed', 'category-and-tag-feeds' ),
		'rdf'  => __( 'RDF', 'category-and-tag-feeds' ),
		'rss'  => __( 'RSS 1.0', 'category-and-tag-feeds' ),
		'rss2' => __( 'RSS 2.0', 'category-and-tag-feeds' ),
		'atom' => __( 'Atom-Feed', 'category-and-tag-feeds' ),
	);

	// loop through array and set everything.
	$array = array();
	foreach ( $wp_rewrite->feeds as $feed ) {
		$label = $feed;
		if ( ! empty( $labels[ $feed ] ) ) {
			$label = $labels[ $feed ];
		}
		$array[ $feed ] = array(
			'label' => $label,
			'value' => $feed,
		);
	}
	return $array;
}

/**
 * Convert the array to one that elementor is using.
 *
 * @return array
 */
function lw_cf_get_rss_types_for_elementor(): array {
	$array = array();
	foreach ( lw_cf_get_rss_types() as $feed => $setting ) {
		$array[ $feed ] = $setting['label'];
	}
	return $array;
}

/**
 * Initialization of available shortcodes.
 *
 * @return       void
 * @noinspection PhpUnused
 */
function lw_cf_frontend_init(): void {
	add_shortcode( 'categoryFeeds', 'lw_cf_get_categories_shortcode' );
	add_shortcode( 'tagFeeds', 'lw_cf_get_tags_shortcode' );
}
add_action( 'init', 'lw_cf_frontend_init' );

/**
 * Get output for category-shortcode.
 *
 * @param  array $attributes The list of attributes.
 * @return string
 */
function lw_cf_get_categories_shortcode( array $attributes ): string {
	return lw_cf_get_categories( $attributes );
}

/**
 * Get output for tags-shortcode.
 *
 * @param  array $attributes The list of attributes.
 * @return string
 */
function lw_cf_get_tags_shortcode( array $attributes ): string {
	return lw_cf_get_tags( $attributes );
}

/**
 * Initialize the Elementor-Widgets.
 *
 * @return       void
 * @noinspection PhpUnused
 */
function lw_cf_add_elementor_widgets(): void {
	if ( did_action( 'elementor/loaded' ) ) {
		add_action( 'elementor/widgets/widgets_registered', 'lw_cf_register_elementor_widgets' );
	}
}
add_action( 'init', 'lw_cf_add_elementor_widgets' );

/**
 * Register all Elementor-widgets this plugin supports.
 *
 * @return       void
 * @noinspection PhpUnused
 */
function lw_cf_register_elementor_widgets(): void {
	include_once 'classes/class-category-widget.php';
	include_once 'classes/class-tag-widget.php';
	Plugin::instance()->widgets_manager->register( new lwCf\Category_Widget() );
	Plugin::instance()->widgets_manager->register( new lwCf\Tag_Widget() );
}

/**
 * Hide feed in html-head if it is not set to public.
 *
 * @param        string $link The called URL.
 * @return       string
 * @noinspection PhpUnused
 */
function lw_cf_hide_category_tag_feed_if_disabled( string $link ): string {
	$object = get_queried_object();
	if ( $object instanceof WP_Term ) {
		if ( 1 !== absint( get_term_meta( $object->term_id, LW_CF_CAT_META, true ) ) ) {
			return '';
		}
	}
	return $link;
}
add_filter( 'category_feed_link', 'lw_cf_hide_category_tag_feed_if_disabled' );

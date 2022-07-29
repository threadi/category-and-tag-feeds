<?php
/**
 * Plugin Name:       Category- and Tag-Feeds
 * Description:       Use the WordPress-generated RSS-feed-Links in category- and tag-listing and visible for your visitors in frontend.
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Version:           @@VersionNumber@@
 * Author:            laOlaWeb
 * Author URI:		  https://laolaweb.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       category-and-tag-feeds
 */

use lwCf\helper;
use Elementor\Plugin;

// save plugin-path
const LW_CF_PLUGIN = __FILE__;
// set field-name for meta-field to enable rss on category-taxonomy
const LW_CF_CAT_META = 'lw_cf_rssfeed';

// embed admin-functions only in wp-admin
if( is_admin() ) {
    require_once 'inc/admin.php';
}

/**
 * Initialize language
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_integration_init() {
    load_plugin_textdomain( 'category-and-tag-feeds', false, dirname( plugin_basename( LW_CF_PLUGIN ) ) . '/languages' );
}
add_action( 'init', 'lw_cf_integration_init', 0 );

/**
 * Register an old-fashion Wordpress-widget only if Block-widgets are disabled.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_register_widget() {
    if( !wp_use_widgets_block_editor() ) {
        require_once 'classes/class-helper.php';
        require_once 'classes/class-widget-categories.php';
        require_once 'classes/class-widget-tags.php';
        register_widget('LwCf\widgetCategories');
        register_widget('LwCf\widgetTags');
    }
}
add_action( 'widgets_init', 'lw_cf_register_widget' );

/**
 * Add own CSS and JS for frontend.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_frontend_enqueue() {
    wp_enqueue_style('lw-cf-css',
        plugin_dir_url(LW_CF_PLUGIN) . '/css/style.css',
        [],
        filemtime(plugin_dir_path(LW_CF_PLUGIN) . '/css/style.css'),
    );
}
add_action( 'wp_enqueue_scripts', 'lw_cf_frontend_enqueue', PHP_INT_MAX );
add_action( 'admin_enqueue_scripts', 'lw_cf_frontend_enqueue', PHP_INT_MAX );

/**
 * Register the Gutenberg-Blocks with all necessary settings.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_add_blocks()
{
    // include Blocks only if Gutenberg exists is set
    if( function_exists('register_block_type') ) {
        register_block_type(plugin_dir_path(LW_CF_PLUGIN).'blocks/categories/', [
            'render_callback' => 'lw_cf_get_categories',
            'attributes' => [
                'rssType' => [
                    'type' => 'array',
                    'default' => ['rss']
                ]
            ]
        ]);
        register_block_type(plugin_dir_path(LW_CF_PLUGIN).'blocks/tags/', [
            'render_callback' => 'lw_cf_get_tags',
            'attributes' => [
                'rssType' => [
                    'type' => 'array',
                    'default' => ['rss']
                ]
            ]
        ]);
        wp_set_script_translations('lwcf-categories-editor-script', 'category-and-tag-feeds', trailingslashit(plugin_dir_path(LW_CF_PLUGIN)) . 'languages/');
        wp_set_script_translations('lwcf-tags-editor-script', 'category-and-tag-feeds', trailingslashit(plugin_dir_path(LW_CF_PLUGIN)) . 'languages/');
    }
}
add_action( 'init', 'lw_cf_add_blocks', 10 );

/**
 * Output Tag-list.
 *
 * @param $attributes
 * @return string
 */
function lw_cf_get_tags( $attributes ): string
{
    // prepare attributes
    require_once 'classes/class-helper.php';
    $attributes = helper::prepareAttributes($attributes);

    // get all RSS-feeds which are marked as published
    $query = [
        'taxonomy' => 'post_tag',
        'hide_empty' => '0',
        'meta_query' => [
            [
                'key' => LW_CF_CAT_META,
                'value' => '1',
                'compare' => '='
            ]
        ]
    ];
    $tags = get_tags($query);
    if( !empty($tags) ) {

        // return list of tags
        ob_start();
        echo '<ul class="lw-cf-rss-list">';
        foreach( $tags as $tag ) {
            $link = get_tag_feed_link($tag->term_id, $attributes['rssType']);
            echo '<li><a href="'.esc_url($link).'"><span class="dashicons dashicons-rss"></span> '.esc_html($tag->name).'</a></li>';
        }
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    // otherwise return empty string
    return '';
}

/**
 * Output category-list.
 *
 * @param $attributes
 * @return string
 */
function lw_cf_get_categories( $attributes ): string
{
    // prepare attributes
    require_once 'classes/class-helper.php';
    $attributes = helper::prepareAttributes($attributes);

    // get all RSS-feeds which are marked as published
    $query = [
        'taxonomy' => 'category',
        'hide_empty' => '0',
        'meta_query' => [
            [
                'key' => LW_CF_CAT_META,
                'value' => '1',
                'compare' => '='
            ]
        ]
    ];
    $categories = get_categories($query);
    if( !empty($categories) ) {

        // return list of categories
        ob_start();
        echo '<ul class="lw-cf-rss-list">';
        foreach( $categories as $category ) {
            $link = get_category_feed_link($category->term_id, $attributes['rssType']);
            echo '<li><a href="'.esc_url($link).'"><span class="dashicons dashicons-rss"></span> '.esc_html($category->name).'</a></li>';
        }
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    // otherwise return hint in Gutenberg
    return '';
}

/**
 * Add endpoint for requests from our own Block.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_add_rest_api() {
    register_rest_route( 'lwcf/v1', '/rssTypes/', array(
        'methods' => WP_REST_SERVER::READABLE,
        'callback' => 'lw_cf_api_return_rss_types',
        'permission_callback' => function () {
            return current_user_can( 'edit_posts' );
        }
    ) );
}
add_action( 'rest_api_init', 'lw_cf_add_rest_api');

/**
 * Return available rssTypes.
 *
 * @param WP_REST_Request $request
 * @return string[]
 * @noinspection PhpUnused
 */
function lw_cf_api_return_rss_types( WP_REST_Request $request ): array
{
    return lw_cf_get_rss_types();
}

/**
 * Get available rss-types incl. their translations
 *
 * @return array
 */
function lw_cf_get_rss_types(): array
{
    global $wp_rewrite;

    // set labels
    $labels = [
        'feed' => __('Feed', 'category-and-tag-feeds'),
        'rdf' => __('RDF', 'category-and-tag-feeds'),
        'rss' => __('RSS 1.0', 'category-and-tag-feeds'),
        'rss2' => __('RSS 2.0', 'category-and-tag-feeds'),
        'atom' => __('Atom-Feed', 'category-and-tag-feeds'),
    ];

    // loop through array and set everything
    $array = [];
    foreach( $wp_rewrite->feeds as $feed ) {
        $label = $feed;
        if( !empty($labels[$feed]) ) {
            $label = $labels[$feed];
        }
        $array[$feed] = [
            'label' => $label,
            'value' => $feed
        ];
    }
    return $array;
}

/**
 * Convert the array to one that elementor is using.
 *
 * @return array
 */
function lw_cf_get_rss_types_for_elementor(): array
{
    $array = [];
    foreach( lw_cf_get_rss_types() as $feed => $setting ) {
        $array[$feed] = $setting['label'];
    }
    return $array;
}

/**
 * Initialization of available shortcodes.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_frontend_init() {
    add_shortcode('categoryFeeds', 'lw_cf_get_categories_shortcode' );
    add_shortcode('tagFeeds', 'lw_cf_get_tags_shortcode');
}
add_action( 'init', 'lw_cf_frontend_init' );

/**
 * Get output for category-shortcode.
 *
 * @param $attributes
 * @return string
 */
function lw_cf_get_categories_shortcode( $attributes ): string
{
    return lw_cf_get_categories($attributes);
}

/**
 * Get output for tags-shortcode.
 *
 * @param $attributes
 * @return string
 */
function lw_cf_get_tags_shortcode( $attributes ): string
{
    return lw_cf_get_tags($attributes);
}

/**
 * Initialize the Elementor-Widgets.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_add_elementor_widgets() {
    if ( did_action( 'elementor/loaded' ) ) {
        add_action('elementor/widgets/widgets_registered', 'lw_cf_register_elementor_widgets');
    }
}
add_action('init', 'lw_cf_add_elementor_widgets', 10);

/**
 * Register all Elementor-widgets this plugin supports.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_register_elementor_widgets() {
    require_once 'classes/class-categoryWidget.php';
    require_once 'classes/class-tagWidget.php';
    Plugin::instance()->widgets_manager->register(new lwCf\categoryWidget());
    Plugin::instance()->widgets_manager->register(new lwCf\tagWidget());
}

/**
 * Hide feed in html-head if it is not set to public.
 *
 * @param $link
 * @return mixed|void
 * @noinspection PhpUnused
 */
function lw_cf_hide_category_tag_feed_if_disabled( $link ) {
    $object = get_queried_object();
    if( $object instanceof WP_Term ) {
        if( get_term_meta($object->term_id, LW_CF_CAT_META, true) != 1 ) {
            return;
        }
    }
    return $link;
}
add_filter('category_feed_link', 'lw_cf_hide_category_tag_feed_if_disabled', 10, 1);
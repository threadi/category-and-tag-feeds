=== Category- and Tag-Feeds ===
Contributors: laolaweb, threadi
Tags: category, category feed, feed, tag, tag feed
Requires at least: 5.9.3
Tested up to: 6.6
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 1.1.4

Get full control over the output of WordPress-generated feeds of your categories and keywords!

== Description ==

Get full control over the output of WordPress-generated feeds of your categories and keywords! No more searching for feed URLs. Manage the visibility of the feeds in the category and keyword lists. Get all the possible feed URLs here too.

= Features =

* 2 Gutenberg blocks to output the list of publicly accessible feeds of categories and keywords
* also 2 Elementor widgets
* and 2 classic widgets and 2 Shortcodes
* if feeds of a category or keyword should not be publicly viewable, they are not at any place - nevertheless you can access the URLs of the feeds at any time

= Shortcodes =

* `[categoryFeeds]` to show a list of all public category-feeds
* `[tagFeeds]` to show a list of all public tag-feeds
* each Shortcode accept the parameter "rssType" to set the feed-format
* available feed-formats are the WordPress-defaults: rss, rss2, atom, feed, rdf as well as added formats by other plugins

== Installation ==

1. Upload "category-and-tag-feeds" to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to Posts > Categories or Tags to display the Feed-URLs or create a post, page or widget to create the output in frontend.

== Changelog ==

= 1.0.0 =
* Initial release

= 1.0.1 =
* Updated assets

= 1.0.2 =
* Compatibility with WordPress 6.1

= 1.0.3 =
* Compatibility with WordPress 6.2

= 1.1.0 =
* Cleanup build-scripts
* Compatibility with WordPress 6.3
* Code is now WordPress Coding Standards compatible

= 1.1.1 =
* Compatibility with WordPress 6.4
* Code is now WordPress Coding Standards 3.0 compatible
* Remove local embedded translation-files
* Fixed handling of bulk actions in categories and post-tags

= 1.1.2 =
* Removed all translation files from release
* Updated dependencies

= 1.1.3 =
* Added check for WCS on build of each release
* Code is now WordPress Coding Standards 3.1 compatible
* Compatibility with WordPress 6.5.3
* Updated dependencies

= 1.1.4 =
* Compatibility with WordPress 6.6.x
* Updated dependencies

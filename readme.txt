=== Category- and Tag-Feeds ===
Contributors: laolaweb, threadi
Tags: category, category feed, feed, tag, tag feed
Requires at least: 5.9.3
Tested up to: 6.1
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 1.0.0

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

1.0.0
-----
* Initial release
<?php

/**
 * Uninstall-handling
 */

// remove all marker on categories
// -> get all post-categories where rss-feed-marker exist
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
foreach( $categories as $category ) {
    // -> remove marker
    delete_term_meta( $category->term_id, LW_CF_CAT_META);
}

// remove all marker on tags
// -> get all post-categories where rss-feed-marker exist
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
$tags = get_categories($query);
foreach( $tags as $tag ) {
    // -> remove marker
    delete_term_meta( $tag->term_id, LW_CF_CAT_META);
}

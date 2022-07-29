<?php

/**
 * Add own CSS and JS for backend.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_add_js_admin() {
    // admin-specific styles
    wp_enqueue_style('lw-cf-admin-css',
        plugin_dir_url(LW_CF_PLUGIN) . '/admin/style.css',
        [],
        filemtime(plugin_dir_path(LW_CF_PLUGIN) . '/admin/style.css'),
    );
}
add_action( 'admin_enqueue_scripts', 'lw_cf_add_js_admin', PHP_INT_MAX );

/**
 * Add post fields to each category-taxonomy.
 *
 * @param $taxonomy
 *
 * @return void
 * @noinspection PhpUnused
 * @noinspection PhpUnusedParameterInspection
 */
function lw_cf_add_category_fields( $taxonomy ): void {
    ?>
    <div class="form-field term-rss">
        <label for="tag-rss"><?php echo __('Show feeds in frontend', 'category-and-tag-feeds'); ?>:</label>
        <input name="rss" id="tag-rss" type="checkbox" value="1">
    </div>
    <?php
}
add_action( 'category_add_form_fields', 'lw_cf_add_category_fields', 10);
add_action( 'post_tag_add_form_fields', 'lw_cf_add_category_fields', 10);

/**
 * Add post fields to each category-taxonomy.
 *
 * @param $term
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_edit_category_fields( $term ): void {
    $checked = get_term_meta($term->term_id, LW_CF_CAT_META, true) == 1 ? ' checked="checked"' : '';

    ?>
    <tr class="form-field term-rss-wrap">
        <th><label for="tag-rss"><?php echo __('Show feeds in frontend', 'category-and-tag-feeds'); ?>:</label></th>
        <td><input name="rss" id="tag-rss" type="checkbox" value="1"<?php echo esc_attr($checked); ?>></td>
    </tr>
    <?php
}
add_action( 'category_edit_form_fields', 'lw_cf_edit_category_fields', 10);
add_action( 'post_tag_edit_form_fields', 'lw_cf_edit_category_fields', 10);

/**
 * Save term fields in backend.
 *
 * @param $term_id
 * @param string $tt_id
 *
 * @return void
 * @noinspection PhpUnused
 * @noinspection PhpUnusedParameterInspection
 * @noinspection PhpMissingParamTypeInspection
 */
function lw_cf_save_category_fields( $term_id, $tt_id = '' ): void {
    if( isset($_POST['rss']) ) {
        update_term_meta($term_id, LW_CF_CAT_META, 1);
    }
    else {
        delete_term_meta($term_id, LW_CF_CAT_META);
    }
}
add_action( 'edit_category', 'lw_cf_save_category_fields', 10, 3);
add_action( 'edit_post_tag', 'lw_cf_save_category_fields', 10, 3);

/**
 * Add row to category-table.
 *
 * @param $columns
 * @return array
 * @noinspection PhpUnused
 */
function lw_cf_add_category_column( $columns ): array {
    $columns['feed_public'] = __('Show feeds public', 'category-and-tag-feeds');
    $columns['feeds'] = __('Feed-URLs', 'category-and-tag-feeds');
    return $columns;
}
add_filter( 'manage_edit-category_columns', 'lw_cf_add_category_column', 10, 1 );
add_filter( 'manage_edit-post_tag_columns', 'lw_cf_add_category_column', 10, 1 );

/**
 * Add content in category-table.
 *
 * @param $string
 * @param $column_name
 * @param $term_id
 * @return void
 * @noinspection PhpUnused
 * @noinspection PhpUnusedParameterInspection
 */
function lw_cf_category_column_content( $string, $column_name,$term_id ): void {
    switch( $column_name ) {
        case "feeds":
            echo '<ul class="lw-cf-iconlist">';
            foreach( lw_cf_get_rss_types() as $feed => $settings ) {
                // get WordPress-generated rss-link
                $link = get_category_feed_link($term_id, $feed);

                // output
                echo '<li><a href="' . esc_url($link) . '" target="_blank"><span class="dashicons dashicons-rss"></span> '.esc_html($settings['label']).'</a></li>';
            }
            echo "</ul>";
            break;
        case "feed_public":
            $public = get_term_meta($term_id,LW_CF_CAT_META, 1);

            // output check-icon if it is set to public
            if( $public == 1 ) {
                echo '<span class="dashicons dashicons-yes"></span>';
            }
            break;
    }
}
add_filter ('manage_category_custom_column', 'lw_cf_category_column_content', 10,3);

/**
 * Add content in tab-table.
 *
 * @param $string
 * @param $column_name
 * @param $term_id
 * @return void
 * @noinspection PhpUnused
 * @noinspection PhpUnusedParameterInspection
 */
function lw_cf_tag_column_content( $string, $column_name,$term_id ): void {
    global $wp_rewrite;
    switch( $column_name ) {
        case "feeds":
            echo '<ul class="lw-cf-iconlist">';
            foreach( lw_cf_get_rss_types() as $feed => $settings ) {
                // get WordPress-generated rss-link
                $link = get_tag_feed_link($term_id, $feed);

                // output
                echo '<li><a href="' . esc_url($link) . '" target="_blank"><span class="dashicons dashicons-rss"></span> '.esc_html($settings['label']).'</a></li>';
            }
            echo "</ul>";
            break;
        case "feed_public":
            $public = get_term_meta($term_id,LW_CF_CAT_META, 1);

            // output check-icon if it is set to public
            if( $public == 1 ) {
                echo '<span class="dashicons dashicons-yes"></span>';
            }
            break;
    }
}
add_filter ('manage_post_tag_custom_column', 'lw_cf_tag_column_content', 10,3);

/**
 * Add bulk action to enable or disable multiple feeds on categories.
 *
 * @param $bulk_array
 * @return array
 * @noinspection PhpUnused
 */
function lw_cf_add_category_bulk_action( $bulk_array ): array {
    $bulk_array['show_rss'] = __('Show feeds in frontend', 'category-and-tag-feeds');
    $bulk_array['hide_rss'] = __('Hide feeds in frontend', 'category-and-tag-feeds');
    return $bulk_array;
}
add_filter( 'bulk_actions-edit-category', 'lw_cf_add_category_bulk_action' );
add_filter( 'bulk_actions-edit-post_tag', 'lw_cf_add_category_bulk_action' );

/**
 * Handle bulk action in categories.
 *
 * @param $redirect
 * @param $action
 * @param $object_ids
 * @return false|string
 * @noinspection PhpUnused
 */
function lw_cf_category_bulk_action_handler( $redirect, $action, $object_ids) {
    // cleanup redirect url
    $redirect = remove_query_arg( ['show_rss', 'hide_rss', 'show_rss_done', 'hide_rss_done' ], $redirect );

    // enable rss on marked categories
    if ( $action == 'show_rss' ) {
        foreach ( $object_ids as $term_id ) {
            update_term_meta($term_id,LW_CF_CAT_META, 1);
        }

        // do not forget to add query args to URL because we will show notices later
        $redirect = add_query_arg(
            'show_rss_done',
            count( $object_ids ),
            $redirect );
    }

    // disable rss on marked categories
    if ( $action == 'hide_rss' ) {
        foreach ( $object_ids as $term_id ) {
            delete_term_meta($term_id,LW_CF_CAT_META);
        }
        // add marker in URL to show hint
        $redirect = add_query_arg(
            'hide_rss_done',
            count( $object_ids ),
            $redirect
        );
    }

    // return redirect-url
    return $redirect;
}
add_filter( 'handle_bulk_actions-edit-category', 'lw_cf_category_bulk_action_handler', 10, 3 );
add_filter( 'handle_bulk_actions-edit-post_tag', 'lw_cf_category_bulk_action_handler', 10, 3 );

/**
 * Show hint after running bulk action.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_category_bulk_notices(): void {
    if ( ! empty( $_REQUEST['show_rss_done'] ) && absint($_REQUEST['show_rss_done']) == 1 ) {
        echo '<div id="message" class="updated notice is-dismissible">
			<p>'.__('The feeds of the chosen items are now public visible.', 'category-and-tag-feeds').'</p>
		</div>';
    }

    if ( ! empty( $_REQUEST['hide_rss_done'] ) && absint($_REQUEST['hide_rss_done']) == 1 ) {
        echo '<div id="message" class="updated notice is-dismissible">
			<p>'.__('The feeds of the chosen items are now not public visible.', 'category-and-tag-feeds').'</p>
		</div>';
    }
}
add_action( 'admin_notices', 'lw_cf_category_bulk_notices' );
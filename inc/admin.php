<?php
/**
 * File for admin functions.
 *
 * @package category-and-tag-feeds
 */

/**
 * Add own CSS and JS for backend.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_add_js_admin(): void {
	// admin-specific styles.
	wp_enqueue_style(
		'lw-cf-admin-css',
		plugin_dir_url( LW_CF_PLUGIN ) . '/admin/style.css',
		array(),
		filemtime( plugin_dir_path( LW_CF_PLUGIN ) . '/admin/style.css' ),
	);
}
add_action( 'admin_enqueue_scripts', 'lw_cf_add_js_admin', PHP_INT_MAX );

/**
 * Add post fields to each category-taxonomy.
 *
 * @return void
 */
function lw_cf_add_category_fields(): void {
	?>
	<div class="form-field term-rss">
		<label for="tag-rss"><?php echo esc_html__( 'Show feeds in frontend', 'category-and-tag-feeds' ); ?>:</label>
		<input name="rss" id="tag-rss" type="checkbox" value="1">
	</div>
	<?php
}
add_action( 'category_add_form_fields', 'lw_cf_add_category_fields', 10, 0 );
add_action( 'post_tag_add_form_fields', 'lw_cf_add_category_fields', 10, 0 );

/**
 * Add post fields to each category-taxonomy.
 *
 * @param WP_Term $term The Term-obejct.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_edit_category_fields( WP_Term $term ): void {
	// get checked marker.
	$checked = 1 === absint( get_term_meta( $term->term_id, LW_CF_CAT_META, true ) ) ? ' checked="checked"' : '';

	// output.
	?>
	<tr class="form-field term-rss-wrap">
		<th><label for="tag-rss"><?php echo esc_html__( 'Show feeds in frontend', 'category-and-tag-feeds' ); ?>:</label></th>
		<td><input name="rss" id="tag-rss" type="checkbox" value="1"<?php echo esc_attr( $checked ); ?>></td>
	</tr>
	<?php
}
add_action( 'category_edit_form_fields', 'lw_cf_edit_category_fields', 10 );
add_action( 'post_tag_edit_form_fields', 'lw_cf_edit_category_fields', 10 );

/**
 * Save term fields in backend.
 *
 * @param int $term_id The term ID.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_save_category_fields( int $term_id ): void {
	$rss = filter_input( INPUT_POST, 'rss', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	if ( ! is_null( $rss ) ) {
		update_term_meta( $term_id, LW_CF_CAT_META, 1 );
	} else {
		delete_term_meta( $term_id, LW_CF_CAT_META );
	}
}
add_action( 'edit_category', 'lw_cf_save_category_fields', 10, 1 );
add_action( 'edit_post_tag', 'lw_cf_save_category_fields', 10, 1 );

/**
 * Add row to category-table.
 *
 * @param array $columns List of columns.
 * @return array
 */
function lw_cf_add_category_column( array $columns ): array {
	$columns['feed_public'] = __( 'Show feeds public', 'category-and-tag-feeds' );
	$columns['feeds']       = __( 'Feed-URLs', 'category-and-tag-feeds' );
	return $columns;
}
add_filter( 'manage_edit-category_columns', 'lw_cf_add_category_column' );
add_filter( 'manage_edit-post_tag_columns', 'lw_cf_add_category_column' );

/**
 * Add content in category-table.
 *
 * @param string $column_content The column content.
 * @param string $column_name The column name.
 * @param int    $term_id The term ID.
 * @return void
 * @noinspection PhpUnused
 * @noinspection PhpUnusedParameterInspection
 */
function lw_cf_category_column_content( string $column_content, string $column_name, int $term_id ): void {
	switch ( $column_name ) {
		case 'feeds':
			echo '<ul class="lw-cf-iconlist">';
			foreach ( lw_cf_get_rss_types() as $feed => $settings ) {
				// get WordPress-generated rss-link.
				$link = get_category_feed_link( $term_id, $feed );

				// output.
				echo '<li><a href="' . esc_url( $link ) . '" target="_blank"><span class="dashicons dashicons-rss"></span> ' . esc_html( $settings['label'] ) . '</a></li>';
			}
			echo '</ul>';
			break;
		case 'feed_public':
			$public = absint( get_term_meta( $term_id, LW_CF_CAT_META, 1 ) );

			// output check-icon if it is set to public.
			if ( 1 === $public ) {
				echo '<span class="dashicons dashicons-yes"></span>';
			}
			break;
	}
}
add_filter( 'manage_category_custom_column', 'lw_cf_category_column_content', 10, 3 );

/**
 * Add content in tab-table.
 *
 * @param string $column_content Column content.
 * @param string $column_name The column name.
 * @param int    $term_id The term ID.
 * @return void
 * @noinspection PhpUnused
 * @noinspection PhpUnusedParameterInspection
 */
function lw_cf_tag_column_content( string $column_content, string $column_name, int $term_id ): void {
	switch ( $column_name ) {
		case 'feeds':
			echo '<ul class="lw-cf-iconlist">';
			foreach ( lw_cf_get_rss_types() as $feed => $settings ) {
				// get WordPress-generated rss-link.
				$link = get_tag_feed_link( $term_id, $feed );

				// output.
				echo '<li><a href="' . esc_url( $link ) . '" target="_blank"><span class="dashicons dashicons-rss"></span> ' . esc_html( $settings['label'] ) . '</a></li>';
			}
			echo '</ul>';
			break;
		case 'feed_public':
			$public = absint( get_term_meta( $term_id, LW_CF_CAT_META, 1 ) );

			// output check-icon if it is set to public.
			if ( 1 === $public ) {
				echo '<span class="dashicons dashicons-yes"></span>';
			}
			break;
	}
}
add_filter( 'manage_post_tag_custom_column', 'lw_cf_tag_column_content', 10, 3 );

/**
 * Add bulk action to enable or disable multiple feeds on categories.
 *
 * @param array $bulk_array The list of bulk actions.
 * @return array
 * @noinspection PhpUnused
 */
function lw_cf_add_category_bulk_action( array $bulk_array ): array {
	$bulk_array['show_rss'] = __( 'Show feeds in frontend', 'category-and-tag-feeds' );
	$bulk_array['hide_rss'] = __( 'Hide feeds in frontend', 'category-and-tag-feeds' );
	return $bulk_array;
}
add_filter( 'bulk_actions-edit-category', 'lw_cf_add_category_bulk_action' );
add_filter( 'bulk_actions-edit-post_tag', 'lw_cf_add_category_bulk_action' );

/**
 * Handle bulk action in categories.
 *
 * @param string $redirect The called URL.
 * @param string $action The called action.
 * @param array  $object_ids The list of marked IDs.
 * @return string
 * @noinspection PhpUnused
 */
function lw_cf_category_bulk_action_handler_categories( string $redirect, string $action, array $object_ids ): string {
	$redirect = add_query_arg(
		array(
			'taxonomy' => 'category',
		),
		$redirect
	);

	// return redirect-url.
	return lw_cf_category_bulk_action_handler( $redirect, $action, $object_ids );
}
add_filter( 'handle_bulk_actions-edit-category', 'lw_cf_category_bulk_action_handler_categories', 10, 3 );

/**
 * Handle bulk action in tags.
 *
 * @param string $redirect The called URL.
 * @param string $action The called action.
 * @param array  $object_ids The list of marked IDs.
 * @return string
 * @noinspection PhpUnused
 */
function lw_cf_category_bulk_action_handler_tags( string $redirect, string $action, array $object_ids ): string {
	$redirect = add_query_arg(
		array(
			'taxonomy' => 'post_tag',
		),
		$redirect
	);

	// return redirect-url.
	return lw_cf_category_bulk_action_handler( $redirect, $action, $object_ids );
}
add_filter( 'handle_bulk_actions-edit-post_tag', 'lw_cf_category_bulk_action_handler_tags', 10, 3 );

/**
 * Handle bulk action in tags.
 *
 * @param string $redirect The called URL.
 * @param string $action The called action.
 * @param array  $object_ids The list of marked IDs.
 * @return string
 * @noinspection PhpUnused
 */
function lw_cf_category_bulk_action_handler( string $redirect, string $action, array $object_ids ): string {
	$redirect = remove_query_arg( array( 'show_rss', 'hide_rss', 'show_rss_done', 'hide_rss_done' ), $redirect );

	// enable rss on marked categories.
	if ( 'show_rss' === $action ) {
		foreach ( $object_ids as $term_id ) {
			update_term_meta( $term_id, LW_CF_CAT_META, 1 );
		}

		// do not forget to add query args to URL because we will show notices later.
		$redirect = add_query_arg(
			'show_rss_done',
			count( $object_ids ),
			$redirect
		);
	}

	// disable rss on marked categories.
	if ( 'hide_rss' === $action ) {
		foreach ( $object_ids as $term_id ) {
			delete_term_meta( $term_id, LW_CF_CAT_META );
		}
		// add marker in URL to show hint.
		$redirect = add_query_arg(
			'hide_rss_done',
			count( $object_ids ),
			$redirect
		);
	}

	return $redirect;
}

/**
 * Show hint after running bulk action.
 *
 * @return void
 * @noinspection PhpUnused
 */
function lw_cf_category_bulk_notices(): void {
	$show_rss_done = filter_input( INPUT_GET, 'show_rss_done', FILTER_SANITIZE_NUMBER_INT );
	if ( 1 === absint( $show_rss_done ) ) {
		echo '<div id="message" class="updated notice is-dismissible">
			<p>' . esc_html__( 'The feeds of the chosen items are now public visible.', 'category-and-tag-feeds' ) . '</p>
		</div>';
	}

	$hide_rss_done = filter_input( INPUT_GET, 'hide_rss_done', FILTER_SANITIZE_NUMBER_INT );
	if ( 1 === absint( $hide_rss_done ) ) {
		echo '<div id="message" class="updated notice is-dismissible">
			<p>' . esc_html__( 'The feeds of the chosen items are now not public visible.', 'category-and-tag-feeds' ) . '</p>
		</div>';
	}
}
add_action( 'admin_notices', 'lw_cf_category_bulk_notices' );

<?php
/**
 * File for helper-functions for this plugin.
 *
 * @package category-and-tag-feeds
 */

namespace LwCf;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Define helper as trait.
 */
trait Widget_Helper {

	/**
	 * Get the fields for this widget.
	 *
	 * @return array<string,mixed>
	 */
	private function get_widget_fields(): array {
		$feed_type_list = array();
		foreach ( lw_cf_get_rss_types() as $feed => $settings ) {
			$feed_type_list[ $feed ] = $settings['label'];
		}

		return array(
			'rssType' => array(
				'type'   => 'select',
				'title'  => __( 'Choose feed-type to show', 'category-and-tag-feeds' ),
				'std'    => 'rss',
				'values' => $feed_type_list,
			),
		);
	}

	/**
	 * Save update on widget.
	 *
	 * @param array<string,array<string,string>> $fields List of fields.
	 * @param array<string,string>               $new_instance The new instance.
	 * @param array<string,string>               $instance The old instance.
	 * @return array<string,string>
	 */
	public function update_widget( array $fields, array $new_instance, array $instance ): array {
		foreach ( $fields as $name => $field ) {
			if ( 'select' === $field['type'] ) {
				$instance[ $name ] = sanitize_text_field( $new_instance[ $name ] );
			}
		}
		return $instance;
	}

	/**
	 * Get and output widget form.
	 *
	 * @param array<string,mixed>  $fields List of fields.
	 * @param array<string,string> $instance The instance.
	 * @return void
	 */
	public function get_widget_form( array $fields, array $instance ): void {
		foreach ( $fields as $name => $field ) {
			// get actual value.
			if ( 'select' === $field['type'] ) {
				$selected_value = array( ! empty( $instance[ $name ] ) ? $instance[ $name ] : $field['std'] );
				?>
				<p>
					<label
						for="<?php echo esc_attr( $this->get_field_name( $name ) ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
					<select class="widefat" id="<?php echo esc_attr( $this->get_field_name( $name ) ); ?>" name="
						<?php
						echo esc_attr( $this->get_field_name( $name ) );
						echo ( isset( $field['multiple'] ) && false !== $field['multiple'] ) ? '[]' : '';
						?>
						">
						<?php
						foreach ( $field['values'] as $value => $title ) {
							?>
							<option
								value="<?php echo esc_attr( $value ); ?>"<?php echo( in_array( $value, $selected_value, true ) ? ' selected="selected"' : '' ); ?>><?php echo esc_html( $title ); ?></option>
							<?php
						}
						?>
					</select>
				</p>
				<?php
			}
		}
	}
}

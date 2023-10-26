<?php
/**
 * File for category widget.
 *
 * @package category-and-tag-feeds
 */

namespace LwCf;

use WP_Widget;

/**
 * Object to provide an old-fashion widget for categories.
 */
class Widget_Categories extends WP_Widget {

	use Helper;

	/**
	 * Initialize this widget.
	 */
	public function __construct() {
		$widget_options = array(
			'classname'   => 'lwCfRss\WidgetCategories',
			'description' => __( 'Provides a Widget to show list of public available RSS-feeds of categories.', 'category-and-tag-feeds' ),
		);
		parent::__construct(
			'LwCfRssWidgetCategories',
			__( 'Feed-list of categories', 'category-and-tag-feeds' ),
			$widget_options
		);
	}

	/**
	 * Get the fields for this widget.
	 *
	 * @return array[]
	 */
	private function getFields(): array {
		return $this->get_widget_fields();
	}

	/**
	 * Add entry-formular with settings for the widget.
	 *
	 * @param array $instance Current settings.
	 * @return void
	 */
	public function form( $instance ): void {
		$this->get_widget_form( $this->getFields(), $instance );
	}

	/**
	 * Save updated settings from the formular.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ): array {
		return $this->update_widget( $this->getFields(), $new_instance, $old_instance );
	}

	/**
	 * Output of the widget in frontend.
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 * @return void
	 */
	public function widget( $args, $instance ): void {
		echo wp_kses_post( lw_cf_get_categories( $instance ) );
	}
}

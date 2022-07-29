<?php

namespace LwCf;

use WP_Widget;

/**
 * Object to provide an old-fashion widget for tags.
 */
class widgetTags extends WP_Widget {

    use helper;

    /**
     * Initialize this widget.
     */
    public function __construct() {
        $widget_options = array (
            'classname' => 'lwCfRss\WidgetTags',
            'description' => __('Provides a Widget to show list of public available RSS-feeds of tags.', 'category-and-tag-feeds')
        );
        parent::__construct(
            'LwCfRssWidgetTags',
            __( 'RSS-list of tags', 'category-and-tag-feeds' ),
            $widget_options
        );
    }

    /**
     * Get the fields for this widget.
     *
     * @return array[]
     */
    private function getFields(): array
    {
        return $this->getWidgetFields();
    }

    /**
     * Add entry-formular with settings for the widget.
     *
     * @param $instance
     * @return void
     */
    function form( $instance ) {
        $this->getWidgetForm( $this->getFields(), $instance );
    }

    /**
     * Save updated settings from the formular.
     *
     * @param $new_instance
     * @param $old_instance
     * @return array
     */
    function update( $new_instance, $old_instance ): array
    {
        return $this->updateWidget( $this->getFields(), $new_instance, $old_instance );
    }

    /**
     * Output of the widget in frontend.
     *
     * @param $args
     * @param $settings
     * @return void
     */
    function widget( $args, $settings ) {
        echo lw_cf_get_tags( $settings );
    }

}
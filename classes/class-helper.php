<?php

namespace lwCf;

trait helper {

    /**
     * Get the fields for this widget.
     *
     * @return array[]
     */
    private function getWidgetFields(): array
    {
        $feedTypeList = [];
        foreach( lw_cf_get_rss_types() as $feed => $settings ) {
            $feedTypeList[$feed] = $settings['label'];
        }

        return [
            'rssType'     => array(
                'type'          => 'select',
                'title'         => __( 'Choose feed-type to show', 'category-and-tag-feeds' ),
                'std'           => 'rss',
                'values'        => $feedTypeList
            ),
        ];
    }

    /**
     * Save update on widget.
     *
     * @param $fields
     * @param $new_instance
     * @param $instance
     * @return array
     */
    public function updateWidget( $fields, $new_instance, $instance ): array
    {
        foreach( $fields as $name => $field ) {
            switch( $field["type"] ) {
                case "select":
                    $instance[$name] = sanitize_text_field($new_instance[$name]);
                    break;
            }
        }
        return $instance;
    }

    /**
     * Get and output widget form.
     *
     * @param $fields
     * @param $instance
     * @return void
     */
    public function getWidgetForm( $fields, $instance ) {
        foreach( $fields as $name => $field ) {
            switch( $field["type"] ) {
                case "select":
                    // get actual value
                    $selectedValue = [!empty($instance[$name]) ? $instance[$name] : $field["std"]];
                    ?>
                    <p>
                        <label for="<?php echo esc_attr($this->get_field_name($name)); ?>"><?php echo esc_html($field["title"]); ?></label>
                        <select class="widefat" id="<?php echo esc_attr($this->get_field_name($name)); ?>" name="<?php echo esc_attr($this->get_field_name($name));echo (isset($field["multiple"]) && false !== $field["multiple"]) ? '[]' : ''; ?>">
                            <?php
                            foreach( $field["values"] as $value => $title ) {
                                ?><option value="<?php echo esc_attr($value); ?>"<?php echo (in_array($value, $selectedValue) ? ' selected="selected"' : ''); ?>><?php echo esc_html($title); ?></option><?php
                            }
                            ?>
                        </select>
                    </p>
                    <?php
                    break;
            }
        }
    }

    /**
     * Convert attribute-array to usable by own functions.
     *
     * @param $attributes
     * @return mixed
     */
    public static function prepareAttributes( $attributes ) {
        // set default rssType of none is set
        if( empty($attributes) ) {
            $attributes['rssType'] = 'rss';
        }

        // convert lowercase rssType to uppercase
        if( !empty($attributes) && isset($attributes['rsstype']) ) {
            $attributes['rssType'] = $attributes['rsstype'];
            unset($attributes['rsstype']);
        }

        // convert sub-array in string
        if( !empty($attributes) && is_array($attributes['rssType']) ) {
            $attributes['rssType'] = $attributes['rssType'][0];
        }

        // return resulting array
        return $attributes;
    }

}
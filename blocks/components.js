/**
 * Update rssType-setting on Block
 *
 * @param newValue
 * @param object
 */
export const onChangeRssType = ( newValue, object ) => {
    object.setAttributes( { rssType: newValue } );
}

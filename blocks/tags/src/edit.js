/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * Add individual dependencies.
 */
import {
	PanelBody,
	SelectControl
} from '@wordpress/components';
import {
	InspectorControls,
	useBlockProps
} from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import {onChangeRssType} from "../../components";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @param object
 * @return {WPElement} Element to render.
 */
export default function Edit( object ) {

	/**
	 * Collect return for the edit-function
	 */
	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'category-and-tag-feeds' ) }>
					<SelectControl
						label={__('Choose feed-type to show', 'category-and-tag-feeds')}
						value={ object.attributes.rssType }
						options={ [
							{ label: __('rss', 'category-and-tag-feeds'), value: 'rss' },
							{ label: __('feed', 'category-and-tag-feeds'), value: 'feed' }
						] }
						onChange={ value => onChangeRssType( value, object ) }
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block="lwcf/tags"
				attributes={ object.attributes }
				httpMethod="POST"
			/>
		</div>
	);
}

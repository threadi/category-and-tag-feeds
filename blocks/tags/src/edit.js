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
	SelectControl,
	Placeholder
} from '@wordpress/components';
import {
	InspectorControls,
	useBlockProps
} from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import {onChangeRssType} from "../../components";
const { useSelect } = wp.data;

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
	 * Define dispatch for request of available rssTypes
	 */
	let dispatch = wp.data.dispatch;
	dispatch( 'core' ).addEntities( [
		{
			name: 'rssTypes', // route name
			kind: 'lwcf/v1', // namespace
			baseURL: '/lwcf/v1/rssTypes',
			key: 'value' // API path without /wp-json
		}
	]);

	/**
	 * Get available rssTypes
	 */
	let rssTypes = useSelect( ( select ) => {
		return select('core').getEntityRecords('lwcf/v1', 'rssTypes', {}, [] ) || null;
	});

	/**
	 * Create helper component if response from server for rendering is empty.
	 *
	 * @returns {JSX.Element}
	 */
	let emptyResponsePlaceholder = function() {
		return (
			<Placeholder icon='list-view' label={ __('Hint', 'category-and-tag-feeds') }>
				{ __( 'Actually no tag is enabled for public view. Please enable them through the following link:', 'category-and-tag-feeds' )}
				<a href="edit-tags.php?taxonomy=post_tag" target="_blank">{__('Go to tag-list', 'category-and-tag-feeds')}</a>
			</Placeholder>
		);
	};

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
						options={ rssTypes }
						onChange={ value => onChangeRssType( value, object ) }
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block="lwcf/tags"
				attributes={ object.attributes }
				httpMethod="POST"
				EmptyResponsePlaceholder={emptyResponsePlaceholder}
			/>
		</div>
	);
}

import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps( {
		className: 'brophy-quote-form',
	} );

	return (
		<div { ...blockProps }>
			<RichText
				tagName="h2"
				className="brophy-quote-form__heading"
				value={ attributes.heading }
				placeholder={ __( 'Add a heading…', 'brophy-quote-form' ) }
				onChange={ ( heading ) => setAttributes( { heading } ) }
				allowedFormats={ [] }
			/>
			<div className="brophy-quote-form__body">
				<p className="brophy-quote-form__placeholder">
					{ __( 'Quote form fields', 'brophy-quote-form' ) }
				</p>
			</div>
		</div>
	);
}

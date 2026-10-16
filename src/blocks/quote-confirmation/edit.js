import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps( {
		className: 'bqf-quote-confirmation',
	} );

	return (
		<div { ...blockProps }>
			<RichText
				tagName="p"
				className="bqf-quote-confirmation__message"
				value={ attributes.message }
				placeholder={ __( 'Add a confirmation message…', 'brophy-quote-form' ) }
				onChange={ ( message ) => setAttributes( { message } ) }
			/>
		</div>
	);
}

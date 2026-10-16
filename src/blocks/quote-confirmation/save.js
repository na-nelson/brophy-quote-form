import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const blockProps = useBlockProps.save( {
		className: 'bqf-quote-confirmation',
	} );

	return (
		<div { ...blockProps }>
			<RichText.Content
				tagName="p"
				className="bqf-quote-confirmation__message"
				value={ attributes.message }
			/>
		</div>
	);
}

import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import './admin.scss';

function AdminPage() {
	return (
		<div className="bqf-admin">
			<h3>Quote Form Shortcode Options</h3>
			<p>The default option has both the form and results in the same page.<br/>
			<strong style={{ fontFamily: 'monospace' }}>[brophy_quote_form]</strong></p>

			<p>Add 'results_page' to show the full form and have the results display on a separate page.<br/>
			<strong style={{ fontFamily: 'monospace' }}>[brophy_quote_form results_page="/example/page-path"]</strong></p>

			<p>Add 'form_type="intro"' to show an abbreviated form. This option requires a 'results_page' to be set.<br/>
			<strong style={{ fontFamily: 'monospace' }}>[brophy_quote_form form_type="intro" results_page="/example/page-path"]</strong></p>
		</div>
	);
}

const container = document.getElementById( 'bqf-admin-root' );

if ( container ) {
	createRoot( container ).render( <AdminPage /> );
}

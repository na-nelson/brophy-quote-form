import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import './admin.scss';

function AdminPage() {
	return (
		<div className="bqf-admin">
			<p className="bqf-admin__intro">
				{ __( 'Settings for the quote form will live here. [brophy_quote_form results_page="page-path"]', 'brophy-quote-form' ) }
			</p>
		</div>
	);
}

const container = document.getElementById( 'bqf-admin-root' );

if ( container ) {
	createRoot( container ).render( <AdminPage /> );
}

import './form.scss';

const bqfZipPattern = /^\d{5}(-\d{4})?$/;
const bqfEmailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const bqfFields = {
	quoteType: {
		type: 'radio',
		name: 'quote-type',
	},
	qty20: {
		type: 'select',
		name: 'qty-20',
		id: 'qty-20',
	},
	qty40: {
		type: 'select',
		name: 'qty-40',
		id: 'qty-40',
	},
	zip: {
		type: 'text',
		name: 'zip',
		id: 'zip',
	},
	newUsed: {
		type: 'select',
		name: 'new-used',
		id: 'new-used',
		condition: 'purchase',
	},
	duration: {
		type: 'select',
		name: 'duration',
		id: 'duration',
		condition: 'rent',
	},
	deliveryDate: {
		type: 'date',
		name: 'delivery-date',
		id: 'delivery-date',
	},
	email: {
		type: 'text',
		name: 'email',
		id: 'email',
	},
	fullName: {
		type: 'text',
		name: 'full-name',
		id: 'full-name',
	},
	phone: {
		type: 'text',
		name: 'phone',
		id: 'phone',
	},
};

document.querySelectorAll( '.bqf-form' ).forEach( ( $form ) => {
	$form.addEventListener( 'submit', ( event ) => {
		bqfHandleSubmit( event, $form );
	} );
} );

function bqfToday() {
	const now = new Date();
	const month = String( now.getMonth() + 1 ).padStart( 2, '0' );
	const day = String( now.getDate() ).padStart( 2, '0' );

	return `${ now.getFullYear() }-${ month }-${ day }`;
}

function bqfQuoteType( $form ) {
	const $checked = $form.querySelector( '[name="quote-type"]:checked' );

	return $checked ? $checked.value : '';
}

function bqfFieldApplies( field, quoteType ) {
	return ! field.condition || field.condition === quoteType;
}

function bqfFieldMessage( field, value ) {
	const trimmed = value.trim();

	if ( ! trimmed ) {
		return 'This field is required.';
	}

	if ( field.name === 'zip' && ! bqfZipPattern.test( trimmed ) ) {
		return 'Enter a 5-digit ZIP code.';
	}

	if ( field.name === 'email' && ! bqfEmailPattern.test( trimmed ) ) {
		return 'Enter a valid email address.';
	}

	if ( field.name === 'delivery-date' && trimmed < bqfToday() ) {
		return 'Choose today or a later date.';
	}

	if ( field.name === 'phone' && ! bqfValidUsPhone( trimmed ) ) {
		return 'Enter a 10-digit phone number.';
	}

	return '';
}

function bqfValidUsPhone( digits ) {
	return digits.length === 10 || ( digits.length === 11 && digits.startsWith( '1' ) );
}

function bqfClearErrors( $form ) {
	$form.querySelectorAll( '.bqf-field-error' ).forEach( ( $error ) => {
		$error.remove();
	} );

	$form.querySelectorAll( '[aria-invalid="true"]' ).forEach( ( $control ) => {
		$control.removeAttribute( 'aria-invalid' );
	} );
}

function bqfShowError( $control, message ) {
	const $field = $control.closest( '.bqf-field' );

	if ( ! $field ) {
		return;
	}

	$control.setAttribute( 'aria-invalid', 'true' );

	const $error = document.createElement( 'div' );
	$error.className = 'bqf-field-error';
	$error.textContent = message;
	$field.appendChild( $error );
}

function bqfValidateForm( $form ) {
	const quoteType = bqfQuoteType( $form );
	let valid = true;

	bqfClearErrors( $form );

	Object.entries( bqfFields ).forEach( ( [ key, field ] ) => {
		if ( key === 'qty20' || key === 'qty40' || key === 'quoteType' ) {
			return;
		}

		if ( ! bqfFieldApplies( field, quoteType ) ) {
			return;
		}

		const $control = $form.querySelector( `[name="${ field.name }"]` );

		if ( ! $control ) {
			return;
		}

		if ( field.name === 'phone' ) {
			$control.value = ( $control.value || '' ).replace( /\D/g, '' );
		}

		const message = bqfFieldMessage( field, $control.value || '' );

		if ( message ) {
			valid = false;
			bqfShowError( $control, message );
		}
	} );

	return valid;
}

async function bqfHandleSubmit( event, $form ) {
	event.preventDefault();

	const $status = $form.querySelector( '.bqf-status' );
	const $btnSubmit = $form.querySelector( '[type="submit"]' );

	if ( ! bqfValidateForm( $form ) ) {
		return;
	}

	$btnSubmit.disabled = true;
	$status.hidden = true;

	try {
		const response = await fetch( $form.getAttribute( 'action' ), {
			method: 'POST',
			body: new FormData( $form ),
		} );

		const result = await response.json();
		let message = null;

		if ( ! result ) {
			throw new Error( 'Something went wrong.' );
		}

		message = result.data?.message ? result.data.message : 'Something went wrong.';
		console.log( result.data );

		$status.hidden = false;
		$status.textContent = message;
	} catch ( error ) {
		$status.hidden = false;
		$status.textContent = 'Something went wrong.';
	}

	$btnSubmit.disabled = false;
}

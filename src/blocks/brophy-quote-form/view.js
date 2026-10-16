document.querySelectorAll( '.brophy-quote-form__form' ).forEach( ( form ) => {
	form.addEventListener( 'submit', async ( event ) => {
		event.preventDefault();

		const status = form.querySelector( '.brophy-quote-form__status' );
		const button = form.querySelector( '[type="submit"]' );

		button.disabled = true;
		status.hidden = true;

		try {
			const response = await fetch( form.action, {
				method: 'POST',
				body: new FormData( form ),
			} );
			const result = await response.json();
			const message =
				result && result.data && result.data.message
					? result.data.message
					: 'Something went wrong.';

			status.hidden = false;
			status.textContent = message;
		} catch ( error ) {
			status.hidden = false;
			status.textContent = 'Something went wrong.';
		}

		button.disabled = false;
	} );
} );

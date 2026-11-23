<?php
/**
 * Quote form markup.
 *
 * @package BrophyQuoteForm
 *
 * @var array  $atts Shortcode attributes.
 * @var string $results_page_url Permalink for a published results page, or empty.
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !isset($results_page_url) ) {
	$results_page_url = '';
}

$form_type = isset($atts['form_type']) ? $atts['form_type'] : 'full';

?>

<div class="bqf-form-container">
	<div class="bqf-form-wrap">
		<form class="bqf-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" novalidate
			<?php echo $results_page_url ? ' data-results-page="' . esc_url( $results_page_url ) . '"' : ''; ?>
		>
			<?php wp_nonce_field( 'bqf_submit_quote', 'bqf_nonce' ); ?>
			<input type="hidden" name="action" value="bqf_submit_quote" />

			<?php if ( $atts['heading'] ) : ?>
				<header class="bqf-form-header">
					<h3 class="heading"><?php echo esc_html( $atts['heading'] ); ?></h3>
				</header>
			<?php endif; ?>

			<div class="bqf-field-group bqf-field-group--quote-type">
				<label class="bqf-radio-group" for="quote-type-purchase">
					<input id="quote-type-purchase" type="radio" name="quote-type" value="Purchase" checked />
					<span class="bqf-type-label-text">Purchase</span>
				</label>
				<label class="bqf-radio-group" for="quote-type-rent">
					<input id="quote-type-rent" type="radio" name="quote-type" value="Rental" />
					<span class="bqf-type-label-text">Rental</span>
				</label>
			</div>

			<div class="bqf-form-body">
				<?php /*
                <div class="bqf-form-row bqf-form-row--radio">
                    <span class="bqf-label-text">Purchase or Rental?<span class="bqf-required">*</span></span>

                    <label class="bqf-radio-group" for="quote-type-purchase">
                        <input id="quote-type-purchase" type="radio" name="quote-type" value="Purchase" checked />
                        <span class="bqf-type-label-text">Purchase</span>
                    </label>
                    <label class="bqf-radio-group" for="quote-type-rent">
                        <input id="quote-type-rent" type="radio" name="quote-type" value="Rental" />
                        <span class="bqf-type-label-text">Rental</span>
                    </label>
				</div>
				*/ ?>

				<div class="bqf-form-row">
					<label class="bqf-field" data-group-field="zip">
						<span class="bqf-label-text">Delivery ZIP code<span class="bqf-required">*</span></span>
						<input type="text" name="zip" placeholder="Where is this container going?"
							pattern="^\d{5}(-\d{4})?$"
							autocomplete="postal-code"
							required
						/>
					</label>
				</div>
			</div>

			<div class="bqf-submit-wrap">
				<button type="submit" class="btn">Get a quote</button>
				<span class="bqf-status" role="status">
					<span class="bqf-status__text"></span>
					<span class="bqf-status__spinner"></span>
				</span>
			</div>
		</form>
	</div>
</div>

<?php
/**
 * Quote form markup.
 *
 * @package BrophyQuoteForm
 *
 * @var array  $atts              Shortcode attributes.
 * @var string $results_page_url Permalink for a published results page, or empty.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $results_page_url ) ) {
	$results_page_url = '';
}

?>
<div class="bqf-form-container">
	<?php if ( !empty( $atts['heading'] ) ) : ?>
		<h2 class="bqf-heading"><?php echo esc_html( $atts['heading'] ); ?></h2>
	<?php endif; ?>

	<div class="bqf-form-wrap">
		<form class="bqf-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" novalidate
			<?php echo $results_page_url ? ' data-results-page="' . esc_url( $results_page_url ) . '"' : ''; ?>
		>
			<?php wp_nonce_field( 'bqf_submit_quote', 'bqf_nonce' ); ?>
			<input type="hidden" name="action" value="bqf_submit_quote" />

			<div class="bqf-field-group bqf-field-group--quote-type">
				<label class="bqf-radio-group" for="quote-type-purchase">
					<input id="quote-type-purchase" type="radio" name="quote-type" value="purchase" checked />
					<span class="bqf-type-label-text">Purchase</span>
				</label>
				<label class="bqf-radio-group" for="quote-type-rent">
					<input id="quote-type-rent" type="radio" name="quote-type" value="rent" />
					<span class="bqf-type-label-text">Rental</span>
				</label>
			</div>

			<div class="bqf-form-body">
				<header class="bqf-form-header bqf-form-header--purchase" data-cond-type="purchase">
					<h3 class="heading">Purchase Quote</h3>
				</header>
				<header class="bqf-form-header bqf-form-header--rent" data-cond-type="rent">
					<h3 class="heading">Rental Quote</h3>
				</header>

				<div class="bqf-form-row bqf-form-row--qty">
					<span class="bqf-label-text">How many containers?<span class="bqf-required">*</span></span>

					<label class="bqf-qty-group" data-group-field="qty-20">
						<span class="bqf-qty-group__label">20 footer</span>
						<span class="bqf-qty-group__desc">20′ L x 8′ W x 8.5′ H</span>
						<select id="qty-20" name="qty-20">
							<option value="0">0</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
						</select>
					</label>

					<label class="bqf-qty-group" data-group-field="qty-40">
						<span class="bqf-qty-group__label">40 footer</span>
						<span class="bqf-qty-group__desc">40′ L x 8′ W x 8.5′ H</span>
						<select id="qty-40" name="qty-40">
							<option value="0">0</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
						</select>
					</label>
				</div>

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

				<div class="bqf-form-row" data-cond-type="purchase">
					<label class="bqf-field" data-group-field="new-used">
						<span class="bqf-label-text">Would you like a new or a used container?<span class="bqf-required">*</span></span>
						<select id="new-used" name="new-used">
							<option value="new">New</option>
							<option value="used">Used</option>
						</select>
					</label>
				</div>

				<div class="bqf-form-row" data-cond-type="rent">
					<label class="bqf-field" data-group-field="duration">
						<span class="bqf-label-text">Rental Duration<span class="bqf-required">*</span></span>
						<select id="duration" name="duration">
							<option value="1">1 month</option>
							<option value="2">2 months</option>
							<option value="3">3 months</option>
							<option value="4">4 months</option>
							<option value="5">5 months</option>
							<option value="6">6 months</option>
						</select>
					</label>
				</div>

				<div class="bqf-form-row">
					<label class="bqf-field" data-group-field="delivery-date">
						<span class="bqf-label-text">Preferred delivery date<span class="bqf-required">*</span></span>
						<input type="date" name="delivery-date" placeholder="When would you like your container delivered?"
							min="<?php echo date( 'Y-m-d' ); ?>"
							required
						/>
					</label>
				</div>

				<div class="bqf-form-row">
					<label class="bqf-field" data-group-field="email">
						<span class="bqf-label-text">Email<span class="bqf-required">*</span></span>
						<input type="text" name="email" placeholder="your-email@example.com"
							required
						/>
					</label>
				</div>

				<div class="bqf-form-row bqf-form-row--cols-2">
					<label class="bqf-field" data-group-field="full-name">
						<span class="bqf-label-text">Name<span class="bqf-required">*</span></span>
						<input type="text" name="full-name" placeholder="Your name"
							required
						/>
					</label>

					<label class="bqf-field" data-group-field="phone">
						<span class="bqf-label-text">Phone<span class="bqf-required">*</span></span>
						<input type="tel" name="phone" placeholder="Your phone number"
							required
						/>
					</label>
				</div>
			</div>

			<div class="bqf-submit-wrap">
				<button type="submit" class="btn">Get a quote</button>
				<p class="bqf-status" role="status" hidden></p>
			</div>
		</form>
	</div>
</div>

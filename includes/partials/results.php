<?php
/**
 * Quote results markup.
 *
 * @package BrophyQuoteForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="bqf-results-container">
	<h2>Results</h2>

	<div class="bqf-results-error">
		<div class="bqf-results-error__zip">
			<?php echo wp_kses_post( bqf_get_zip_error_message() ); ?>
		</div>
	</div>

	<div class="bqf-results-wrap">
		<div class="bqf-results__section">
			<div class="bqf-results__section-label">Containers</div>
			<div class="bqf-results__row row-size-20">
				<span class="row-label">20 Footer</span>
				<span class="row-value size-20-value">
					<span class="qty"></span> 
					<span class="unit-price"></span>
				</span>
			</div>
			<div class="bqf-results__row row-size-40">
				<span class="row-label">40 Footer</span>
				<span class="row-value size-40-value">
					<span class="qty"></span> 
					<span class="unit-price"></span>
				</span>
			</div>

			<div class="bqf-results__row bqf-results__row-subtotal row-lines-subtotal">
				<span class="row-label">Subtotal</span>
				<span class="row-value lines-subtotal-value">
					<span class="unit-price"></span>
				</span>
			</div>
		</div>

		<div class="bqf-results__section">
			<div class="bqf-results__section-label">Transportation</div>
			<div class="bqf-results__row row-delivery">
				<span class="row-label">Delivery</span>
				<span class="row-value delivery-value">
					<span class="qty"></span> 
					<span class="unit-price"></span>
				</span>
			</div>
			<div class="bqf-results__row row-pickup">
				<span class="row-label">Pickup</span>
				<span class="row-value pickup-value">
					<span class="qty"></span> 
					<span class="unit-price"></span>
				</span>
			</div>

			<div class="bqf-results__row bqf-results__row-subtotal row-charges-subtotal">
				<span class="row-label">Subtotal</span>
				<span class="row-value charges-subtotal-value">
					<span class="unit-price"></span>
				</span>
			</div>
		</div>

		<div class="bqf-results__total-section">
			<span class="total-label">Total</span>
			<span class="total-value"></span>
			<div class="discount">
				<span class="discount-value"></span>
				<span class="discount-message"></span>
			</div>
		</div>

		<div class="bqf-results__bottom">
			<div class="bqf-results__row">
				<span class="row-label">Due Today</span>
				<span class="row-value">$0.00</span>
			</div>
		</div>

		<footer class="bqf-results__footer">
			<a href="tel:5713646702" class="btn">Call to book: (571) 364-6702</a>
		</footer>
	</div>
</div>

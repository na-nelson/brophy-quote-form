<?php
/**
 * Server render for the quote form block.
 *
 * @package BrophyQuoteForm
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bqf_heading = isset( $attributes['heading'] ) ? $attributes['heading'] : '';
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'brophy-quote-form' ) ); ?>>
	<?php if ( $bqf_heading ) : ?>
		<h2 class="brophy-quote-form__heading"><?php echo wp_kses_post( $bqf_heading ); ?></h2>
	<?php endif; ?>
	<div class="brophy-quote-form__body">
		<form
			class="brophy-quote-form__form"
			method="post"
			action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
		>
			<?php wp_nonce_field( 'bqf_submit_quote', 'bqf_nonce' ); ?>
			<input type="hidden" name="action" value="bqf_submit_quote" />

			<label class="brophy-quote-form__field">
				<span class="brophy-quote-form__label"><?php esc_html_e( 'Name', 'brophy-quote-form' ); ?></span>
				<input type="text" name="name" required />
			</label>

			<button type="submit"><?php esc_html_e( 'Get a quote', 'brophy-quote-form' ); ?></button>
			<p class="brophy-quote-form__status" role="status" hidden></p>
		</form>
	</div>
</div>

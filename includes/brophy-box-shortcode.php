<?php
/**
 * Quote form shortcode.
 *
 * @package BrophyQuoteForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the form script and styles.
 */
function bqf_enqueue_form_assets() {
	$asset_path = BQF_PATH . 'build/form.asset.php';

	if ( ! file_exists( $asset_path ) ) {
		return;
	}

	$asset = include $asset_path;

	wp_enqueue_style(
		'bqf-form',
		BQF_URL . 'build/form.css',
		array(),
		$asset['version']
	);

	wp_enqueue_script(
		'bqf-form',
		BQF_URL . 'build/form.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);
}

/**
 * Print the quote form.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function bqf_render_quote_form( $atts ) {
	$atts = shortcode_atts(
		array(
			'heading' => '',
			'results_page' => '',
		),
		$atts,
		'brophy_quote_form'
	);

	bqf_enqueue_form_assets();

	$block_class = ( empty($atts['results_page']) ) ? 'bqf-has-results-section' : '';

	ob_start();
	?>
	<div class="brophy-quote-form bqf <?php echo esc_attr( $block_class ); ?>">
		<?php include BQF_PATH . 'includes/partials/form.php'; ?>
		<?php include BQF_PATH . 'includes/partials/results.php'; ?>
	</div>
	<?php

	return ob_get_clean();
}
add_shortcode( 'brophy_quote_form', 'bqf_render_quote_form' );

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
 * Permalink for a results page path, or an empty string.
 *
 * The path must start with /.
 *
 * @param string $path Site path such as /quote-results.
 * @return string
 */
function bqf_results_page_url( $path ) {
	$path = trim( (string) $path );

	if ( '' === $path || '/' !== substr( $path, 0, 1 ) ) {
		return '';
	}

	$lookup = trim( $path, '/' );

	if ( '' === $lookup ) {
		return '';
	}

	$page = get_page_by_path( $lookup );

	if ( ! $page instanceof WP_Post || 'publish' !== $page->post_status ) {
		return '';
	}

	$permalink = get_permalink( $page );

	return is_string( $permalink ) ? $permalink : '';
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

	$results_page_url = bqf_results_page_url( $atts['results_page'] );
	$block_class      = ( '' === $results_page_url ) ? 'bqf-has-results-section' : '';

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

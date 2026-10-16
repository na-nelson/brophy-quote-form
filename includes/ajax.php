<?php
/**
 * Quote form submission.
 *
 * @package BrophyQuoteForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Receive a quote form submission.
 */
function bqf_submit_quote() {
	if ( ! check_ajax_referer( 'bqf_submit_quote', 'bqf_nonce', false ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'The form expired. Refresh the page and try again.', 'brophy-quote-form' ),
			),
			403
		);
	}

	$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

	if ( '' === $name ) {
		wp_send_json_error(
			array(
				'message' => __( 'Name is required.', 'brophy-quote-form' ),
			),
			400
		);
	}

	wp_send_json_success(
		array(
			'message' => __( 'Quote sent.', 'brophy-quote-form' ),
		)
	);
}
add_action( 'wp_ajax_bqf_submit_quote', 'bqf_submit_quote' );
add_action( 'wp_ajax_nopriv_bqf_submit_quote', 'bqf_submit_quote' );

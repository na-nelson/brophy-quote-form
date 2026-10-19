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
add_action( 'wp_ajax_bqf_submit_quote', 'bqf_submit_quote' );
add_action( 'wp_ajax_nopriv_bqf_submit_quote', 'bqf_submit_quote' );

function bqf_submit_quote() {
	// get data tables
	$container_prices = bb_get_airtable_table('container_prices');
	$delivery_zones   = bb_get_airtable_table('delivery_pricing_zones');
	$zip_codes        = bb_get_airtable_table('zip_codes');
	$discounts        = bb_get_airtable_table('discounts');

	// setup input data
	$input_data = array(
		'quote-type' => isset( $_POST['quote-type'] ) ? sanitize_text_field( wp_unslash( $_POST['quote-type'] ) ) : 'purchase',
		'qty-20' => isset( $_POST['qty-20'] ) ? sanitize_text_field( wp_unslash( $_POST['qty-20'] ) ) : '',
		'qty-40' => isset( $_POST['qty-40'] ) ? sanitize_text_field( wp_unslash( $_POST['qty-40'] ) ) : '',
		'zip' => isset( $_POST['zip'] ) ? sanitize_text_field( wp_unslash( $_POST['zip'] ) ) : '',
		'new-used' => isset( $_POST['new-used'] ) ? sanitize_text_field( wp_unslash( $_POST['new-used'] ) ) : '',
		'duration' => isset( $_POST['duration'] ) ? sanitize_text_field( wp_unslash( $_POST['duration'] ) ) : '',
		'delivery-date' => isset( $_POST['delivery-date'] ) ? sanitize_text_field( wp_unslash( $_POST['delivery-date'] ) ) : '',
		'full-name' => isset( $_POST['full-name'] ) ? sanitize_text_field( wp_unslash( $_POST['full-name'] ) ) : '',
		'email' => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'phone' => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
	);

	// initialize response array
	$arr_response = array(
		'message' => null,
		'fields' => $input_data,
		'results' => array(),
		'debug' => array(),
	);

	// setup calc data
	$calc_data = array();

	$calc_data['discount'] = null;
	foreach ( $discounts as $discount ) {
		$status     = isset( $discount['status'] ) ? $discount['status'] : '';
		$quote_type = isset( $discount['quote-type'] ) ? $discount['quote-type'] : '';

		if ( 'active' === $status && $input_data['quote-type'] === $quote_type ) {
			$calc_data['discount'] = $discount;
			break;
		}
	}


	// ========== check nonce ==========
	if ( ! check_ajax_referer( 'bqf_submit_quote', 'bqf_nonce', false ) ) {
		$arr_response['message'] = 'The form expired. Refresh the page and try again.';

		wp_send_json_error($arr_response, 403);
	}


	// ================================================================================
	// get the chosen data
	// ================================================================================
	// ===== get the pricing zone ID =====
	$pricing_zone_id = $zip_codes[$input_data['zip']]['pricing-zone-id'];
	
	if ( !isset( $pricing_zone_id ) ) {
		$arr_response['message'] = 'ZIP code not found.';
		wp_send_json_error($arr_response, 400);
	}

	// ===== get calc data =====
	if ( isset($input_data['quote-type']) ) {
		if ( 'rent' == $input_data['quote-type'] ) {
			// continer price
			$calc_data['price-20'] = $container_prices['20-standard-used']['price-rent-mo'];
			$calc_data['price-40'] = $container_prices['40-standard-used']['price-rent-mo'];

			// delivery price
			$calc_data['price-delivery'] = $delivery_zones[$pricing_zone_id]['price-delivery-rent'];
			$calc_data['price-pickup'] = $delivery_zones[$pricing_zone_id]['price-delivery-pickup'];
		} else if ( 'purchase' == $input_data['quote-type'] ) {
			// continer price
			if ( 'new' == $input_data['new-used'] ) {
				$calc_data['price-20'] = $container_prices['20-standard-new']['price-sale'];
				$calc_data['price-40'] = $container_prices['40-standard-new']['price-sale'];
			} else if ( 'used' == $input_data['new-used'] ) {
				$calc_data['price-20'] = $container_prices['20-standard-used']['price-sale'];
				$calc_data['price-40'] = $container_prices['40-standard-used']['price-sale'];
			}

			// delivery price
			$calc_data['price-delivery'] = $delivery_zones[$pricing_zone_id]['price-delivery-sale'];
		}
	} else {
		$arr_response['message'] = 'Quote type is required.';
		wp_send_json_error($arr_response, 400);
	}

	
	$arr_response['debug']['calc-data'] = $calc_data;


	// ================================================================================
	// Calculations
	// ================================================================================




	// ========== validation ==========
	$vErrorsExist = false;

	// if ( $vErrorsExist ) {
	// 	wp_send_json_error($arr_response, 400);
	// }


	// ========== success ==========
	$arr_response['message'] = 'Quote sent.';

	wp_send_json_success($arr_response, 200);
}


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
		'error'   => null,
		'fields'  => $input_data,
		'results' => array(),
		'debug'   => array(),
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
	$zip = $input_data['zip'];

	if ( ! isset( $zip_codes[ $zip ]['pricing-zone-id'] ) || '' === $zip_codes[ $zip ]['pricing-zone-id'] ) {
		$arr_response['error'] = 'zip';
		wp_send_json_error( $arr_response, 400 );
	}

	$pricing_zone_id = $zip_codes[ $zip ]['pricing-zone-id'];

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



	// ================================================================================
	// Calculations
	// ================================================================================
	$quote_type = $input_data['quote-type'];
	$is_rent    = ( 'rent' === $quote_type );
	$qty_20     = (float) $input_data['qty-20'];
	$qty_40     = (float) $input_data['qty-40'];
	$duration   = (float) $input_data['duration'];
	$lines      = array();
	$subtotal   = 0;

	$arr_response['debug']['discounts'] = $discounts;

	$containers = array(
		array(
			'label'      => "20' container",
			'quantity'   => $qty_20,
			'unit_price' => isset( $calc_data['price-20'] ) ? (float) $calc_data['price-20'] : 0,
		),
		array(
			'label'      => "40' container",
			'quantity'   => $qty_40,
			'unit_price' => isset( $calc_data['price-40'] ) ? (float) $calc_data['price-40'] : 0,
		),
	);

	foreach ( $containers as $container ) {
		if ( $container['quantity'] <= 0 ) {
			continue;
		}

		$amount = $container['quantity'] * $container['unit_price'];

		if ( $is_rent ) {
			$amount *= $duration;
		}

		$line = array(
			'label'      => $container['label'],
			'quantity'   => $container['quantity'],
			'unit_price' => $container['unit_price'],
			'amount'     => $amount,
		);

		if ( $is_rent ) {
			$line['duration'] = $duration;
		}

		$lines[]   = $line;
		$subtotal += $amount;
	}

	$container_count = $qty_20 + $qty_40;
	$delivery_unit   = isset( $calc_data['price-delivery'] ) ? (float) $calc_data['price-delivery'] : 0;
	$charges         = array(
		array(
			'label'      => 'Delivery',
			'quantity'   => $container_count,
			'unit_price' => $delivery_unit,
			'amount'     => $container_count * $delivery_unit,
		),
	);

	if ( $is_rent ) {
		$pickup_unit = isset( $calc_data['price-pickup'] ) ? (float) $calc_data['price-pickup'] : 0;
		$charges[]   = array(
			'label'      => 'Pickup',
			'quantity'   => $container_count,
			'unit_price' => $pickup_unit,
			'amount'     => $container_count * $pickup_unit,
		);
	}

	$charges_subtotal = 0;

	foreach ( $charges as $charge ) {
		$charges_subtotal += $charge['amount'];
	}

	// ========== discounts ==========
	$discount = isset( $calc_data['discount'] ) && is_array( $calc_data['discount'] ) ? $calc_data['discount'] : null;
	$discount_amount = 0;
	$discount_note   = '';

	if ( $discount && isset( $discount['value-type'] ) && 'percent' === $discount['value-type'] ) {
		$rate = ( isset( $discount['value'] ) ? (float) $discount['value'] : 0 ) / 100;
		$discount_type = isset( $discount['discount-type'] ) ? $discount['discount-type'] : '';
		$discount_note = isset( $discount['front-end-note'] ) ? $discount['front-end-note'] : '';

		if ( 'purchase-price' === $discount_type ) {
			$discount_amount = $subtotal * $rate;
		} elseif ( 'rent-1-mo' === $discount_type ) {
			$first_month = 0;

			foreach ( $lines as $line ) {
				$first_month += $line['quantity'] * $line['unit_price'];
			}

			$discount_amount = $first_month * $rate;
		}
	}

	$discount_amount = round( $discount_amount, 2 );
	$original_total  = round( $subtotal + $charges_subtotal, 2 );

	$arr_response['results'] = array(
		'lines'            => $lines,
		'subtotal'         => $subtotal,
		'charges'          => $charges,
		'charges_subtotal' => $charges_subtotal,
		'discount'         => array(
			'amount'         => $discount_amount,
			'message'        => $discount_note,
			'original_total' => $original_total,
		),
		'total'            => round( $original_total - $discount_amount, 2 ),
	);


	// ========== validation ==========
	$vErrorsExist = false;

	// if ( $vErrorsExist ) {
	// 	wp_send_json_error($arr_response, 400);
	// }


	// ========== success ==========
	$arr_response['message'] = 'Quote sent.';

	wp_send_json_success($arr_response, 200);
}


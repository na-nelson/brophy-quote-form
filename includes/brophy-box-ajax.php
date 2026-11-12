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
		'first-name' => isset( $_POST['first-name'] ) ? sanitize_text_field( wp_unslash( $_POST['first-name'] ) ) : '',
		'last-name' => isset( $_POST['last-name'] ) ? sanitize_text_field( wp_unslash( $_POST['last-name'] ) ) : '',
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
	$zip_within_range = isset( $zip_codes[ $zip ]['pricing-zone-id'] ) && '' !== $zip_codes[ $zip ]['pricing-zone-id'];

	if ( ! $zip_within_range ) {
		$arr_response['error'] = 'zip';
	} else {
		$pricing_zone_id = $zip_codes[ $zip ]['pricing-zone-id'];
	}

	// ===== get calc data =====
	if ( ! $zip_within_range ) {
		// Pricing needs a zone, so the receipt stays empty. The webhook still sends.
	} elseif ( isset($input_data['quote-type']) ) {
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
	if ( $zip_within_range ) {
		$quote_type = $input_data['quote-type'];
		$is_rent    = ('rent' === $quote_type);
		$qty_20     = (int) $input_data['qty-20'];
		$qty_40     = (int) $input_data['qty-40'];
		$duration   = (int) $input_data['duration'];
		$lines      = array();
		$subtotal   = 0;

		// $arr_response['debug']['discounts'] = $discounts;

		// =========== line items ==========
		// make a $containers array to loop through
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

			$lines[] = $line;
			$subtotal += $amount;
		}


		// =========== charges ==========
		$container_total_count = $qty_20 + $qty_40;
		$delivery_unit = isset( $calc_data['price-delivery'] ) ? (float) $calc_data['price-delivery'] : 0;
		$charges = array(
			array(
				'label'      => 'Delivery',
				'quantity'   => $container_total_count,
				'unit_price' => $delivery_unit,
				'amount'     => $container_total_count * $delivery_unit,
			),
		);

		if ( $is_rent ) {
			$pickup_unit = isset( $calc_data['price-pickup'] ) ? (float) $calc_data['price-pickup'] : 0;
			$charges[]   = array(
				'label'      => 'Pickup',
				'quantity'   => $container_total_count,
				'unit_price' => $pickup_unit,
				'amount'     => $container_total_count * $pickup_unit,
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
				'amount'              => $discount_amount,
				'message'             => $discount_note,
				'original_total'      => $original_total,
				'discounted_subtotal' => round( $subtotal - $discount_amount, 2 ),
			),
			'total'            => round( $original_total - $discount_amount, 2 ),
		);
	}



	// ================================================================================
	// GHL
	// ================================================================================
	$ghlWebhookUrl = 'https://services.leadconnectorhq.com/hooks/PwiybZIuisMhkmSYHlPz/webhook-trigger/182a51d1-765a-4913-9559-f9c202bf16c1';
	$arr_ghl = array(
		'quote-type' => $input_data['quote-type'],
		'qty-20' => $input_data['qty-20'],
		'qty-40' => $input_data['qty-40'],
		'zip' => $input_data['zip'],
		'zip-within-range' => $zip_within_range,
		'new-used' => $input_data['new-used'],
		'duration' => $input_data['duration'],
		'delivery-date' => $input_data['delivery-date'],
		'first-name' => $input_data['first-name'],
		'last-name' => $input_data['last-name'],
		'email' => $input_data['email'],
		'phone' => $input_data['phone'],
		'quote-total' => $arr_response['results']['total'] ?? 0,
		'source-url' => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
		'notification-text' => bqf_quote_notification_text( $input_data, $arr_response['results'], $zip_within_range ),
	);

	$ghl_response = wp_remote_post(
		$ghlWebhookUrl,
		array(
			'timeout' => 15,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => wp_json_encode( $arr_ghl ),
		)
	);

	if ( is_wp_error( $ghl_response ) ) {
		$arr_response['message'] = 'The quote could not be sent. Please try again.';
		wp_send_json_error( $arr_response, 502 );
	}

	$ghl_status = (int) wp_remote_retrieve_response_code( $ghl_response );

	if ( $ghl_status < 200 || $ghl_status >= 300 ) {
		$arr_response['message'] = 'The quote could not be sent. Please try again.';
		wp_send_json_error( $arr_response, 502 );
	}


	if ( 'zip' === $arr_response['error'] ) {
		wp_send_json_error( $arr_response, 400 );
	}

	// ================================================================================
	// Csuccess
	// ================================================================================
	$arr_response['message'] = 'Quote sent.';

	wp_send_json_success($arr_response, 200);
}

/**
 * Plain-text body for the quote notification email.
 *
 * @param array $input_data       Submitted fields.
 * @param array $results          Calculated quote.
 * @param bool  $zip_within_range Whether the ZIP matched a pricing zone.
 * @return string
 */
function bqf_quote_notification_text( $input_data, $results, $zip_within_range ) {
	$money = function ( $amount ) {
		return '$' . number_format( (float) $amount, 2 );
	};

	$lines = array( 'Quote request', '' );

	$fields = array(
		'first-name'    => 'First name',
		'last-name'     => 'Last name',
		'email'         => 'Email',
		'phone'         => 'Phone',
		'quote-type'    => 'Quote type',
		'new-used'      => 'New or used',
		'qty-20'        => "20' containers",
		'qty-40'        => "40' containers",
		'duration'      => 'Rental duration (months)',
		'zip'           => 'ZIP code',
		'delivery-date' => 'Delivery date',
	);

	foreach ( $fields as $key => $label ) {
		$lines[] = $label . ': ' . ( isset( $input_data[ $key ] ) ? $input_data[ $key ] : '' );
	}

	foreach ( $input_data as $key => $value ) {
		if ( isset( $fields[ $key ] ) ) {
			continue;
		}

		$lines[] = $key . ': ' . ( is_scalar( $value ) ? $value : wp_json_encode( $value ) );
	}

	$lines[] = 'ZIP within range: ' . ( $zip_within_range ? 'yes' : 'no' );

	if ( ! $zip_within_range ) {
		return implode( "\n", $lines );
	}

	$lines[] = '';
	$lines[] = 'Containers';

	$result_lines = isset( $results['lines'] ) && is_array( $results['lines'] ) ? $results['lines'] : array();

	if ( ! $result_lines ) {
		$lines[] = 'None';
	}

	foreach ( $result_lines as $line ) {
		$text = ( isset( $line['quantity'] ) ? $line['quantity'] : 0 ) . ' x ' . ( isset( $line['label'] ) ? $line['label'] : '' );

		if ( isset( $line['duration'] ) && '' !== $line['duration'] ) {
			$text .= ' x ' . $line['duration'] . ' mo';
		}

		$text   .= ' @ ' . $money( isset( $line['unit_price'] ) ? $line['unit_price'] : 0 );
		$text   .= ' = ' . $money( isset( $line['amount'] ) ? $line['amount'] : 0 );
		$lines[] = $text;
	}

	$lines[] = 'Subtotal: ' . $money( isset( $results['subtotal'] ) ? $results['subtotal'] : 0 );
	$lines[] = '';
	$lines[] = 'Transportation';

	$charges = isset( $results['charges'] ) && is_array( $results['charges'] ) ? $results['charges'] : array();

	foreach ( $charges as $charge ) {
		$text    = ( isset( $charge['quantity'] ) ? $charge['quantity'] : 0 ) . ' x ' . ( isset( $charge['label'] ) ? $charge['label'] : '' );
		$text   .= ' @ ' . $money( isset( $charge['unit_price'] ) ? $charge['unit_price'] : 0 );
		$text   .= ' = ' . $money( isset( $charge['amount'] ) ? $charge['amount'] : 0 );
		$lines[] = $text;
	}

	$lines[] = 'Subtotal: ' . $money( isset( $results['charges_subtotal'] ) ? $results['charges_subtotal'] : 0 );

	$discount = isset( $results['discount'] ) && is_array( $results['discount'] ) ? $results['discount'] : array();
	$note     = isset( $discount['message'] ) ? trim( wp_strip_all_tags( (string) $discount['message'] ) ) : '';

	$lines[] = '';
	$lines[] = 'Discount: ' . $money( isset( $discount['amount'] ) ? $discount['amount'] : 0 );

	if ( '' !== $note ) {
		$lines[] = 'Discount note: ' . $note;
	}

	$lines[] = 'Original total: ' . $money( isset( $discount['original_total'] ) ? $discount['original_total'] : 0 );
	$lines[] = 'Total: ' . $money( isset( $results['total'] ) ? $results['total'] : 0 );

	return implode( "\n", $lines );
}


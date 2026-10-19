<?php
/**
 * Airtable fetch and local copies of the quote tables.
 *
 * @package BrophyQuoteForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Option name for a saved Airtable table.
 *
 * @param string $key Key from BB_TABLES.
 * @return string
 */
function bb_airtable_option_name( $key ) {
	return 'bqf_airtable_' . $key;
}

/**
 * Saved records for one table. Does not call Airtable.
 *
 * @param string $key Key from BB_TABLES.
 * @return array
 */
function bb_get_airtable_table( $key ) {
	if ( ! isset( BB_TABLES[ $key ] ) ) {
		return array();
	}

	$records = get_option( bb_airtable_option_name( $key ), array() );

	return is_array( $records ) ? $records : array();
}

/**
 * Fetch every record from an Airtable table.
 *
 * @param string $base_id  Airtable base ID.
 * @param string $table_id Airtable table ID.
 * @return array|false Records, or false when the request fails.
 */
function bb_fetch_airtable_table( $base_id, $table_id ) {
	if ( ! defined( 'BB_AIRTABLE_TOKEN' ) || '' === BB_AIRTABLE_TOKEN ) {
		return false;
	}

	$records = array();
	$offset  = null;

	do {
		$url = 'https://api.airtable.com/v0/' . rawurlencode( $base_id ) . '/' . rawurlencode( $table_id ) . '?pageSize=100';

		if ( $offset ) {
			$url .= '&offset=' . rawurlencode( $offset );
		}

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . BB_AIRTABLE_TOKEN,
				),
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) || ! isset( $body['records'] ) || ! is_array( $body['records'] ) ) {
			return false;
		}

		$records = array_merge( $records, $body['records'] );
		$offset  = isset( $body['offset'] ) ? $body['offset'] : null;
	} while ( $offset );

	return $records;
}

/**
 * Store field values only, keyed by the table's primary field when one is set.
 *
 * @param string $table_key Key from BB_TABLES.
 * @param array  $records   Raw Airtable records.
 * @return array
 */
function bb_shape_airtable_records( $table_key, $records ) {
	$primary = '';

	if ( isset( BB_TABLE_PRIMARY_FIELDS[ $table_key ] ) ) {
		$primary = BB_TABLE_PRIMARY_FIELDS[ $table_key ];
	}

	if ( '' === $primary ) {
		$saved = array();

		foreach ( $records as $record ) {
			$fields = isset( $record['fields'] ) && is_array( $record['fields'] ) ? $record['fields'] : array();
			$saved[] = $fields;
		}

		return $saved;
	}

	$saved = new stdClass();

	foreach ( $records as $record ) {
		$fields = isset( $record['fields'] ) && is_array( $record['fields'] ) ? $record['fields'] : array();

		if ( ! isset( $fields[ $primary ] ) || is_array( $fields[ $primary ] ) ) {
			continue;
		}

		$row_key = trim( (string) $fields[ $primary ] );

		if ( '' === $row_key ) {
			continue;
		}

		$saved->{$row_key} = $fields;
	}

	$decoded = json_decode( wp_json_encode( $saved ), true );

	return is_array( $decoded ) ? $decoded : array();
}

/**
 * Refresh every saved table from Airtable.
 *
 * A failed fetch leaves that table's saved copy unchanged.
 *
 * @return array<string, int|false> Record count on success, false on failure.
 */
function bb_refresh_airtable_tables() {
	$results     = array();
	$any_success = false;

	if ( ! defined( 'BB_AIRTABLE_BASE_ID' ) || '' === BB_AIRTABLE_BASE_ID ) {
		foreach ( BB_TABLES as $key => $table_id ) {
			$results[ $key ] = false;
		}

		return $results;
	}

	foreach ( BB_TABLES as $key => $table_id ) {
		$records = bb_fetch_airtable_table( BB_AIRTABLE_BASE_ID, $table_id );

		if ( false === $records ) {
			$results[ $key ] = false;
			continue;
		}

		$saved = bb_shape_airtable_records( $key, $records );

		update_option( bb_airtable_option_name( $key ), $saved, false );
		$results[ $key ] = count( $saved );
		$any_success     = true;
	}

	if ( $any_success ) {
		update_option( 'bqf_airtable_refreshed_at', time(), false );
	}

	return $results;
}

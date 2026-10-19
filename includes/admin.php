<?php
/**
 * Admin screen.
 *
 * @package BrophyQuoteForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add the plugin admin page.
 */
function bqf_admin_menu() {
	add_menu_page(
		__( 'Quote Form', 'brophy-quote-form' ),
		__( 'Quote Form', 'brophy-quote-form' ),
		'manage_options',
		'brophy-quote-form',
		'bqf_render_admin_page',
		'dashicons-feedback',
		58
	);
}
add_action( 'admin_menu', 'bqf_admin_menu' );

/**
 * Labels for the saved Airtable tables.
 *
 * @return array<string, string>
 */
function bqf_airtable_table_labels() {
	return array(
		'container_prices'       => 'Container prices',
		'delivery_pricing_zones' => 'Delivery pricing zones',
		'zip_codes'              => 'Zip codes',
		'discounts'              => 'Discounts',
	);
}

/**
 * Refresh the saved Airtable tables and return to this screen.
 */
function bqf_handle_airtable_refresh() {
	if ( ! isset( $_POST['bqf_refresh_airtable'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'bqf_refresh_airtable' );

	set_transient( 'bqf_airtable_refresh_notice', bb_refresh_airtable_tables(), MINUTE_IN_SECONDS );
	wp_safe_redirect( admin_url( 'admin.php?page=brophy-quote-form' ) );
	exit;
}
add_action( 'admin_init', 'bqf_handle_airtable_refresh' );

/**
 * Print the admin page.
 */
function bqf_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$labels      = bqf_airtable_table_labels();
	$refreshed   = get_option( 'bqf_airtable_refreshed_at' );
	$notice      = get_transient( 'bqf_airtable_refresh_notice' );
	$notice_html = '';

	if ( false !== $notice ) {
		delete_transient( 'bqf_airtable_refresh_notice' );
		$notice_html = bqf_airtable_refresh_notice( $notice, $labels );
	}

	echo '<div class="wrap">';
	echo '<h1>Quote Form</h1>';
	echo $notice_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in bqf_airtable_refresh_notice().
	echo '<h2>Airtable data</h2>';

	if ( $refreshed ) {
		echo '<p>Last refresh: ' . esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $refreshed ) ) . '</p>';
	} else {
		echo '<p>Not refreshed yet.</p>';
	}

	echo '<table class="widefat striped" style="max-width: 40rem; margin-bottom: 1rem;">';
	echo '<thead><tr><th>Table</th><th>Saved records</th></tr></thead><tbody>';

	foreach ( BB_TABLES as $key => $table_id ) {
		$label = isset( $labels[ $key ] ) ? $labels[ $key ] : $key;
		$count = count( bb_get_airtable_table( $key ) );

		echo '<tr><td>' . esc_html( $label ) . '</td><td>' . esc_html( (string) $count ) . '</td></tr>';
	}

	echo '</tbody></table>';

	echo '<form method="post">';
	wp_nonce_field( 'bqf_refresh_airtable' );
	echo '<button type="submit" class="button button-primary" name="bqf_refresh_airtable" value="1">Refresh from Airtable</button>';
	echo '</form>';

	echo '<div id="bqf-admin-root"></div>';
	echo '</div>';
}

/**
 * Build the notice shown after a refresh.
 *
 * @param array<string, int|false> $results Refresh results.
 * @param array<string, string>    $labels  Table labels.
 * @return string
 */
function bqf_airtable_refresh_notice( $results, $labels ) {
	$lines   = array();
	$failed  = false;

	foreach ( $results as $key => $result ) {
		$label = isset( $labels[ $key ] ) ? $labels[ $key ] : $key;

		if ( false === $result ) {
			$failed  = true;
			$lines[] = $label . ' could not be refreshed. The saved copy was kept.';
			continue;
		}

		$lines[] = $label . ' updated (' . (int) $result . ' records).';
	}

	$class = $failed ? 'notice notice-warning' : 'notice notice-success';

	$html = '<div class="' . esc_attr( $class ) . '"><p>';
	$html .= implode( '<br>', array_map( 'esc_html', $lines ) );
	$html .= '</p></div>';

	return $html;
}

/**
 * Enqueue the admin script and styles on the plugin page.
 *
 * @param string $hook Current admin page hook.
 */
function bqf_enqueue_admin_assets( $hook ) {
	if ( 'toplevel_page_brophy-quote-form' !== $hook ) {
		return;
	}

	$asset_path = BQF_PATH . 'build/admin.asset.php';

	if ( ! file_exists( $asset_path ) ) {
		return;
	}

	$asset = include $asset_path;

	wp_enqueue_script(
		'bqf-admin',
		BQF_URL . 'build/admin.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_enqueue_style(
		'bqf-admin',
		BQF_URL . 'build/admin.css',
		array(),
		$asset['version']
	);
}
add_action( 'admin_enqueue_scripts', 'bqf_enqueue_admin_assets' );

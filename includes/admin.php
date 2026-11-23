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
 *
 * The menu is visible to anyone who can view submissions. Settings is its own
 * item for editors and administrators.
 */
function bqf_admin_menu() {
	add_menu_page(
		__( 'Quote Form', 'brophy-quote-form' ),
		__( 'Quote Form', 'brophy-quote-form' ),
		'read',
		'brophy-quote-form',
		'bqf_render_admin_page',
		'dashicons-feedback',
		58
	);

	add_submenu_page(
		'brophy-quote-form',
		__( 'Settings', 'brophy-quote-form' ),
		__( 'Settings', 'brophy-quote-form' ),
		'edit_others_posts',
		'brophy-quote-form'
	);

	add_submenu_page(
		'brophy-quote-form',
		__( 'Options', 'brophy-quote-form' ),
		__( 'Options', 'brophy-quote-form' ),
		'edit_others_posts',
		'brophy-quote-form-options',
		'bqf_render_options_page'
	);
}
add_action( 'admin_menu', 'bqf_admin_menu' );

/**
 * Drop the automatic parent link for roles that cannot open settings.
 *
 * WordPress adds that link when Submissions is the first submenu item.
 */
function bqf_limit_quote_form_settings_menu() {
	global $submenu;

	if ( current_user_can( 'edit_others_posts' ) || empty( $submenu['brophy-quote-form'] ) || ! is_array( $submenu['brophy-quote-form'] ) ) {
		return;
	}

	foreach ( $submenu['brophy-quote-form'] as $index => $item ) {
		if ( isset( $item[2] ) && 'brophy-quote-form' === $item[2] ) {
			unset( $submenu['brophy-quote-form'][ $index ] );
		}
	}
}
add_action( 'admin_menu', 'bqf_limit_quote_form_settings_menu', 99 );

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

	if ( ! current_user_can( 'edit_others_posts' ) ) {
		return;
	}

	check_admin_referer( 'bqf_refresh_airtable' );

	set_transient( 'bqf_airtable_refresh_notice', bb_refresh_airtable_tables(), MINUTE_IN_SECONDS );
	wp_safe_redirect( admin_url( 'admin.php?page=brophy-quote-form' ) );
	exit;
}
add_action( 'admin_init', 'bqf_handle_airtable_refresh' );

/**
 * Default message shown when a ZIP is not in the lookup table.
 *
 * @return string
 */
function bqf_zip_error_message_default() {
	return 'We cannot provide a quote for your ZIP code.';
}

/**
 * Saved ZIP error message, or the default sentence.
 *
 * @return string
 */
function bqf_get_zip_error_message() {
	$saved = get_option( 'bqf_zip_error_message', '' );

	if ( ! is_string( $saved ) || '' === trim( wp_strip_all_tags( $saved ) ) ) {
		return bqf_zip_error_message_default();
	}

	return $saved;
}

/**
 * Save the ZIP error message from the admin editor.
 */
function bqf_handle_zip_error_message() {
	if ( ! isset( $_POST['bqf_save_zip_error'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_others_posts' ) ) {
		return;
	}

	check_admin_referer( 'bqf_save_zip_error' );

	$message = isset( $_POST['bqf_zip_error_message'] ) ? wp_kses_post( wp_unslash( $_POST['bqf_zip_error_message'] ) ) : '';

	update_option( 'bqf_zip_error_message', $message, false );
	set_transient( 'bqf_zip_error_notice', '1', MINUTE_IN_SECONDS );
	wp_safe_redirect( admin_url( 'admin.php?page=brophy-quote-form' ) );
	exit;
}
add_action( 'admin_init', 'bqf_handle_zip_error_message' );

/**
 * Print the admin page.
 */
function bqf_render_admin_page() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'brophy-quote-form' ), 403 );
	}

	$labels      = bqf_airtable_table_labels();
	$refreshed   = get_option( 'bqf_airtable_refreshed_at' );
	$notice      = get_transient( 'bqf_airtable_refresh_notice' );
	$notice_html = '';

	if ( false !== $notice ) {
		delete_transient( 'bqf_airtable_refresh_notice' );
		$notice_html = bqf_airtable_refresh_notice( $notice, $labels );
	}

	$zip_notice = get_transient( 'bqf_zip_error_notice' );

	if ( false !== $zip_notice ) {
		delete_transient( 'bqf_zip_error_notice' );
		$notice_html .= '<div class="notice notice-success"><p>ZIP code message saved.</p></div>';
	}

	echo '<div class="wrap">';
	echo '<h1>Quote Form Settings</h1>';
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

	echo '</div>';

	echo '<hr style="margin: 2rem 0;">';

	echo '<h2>ZIP code message</h2>';
	echo $notice_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in bqf_airtable_refresh_notice().
	echo '<p>Shown when a delivery ZIP is not in the lookup table.</p>';
	echo '<form method="post" style="max-width: 40rem; margin-bottom: 2rem;">';
	wp_nonce_field( 'bqf_save_zip_error' );
	wp_editor(
		bqf_get_zip_error_message(),
		'bqf_zip_error_message',
		array(
			'textarea_name' => 'bqf_zip_error_message',
			'textarea_rows' => 8,
			'media_buttons' => true,
		)
	);
	echo '<p><button type="submit" class="button button-primary" name="bqf_save_zip_error" value="1">Save message</button></p>';
	echo '</form>';
}

/**
 * Print the shortcode notes.
 */
function bqf_render_options_page() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'brophy-quote-form' ), 403 );
	}

	echo '<div class="bqf-options-wrap">';
	echo '<h1>Options</h1>';

	echo '<h2>Quote Form Shortcode Options</h2>';

	echo '<div class="bqf-options-item">';
	echo '<p>The default option has both the form and results in the same page.</p>';
	echo '<code>[brophy_quote_form]</code>';
	echo '</div>';

	echo '<div class="bqf-options-item">';
	echo '<p>Add <span class="inline-mono">results_page</span> to show the full form and have the results display on a separate page.</p>';
	echo '<code>[brophy_quote_form results_page="/example/page-path"]</code>';
	echo '</div>';

	echo '<div class="bqf-options-item">';
	echo '<p>Add <span class="inline-mono">form_type="intro"</span> to show an abbreviated form. This option requires a <span class="inline-mono">results_page</span> to be set.</p>';
	echo '<code>[brophy_quote_form form_type="intro" results_page="/example/page-path"]</code>';
	echo '</div>';

	echo '<div class="bqf-options-item">';
	echo '<p>Add <span class="inline-mono">heading="Your Heading Here"</span> to display a custom heading above the form.</p>';
	echo '<code>[brophy_quote_form heading="Your Heading Here"]</code>';
	echo '</div>';

	echo '<hr>';

	echo '<h2>Quote Results Shortcode Options</h2>';

	echo '<div class="bqf-options-item">';
	echo '<p><strong><u>The Results shortcode is required</u></strong> to be included in a page that displays the results.</p>';
	echo '<code>[brophy_quote_results]</code>';
	echo '</div>';

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
 * Load admin styles and the ZIP message editor.
 *
 * @param string $hook Current admin page hook.
 */
function bqf_enqueue_admin_assets( $hook ) {
	if ( 'quote-form_page_brophy-quote-form-options' === $hook ) {
		wp_enqueue_style(
			'bqf-admin',
			BQF_URL . 'assets/admin.css',
			array(),
			BQF_VERSION
		);
	}

	if ( 'toplevel_page_brophy-quote-form' !== $hook ) {
		return;
	}

	wp_enqueue_editor();
}
add_action( 'admin_enqueue_scripts', 'bqf_enqueue_admin_assets' );

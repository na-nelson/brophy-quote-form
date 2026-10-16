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
 * Print the admin page mount point.
 */
function bqf_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Quote Form', 'brophy-quote-form' ) . '</h1>';
	echo '<div id="bqf-admin-root"></div>';
	echo '</div>';
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

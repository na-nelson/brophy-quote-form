<?php
/**
 * Block registration.
 *
 * @package BrophyQuoteForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the plugin block category.
 *
 * @param array[] $categories Existing block categories.
 * @return array[]
 */
function bqf_register_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'brophy-quote-form',
			'title' => __( 'Brophy Quote Form', 'brophy-quote-form' ),
		)
	);

	return $categories;
}
add_filter( 'block_categories_all', 'bqf_register_block_category' );

/**
 * Register every block listed in the generated metadata collection.
 */
function bqf_register_blocks() {
	$manifest = BQF_PATH . 'build/blocks-manifest.php';

	if ( ! function_exists( 'wp_register_block_types_from_metadata_collection' ) || ! file_exists( $manifest ) ) {
		return;
	}

	wp_register_block_types_from_metadata_collection( BQF_PATH . 'build/blocks', $manifest );
}
add_action( 'init', 'bqf_register_blocks' );

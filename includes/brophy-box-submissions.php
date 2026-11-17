<?php
/**
 * Stored quote submissions.
 *
 * @package BrophyQuoteForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the private submissions post type.
 */
function bqf_register_submission_post_type() {
	register_post_type(
		'bqf-submission',
		array(
			'labels'              => array(
				'name'               => __( 'Submissions', 'brophy-quote-form' ),
				'singular_name'      => __( 'Submission', 'brophy-quote-form' ),
				'menu_name'          => __( 'Submissions', 'brophy-quote-form' ),
				'all_items'          => __( 'Submissions', 'brophy-quote-form' ),
				'search_items'       => __( 'Search submissions', 'brophy-quote-form' ),
				'not_found'          => __( 'No submissions found.', 'brophy-quote-form' ),
				'not_found_in_trash' => __( 'No submissions found in Trash.', 'brophy-quote-form' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'brophy-quote-form',
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'query_var'           => false,
			'rewrite'             => false,
			'has_archive'         => false,
			'hierarchical'        => false,
			'supports'            => false,
			'delete_with_user'    => false,
			'map_meta_cap'        => false,
			'capabilities'        => array(
				'edit_post'          => 'edit_bqf_submissions',
				'read_post'          => 'edit_bqf_submissions',
				'delete_post'        => 'delete_bqf_submissions',
				'edit_posts'         => 'edit_bqf_submissions',
				'edit_others_posts'  => 'edit_bqf_submissions',
				'delete_posts'       => 'delete_bqf_submissions',
				'publish_posts'      => 'do_not_allow',
				'read_private_posts' => 'edit_bqf_submissions',
				'create_posts'       => 'do_not_allow',
			),
		)
	);
}
add_action( 'init', 'bqf_register_submission_post_type' );

/**
 * Keep Quote Form open while a submission screen is showing.
 *
 * @param string $parent_file Parent menu slug.
 * @return string
 */
function bqf_submission_parent_file( $parent_file ) {
	global $typenow;

	if ( 'bqf-submission' === $typenow ) {
		return 'brophy-quote-form';
	}

	return $parent_file;
}
add_filter( 'parent_file', 'bqf_submission_parent_file' );

/**
 * Let every role open submissions, and editors and administrators delete them.
 */
function bqf_grant_submission_caps() {
	$wp_roles = wp_roles();

	if ( ! $wp_roles ) {
		return;
	}

	foreach ( array_keys( $wp_roles->roles ) as $role_name ) {
		$role = get_role( $role_name );

		if ( $role && ! $role->has_cap( 'edit_bqf_submissions' ) ) {
			$role->add_cap( 'edit_bqf_submissions' );
		}
	}

	foreach ( array( 'editor', 'administrator' ) as $role_name ) {
		$role = get_role( $role_name );

		if ( $role && ! $role->has_cap( 'delete_bqf_submissions' ) ) {
			$role->add_cap( 'delete_bqf_submissions' );
		}
	}

	$user = wp_get_current_user();

	if ( $user instanceof WP_User && $user->exists() ) {
		$user->get_role_caps();
	}
}
add_action( 'init', 'bqf_grant_submission_caps' );

/**
 * Keep the edit screen view-only.
 *
 * @param bool   $use       Whether the block editor is enabled.
 * @param string $post_type Post type.
 * @return bool
 */
function bqf_disable_submission_block_editor( $use, $post_type ) {
	if ( 'bqf-submission' === $post_type ) {
		return false;
	}

	return $use;
}
add_filter( 'use_block_editor_for_post_type', 'bqf_disable_submission_block_editor', 10, 2 );

/**
 * Remove the publish box. There is nothing to save on this screen.
 */
function bqf_submission_remove_metaboxes() {
	remove_meta_box( 'submitdiv', 'bqf-submission', 'side' );
}
add_action( 'add_meta_boxes_bqf-submission', 'bqf_submission_remove_metaboxes' );

/**
 * Show the submission title at the top of the detail screen.
 *
 * @param WP_Post $post Submission post.
 */
function bqf_submission_detail_title( $post ) {
	if ( ! $post instanceof WP_Post || 'bqf-submission' !== $post->post_type ) {
		return;
	}

	echo '<h1 class="wp-heading-inline">' . esc_html( $post->post_title ) . '</h1>';
	echo '<hr class="wp-header-end" />';
}
add_action( 'edit_form_top', 'bqf_submission_detail_title' );

/**
 * Hide the generic "Edit Submission" heading so the stored title is the page heading.
 */
function bqf_submission_detail_title_css() {
	$screen = get_current_screen();

	if ( ! $screen || 'bqf-submission' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}

	echo '<style>.post-type-bqf-submission.post-php .wrap > h1.wp-heading-inline,.post-type-bqf-submission.post-php .wrap > hr.wp-header-end{display:none}</style>';
}
add_action( 'admin_head', 'bqf_submission_detail_title_css' );

/**
 * Show the stored JSON.
 */
function bqf_submission_json_metabox() {
	add_meta_box(
		'bqf-submission-json',
		__( 'Submission', 'brophy-quote-form' ),
		'bqf_render_submission_json',
		'bqf-submission',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_bqf-submission', 'bqf_submission_json_metabox' );

/**
 * Print the submission JSON.
 *
 * @param WP_Post $post Submission post.
 */
function bqf_render_submission_json( $post ) {
	echo '<pre style="white-space:pre-wrap;overflow-wrap:anywhere;margin:0;">';
	echo esc_html( $post->post_content );
	echo '</pre>';
}

/**
 * Stop admin saves from changing a stored submission.
 *
 * Editors and administrators can still trash and restore items.
 *
 * @param array $data    Slashed post data about to be written.
 * @param array $postarr Sanitized post data.
 * @param array $unsanitized Original post data.
 * @param bool  $update  Whether this is an update.
 * @return array
 */
function bqf_lock_submission_content( $data, $postarr, $unsanitized, $update ) {
	if ( ! $update || 'bqf-submission' !== $data['post_type'] ) {
		return $data;
	}

	$existing = get_post( $postarr['ID'] );

	if ( ! $existing ) {
		return $data;
	}

	$data['post_title']   = wp_slash( $existing->post_title );
	$data['post_content'] = wp_slash( $existing->post_content );
	$data['post_excerpt'] = wp_slash( $existing->post_excerpt );
	$data['post_author']  = $existing->post_author;

	if ( ! current_user_can( 'delete_bqf_submissions' ) ) {
		$data['post_status'] = $existing->post_status;
		$data['post_name']   = $existing->post_name;
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'bqf_lock_submission_content', 10, 4 );

/**
 * Turn off quick edit for submissions.
 *
 * @param bool   $enabled   Whether quick edit is enabled.
 * @param string $post_type Post type.
 * @return bool
 */
function bqf_disable_submission_quick_edit( $enabled, $post_type ) {
	if ( 'bqf-submission' === $post_type ) {
		return false;
	}

	return $enabled;
}
add_filter( 'quick_edit_enabled_for_post_type', 'bqf_disable_submission_quick_edit', 10, 2 );

/**
 * Remove bulk edit. Trash stays available to roles that can delete.
 *
 * @param array $actions Bulk actions.
 * @return array
 */
function bqf_submission_bulk_actions( $actions ) {
	unset( $actions['edit'] );

	return $actions;
}
add_filter( 'bulk_actions-edit-bqf-submission', 'bqf_submission_bulk_actions' );

/**
 * List columns for a submission.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function bqf_submission_columns( $columns ) {
	$submission_columns = array(
		'bqf_name'     => __( 'Name', 'brophy-quote-form' ),
		'bqf_zip'      => __( 'Zip', 'brophy-quote-form' ),
		'bqf_email'    => __( 'Email', 'brophy-quote-form' ),
		'bqf_datetime' => __( 'Datetime', 'brophy-quote-form' ),
	);

	if ( current_user_can( 'delete_bqf_submissions' ) && isset( $columns['cb'] ) ) {
		return array( 'cb' => $columns['cb'] ) + $submission_columns;
	}

	return $submission_columns;
}
add_filter( 'manage_bqf-submission_posts_columns', 'bqf_submission_columns' );

/**
 * Put row actions on the name column.
 *
 * @param string $column  Default primary column.
 * @param string $screen  Screen ID.
 * @return string
 */
function bqf_submission_primary_column( $column, $screen ) {
	if ( 'edit-bqf-submission' === $screen ) {
		return 'bqf_name';
	}

	return $column;
}
add_filter( 'list_table_primary_column', 'bqf_submission_primary_column', 10, 2 );

/**
 * Fill a submission list column.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function bqf_submission_column_content( $column, $post_id ) {
	$fields = bqf_submission_fields( $post_id );

	if ( 'bqf_name' === $column ) {
		$first = isset( $fields['first-name'] ) ? $fields['first-name'] : '';
		$last  = isset( $fields['last-name'] ) ? $fields['last-name'] : '';
		$name  = trim( $first . ' ' . $last );
		$label = '' !== $name ? $name : __( '(no name)', 'brophy-quote-form' );
		$link  = get_edit_post_link( $post_id );

		if ( $link ) {
			echo '<a class="row-title" href="' . esc_url( $link ) . '">' . esc_html( $label ) . '</a>';
		} else {
			echo esc_html( $label );
		}

		return;
	}

	if ( 'bqf_zip' === $column ) {
		echo esc_html( isset( $fields['zip'] ) && '' !== $fields['zip'] ? $fields['zip'] : '—' );
		return;
	}

	if ( 'bqf_email' === $column ) {
		echo esc_html( isset( $fields['email'] ) && '' !== $fields['email'] ? $fields['email'] : '—' );
		return;
	}

	if ( 'bqf_datetime' === $column ) {
		echo esc_html( bqf_submission_datetime( $post_id ) );
	}
}
add_action( 'manage_bqf-submission_posts_custom_column', 'bqf_submission_column_content', 10, 2 );

/**
 * Label the row link View, since the screen cannot be edited.
 *
 * @param array   $actions Row actions.
 * @param WP_Post $post    Submission post.
 * @return array
 */
function bqf_submission_row_actions( $actions, $post ) {
	if ( 'bqf-submission' !== $post->post_type ) {
		return $actions;
	}

	unset( $actions['inline hide-if-no-js'] );

	if ( isset( $actions['edit'] ) ) {
		unset( $actions['edit'] );

		$view = array(
			'view' => sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_edit_post_link( $post->ID ) ),
				esc_html__( 'View', 'brophy-quote-form' )
			),
		);

		$actions = $view + $actions;
	}

	return $actions;
}
add_filter( 'post_row_actions', 'bqf_submission_row_actions', 10, 2 );

/**
 * Fields stored in a submission's JSON.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function bqf_submission_fields( $post_id ) {
	$post = get_post( $post_id );

	if ( ! $post ) {
		return array();
	}

	$data = json_decode( $post->post_content, true );

	if ( ! is_array( $data ) || empty( $data['fields'] ) || ! is_array( $data['fields'] ) ) {
		return array();
	}

	return $data['fields'];
}

/**
 * Submission date and time in the site timezone.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function bqf_submission_datetime( $post_id ) {
	$timestamp = get_post_timestamp( $post_id );

	if ( ! $timestamp ) {
		return '';
	}

	return wp_date(
		get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
		$timestamp
	);
}

/**
 * Save a quote response as pretty-printed JSON.
 *
 * A failed insert does not affect the response sent to the visitor.
 *
 * @param array $arr_response Response about to be returned.
 */
function bqf_store_submission( $arr_response ) {
	$fields   = isset( $arr_response['fields'] ) && is_array( $arr_response['fields'] ) ? $arr_response['fields'] : array();
	$first    = isset( $fields['first-name'] ) ? $fields['first-name'] : '';
	$last     = isset( $fields['last-name'] ) ? $fields['last-name'] : '';
	$zip      = isset( $fields['zip'] ) ? $fields['zip'] : '';
	$format   = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
	$now      = time();
	$datetime = wp_date( $format, $now );
	$title    = trim( $first . ' ' . $last ) . ' - ' . $zip . ' - ' . $datetime;
	$json     = wp_json_encode( $arr_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

	if ( ! is_string( $json ) ) {
		return;
	}

	$restore_kses = false;

	if ( ! current_user_can( 'unfiltered_html' ) ) {
		kses_remove_filters();
		$restore_kses = true;
	}

	wp_insert_post(
		wp_slash(
			array(
				'post_type'      => 'bqf-submission',
				'post_status'    => 'publish',
				'post_title'     => $title,
				'post_content'   => $json,
				'post_author'    => 0,
				'post_date'      => wp_date( 'Y-m-d H:i:s', $now ),
				'post_date_gmt'  => gmdate( 'Y-m-d H:i:s', $now ),
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			)
		)
	);

	if ( $restore_kses ) {
		kses_init_filters();
	}
}

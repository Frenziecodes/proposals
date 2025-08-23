<?php

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 */
function wpbp_init() {
	return WP_Business_Proposals::get_instance();
}

// Initialize plugin.
add_action( 'plugins_loaded', 'wpbp_init' );

/**
 * Plugin activation hook
 *
 * @since 1.0.0
 */
function wpbp_activate() {
	// Initialize the plugin to register post type.
	wpbp_init();
	
	// Create default proposal statuses.
	$statuses = array(
		'draft'     => __( 'Draft', 'proposals' ),
		'sent'      => __( 'Sent', 'proposals' ),
		'accepted'  => __( 'Accepted', 'proposals' ),
		'declined'  => __( 'Declined', 'proposals' ),
	);
	
	foreach ( $statuses as $slug => $name ) {
		if ( ! term_exists( $slug, 'wpbp_proposal_status' ) ) {
			wp_insert_term( $name, 'wpbp_proposal_status', array( 'slug' => $slug ) );
		}
	}
	
	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wpbp_activate' );

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 */
function wpbp_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wpbp_deactivate' );

/**
 * AJAX handler for regenerating access token
 *
 * @since 1.0.0
 */
function wpbp_regenerate_access_token() {
	// Check nonce and permissions here in a real implementation.
	$post_id = intval( $_POST['post_id'] ?? 0 );
	
	if ( $post_id && current_user_can( 'edit_post', $post_id ) ) {
		$new_token = wp_generate_password( 32, false );
		update_post_meta( $post_id, '_wpbp_access_token', $new_token );
		
		wp_send_json_success( array(
			'token' => $new_token,
			'url'   => get_permalink( $post_id ) . '?token=' . $new_token,
		) );
	}
	
	wp_send_json_error();
}
add_action( 'wp_ajax_wpbp_regenerate_token', 'wpbp_regenerate_access_token' );

// End of file.

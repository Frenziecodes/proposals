<?php
/**
 * Plugin Name: WP Business Proposals
 * Plugin URI: https://github.com/Frenziecodes/proposals
 * Description: Create and manage business proposals in WordPress.
 * Version: 1.0.0
 * Author: Lewis Ushindi
 * Author URI: github.com/frenziecodes
 * Text Domain: wp-business-proposals
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Proposal Custom Post Type
 */
function wpbp_register_proposal_cpt() {
	$labels = array(
		'name'                  => __( 'Proposals', 'wp-business-proposals' ),
		'singular_name'         => __( 'Proposal', 'wp-business-proposals' ),
		'menu_name'             => __( 'Proposals', 'wp-business-proposals' ),
		'name_admin_bar'        => __( 'Proposal', 'wp-business-proposals' ),
		'add_new'               => __( 'Add New', 'wp-business-proposals' ),
		'add_new_item'          => __( 'Add New Proposal', 'wp-business-proposals' ),
		'new_item'              => __( 'New Proposal', 'wp-business-proposals' ),
		'edit_item'             => __( 'Edit Proposal', 'wp-business-proposals' ),
		'view_item'             => __( 'View Proposal', 'wp-business-proposals' ),
		'all_items'             => __( 'All Proposals', 'wp-business-proposals' ),
		'not_found'             => __( 'No proposals found.', 'wp-business-proposals' ),
		'not_found_in_trash'    => __( 'No proposals found in Trash.', 'wp-business-proposals' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'proposal' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 20,
		'supports'           => array( 'title', 'editor', 'author' ),
		'show_in_rest'       => true,
	);

	register_post_type( 'proposal', $args );
}
add_action( 'init', 'wpbp_register_proposal_cpt' );

/**
 * Secure output for proposal content on the front end
 */
function wpbp_proposal_content_filter( $content ) {
	if ( is_singular( 'proposal' ) && in_the_loop() && is_main_query() ) {
		global $post;
		$title = esc_html( get_the_title( $post ) );
		$body  = wp_kses_post( $post->post_content );
		return '<h2>' . $title . '</h2><div class="proposal-content">' . $body . '</div>';
	}
	return $content;
}
add_filter( 'the_content', 'wpbp_proposal_content_filter' );

/**
 * Add custom capabilities on activation
 */
function wpbp_activate() {
	wpbp_register_proposal_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wpbp_activate' );

/**
 * Flush rewrite rules on deactivation
 */
function wpbp_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wpbp_deactivate' );

// End of file 
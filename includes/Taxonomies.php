<?php
namespace Yoostrap\Yosal;

defined( 'ABSPATH' ) || exit;

/**
 * Handles taxonomy registration
 */
class Taxonomies {

	/**
	 * Constructor
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
	}

	/**
	 * Register taxonomies
	 */
	public static function register_taxonomies() {
		
		// Register Proposal Status taxonomy
		register_taxonomy(
			'wbp_proposal_status',
			'wbp_proposal',
			array(
				'labels'              => array(
					'name'                  => __( 'Proposal Statuses', 'wp-business-proposals' ),
					'singular_name'         => __( 'Proposal Status', 'wp-business-proposals' ),
					'search_items'          => __( 'Search Statuses', 'wp-business-proposals' ),
					'all_items'             => __( 'All Statuses', 'wp-business-proposals' ),
					'parent_item'           => __( 'Parent Status', 'wp-business-proposals' ),
					'parent_item_colon'     => __( 'Parent Status:', 'wp-business-proposals' ),
					'edit_item'             => __( 'Edit Status', 'wp-business-proposals' ),
					'update_item'           => __( 'Update Status', 'wp-business-proposals' ),
					'add_new_item'          => __( 'Add New Status', 'wp-business-proposals' ),
					'new_item_name'         => __( 'New Status Name', 'wp-business-proposals' ),
					'menu_name'             => __( 'Statuses', 'wp-business-proposals' ),
				),
				'hierarchical'        => true,
				'show_ui'             => true,
				'show_admin_column'   => true,
				'query_var'           => true,
				'rewrite'             => array( 'slug' => 'proposal-status' ),
				'show_in_rest'        => true,
			)
		);
		
		// Register Proposal Type taxonomy
		register_taxonomy(
			'wbp_proposal_type',
			'wbp_proposal',
			array(
				'labels'              => array(
					'name'                  => __( 'Proposal Types', 'wp-business-proposals' ),
					'singular_name'         => __( 'Proposal Type', 'wp-business-proposals' ),
					'search_items'          => __( 'Search Types', 'wp-business-proposals' ),
					'all_items'             => __( 'All Types', 'wp-business-proposals' ),
					'parent_item'           => __( 'Parent Type', 'wp-business-proposals' ),
					'parent_item_colon'     => __( 'Parent Type:', 'wp-business-proposals' ),
					'edit_item'             => __( 'Edit Type', 'wp-business-proposals' ),
					'update_item'           => __( 'Update Type', 'wp-business-proposals' ),
					'add_new_item'          => __( 'Add New Type', 'wp-business-proposals' ),
					'new_item_name'         => __( 'New Type Name', 'wp-business-proposals' ),
					'menu_name'             => __( 'Types', 'wp-business-proposals' ),
				),
				'hierarchical'        => true,
				'show_ui'             => true,
				'show_admin_column'   => true,
				'query_var'           => true,
				'rewrite'             => array( 'slug' => 'proposal-type' ),
				'show_in_rest'        => true,
			)
		);
		
		// Register Client Category taxonomy
		register_taxonomy(
			'wbp_client_category',
			'wbp_client',
			array(
				'labels'              => array(
					'name'                  => __( 'Client Categories', 'wp-business-proposals' ),
					'singular_name'         => __( 'Client Category', 'wp-business-proposals' ),
					'search_items'          => __( 'Search Categories', 'wp-business-proposals' ),
					'all_items'             => __( 'All Categories', 'wp-business-proposals' ),
					'parent_item'           => __( 'Parent Category', 'wp-business-proposals' ),
					'parent_item_colon'     => __( 'Parent Category:', 'wp-business-proposals' ),
					'edit_item'             => __( 'Edit Category', 'wp-business-proposals' ),
					'update_item'           => __( 'Update Category', 'wp-business-proposals' ),
					'add_new_item'          => __( 'Add New Category', 'wp-business-proposals' ),
					'new_item_name'         => __( 'New Category Name', 'wp-business-proposals' ),
					'menu_name'             => __( 'Categories', 'wp-business-proposals' ),
				),
				'hierarchical'        => true,
				'show_ui'             => true,
				'show_admin_column'   => true,
				'query_var'           => true,
				'rewrite'             => array( 'slug' => 'client-category' ),
				'show_in_rest'        => true,
			)
		);
	}
	
	/**
	 * Add default terms
	 */
	public static function add_default_terms() {
		// Add default proposal statuses
		$statuses = array(
			'draft'     => __( 'Draft', 'wp-business-proposals' ),
			'sent'      => __( 'Sent', 'wp-business-proposals' ),
			'accepted'  => __( 'Accepted', 'wp-business-proposals' ),
			'rejected'  => __( 'Rejected', 'wp-business-proposals' ),
			'expired'   => __( 'Expired', 'wp-business-proposals' ),
		);
		
		foreach ( $statuses as $slug => $name ) {
			if ( ! term_exists( $slug, 'wbp_proposal_status' ) ) {
				wp_insert_term( $name, 'wbp_proposal_status', array( 'slug' => $slug ) );
			}
		}
		
		// Add default proposal types
		$types = array(
			'service'    => __( 'Service', 'wp-business-proposals' ),
			'product'    => __( 'Product', 'wp-business-proposals' ),
			'consulting' => __( 'Consulting', 'wp-business-proposals' ),
		);
		
		foreach ( $types as $slug => $name ) {
			if ( ! term_exists( $slug, 'wbp_proposal_type' ) ) {
				wp_insert_term( $name, 'wbp_proposal_type', array( 'slug' => $slug ) );
			}
		}
	}
}

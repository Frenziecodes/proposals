<?php
namespace Yoostrap\Yosal;

defined('ABSPATH') || exit;

/**
 * Handles post type registration for WP Business Proposals
 */
class Post_Types
{

    /**
     * Constructor
     */
    public static function init()
    {
        add_action('init', array( __CLASS__, 'register_post_types' ), 5);
        register_activation_hook(WP_BUSINESS_PROPOSALS_FILE, array( __CLASS__, 'on_activation' ));
    }

    /**
     * Register post types
     */
    public static function register_post_types()
    {

        // Register Client post type
        register_post_type(
            'wbp_client',
            array(
            'labels'            => array(
            'name'               => __('Clients', 'wp-business-proposals'),
            'singular_name'      => __('Client', 'wp-business-proposals'),
            'add_new'            => __('Add New', 'wp-business-proposals'),
            'add_new_item'       => __('Add New Client', 'wp-business-proposals'),
            'edit_item'          => __('Edit Client', 'wp-business-proposals'),
            'new_item'           => __('New Client', 'wp-business-proposals'),
            'view_item'          => __('View Client', 'wp-business-proposals'),
            'search_items'       => __('Search Clients', 'wp-business-proposals'),
            'not_found'          => __('No clients found', 'wp-business-proposals'),
            'not_found_in_trash' => __('No clients found in Trash', 'wp-business-proposals'),
            'parent_item_colon'  => __('Parent Client:', 'wp-business-proposals'),
            'menu_name'          => __('Clients', 'wp-business-proposals'),
            ),
            'public'            => true,
            'hierarchical'      => false,
            'show_ui'           => false,
            'show_in_menu'      => false,
            'show_in_nav_menus' => true,
            'supports'          => array( 'title', 'custom-fields' ),
            'has_archive'       => true,
            'rewrite'           => array( 'slug' => 'clients' ),
            'menu_icon'         => 'dashicons-businessperson',
            'show_in_rest'      => true,
            'capabilities'      => array(
                    'create_posts'       => 'edit_posts',
                    'edit_post'          => 'edit_posts',
                    'read_post'          => 'edit_posts',
                    'delete_post'        => 'edit_posts',
                    'edit_posts'         => 'edit_posts',
                    'edit_others_posts'  => 'edit_posts',
                    'publish_posts'      => 'edit_posts',
                    'read_private_posts' => 'edit_posts',
            ),
            )
        );

        // Register Proposal post type
        register_post_type(
            'wbp_proposal',
            array(
            'labels'            => array(
            'name'               => __('Proposals', 'wp-business-proposals'),
            'singular_name'      => __('Proposal', 'wp-business-proposals'),
            'add_new'            => __('Add New', 'wp-business-proposals'),
            'add_new_item'       => __('Add New Proposal', 'wp-business-proposals'),
            'edit_item'          => __('Edit Proposal', 'wp-business-proposals'),
            'new_item'           => __('New Proposal', 'wp-business-proposals'),
            'view_item'          => __('View Proposal', 'wp-business-proposals'),
            'search_items'       => __('Search Proposals', 'wp-business-proposals'),
            'not_found'          => __('No proposals found', 'wp-business-proposals'),
            'not_found_in_trash' => __('No proposals found in Trash', 'wp-business-proposals'),
            'parent_item_colon'  => __('Parent Proposal:', 'wp-business-proposals'),
            'menu_name'          => __('Proposals', 'wp-business-proposals'),
            ),
            'public'            => true,
            'hierarchical'      => false,
            'show_ui'           => true,
            'show_in_menu'      => 'wp-business-proposals',
            'show_in_nav_menus' => true,
            'supports'          => array( 'title', 'editor', 'thumbnail', 'revisions', 'custom-fields' ),
            'has_archive'       => true,
            'rewrite'           => array( 'slug' => 'proposals' ),
            'menu_icon'         => 'dashicons-media-text',
            'show_in_rest'      => true,
            'capability_type'   => 'post',
            'map_meta_cap'      => true,
            )
        );
    }

    /**
     * Flush rewrite rules on activation
     */
    public static function on_activation()
    {
        self::register_post_types();
        flush_rewrite_rules();
    }
}

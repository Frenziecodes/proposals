<?php
namespace Yoostrap\Yosal\Admin;

defined('ABSPATH') || exit;

/**
 * Handles admin menu registration
 */
class Menu
{

    /**
     * Constructor
     */
    public static function init()
    {
        add_action('admin_menu', array( __CLASS__, 'register_menus' ));
    }

    /**
     * Register admin menus
     */
    public static function register_menus()
    {
        // Add main menu page
        add_menu_page(
            __('Business Proposals', 'wp-business-proposals'),
            __('Proposals', 'wp-business-proposals'),
            'manage_options',
            'wp-business-proposals',
            array( __CLASS__, 'main_page' ),
            'dashicons-media-text',
            30
        );

        add_submenu_page(
            'wp-business-proposals',
            __('Clients', 'wp-business-proposals'),
            __('Clients', 'wp-business-proposals'),
            'manage_options',
            'wbp-clients',
            array( __CLASS__, 'clients_page' )
        );
 
    }
    
    /**
     * Main plugin page
     */
    public static function main_page()
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Business Proposals Dashboard', 'wp-business-proposals') . '</h1>';
        echo '<p>' . esc_html__('Welcome to WP Business Proposals plugin.', 'wp-business-proposals') . '</p>';
        echo '</div>';
    }
    
    /**
     * Clients page
     */
    public static function clients_page()
    {
        include_once WP_BUSINESS_PROPOSALS_PLUGIN_DIR . 'includes/admin/clients.php';
    }
}

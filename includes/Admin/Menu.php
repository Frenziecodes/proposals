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
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_menu', array( __CLASS__, 'registerMenus' ));
    }

    /**
     * Register admin menus
     *
     * @return void
     */
    public static function registerMenus()
    {

        add_menu_page(
            __('Business Proposals', 'wp-business-proposals'),
            __('Proposals', 'wp-business-proposals'),
            'manage_options',
            'wbp-proposals',
            array( __CLASS__, 'proposalsPage' ),
            'dashicons-media-text',
            30
        );      

        add_submenu_page(
            'wbp-proposals',
            __('Clients', 'wp-business-proposals'),
            __('Clients', 'wp-business-proposals'),
            'manage_options',
            'wbp-clients',
            array( __CLASS__, 'clientsPage' )
        );

    }

    
    /**
     * All proposals page
     *
     * @return void
     */
    public static function proposalsPage()
    {
        include_once WP_BUSINESS_PROPOSALS_PLUGIN_DIR . 'includes/admin/proposals.php';
    }
    
    /**
     * Clients page
     *
     * @return void
     */
    public static function clientsPage()
    {
        include_once WP_BUSINESS_PROPOSALS_PLUGIN_DIR . 'includes/admin/clients.php';
    }

}

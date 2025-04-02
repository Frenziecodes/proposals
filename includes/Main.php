<?php
namespace Yoostrap\Yosal;

use Yoostrap\Yosal\Admin\Menu;

defined('ABSPATH') || exit;

/**
 * Main plugin class
 */
class Main
{

    /**
     * Main Instance
     */
    public static function init()
    {
        Post_Types::init();
        Menu::init();

        // Add activation hook
        register_activation_hook(WP_BUSINESS_PROPOSALS_FILE, array( __CLASS__, 'activate' ));
    }
    
    /**
     * Plugin activation
     */
    public static function activate()
    {
        
        // Register post types and taxonomies
        Post_Types::on_activation();
        
        // Maybe set default options
        if (! get_option('wp_business_proposals_version') ) {
            add_option('wp_business_proposals_version', WP_BUSINESS_PROPOSALS_VERSION);
        }

    }
}

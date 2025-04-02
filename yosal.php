<?php
/**
 * Plugin Name: WP Business Proposals
 * Plugin URI: https://yoostrap.com/wp-business-proposals
 * Description: A WordPress plugin that allows businesses to create and manage proposals directly within WordPress.
 * Version: 0.1.0
 * Author: Lewis Ushindi
 * Author URI: https://yostrap.com
 * Text Domain: wp-business-proposals
 * Domain Path: /languages
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Define plugin constants
define( 'WP_BUSINESS_PROPOSALS_FILE', __FILE__ );
define('WP_BUSINESS_PROPOSALS_VERSION', '0.1.0');
define('WP_BUSINESS_PROPOSALS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_BUSINESS_PROPOSALS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the auto loader.
require 'vendor/autoload.php';

// Start the plugin
Yoostrap\Yosal\Main::init();
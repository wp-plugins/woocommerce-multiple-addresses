<?php
/**
 * Woocommerce Multiple Addresses plugin.
 *
 * @package   WC_Multiple_addresses
 * @author    Alexander Tinyaev <alexander.tinyaev@n3wnormal.com>
 * @license   GPL-2.0+
 * @link      http://n3wnormal.com
 * @copyright 2015 N3wNormal
 *
 * @wordpress-plugin
 * Plugin Name: Woocommerce Multiple Addresses
 * Plugin URI:  http://n3wnormal.com
 * Description: The plugin allows customers have more than one shipping addresses. Customers can switch one to another on checkout or setup a default one in My Account.
 * Version:     1.0.7.1
 * Author:      Alexander Tinyaev
 * Author URI:  http://n3wnormal.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if WooCommerce is active
 **/
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	deactivate_plugins( plugin_dir_path( __FILE__ ) . 'woocommerce-multiple-addresses.php', false );
	die ( 'Please activate WooCommerce plugin.' );
}

/**
 * Require plugin class
 **/
require_once( plugin_dir_path( __FILE__ ) . 'class-woocommerce-multiple-addresses.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'WC_Multiple_addresses', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WC_Multiple_addresses', 'deactivate' ) );

WC_Multiple_addresses::get_instance();
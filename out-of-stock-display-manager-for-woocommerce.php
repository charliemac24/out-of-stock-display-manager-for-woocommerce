<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://charliemacaraeg.com
 * @since             1.0.0
 * @package           Out_Of_Stock_Display_Manager_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Out of Stock Display Manager for WooCommerce
 * Plugin URI:        https://outofstockdisplaymanager.com
 * Description:       Transform how your WooCommerce store handles out-of-stock and low-stock products with a powerful yet user-friendly solution designed to boost customer experience and optimize sales.
 * Version:           1.0.0
 * Author:            Charlie Macaraeg
 * Author URI:        https://charliemacaraeg.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       out-of-stock-display-manager-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'OUT_OF_STOCK_DISPLAY_MANAGER_FOR_WOOCOMMERCE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-out-of-stock-display-manager-for-woocommerce-activator.php
 */
function activate_out_of_stock_display_manager_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-out-of-stock-display-manager-for-woocommerce-activator.php';
	Out_Of_Stock_Display_Manager_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-out-of-stock-display-manager-for-woocommerce-deactivator.php
 */
function deactivate_out_of_stock_display_manager_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-out-of-stock-display-manager-for-woocommerce-deactivator.php';
	Out_Of_Stock_Display_Manager_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_out_of_stock_display_manager_for_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_out_of_stock_display_manager_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-out-of-stock-display-manager-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_out_of_stock_display_manager_for_woocommerce() {

	$plugin = new Out_Of_Stock_Display_Manager_For_Woocommerce();
	$plugin->run();

}
run_out_of_stock_display_manager_for_woocommerce();

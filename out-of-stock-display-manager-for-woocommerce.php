<?php
/**
 * Plugin Name:		Out of Stock Display Manager for WooCommerce
 * Description: 	A handy plugin for bulk managing the visibility of your out of stock products.
 * Author: 			Charlie Macaraeg
 * Version: 		1.0
 * Author URI:		https://profiles.wordpress.org/charliemac24
 * License:         GPLv3
 * License URI:     https://www.gnu.org/licenses/gpl.txt
 * Text Domain:		out-of-stock-display-manager-for-woocommerce
 * Domain Path:		/languages
 *
 * @package		Out_of_Stock_Display_Manager_for_WooCommerce
 * @author		charliemac24
 * @license		GPLv3
 * @link		https://www.gnu.org/licenses/gpl.txt
 *
 * Out of Stock Display Manager for WooCommerce is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 3, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.e
 *
 * Out of Stock Display Manager for WooCommerce is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
**/

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
if (!defined('OOSDM_PLUGIN_VERSION')) {
    define('OOSDM_PLUGIN_VERSION', "1.0");
}
if (!defined('OOSDM_PLUGIN_DIR')) {
    define('OOSDM_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
}
if (!defined('OOSDM_PLUGIN_URI')) {
    define('OOSDM_PLUGIN_URI', plugin_dir_url( __FILE__ ));
}


load_plugin_textdomain( 'out-of-stock-display-manager-for-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages/' );

include_once(OOSDM_PLUGIN_DIR ."functions.php");



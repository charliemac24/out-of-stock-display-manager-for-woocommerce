<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://charliemacaraeg.com
 * @since      1.0.0
 *
 * @package    Out_Of_Stock_Display_Manager_For_Woocommerce
 * @subpackage Out_Of_Stock_Display_Manager_For_Woocommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Out_Of_Stock_Display_Manager_For_Woocommerce
 * @subpackage Out_Of_Stock_Display_Manager_For_Woocommerce/includes
 * @author     Charlie Macaraeg <charlieanchetamacaraeg@gmail.com>
 */
class Out_Of_Stock_Display_Manager_For_Woocommerce_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'out-of-stock-display-manager-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}

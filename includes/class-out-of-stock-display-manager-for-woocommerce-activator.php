<?php

/**
 * Fired during plugin activation
 *
 * @link       https://charliemacaraeg.com
 * @since      1.0.0
 *
 * @package    Out_Of_Stock_Display_Manager_For_Woocommerce
 * @subpackage Out_Of_Stock_Display_Manager_For_Woocommerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Out_Of_Stock_Display_Manager_For_Woocommerce
 * @subpackage Out_Of_Stock_Display_Manager_For_Woocommerce/includes
 * @author     Charlie Macaraeg <charlieanchetamacaraeg@gmail.com>
 */
class Out_Of_Stock_Display_Manager_For_Woocommerce_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		global $wpdb;
    
        // Query the database for out-of-stock products
        $query = "
            SELECT posts.ID 
            FROM {$wpdb->prefix}posts AS posts
            INNER JOIN {$wpdb->prefix}postmeta AS meta_stock_status
            ON posts.ID = meta_stock_status.post_id
            WHERE posts.post_type = 'product'
            AND posts.post_status = 'publish'
            AND meta_stock_status.meta_key = '_stock_status'
            AND meta_stock_status.meta_value = 'outofstock'
        ";
    
        // Fetch product IDs
        $results = $wpdb->get_col($query);

		foreach( $results as $product_id ){
			wp_remove_object_terms($product_id, 'exclude-from-catalog', 'product_visibility');
            wp_remove_object_terms($product_id, 'exclude-from-search', 'product_visibility');
		}
	}

}

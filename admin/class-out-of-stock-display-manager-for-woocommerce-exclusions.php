<?php

/**
 * Handles exclusions for out-of-stock products in WooCommerce.
 */
class Out_Of_Stock_Display_Manager_For_Woocommerce_Exclusions {

    /**
     * Retrieves the list of product IDs to be excluded.
     *
     * @return array List of excluded product IDs.
     */
    public function get_excluded_product_ids() {
        // Get the option from the WooCommerce settings
        $options = get_option('woocommerce_out_of_stock_exclusions', []);
        $excluded_products = isset($options['excluded_products']) ? $options['excluded_products'] : '';
        
        // Return the excluded product IDs as an array, trimming any extra spaces
        return !empty($excluded_products) ? array_map('trim', explode(',', $excluded_products)) : [];
    }

    /**
     * Retrieves the list of hidden category IDs.
     *
     * @return array List of hidden category IDs.
     */
    public function get_hidden_category_ids() {
        // Get the option from the WooCommerce settings
        $options = get_option('woocommerce_out_of_stock_exclusions', []);
        $hidden_categories = isset($options['hidden_categories']) ? $options['hidden_categories'] : '';
        
        // Return the hidden category IDs as an array, trimming any extra spaces
        return !empty($hidden_categories) ? array_map('trim', explode(',', $hidden_categories)) : [];
    }

    /**
     * Checks if out-of-stock products are excluded from the shop page.
     *
     * @return string 'yes' if excluded, empty string otherwise.
     */
    public function is_excluded_from_shop() {
        $options = get_option('woocommerce_out_of_stock_exclusions', []);
        return isset($options['excluded_pages']['shop']) ? $options['excluded_pages']['shop'] : "";
    }

    /**
     * Checks if out-of-stock products are excluded from category pages.
     *
     * @return string 'yes' if excluded, empty string otherwise.
     */
    public function is_excluded_from_category() {
        $options = get_option('woocommerce_out_of_stock_exclusions', []);
        return isset($options['excluded_pages']['category']) ? $options['excluded_pages']['category'] : "";
    }

    /**
     * Checks if out-of-stock products are excluded from search results.
     *
     * @return string 'yes' if excluded, empty string otherwise.
     */
    public function is_excluded_from_search() {
        $options = get_option('woocommerce_out_of_stock_exclusions', []);
        return isset($options['excluded_pages']['search']) ? $options['excluded_pages']['search'] : "";
    }
}

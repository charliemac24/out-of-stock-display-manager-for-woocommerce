<?php

/**
 * Handles exclusions for out-of-stock products in WooCommerce.
 */
class Out_Of_Stock_Display_Manager_For_Woocommerce_Exclusions {

    /**
     * Retrieves the WooCommerce exclusion options.
     *
     * @return array The stored options for out-of-stock exclusions.
     */
    private function get_options() {
        return get_option('woocommerce_out_of_stock_settings', []);
    }

    /**
     * Retrieves a specific option value from the exclusions settings.
     *
     * @param string $key The key to retrieve.
     * @param mixed $default The default value if the key doesn't exist.
     * @return mixed The option value.
     */
    private function get_option_value($key, $default = '') {
        $options = $this->get_options();
        return isset($options[$key]) ? $options[$key] : $default;
    }

    /**
     * Converts a comma-separated string to an array, trimming extra spaces.
     *
     * @param string $value The comma-separated string.
     * @return array The resulting array.
     */
    private function parse_comma_separated_value($value) {
        return !empty($value) ? array_map('trim', explode(',', $value)) : [];
    }

    /**
     * Retrieves the list of product IDs to be excluded.
     *
     * @return array List of excluded product IDs.
     */
    public function get_excluded_product_ids() {
        $excluded_products = $this->get_option_value('excluded_products', '');
        return $this->parse_comma_separated_value($excluded_products);
    }

    /**
     * Retrieves the list of hidden category IDs.
     *
     * @return array List of hidden category IDs.
     */
    public function get_hidden_category_ids() {
        $hidden_categories = $this->get_option_value('hidden_categories', '');
        return $this->parse_comma_separated_value($hidden_categories);
    }

    /**
     * Checks if out-of-stock products are hidden from a specific page type.
     *
     * @param string $page The page type (e.g., 'shop', 'category', 'search').
     * @return string 'yes' if hidden, empty string otherwise.
     */
    public function is_hidden_from($page) {
        $hidden_from_pages = $this->get_option_value('hidden_from_pages', []);
        return isset($hidden_from_pages[$page]) ? $hidden_from_pages[$page] : '';
    }

    /**
     * Checks if out-of-stock products are hidden from the shop page.
     *
     * @return string 'yes' if hidden, empty string otherwise.
     */
    public function is_hidden_from_shop() {
        return $this->is_hidden_from('shop');
    }

    /**
     * Checks if out-of-stock products are hidden from category pages.
     *
     * @return string 'yes' if hidden, empty string otherwise.
     */
    public function is_hidden_from_category() {
        return $this->is_hidden_from('category');
    }

    /**
     * Checks if out-of-stock products are hidden from search results.
     *
     * @return string 'yes' if hidden, empty string otherwise.
     */
    public function is_hidden_from_search() {
        return $this->is_hidden_from('search');
    }
}
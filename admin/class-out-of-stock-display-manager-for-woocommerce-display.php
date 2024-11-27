<?php

require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-out-of-stock-display-manager-for-woocommerce-exclusions.php';

/**
 * Class Out_Of_Stock_Display_Manager_For_Woocommerce_Display
 *
 * Manages the display of out-of-stock products in WooCommerce based on specific settings and exclusions.
 */
class Out_Of_Stock_Display_Manager_For_Woocommerce_Display {

    /**
     * @var array List of excluded product IDs.
     */
    protected $_exclude_prod_ids;

    /**
     * @var bool Determines if out-of-stock products should be excluded from the shop page.
     */
    protected $_is_excluded_from_shop;

    /**
     * @var bool Determines if out-of-stock products should be excluded from category pages.
     */
    protected $_is_excluded_from_category;

    /**
     * @var bool Determines if out-of-stock products should be excluded from search results.
     */
    protected $_is_excluded_from_search;

    /**
     * @var array List of category IDs where out-of-stock products should be hidden.
     */
    protected $_hidden_category_ids;

    /**
     * Constructor
     *
     * Initializes the class and sets up WordPress hooks.
     */
    public function __construct() {

        // Initialize the Exclusion class
        $_exclusions = new Out_Of_Stock_Display_Manager_For_Woocommerce_Exclusions();

        $this->_exclude_prod_ids = $_exclusions->get_excluded_product_ids();
        $this->_is_excluded_from_shop = $_exclusions->is_excluded_from_shop();
        $this->_is_excluded_from_category = $_exclusions->is_excluded_from_category();
        $this->_is_excluded_from_search = $_exclusions->is_excluded_from_search();
        $this->_hidden_category_ids = $_exclusions->get_hidden_category_ids();

        add_action('woocommerce_product_query', [$this, 'filter_products_query']);
    }

    /**
     * Filters WooCommerce product queries to exclude out-of-stock products.
     *
     * @param WC_Query $query The WooCommerce query object.
     */
    public function filter_products_query($query) {
        // Ensure this logic applies only on the frontend and for WooCommerce queries
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        // Get out-of-stock display setting
        $setting = $this->get_out_of_stock_display_setting();
        $out_of_stock_display = $setting['out_of_stock_display'];

        // Proceed only if the display setting is "hide"
        if ($out_of_stock_display !== 'hide') {
            return;
        }

        // Prepare the meta query to exclude out-of-stock products
        $meta_query = $query->get('meta_query') ?: [];
        $meta_query = array_merge($meta_query, $this->exclude_out_of_stock());

        // Apply conditions based on page type
        if (is_shop() && $this->_is_excluded_from_shop) {
            $query->set('meta_query', $meta_query);
        }

        // Exclude by category ids
        if (is_shop() && $this->_hidden_category_ids) {
            $query->set('post__not_in', $this->get_out_of_stock_by_category());
        }

        if (is_product_category() && $this->_is_excluded_from_category) {
            $query->set('meta_query', $meta_query);
        }

        if (is_search() && $this->_is_excluded_from_search) {
            $query->set('meta_query', $meta_query);
        }

        // Exclude by category ids on search results
        if (is_search() && $this->_hidden_category_ids) {
            $query->set('post__not_in', $this->get_out_of_stock_by_category());
        }

        // Hide all if no conditions meet
        if (!$this->_is_excluded_from_shop && !$this->_is_excluded_from_category && !$this->_is_excluded_from_search) {
            $query->set('meta_query', $meta_query);
        }

        // Always show excluded IDs mixed with in-stock products
        if ($this->_exclude_prod_ids) {
            $meta_query = array_merge($meta_query, $this->show_exclude_product_ids());
            $query->set('meta_query', $meta_query);
            $query->set('post__in', array_merge($this->_exclude_prod_ids, $this->get_instock_product_ids()));
        }
    }

    /**
     * Retrieves IDs of in-stock products.
     *
     * @return array List of in-stock product IDs.
     */
    private function get_instock_product_ids() {
        $query = new WP_Query(array(
            'post_type' => 'product',
            'posts_per_page' => -1, // Retrieve all products
            'fields' => 'ids',      // Only fetch IDs
            'meta_query' => array(
                array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '='
                )
            )
        ));

        return $query->posts;
    }

    /**
     * Retrieves the meta query to exclude out-of-stock products.
     *
     * @return array Meta query array.
     */
    private function exclude_out_of_stock() {
        return [
            [
                'key'     => '_stock_status',
                'value'   => 'instock',
                'compare' => '='
            ]
        ];
    }

    /**
     * Retrieves the meta query to include excluded out-of-stock products.
     *
     * @return array Meta query array.
     */
    private function show_exclude_product_ids() {
        return [
            [
                'key' => '_stock_status',
                'value' => 'outofstock',
                'compare' => '='
            ],
            'relation' => 'OR'
        ];
    }

    /**
     * Retrieves out-of-stock product IDs by specified category IDs.
     *
     * @return array List of product IDs.
     */
    private function get_out_of_stock_by_category() {
        if (empty($this->_hidden_category_ids)) {
            return [];
        }

        $query = new WP_Query([
            'post_type' => 'product',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $this->_hidden_category_ids,
                ],
            ],
            'meta_query' => [
                [
                    'key'     => '_stock_status',
                    'value'   => 'outofstock',
                    'compare' => '=',
                ],
            ],
        ]);

        return $query->posts;
    }

    /**
     * Retrieves the out-of-stock display settings from WooCommerce.
     *
     * @return array The settings array.
     */
    private function get_out_of_stock_display_setting() {
        return get_option('woocommerce_out_of_stock_settings');
    }
}

// Initialize the class.
new Out_Of_Stock_Display_Manager_For_Woocommerce_Display();
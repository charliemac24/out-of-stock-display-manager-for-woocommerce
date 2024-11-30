<?php

require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-out-of-stock-display-manager-for-woocommerce-exclusions.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-out-of-stock-display-manager-for-woocommerce-backorder.php';

/**
 * Class Out_Of_Stock_Display_Manager_For_Woocommerce_Display
 *
 * Manages the display of out-of-stock products in WooCommerce based on specific settings and exclusions. 
 * This class handles customizations for product availability labels, filtering out-of-stock products 
 * from product queries, and managing exclusions for different pages like the shop, product category, and search.
 */
class Out_Of_Stock_Display_Manager_For_Woocommerce_Display {

    /**
     * @var Out_Of_Stock_Display_Manager_For_Woocommerce_Exclusions Manager for product exclusions settings.
     */
    protected $exclusions_manager;

    /**
     * @var Out_Of_Stock_Display_Manager_For_Woocommerce_Backorder Manager for backorder settings.
     */
    protected $backorder_manager;

    /**
     * Class constructor.
     * 
     * Initializes the exclusion and backorder managers and hooks into WooCommerce actions and filters.
     */
    public function __construct() {
        // Initialize the Exclusion and Backorder managers
        $this->exclusions_manager = new Out_Of_Stock_Display_Manager_For_Woocommerce_Exclusions();
        $this->backorder_manager = new Out_Of_Stock_Display_Manager_For_Woocommerce_Backorder();
        
        // Add action and filter hooks
        add_action('woocommerce_product_query', [$this, 'filter_products_query']);
        add_filter('woocommerce_get_availability_text', [$this, 'customize_out_of_stock_label'], 10, 2);
    }

    /**
     * Customizes the out-of-stock label based on plugin settings.
     *
     * @param string $availability_text The default availability text.
     * @param WC_Product $product The WooCommerce product object.
     * 
     * @return string Customized availability text, either the custom backorder message or a default out-of-stock label.
     */
    public function customize_out_of_stock_label($availability_text, $product) {
        if (!$product->is_in_stock()) {

            // Retrieve the custom backorder message from the Backorder manager
            $custom_message = $this->backorder_manager->get_custom_backorder_message();
            
            // If custom message exists, replace the default label
            if ($custom_message) {
                return $custom_message;
            }

            // Retrieve the default out-of-stock label from the plugin settings
            $options = get_option('woocommerce_out_of_stock_settings');
            return $options['out_of_stock_label'] ?? __('Out of Stock', 'woocommerce');
        }
        return $availability_text;
    }

    /**
     * Filters WooCommerce product queries to exclude out-of-stock products.
     * 
     * @param WP_Query $query The WP_Query object used to fetch products.
     */
    public function filter_products_query($query) {
        // Skip if not the main query or in the admin area
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        // Get out-of-stock display setting
        $out_of_stock_display = $this->get_out_of_stock_display_setting()['out_of_stock_display'] ?? 'show';

        // Skip if the display setting is not to hide out-of-stock products
        if ($out_of_stock_display !== 'hide') {
            return;
        }

        // Prepare meta query to exclude out-of-stock products
        $meta_query = $this->exclude_out_of_stock();

        // Apply filters based on page type (shop, category, search)
        $this->apply_page_filters($query, $meta_query);

        // Always include excluded IDs mixed with in-stock products
        if ($this->exclusions_manager->get_excluded_product_ids()) {
            $meta_query[] = $this->show_exclude_product_ids();
            $query->set('meta_query', $meta_query);
            $query->set('post__in', array_merge($this->exclusions_manager->get_excluded_product_ids(), $this->get_instock_product_ids()));
        }
    }

    /**
     * Apply filters for specific page types (shop, category, search).
     * 
     * @param WP_Query $query The WP_Query object used to fetch products.
     * @param array $meta_query The meta query to be applied for product filtering.
     */
    private function apply_page_filters($query, &$meta_query) {
        // Hide from shop page
        if (is_shop() && $this->exclusions_manager->is_hidden_from_shop()) {
            $query->set('meta_query', $meta_query);
        }

        // Hide from category page
        if (is_product_category() && $this->exclusions_manager->is_hidden_from_shop()) {
            $query->set('meta_query', $meta_query);
        }

        // Hide from search results
        if (is_search() && $this->exclusions_manager->is_hidden_from_search()) {
            $query->set('meta_query', $meta_query);
        }

        // Exclude by category ids
        if (($category_check = $this->get_out_of_stock_by_category())) {
            $query->set('post__not_in', $category_check);
        }
    }

    /**
     * Retrieves IDs of in-stock products, cached for better performance.
     * 
     * @return array List of product IDs that are currently in stock.
     */
    private function get_instock_product_ids() {
        // Return cached in-stock product IDs if available
        $instock_prod_ids = "";
        if ($instock_prod_ids === null) {
            $query = new WP_Query([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query' => [
                    [
                        'key'     => '_stock_status',
                        'value'   => 'instock',
                        'compare' => '='
                    ]
                ]
            ]);
            $instock_prod_ids = $query->posts;
        }
        return $instock_prod_ids;
    }

    /**
     * Retrieves the meta query to exclude out-of-stock products.
     * 
     * @return array The meta query to exclude out-of-stock products.
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
     * @return array The meta query to include excluded out-of-stock products.
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
     * @return array List of product IDs that are out-of-stock and belong to excluded categories.
     */
    private function get_out_of_stock_by_category() {
        if (empty($this->exclusions_manager->get_hidden_category_ids())) {
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
                    'terms'    => $this->exclusions_manager->get_hidden_category_ids(),
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
     * @return array The out-of-stock settings option from WooCommerce.
     */
    private function get_out_of_stock_display_setting() {
        return get_option('woocommerce_out_of_stock_settings');
    }
}

// Initialize the class.
new Out_Of_Stock_Display_Manager_For_Woocommerce_Display();

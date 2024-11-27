<?php

/**
 * Out Of Stock Display Manager for WooCommerce Settings.
 *
 * Handles the settings page for customizing the display of out-of-stock products in WooCommerce,
 * including global display settings and granular exclusion rules for specific products and categories.
 *
 * @package WooCommerce
 */
class Out_Of_Stock_Display_Manager_For_Woocommerce_Settings {

    /**
     * Option name for general settings.
     *
     * @var string
     */
    private $option_name = 'woocommerce_out_of_stock_settings';

    /**
     * Option name for exclusion settings.
     *
     * @var string
     */
    private $exclusion_option_name = 'woocommerce_out_of_stock_exclusions';

    /**
     * Constructor. Hooks into WordPress admin menu and initialization actions.
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Adds the settings page under the WooCommerce menu in the admin panel.
     *
     * @return void
     */
    public function add_settings_page() {
        add_submenu_page(
            'woocommerce',
            __('Out of Stock Display', 'woocommerce'),
            __('Out of Stock Display', 'woocommerce'),
            'manage_woocommerce',
            'woocommerce-out-of-stock-display',
            [$this, 'settings_page']
        );
    }

    /**
     * Registers the settings and fields for global display settings and granular exclusion rules.
     *
     * @return void
     */
    public function register_settings() {

        // Register the global settings option
        register_setting($this->option_name, $this->option_name);

        // Section: Global Settings
        add_settings_section(
            'global_settings',
            __('Global Display Settings', 'woocommerce'),
            null,
            $this->option_name
        );

        add_settings_field(
            'out_of_stock_display',
            __('Out of Stock Product Display', 'woocommerce'),
            [$this, 'out_of_stock_display_field'],
            $this->option_name,
            'global_settings'
        );

        // Register the exclusion settings option
        register_setting($this->exclusion_option_name, $this->exclusion_option_name);

        // Section: Granular Rules (Only applicable when 'Hide' is selected)
        add_settings_section(
            'granular_rules',
            __('Exclusion Rules', 'woocommerce'),
            null,
            $this->exclusion_option_name
        );

        add_settings_field(
            'excluded_products',
            __('Exclude Specific Products', 'woocommerce'),
            [$this, 'excluded_products_field'],
            $this->exclusion_option_name,
            'granular_rules'
        );

        add_settings_field(
            'excluded_pages',
            __('Exclude Products on Specific Pages', 'woocommerce'),
            [$this, 'excluded_pages_field'],
            $this->exclusion_option_name,
            'granular_rules'
        );

        add_settings_field(
            'hidden_categories',
            __('Hide Products from Specific Categories', 'woocommerce'),
            [$this, 'hidden_categories_field'],
            $this->exclusion_option_name,
            'granular_rules'
        );
    }

    /**
     * Displays the settings page with the forms for general settings and exclusion rules.
     *
     * @return void
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Out of Stock Display Settings', 'woocommerce'); ?></h1>
    
            <!-- General Settings Form -->
            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_name); // General settings fields
                do_settings_sections($this->option_name); // General settings sections
                submit_button(__('Save General Settings', 'woocommerce'));
                ?>
            </form>
    
            <!-- Exclusion Rules Form -->
            <form method="post" action="options.php" class="exclusion-rules-wrapper-form">
                <?php
                settings_fields($this->exclusion_option_name); // Exclusion settings fields
                do_settings_sections($this->exclusion_option_name); // Exclusion settings sections
                submit_button(__('Save Exclusion Rules', 'woocommerce'));
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Displays the field for selecting the out-of-stock product display option.
     *
     * @return void
     */
    public function out_of_stock_display_field() {
        $options = get_option($this->option_name);
        $display_option = isset($options['out_of_stock_display']) ? $options['out_of_stock_display'] : 'hide';
        ?>
        <select name="<?php echo $this->option_name; ?>[out_of_stock_display]">
            <option value="hide" <?php selected($display_option, 'hide'); ?>>
                <?php _e('Hide', 'woocommerce'); ?>
            </option>
            <option value="label" <?php selected($display_option, 'label'); ?>>
                <?php _e('Show with Label', 'woocommerce'); ?>
            </option>
            <option value="backorder" <?php selected($display_option, 'backorder'); ?>>
                <?php _e('Allow Backorders', 'woocommerce'); ?>
            </option>
        </select>
        <p class="description"><?php _e('Choose how to display out-of-stock products.', 'woocommerce'); ?></p>
        <?php
    }

    /**
     * Displays the field for excluding specific products by IDs.
     *
     * @return void
     */
    public function excluded_products_field() {
        $options = get_option($this->exclusion_option_name);
        $excluded_products = isset($options['excluded_products']) ? $options['excluded_products'] : '';
        ?>
        <input type="text" name="<?php echo $this->exclusion_option_name; ?>[excluded_products]" 
            value="<?php echo esc_attr($excluded_products); ?>" 
            placeholder="<?php _e('Enter product IDs, comma-separated', 'woocommerce'); ?>" 
            class="regular-text">
        <p class="description"><?php _e('Exclude specific products from being hidden. Applies only if "Hide" is selected above.', 'woocommerce'); ?></p>
        <?php
    }

    /**
     * Displays the field for excluding products on specific pages (shop, search, category).
     *
     * @return void
     */
    public function excluded_pages_field() {
        $options = get_option($this->exclusion_option_name);
        $excluded_pages = isset($options['excluded_pages']) ? $options['excluded_pages'] : [];
        ?>
        <label>
            <input type="checkbox" name="<?php echo $this->exclusion_option_name; ?>[excluded_pages][shop]" 
                value="1" <?php checked(isset($excluded_pages['shop']), true); ?>>
            <?php _e('Exclude products from the shop page', 'woocommerce'); ?>
        </label><br>
        <label>
            <input type="checkbox" name="<?php echo $this->exclusion_option_name; ?>[excluded_pages][search]" 
                value="1" <?php checked(isset($excluded_pages['search']), true); ?>>
            <?php _e('Exclude products from the search results', 'woocommerce'); ?>
        </label><br>
        <label>
            <input type="checkbox" name="<?php echo $this->exclusion_option_name; ?>[excluded_pages][category]" 
                value="1" <?php checked(isset($excluded_pages['category']), true); ?>>
            <?php _e('Exclude products from category pages', 'woocommerce'); ?>
        </label>
        <?php
    }

    /**
     * Displays the field for hiding out-of-stock products from specific categories.
     *
     * @return void
     */
    public function hidden_categories_field() {
        $options = get_option($this->exclusion_option_name);
        $hidden_categories = isset($options['hidden_categories']) ? $options['hidden_categories'] : '';
        ?>
        <input type="text" name="<?php echo $this->exclusion_option_name; ?>[hidden_categories]" 
            value="<?php echo esc_attr($hidden_categories); ?>" 
            placeholder="<?php _e('Enter category IDs, comma-separated', 'woocommerce'); ?>" 
            class="regular-text">
        <p class="description"><?php _e('Hide all out-of-stock products from specific categories. Enter category IDs, separated by commas.', 'woocommerce'); ?></p>
        <?php
    }

    /**
     * Displays the field for customizing the out-of-stock label.
     *
     * @return void
     */
    public function custom_out_of_stock_label_field() {
        $options = get_option($this->exclusion_option_name);
        $custom_label = isset($options['custom_out_of_stock_label']) ? $options['custom_out_of_stock_label'] : __('Out of Stock', 'woocommerce');
        ?>
        <input type="text" name="<?php echo $this->exclusion_option_name; ?>[custom_out_of_stock_label]" 
            value="<?php echo esc_attr($custom_label); ?>" 
            class="regular-text">
        <p class="description"><?php _e('Customize the label shown on out-of-stock products.', 'woocommerce'); ?></p>
        <?php
    }

    /**
     * Displays the field for selecting the sorting priority of out-of-stock products.
     *
     * @return void
     */
    public function out_of_stock_sorting_field() {
        $options = get_option($this->option_name);
        $sorting = isset($options['out_of_stock_sorting']) ? $options['out_of_stock_sorting'] : 'last';
        ?>
        <select name="<?php echo $this->option_name; ?>[out_of_stock_sorting]">
            <option value="last" <?php selected($sorting, 'last'); ?>><?php _e('Show Last', 'woocommerce'); ?></option>
            <option value="first" <?php selected($sorting, 'first'); ?>><?php _e('Show First', 'woocommerce'); ?></option>
        </select>
        <p class="description"><?php _e('Set the sorting priority for out-of-stock products in product lists.', 'woocommerce'); ?></p>
        <?php
    }
}

new Out_Of_Stock_Display_Manager_For_Woocommerce_Settings();

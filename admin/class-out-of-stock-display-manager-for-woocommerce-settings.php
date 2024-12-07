<?php

/**
 * Out Of Stock Display Manager for WooCommerce Settings.
 *
 * Provides an interface for customizing the display of out-of-stock products,
 * including global settings and exclusion rules.
 *
 * @package WooCommerce
 */
class Out_Of_Stock_Display_Manager_For_Woocommerce_Settings {

    /**
     * Option name for settings.
     */
    private const OPTION_NAME = 'woocommerce_out_of_stock_settings';

    /**
     * Class constructor.
     * 
     * Sets up the admin-related functionalities for managing settings and dynamic behaviors.
     * 
     * Responsibilities:
     * - Adds the settings page to the WordPress admin menu.
     * - Registers the plugin's settings using the WordPress Settings API.
     * - Enqueues a dynamic toggle script for additional interactivity in the admin footer.
     * 
     * Hooks:
     * - `admin_menu`: Used to add the settings page to the WordPress admin menu.
     * - `admin_init`: Used to initialize and register plugin settings.
     * - `admin_footer`: Used to add custom scripts to the admin footer.
     * 
     * @return void
     */
    public function __construct() {
        // Hook to add the settings page to the WordPress admin menu
        add_action('admin_menu', [$this, 'add_settings_page']);
        
        // Hook to register plugin settings
        add_action('admin_init', [$this, 'register_settings']);
        
        // Hook to enqueue dynamic toggle script in the admin footer
        add_action('admin_footer', [$this, 'add_dynamic_toggle_script']);
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
            [$this, 'render_settings_page']
        );
    }

    /**
     * Registers settings and fields for the settings page.
     *
     * @return void
     */
    public function register_settings() {

        // Global settings
        register_setting(self::OPTION_NAME, self::OPTION_NAME);

        // General
        add_settings_section(
            'global_settings',
            __('Global Display Settings', 'woocommerce'),
            null,
            self::OPTION_NAME
        );

        add_settings_field(
            'out_of_stock_display',
            __('Out of Stock Product Display', 'woocommerce'),
            [$this, 'render_out_of_stock_display_field'],
            self::OPTION_NAME,
            'global_settings'
        );      

        add_settings_field(
            'out_of_stock_label',
            __('Out of Stock Label', 'woocommerce'),
            [$this, 'render_out_of_stock_label_field'],
            self::OPTION_NAME,
            'global_settings'
        );

        // Exclusions
        add_settings_section(
            'exclusion_rules',
            __('Exclusion Rules', 'woocommerce'),
            null,
            self::OPTION_NAME
        );

        $fields = [
            'excluded_products' => __('Exclude Specific Products', 'woocommerce'),
            'hidden_from_pages' => __('Hide Out-of-Stock Products on Specific Pages', 'woocommerce'),
            'excluded_by_categories' => __('Exclude Out-of-Stock Products in Specific Categories from Hiding', 'woocommerce')
        ];

        foreach ($fields as $field_key => $field_label) {
            add_settings_field(
                $field_key,
                $field_label,
                [$this, "render_{$field_key}_field"],
                self::OPTION_NAME,
                'exclusion_rules'
            );
        }

        // Backorders
        add_settings_section(
            'backorder_settings',
            __('Backorder Settings', 'woocommerce'),
            null,
            self::OPTION_NAME
        );

        add_settings_field(
            'custom_backorder_message',
            __('Custom Backorder Message', 'woocommerce'),
            [$this, 'render_custom_backorder_message_field'],
            self::OPTION_NAME,
            'backorder_settings'
        );

        add_settings_field(
            'display_backordered_products',
            __('Display Backordered Products', 'woocommerce'),
            [$this, 'render_display_backordered_products_field'],
            self::OPTION_NAME,
            'backorder_settings'
        );

        add_settings_field(
            'show_estimated_restock_date',
            __('Show Estimated Restock Date', 'woocommerce'),
            [$this, 'render_show_estimated_restock_date_field'],
            self::OPTION_NAME,
            'backorder_settings'
        );

        add_settings_field(
            'backorder_notifications',
            __('Backorder Notifications', 'woocommerce'),
            [$this, 'render_backorder_notifications_field'],
            self::OPTION_NAME,
            'backorder_settings'
        );
    }

    /**
     * Renders the settings page.
     *
     * @return void
     */
    public function render_settings_page() {
        $global_options = get_option(self::OPTION_NAME);
        $display_option = $global_options['out_of_stock_display'] ?? 'hide';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Out of Stock Display Settings', 'woocommerce'); ?></h1>
            <!-- General Settings Form -->
            <form method="post" action="options.php" class="out-of-stock-display-wrapper">
                <?php
                    
                // For saving the settings
                settings_fields(self::OPTION_NAME);

                // Display fields for woocommerce_out_of_stock_settings
                do_settings_sections(self::OPTION_NAME);

                // Submit button
                submit_button(__('Save Settings', 'woocommerce'));
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Renders the custom backorder message input field.
     *
     * This function outputs an input field that allows the user to specify a custom message
     * for backordered products.
     *
     * @return void
     */
    public function render_custom_backorder_message_field() {
        $options = get_option(self::OPTION_NAME);
        $value = $options['custom_backorder_message'] ?? '';
        ?>
        <input type="text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[custom_backorder_message]" 
            value="<?php echo esc_attr($value); ?>" 
            class="regular-text"
            placeholder="<?php esc_attr_e('Enter custom backorder message', 'woocommerce'); ?>">
        <?php
    }

    /**
     * Renders the display backordered products field.
     *
     * This function outputs radio buttons to allow the user to choose whether or not
     * to display backordered products.
     *
     * @return void
     */
    public function render_display_backordered_products_field() {
        $options = get_option(self::OPTION_NAME);
        $value = $options['display_backordered_products'] ?? '';
        ?>
        <input type="radio" name="<?php echo esc_attr(self::OPTION_NAME); ?>[display_backordered_products]" value="yes" <?php checked($value, 'yes'); ?>> Yes
        <input type="radio" name="<?php echo esc_attr(self::OPTION_NAME); ?>[display_backordered_products]" value="no" <?php checked($value, 'no'); ?>> No
        <?php
    }

    /**
     * Renders the show estimated restock date field.
     *
     * This function outputs a checkbox to allow the user to enable or disable
     * the display of an estimated restock date for backordered products.
     *
     * @return void
     */
    public function render_show_estimated_restock_date_field() {
        $options = get_option(self::OPTION_NAME);
        $value = $options['show_estimated_restock_date'] ?? '';
        ?>
        <input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[show_estimated_restock_date]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Show estimated restock date', 'woocommerce'); ?>
        <p class="description"><?php esc_html_e('If this is checked, custom backorder message will not work.', 'woocommerce'); ?></p>
        <?php
    }

    /**
     * Renders the backorder notifications field.
     *
     * This function outputs a checkbox to allow the user to enable or disable
     * sending notifications to customers about backorders.
     *
     * @return void
     */
    public function render_backorder_notifications_field() {
        $options = get_option(self::OPTION_NAME);
        $value = $options['backorder_notifications'] ?? '';
        ?>
        <input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[backorder_notifications]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Send backorder notifications to customers', 'woocommerce'); ?>
        <?php
    }

    /**
     * Renders the out-of-stock label field.
     *
     * @return void
     */
    public function render_out_of_stock_label_field() {
        $options = get_option(self::OPTION_NAME);
        $value = $options['out_of_stock_label'] ?? __('Out of Stock', 'woocommerce');
        ?>
        <input type="text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[out_of_stock_label]" 
            value="<?php echo esc_attr($value); ?>" 
            class="regular-text"
            placeholder="<?php esc_attr_e('Enter the out-of-stock label', 'woocommerce'); ?>">
        <p class="description"><?php esc_html_e('This label will appear for out-of-stock products.', 'woocommerce'); ?></p>
        <?php
    }

    /**
     * Renders the out-of-stock product display field.
     *
     * @return void
     */
    public function render_out_of_stock_display_field() {
        $options = get_option(self::OPTION_NAME);
        $value = $options['out_of_stock_display'] ?? 'hide';

        $display_options = [
            'hide', 
            'label',
            'backorder'
        ];

        ?>
        <select name="<?php echo esc_attr(self::OPTION_NAME); ?>[out_of_stock_display]" id="out-of-stock-display">            
            <?php foreach ($display_options as $option): ?>
                <option value="<?php echo esc_attr($option); ?>" <?php selected($value, $option); ?>>
                    <?php echo esc_html(ucfirst($option)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php esc_html_e('Choose how to display out-of-stock products.', 'woocommerce'); ?></p>
        <?php
    }

    /**
     * Renders the excluded products field.
     *
     * @return void
     */
    public function render_excluded_products_field() {
        $options = get_option(self::OPTION_NAME);
        $value = $options['excluded_products'] ?? '';
        ?>
        <input type="text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[excluded_products]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text"
               placeholder="<?php esc_attr_e('Enter product IDs, comma-separated', 'woocommerce'); ?>">
        <?php
    }

    /**
     * Renders the excluded pages field.
     *
     * @return void
     */
    public function render_hidden_from_pages_field() {
        $options = get_option(self::OPTION_NAME);
        $pages = ['shop', 'search', 'category'];
        $selected = $options['hidden_from_pages'] ?? [];
        foreach ($pages as $page) {
            ?>
            <label>
                <input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[hidden_from_pages][<?php echo esc_attr($page); ?>]" 
                       value="1" <?php checked(isset($selected[$page]), true); ?>>
                <?php echo esc_html(ucfirst($page)); ?>
            </label><br>
            <?php
        }
    }

    /**
     * Renders the hidden categories field.
     *
     * @return void
     */
    public function render_excluded_by_categories_field() {
        $options = get_option(self::OPTION_NAME);
        $value = $options['excluded_by_categories'] ?? '';
        ?>
        <input type="text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[excluded_by_categories]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text"
               placeholder="<?php esc_attr_e('Enter category IDs, comma-separated', 'woocommerce'); ?>">
        <?php
    }

    /**
     * Adds JavaScript to toggle exclusion rules visibility based on the dropdown.
     *
     * @return void
     */
    public function add_dynamic_toggle_script() {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Select the form with the class 'out-of-stock-display-wrapper'
                const form = document.querySelector('form.out-of-stock-display-wrapper');

                // Ensure the form and its h2 elements exist
                if (!form) return;
                const headings = form.querySelectorAll('h2');

                // Check if there are at least three h2 elements for the functionality to work
                if (headings.length >= 3) {
                    // Select the second h2 (Exclusion Rules) and its associated table
                    const exclusionRulesHeading = headings[1];
                    const exclusionRulesTable = exclusionRulesHeading.nextElementSibling;

                    // Select the third h2 (Backorder Settings) and its associated table
                    const backorderSettingsHeading = headings[2];
                    const backorderSettingsTable = backorderSettingsHeading.nextElementSibling;

                    // Create and wrap Exclusion Rules section
                    const exclusionWrapper = document.createElement('div');
                    exclusionWrapper.classList.add('exclusion-rules-wrapper');
                    exclusionWrapper.appendChild(exclusionRulesHeading);
                    exclusionWrapper.appendChild(exclusionRulesTable);
                    form.appendChild(exclusionWrapper);

                    // Create and wrap Backorder Settings section
                    const backorderWrapper = document.createElement('div');
                    backorderWrapper.classList.add('backorder-settings-wrapper');
                    backorderWrapper.appendChild(backorderSettingsHeading);
                    backorderWrapper.appendChild(backorderSettingsTable);
                    form.appendChild(backorderWrapper);
                }

                // Select the dropdown for display settings and the created wrappers
                const displayDropdown = document.getElementById('out-of-stock-display');
                const exclusionForm = document.querySelector('.exclusion-rules-wrapper');
                const backOrderForm = document.querySelector('.backorder-settings-wrapper');

                // Toggle visibility of sections based on dropdown value
                const toggleVisibility = () => {
                    const displayValue = displayDropdown?.value;

                    if (exclusionForm) {
                        exclusionForm.style.display = displayValue === 'hide' ? 'block' : 'none';
                    }

                    if (backOrderForm) {
                        backOrderForm.style.display = displayValue === 'backorder' ? 'block' : 'none';
                    }
                };

                // Attach event listener to monitor dropdown value changes
                if (displayDropdown) {
                    displayDropdown.addEventListener('change', toggleVisibility);

                    // Call toggleVisibility on initial load to ensure correct display state
                    toggleVisibility();
                }

                // Move the submit button to the end of the form
                const submitButton = document.querySelector('p.submit');
                if (submitButton) {
                    form.appendChild(submitButton);
                }
            });
        </script>
        <?php
    }
}

new Out_Of_Stock_Display_Manager_For_Woocommerce_Settings();
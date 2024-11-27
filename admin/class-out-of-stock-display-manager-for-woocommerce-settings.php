<?php

/**
 * Out Of Stock Display Manager for WooCommerce Settings.
 *
 * Provides an interface for customizing the display of out-of-stock products,
 * including global settings and exclusion rules.
 *
 * @package WooCommerce
 */
class OutOfStockDisplayManager {

    /**
     * Option names for settings.
     */
    private const OPTION_NAME = 'woocommerce_out_of_stock_settings';
    private const EXCLUSION_OPTION_NAME = 'woocommerce_out_of_stock_exclusions';

    /**
     * Constructor. Hooks into WordPress admin actions.
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
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

        // Exclusion settings
        register_setting(self::EXCLUSION_OPTION_NAME, self::EXCLUSION_OPTION_NAME);

        add_settings_section(
            'exclusion_rules',
            __('Exclusion Rules', 'woocommerce'),
            null,
            self::EXCLUSION_OPTION_NAME
        );

        $fields = [
            'excluded_products' => __('Exclude Specific Products', 'woocommerce'),
            'excluded_pages' => __('Exclude Products on Specific Pages', 'woocommerce'),
            'hidden_categories' => __('Hide Products from Specific Categories', 'woocommerce')
        ];

        foreach ($fields as $field_key => $field_label) {
            add_settings_field(
                $field_key,
                $field_label,
                [$this, "render_{$field_key}_field"],
                self::EXCLUSION_OPTION_NAME,
                'exclusion_rules'
            );
        }
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
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_NAME);
                do_settings_sections(self::OPTION_NAME);
                submit_button(__('Save General Settings', 'woocommerce'));
                ?>
            </form>

            <!-- Exclusion Rules Form -->
            <form method="post" action="options.php" class="exclusion-rules-form" style="display: none;">
                <?php
                settings_fields(self::EXCLUSION_OPTION_NAME);
                do_settings_sections(self::EXCLUSION_OPTION_NAME);
                submit_button(__('Save Exclusion Rules', 'woocommerce'));
                ?>
            </form>
        </div>
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
        ?>
        <select name="<?php echo esc_attr(self::OPTION_NAME); ?>[out_of_stock_display]" id="out-of-stock-display">
            <?php foreach (['hide', 'label', 'backorder'] as $option): ?>
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
        $options = get_option(self::EXCLUSION_OPTION_NAME);
        $value = $options['excluded_products'] ?? '';
        ?>
        <input type="text" name="<?php echo esc_attr(self::EXCLUSION_OPTION_NAME); ?>[excluded_products]" 
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
    public function render_excluded_pages_field() {
        $options = get_option(self::EXCLUSION_OPTION_NAME);
        $pages = ['shop', 'search', 'category'];
        $selected = $options['excluded_pages'] ?? [];
        foreach ($pages as $page) {
            ?>
            <label>
                <input type="checkbox" name="<?php echo esc_attr(self::EXCLUSION_OPTION_NAME); ?>[excluded_pages][<?php echo esc_attr($page); ?>]" 
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
    public function render_hidden_categories_field() {
        $options = get_option(self::EXCLUSION_OPTION_NAME);
        $value = $options['hidden_categories'] ?? '';
        ?>
        <input type="text" name="<?php echo esc_attr(self::EXCLUSION_OPTION_NAME); ?>[hidden_categories]" 
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
                const displayDropdown = document.getElementById('out-of-stock-display');
                const exclusionForm = document.querySelector('.exclusion-rules-form');

                const toggleVisibility = () => {
                    exclusionForm.style.display = displayDropdown.value === 'hide' ? 'block' : 'none';
                };

                displayDropdown.addEventListener('change', toggleVisibility);
                toggleVisibility();
            });
        </script>
        <?php
    }
}

new OutOfStockDisplayManager();

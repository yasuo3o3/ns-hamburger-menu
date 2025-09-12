<?php
/**
 * Admin functionality
 *
 * @package NS_Hamburger_Menu
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin functionality class
 */
class NSHM_Admin {
    
    /**
     * Options key
     */
    const OPTION_KEY = 'ns_hamburger_options';
    
    /**
     * Initialize admin functionality
     */
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting(self::OPTION_KEY, self::OPTION_KEY, array(
            'sanitize_callback' => array($this, 'sanitize_options')
        ));
    }
    
    /**
     * Sanitize options
     *
     * @param array $input Raw input data
     * @return array Sanitized options
     */
    public function sanitize_options($input) {
        $defaults = $this->get_defaults();
        $output = array();
        
        // Auto inject (boolean)
        $output['auto_inject'] = !empty($input['auto_inject']) ? 1 : 0;
        
        // Columns (1-6)
        $output['columns'] = max(1, min(6, intval($input['columns'] ?? $defaults['columns'])));
        
        // Font sizes (minimum validation)
        $output['top_font_px'] = max(10, intval($input['top_font_px'] ?? $defaults['top_font_px']));
        $output['sub_font_px'] = max(8, intval($input['sub_font_px'] ?? $defaults['sub_font_px']));
        
        // Color scheme validation
        $allowed_schemes = array('custom', 'blue', 'green', 'red', 'orange', 'black');
        $scheme = $input['scheme'] ?? $defaults['scheme'];
        $output['scheme'] = in_array($scheme, $allowed_schemes, true) ? $scheme : 'custom';
        
        // Color validation
        $output['color_start'] = sanitize_hex_color($input['color_start'] ?? $defaults['color_start']) ?: $defaults['color_start'];
        $output['color_end'] = sanitize_hex_color($input['color_end'] ?? $defaults['color_end']) ?: $defaults['color_end'];
        
        // Hue animation settings
        $output['hue_anim'] = !empty($input['hue_anim']) ? 1 : 0;
        $output['hue_speed_sec'] = max(3, intval($input['hue_speed_sec'] ?? $defaults['hue_speed_sec']));
        $output['hue_range_deg'] = max(0, min(360, intval($input['hue_range_deg'] ?? $defaults['hue_range_deg'])));
        
        // Mid color settings
        $output['mid_enabled'] = !empty($input['mid_enabled']) ? 1 : 0;
        $output['color_mid'] = sanitize_hex_color($input['color_mid'] ?? $defaults['color_mid']) ?: $defaults['color_mid'];
        
        // Gradient settings
        $allowed_grad_types = array('linear', 'radial');
        $grad_type = $input['grad_type'] ?? $defaults['grad_type'];
        $output['grad_type'] = in_array($grad_type, $allowed_grad_types, true) ? $grad_type : 'linear';
        
        $allowed_grad_pos = array('right top', 'left top', 'left bottom', 'right bottom', 'top', 'bottom', 'left', 'right');
        $grad_pos = $input['grad_pos'] ?? $defaults['grad_pos'];
        $output['grad_pos'] = in_array($grad_pos, $allowed_grad_pos, true) ? $grad_pos : 'right top';
        
        // Z-index
        $output['z_index'] = max(1000, intval($input['z_index'] ?? $defaults['z_index']));
        
        return $output;
    }
    
    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_options_page(
            __('NS Hamburger Menu', 'ns-hamburger-menu'),
            __('NS Hamburger Menu', 'ns-hamburger-menu'),
            'manage_options',
            'ns-hamburger-menu',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_ns-hamburger-menu') {
            return;
        }
        
        // WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Plugin admin script
        wp_enqueue_script(
            'ns-hmb-admin',
            NSHM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            NSHM_VERSION,
            true
        );
    }
    
    /**
     * Render settings page HTML
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $options = $this->get_options();
        $option_name = self::OPTION_KEY;
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields(self::OPTION_KEY); ?>
                
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Auto Insert', 'ns-hamburger-menu'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($option_name . '[auto_inject]'); ?>" value="1" <?php checked($options['auto_inject'], 1); ?>>
                                <?php esc_html_e('Automatically insert on all pages', 'ns-hamburger-menu'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Color Preset', 'ns-hamburger-menu'); ?></th>
                        <td>
                            <select id="ns_scheme" name="<?php echo esc_attr($option_name . '[scheme]'); ?>">
                                <?php
                                $schemes = array(
                                    'custom' => __('Custom', 'ns-hamburger-menu'),
                                    'blue'   => __('Blue', 'ns-hamburger-menu'),
                                    'green'  => __('Green', 'ns-hamburger-menu'),
                                    'red'    => __('Red', 'ns-hamburger-menu'),
                                    'orange' => __('Orange', 'ns-hamburger-menu'),
                                    'black'  => __('Black', 'ns-hamburger-menu'),
                                );
                                
                                foreach ($schemes as $value => $label) {
                                    printf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr($value),
                                        selected($options['scheme'], $value, false),
                                        esc_html($label)
                                    );
                                }
                                ?>
                            </select>
                            
                            <p class="description">
                                <?php esc_html_e('Start and end colors can be fine-tuned below.', 'ns-hamburger-menu'); ?>
                            </p>
                            
                            <div style="margin-top:8px;">
                                <label>
                                    <?php esc_html_e('Start Color:', 'ns-hamburger-menu'); ?>
                                    <input type="text" class="ns-color" name="<?php echo esc_attr($option_name . '[color_start]'); ?>" value="<?php echo esc_attr($options['color_start']); ?>">
                                </label>
                                <label style="margin-left:12px;">
                                    <?php esc_html_e('End Color:', 'ns-hamburger-menu'); ?>
                                    <input type="text" class="ns-color" name="<?php echo esc_attr($option_name . '[color_end]'); ?>" value="<?php echo esc_attr($options['color_end']); ?>">
                                </label>
                            </div>
                            
                            <div style="margin-top:8px;">
                                <label>
                                    <?php esc_html_e('Hue Range (degrees):', 'ns-hamburger-menu'); ?>
                                    <input type="number" min="0" max="360" step="1" name="<?php echo esc_attr($option_name . '[hue_range_deg]'); ?>" value="<?php echo esc_attr($options['hue_range_deg']); ?>" style="width:90px;">
                                </label>
                                <span class="description">
                                    <?php esc_html_e('Lower values create subtle color shifts', 'ns-hamburger-menu'); ?>
                                </span>
                            </div>
                            
                            <div style="margin-top:8px;">
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr($option_name . '[mid_enabled]'); ?>" value="1" <?php checked($options['mid_enabled'], 1); ?>>
                                    <?php esc_html_e('Use middle color', 'ns-hamburger-menu'); ?>
                                </label>
                                <label style="margin-left:12px;">
                                    <?php esc_html_e('Middle Color:', 'ns-hamburger-menu'); ?>
                                    <input type="text" class="ns-color" name="<?php echo esc_attr($option_name . '[color_mid]'); ?>" value="<?php echo esc_attr($options['color_mid']); ?>">
                                </label>
                            </div>
                            
                            <div style="margin-top:8px;">
                                <label>
                                    <?php esc_html_e('Gradient Type:', 'ns-hamburger-menu'); ?>
                                    <select id="ns_grad_type" name="<?php echo esc_attr($option_name . '[grad_type]'); ?>">
                                        <option value="linear" <?php selected($options['grad_type'], 'linear'); ?>><?php esc_html_e('Linear', 'ns-hamburger-menu'); ?></option>
                                        <option value="radial" <?php selected($options['grad_type'], 'radial'); ?>><?php esc_html_e('Radial', 'ns-hamburger-menu'); ?></option>
                                    </select>
                                </label>
                                
                                <label style="margin-left:12px;">
                                    <?php esc_html_e('Gradient Position:', 'ns-hamburger-menu'); ?>
                                    <select name="<?php echo esc_attr($option_name . '[grad_pos]'); ?>">
                                        <option value="right top" <?php selected($options['grad_pos'], 'right top'); ?>><?php esc_html_e('Right Top', 'ns-hamburger-menu'); ?></option>
                                        <option value="left top" <?php selected($options['grad_pos'], 'left top'); ?>><?php esc_html_e('Left Top', 'ns-hamburger-menu'); ?></option>
                                        <option value="left bottom" <?php selected($options['grad_pos'], 'left bottom'); ?>><?php esc_html_e('Left Bottom', 'ns-hamburger-menu'); ?></option>
                                        <option value="right bottom" <?php selected($options['grad_pos'], 'right bottom'); ?>><?php esc_html_e('Right Bottom', 'ns-hamburger-menu'); ?></option>
                                        <option value="top" <?php selected($options['grad_pos'], 'top'); ?>><?php esc_html_e('Top', 'ns-hamburger-menu'); ?></option>
                                        <option value="bottom" <?php selected($options['grad_pos'], 'bottom'); ?>><?php esc_html_e('Bottom', 'ns-hamburger-menu'); ?></option>
                                        <option value="left" <?php selected($options['grad_pos'], 'left'); ?>><?php esc_html_e('Left', 'ns-hamburger-menu'); ?></option>
                                        <option value="right" <?php selected($options['grad_pos'], 'right'); ?>><?php esc_html_e('Right', 'ns-hamburger-menu'); ?></option>
                                    </select>
                                </label>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Layout & Typography', 'ns-hamburger-menu'); ?></th>
                        <td>
                            <label>
                                <?php esc_html_e('Columns:', 'ns-hamburger-menu'); ?>
                                <select name="<?php echo esc_attr($option_name . '[columns]'); ?>">
                                    <?php
                                    for ($i = 1; $i <= 6; $i++) {
                                        printf(
                                            '<option value="%1$d" %2$s>%1$d %3$s</option>',
                                            $i,
                                            selected($options['columns'], $i, false),
                                            _n('column', 'columns', $i, 'ns-hamburger-menu')
                                        );
                                    }
                                    ?>
                                </select>
                            </label>
                            
                            <div style="margin-top:8px;">
                                <label>
                                    <?php esc_html_e('Parent font size:', 'ns-hamburger-menu'); ?>
                                    <input type="number" min="10" name="<?php echo esc_attr($option_name . '[top_font_px]'); ?>" value="<?php echo esc_attr($options['top_font_px']); ?>" style="width:90px;"> px
                                </label>
                                <label style="margin-left:12px;">
                                    <?php esc_html_e('Child font size:', 'ns-hamburger-menu'); ?>
                                    <input type="number" min="8" name="<?php echo esc_attr($option_name . '[sub_font_px]'); ?>" value="<?php echo esc_attr($options['sub_font_px']); ?>" style="width:90px;"> px
                                </label>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Hue Animation', 'ns-hamburger-menu'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($option_name . '[hue_anim]'); ?>" value="1" <?php checked($options['hue_anim'], 1); ?>>
                                <?php esc_html_e('Enable hue animation', 'ns-hamburger-menu'); ?>
                            </label>
                            
                            <div style="margin-top:8px;">
                                <label>
                                    <?php esc_html_e('Animation speed:', 'ns-hamburger-menu'); ?>
                                    <input type="number" min="3" name="<?php echo esc_attr($option_name . '[hue_speed_sec]'); ?>" value="<?php echo esc_attr($options['hue_speed_sec']); ?>" style="width:90px;">
                                    <?php esc_html_e('seconds per cycle', 'ns-hamburger-menu'); ?>
                                </label>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Z-Index', 'ns-hamburger-menu'); ?></th>
                        <td>
                            <input type="number" min="1000" name="<?php echo esc_attr($option_name . '[z_index]'); ?>" value="<?php echo esc_attr($options['z_index']); ?>">
                            <p class="description">
                                <?php esc_html_e('Adjust if the menu appears behind other elements', 'ns-hamburger-menu'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Get default options
     *
     * @return array
     */
    private function get_defaults() {
        return array(
            'auto_inject'   => 1,
            'columns'       => 2,
            'top_font_px'   => 24,
            'sub_font_px'   => 16,
            'scheme'        => 'custom',
            'color_start'   => '#0ea5e9',
            'color_end'     => '#a78bfa',
            'mid_enabled'   => 0,
            'color_mid'     => '#ffffff',
            'grad_type'     => 'linear',
            'grad_pos'      => 'right top',
            'hue_anim'      => 1,
            'hue_speed_sec' => 12,
            'hue_range_deg' => 24,
            'z_index'       => 9999,
        );
    }
    
    /**
     * Get plugin options with defaults
     *
     * @return array
     */
    private function get_options() {
        $options = get_option(self::OPTION_KEY, array());
        return wp_parse_args($options, $this->get_defaults());
    }
}
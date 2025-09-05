<?php
/**
 * Frontend functionality
 *
 * @package NS_Hamburger_Menu
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend functionality class
 */
class NSHM_Frontend {
    
    /**
     * Initialize frontend functionality
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        $options = $this->get_options();
        $preset = $this->get_scheme_colors($options['scheme']);
        $c_start = $preset ? $preset[0] : $options['color_start'];
        $c_end = $preset ? $preset[1] : $options['color_end'];
        
        // Enqueue stylesheet
        wp_enqueue_style(
            'ns-hmb-style',
            NSHM_PLUGIN_URL . 'assets/css/ns-hamburger.css',
            array(),
            NSHM_VERSION
        );
        
        // Add inline CSS variables
        $inline_css = sprintf(
            ':root{--ns-start:%1$s;--ns-end:%2$s;--ns-columns:%3$d;--ns-top-fz:%4$spx;--ns-sub-fz:%5$spx;--ns-hue-speed:%6$ss;--ns-hue-range:%7$sdeg;--ns-z:%8$d;}',
            esc_html($c_start),
            esc_html($c_end),
            intval($options['columns']),
            intval($options['top_font_px']),
            intval($options['sub_font_px']),
            intval($options['hue_speed_sec']),
            intval($options['hue_range_deg']),
            intval($options['z_index'])
        );
        wp_add_inline_style('ns-hmb-style', $inline_css);
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'ns-hmb-script',
            NSHM_PLUGIN_URL . 'assets/js/ns-hamburger.js',
            array(),
            NSHM_VERSION,
            false
        );
        
        // Set script loading strategy (WordPress 6.3+)
        if (function_exists('wp_script_add_data')) {
            wp_script_add_data('ns-hmb-script', 'strategy', 'defer');
        }
        
        // Localize script
        wp_localize_script('ns-hmb-script', 'NS_HMB', array(
            'hueAnimDefault' => (int) $options['hue_anim'],
            'i18n' => array(
                'openMenu'  => __('Open menu', 'ns-hamburger-menu'),
                'closeMenu' => __('Close menu', 'ns-hamburger-menu'),
            ),
        ));
    }
    
    /**
     * Get plugin options with defaults
     *
     * @return array
     */
    private function get_options() {
        $defaults = array(
            'auto_inject'   => 1,
            'columns'       => 2,
            'top_font_px'   => 24,
            'sub_font_px'   => 16,
            'scheme'        => 'custom',
            'color_start'   => '#0ea5e9',
            'color_end'     => '#a78bfa',
            'hue_anim'      => 1,
            'hue_speed_sec' => 12,
            'hue_range_deg' => 24,
            'z_index'       => 9999,
        );
        
        $options = get_option('ns_hamburger_options', array());
        return wp_parse_args($options, $defaults);
    }
    
    /**
     * Get scheme colors
     *
     * @param string $scheme Color scheme name
     * @return array|null
     */
    private function get_scheme_colors($scheme) {
        $schemes = array(
            'blue'   => array('#0ea5e9', '#60a5fa'),
            'green'  => array('#22c55e', '#86efac'),
            'red'    => array('#ef4444', '#f87171'),
            'orange' => array('#f59e0b', '#fdba74'),
            'black'  => array('#0b0b0b', '#575757'),
        );
        
        return isset($schemes[$scheme]) ? $schemes[$scheme] : null;
    }
}
<?php
/**
 * Default options management
 *
 * @package NS_Hamburger_Menu
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default options class
 */
class NSHM_Defaults {
    
    /**
     * Get default plugin options
     *
     * @return array Default options
     */
    public static function get() {
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
     * Get merged options with defaults
     *
     * @return array Merged options
     */
    public static function get_options() {
        $options = get_option('ns_hamburger_options', array());
        return wp_parse_args($options, self::get());
    }
}
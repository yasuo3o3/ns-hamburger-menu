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
        // Only enqueue if menu is actually needed
        if (!$this->should_enqueue_assets()) {
            return;
        }
        
        $options = NSHM_Defaults::get_options();
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
            ':root{--ns-start:%1$s;--ns-end:%2$s;--ns-columns:%3$d;--ns-top-fz:%4$spx;--ns-sub-fz:%5$spx;--ns-hue-speed:%6$ss;--ns-hue-range:%7$sdeg;--ns-open-speed:%8$sms;--ns-z:%9$d;}',
            esc_html($c_start),
            esc_html($c_end),
            intval($options['columns']),
            intval($options['top_font_px']),
            intval($options['sub_font_px']),
            intval($options['hue_speed_sec']),
            intval($options['hue_range_deg']),
            max(0, min(3000, absint($options['open_speed_ms']))),
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
        
        // Add gradient background override
        $c_mid = null;
        if ($options['mid_enabled']) {
            $c_mid = $preset ? null : $options['color_mid'];
        }
        
        $colors = $c_mid ? "$c_start, $c_mid, $c_end" : "$c_start, $c_end";
        
        // Convert grad_pos for linear gradients
        $linear_directions = array(
            'right top'    => 'to bottom left',
            'left top'     => 'to bottom right', 
            'left bottom'  => 'to top right',
            'right bottom' => 'to top left',
            'top'          => 'to bottom',
            'bottom'       => 'to top',
            'left'         => 'to right',
            'right'        => 'to left'
        );
        
        if ($options['grad_type'] === 'radial') {
            $bg = "radial-gradient(circle at {$options['grad_pos']}, $colors)";
        } else {
            $direction = isset($linear_directions[$options['grad_pos']]) ? $linear_directions[$options['grad_pos']] : 'to bottom left';
            $bg = "linear-gradient($direction, $colors)";
        }
        
        $gradient_css = ".ns-overlay::before{background: {$bg}!important;}";
        wp_add_inline_style('ns-hmb-style', $gradient_css);
        
        // Localize script
        wp_localize_script('ns-hmb-script', 'NS_HMB', array(
            'hueAnimDefault' => (int) $options['hue_anim'],
            'i18n' => array(
                /* translators: Accessible label for hamburger menu button */
                'openMenu'  => __('Open menu', 'ns-hamburger-menu'),
                /* translators: Accessible label for close menu button */
                'closeMenu' => __('Close menu', 'ns-hamburger-menu'),
            ),
        ));
    }
    
    /**
     * Check if assets should be enqueued
     *
     * @return bool
     */
    private function should_enqueue_assets() {
        $options = NSHM_Defaults::get_options();
        
        // Always enqueue if auto-inject is enabled
        if (!empty($options['auto_inject'])) {
            return true;
        }
        
        // Check if shortcode is present in current post
        global $post;
        if ($post && has_shortcode($post->post_content, 'ns_hamburger_menu')) {
            return true;
        }
        
        // Check if block is present
        if ($post && has_block('ns/hamburger-menu', $post)) {
            return true;
        }
        
        // Allow themes/plugins to force enqueue
        return apply_filters('nshm_should_enqueue_assets', false);
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
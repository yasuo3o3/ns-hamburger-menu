<?php
/**
 * Core plugin functionality
 *
 * @package NS_Hamburger_Menu
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core plugin class
 */
class NSHM_Core {
    
    /**
     * Initialize core functionality
     */
    public function __construct() {
        add_action('init', array($this, 'register_menu_location'));
        add_action('init', array($this, 'register_block'));
        add_shortcode('ns_hamburger_menu', array($this, 'shortcode'));
        
        // Auto-inject functionality
        add_action('wp_footer', array($this, 'auto_inject_footer'), 99);
        add_action('wp_body_open', array($this, 'auto_inject_body'), 1);
    }
    
    /**
     * Register menu location
     */
    public function register_menu_location() {
        register_nav_menus(array(
            'ns_hamburger_menu' => esc_html__('Hamburger Overlay Menu', 'ns-hamburger-menu'),
        ));
    }
    
    /**
     * Register block
     */
    public function register_block() {
        register_block_type(NSHM_PLUGIN_PATH . 'blocks', array(
            'render_callback' => array($this, 'render_block')
        ));
    }
    
    /**
     * Auto-inject in footer
     */
    public function auto_inject_footer() {
        $options = $this->get_options();
        if (!empty($options['auto_inject'])) {
            echo $this->render_markup(false, array(), null, '');
        }
    }
    
    /**
     * Auto-inject after body open
     */
    public function auto_inject_body() {
        $options = $this->get_options();
        if (!empty($options['auto_inject'])) {
            echo $this->render_markup(false, array(), null, '');
        }
    }
    
    /**
     * Shortcode handler
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function shortcode($atts = array()) {
        return $this->render_markup(true, array(), null, '');
    }
    
    /**
     * Block render callback
     *
     * @param array  $attributes Block attributes
     * @param string $content    Block content
     * @param object $block      Block object
     * @return string
     */
    public function render_block($attributes = array(), $content = '', $block = null) {
        $attrs = array();
        $attr_map = array('columns', 'topFontPx', 'subFontPx', 'colorStart', 'colorEnd', 'hueAnim', 'hueSpeedSec', 'zIndex');
        
        foreach ($attr_map as $key) {
            if (isset($attributes[$key])) {
                $attrs[$key] = $attributes[$key];
            }
        }
        
        return $this->render_markup(true, $attrs, $block, $content);
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
    
    /**
     * Split slots from block object
     *
     * @param object $block Block object
     * @return array
     */
    private function split_slots_from_block($block) {
        $before = '';
        $after = '';
        
        if (!($block instanceof WP_Block)) {
            return array($before, $after);
        }
        
        foreach ($block->inner_blocks as $child) {
            if ($child->name !== 'ns/hamburger-slot') {
                continue;
            }
            
            $position = isset($child->attributes['position']) ? $child->attributes['position'] : 'before';
            $html = '';
            
            foreach ($child->inner_blocks as $inner_block) {
                if (!empty($inner_block->parsed_block)) {
                    $html .= render_block($inner_block->parsed_block);
                } elseif (method_exists($inner_block, 'render')) {
                    $html .= $inner_block->render();
                }
            }
            
            if ($position === 'after') {
                $after .= $html;
            } else {
                $before .= $html;
            }
        }
        
        return array($before, $after);
    }
    
    /**
     * Split slots from content string
     *
     * @param string $content Block content
     * @return array
     */
    private function split_slots_from_content($content) {
        $before = '';
        $after = '';
        
        if (!$content) {
            return array($before, $after);
        }
        
        $blocks = parse_blocks($content);
        
        foreach ($blocks as $block) {
            if (empty($block['blockName']) || $block['blockName'] !== 'ns/hamburger-slot') {
                continue;
            }
            
            $position = isset($block['attrs']['position']) ? $block['attrs']['position'] : 'before';
            $html = '';
            
            if (!empty($block['innerBlocks'])) {
                foreach ($block['innerBlocks'] as $inner_block) {
                    $html .= render_block($inner_block);
                }
            } elseif (!empty($block['innerHTML'])) {
                $html .= $block['innerHTML'];
            }
            
            if ($position === 'after') {
                $after .= $html;
            } else {
                $before .= $html;
            }
        }
        
        return array($before, $after);
    }
    
    /**
     * Render hamburger menu markup
     *
     * @param bool   $return_string Whether to return or echo
     * @param array  $attrs         Block attributes
     * @param object $block         Block object
     * @param string $content       Block content
     * @return string|void
     */
    private function render_markup($return_string = true, $attrs = array(), $block = null, $content = '') {
        $options = $this->get_options();
        
        // Process attributes with validation
        $columns = isset($attrs['columns']) ? max(1, min(6, intval($attrs['columns']))) : $options['columns'];
        $top_fz = isset($attrs['topFontPx']) ? max(10, intval($attrs['topFontPx'])) : $options['top_font_px'];
        $sub_fz = isset($attrs['subFontPx']) ? max(8, intval($attrs['subFontPx'])) : $options['sub_font_px'];
        
        $preset = $this->get_scheme_colors($options['scheme']);
        $c_start = isset($attrs['colorStart']) ? sanitize_hex_color($attrs['colorStart']) : 
                  ($preset ? $preset[0] : $options['color_start']);
        $c_end = isset($attrs['colorEnd']) ? sanitize_hex_color($attrs['colorEnd']) : 
                 ($preset ? $preset[1] : $options['color_end']);
        
        $hue_on = isset($attrs['hueAnim']) ? (int) !empty($attrs['hueAnim']) : (int) $options['hue_anim'];
        $hue_spd = isset($attrs['hueSpeedSec']) ? max(3, intval($attrs['hueSpeedSec'])) : $options['hue_speed_sec'];
        $z_index = isset($attrs['zIndex']) ? max(1000, intval($attrs['zIndex'])) : $options['z_index'];
        
        // Extract slots
        list($slot_before, $slot_after) = $this->split_slots_from_block($block);
        if ($slot_before === '' && $slot_after === '') {
            list($slot_before, $slot_after) = $this->split_slots_from_content($content);
        }
        
        // Generate unique ID
        $overlay_id = function_exists('wp_unique_id') ? wp_unique_id('ns-overlay-') : 'ns-overlay-' . uniqid();
        
        // CSS variables
        $style_vars = sprintf(
            '--ns-start:%1$s;--ns-end:%2$s;--ns-columns:%3$d;--ns-top-fz:%4$spx;--ns-sub-fz:%5$spx;--ns-hue-speed:%6$ss;--ns-z:%7$d;',
            esc_attr($c_start),
            esc_attr($c_end),
            $columns,
            $top_fz,
            $sub_fz,
            $hue_spd,
            $z_index
        );
        
        // Build markup
        ob_start();
        ?>
        <button class="ns-hb" aria-controls="<?php echo esc_attr($overlay_id); ?>" aria-expanded="false" aria-label="<?php esc_attr_e('Open menu', 'ns-hamburger-menu'); ?>">
            <span class="ns-hb-box"><span class="ns-hb-bar"></span></span>
        </button>
        <div id="<?php echo esc_attr($overlay_id); ?>" class="ns-overlay<?php echo $hue_on ? '' : ' ns-hue-off'; ?>" hidden style="<?php echo esc_attr($style_vars); ?>">
            <div class="ns-overlay__inner">
                <nav class="ns-overlay__nav" aria-label="<?php esc_attr_e('Hamburger menu', 'ns-hamburger-menu'); ?>">
                    <?php
                    echo $slot_before;
                    
                    $menu = wp_nav_menu(array(
                        'theme_location' => 'ns_hamburger_menu',
                        'container'      => false,
                        'menu_class'     => 'ns-menu',
                        'depth'          => 2,
                        'echo'           => false,
                    ));
                    
                    if ($menu) {
                        echo $menu;
                    } elseif (current_user_can('edit_theme_options')) {
                        echo '<p style="color:#fff;opacity:.9">';
                        printf(
                            /* translators: %s: Menu location name */
                            __('Please assign a menu to the "%s" location in Appearance → Menus.', 'ns-hamburger-menu'),
                            __('Hamburger Overlay Menu', 'ns-hamburger-menu')
                        );
                        echo '</p>';
                    }
                    
                    echo $slot_after;
                    ?>
                </nav>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        
        if ($return_string) {
            return $html;
        }
        
        echo $html;
    }
}
<?php
/**
 * Plugin Name:       NS Hamburger Overlay Menu
 * Plugin URI:        https://github.com/netservice/ns-hamburger-menu
 * Description:       Accessible hamburger overlay menu with gradient animations, multi-column layout, and full keyboard navigation support.
 * Version:           0.11.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Netservice
 * Author URI:        https://netservice.jp
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ns-hamburger-menu
 * Domain Path:       /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('NSHM_VERSION', '0.11.0');
define('NSHM_PLUGIN_FILE', __FILE__);
define('NSHM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NSHM_PLUGIN_PATH', plugin_dir_path(__FILE__));


// Require core files
require_once NSHM_PLUGIN_PATH . 'inc/Defaults.php';
require_once NSHM_PLUGIN_PATH . 'inc/Core.php';
require_once NSHM_PLUGIN_PATH . 'inc/Admin.php';
require_once NSHM_PLUGIN_PATH . 'inc/Frontend.php';

// Initialize plugin
function nshm_init() {
    new NSHM_Core();
    if (is_admin()) {
        new NSHM_Admin();
    }
    new NSHM_Frontend();
}
add_action('init', 'nshm_init');

// Legacy compatibility
class NS_Hamburger_Menu {
    const OPT_KEY = 'ns_hamburger_options';
    const VER     = NSHM_VERSION;

    public function __construct() {
        // Deprecated: This class is kept for backward compatibility only
        // The actual functionality has been moved to NSHM_* classes
    }

    public function register_menu_location() {
        register_nav_menus([
            'ns_hamburger_menu' => esc_html__('Hamburger Overlay Menu', 'ns-hamburger-menu'),
        ]);
    }

    private function defaults() {
        return [
            'auto_inject'      => 1,
            'columns'          => 2,
            'top_font_px'      => 24,
            'sub_font_px'      => 16,
            'scheme'           => 'custom',
            'color_start'      => '#0ea5e9',
            'color_end'        => '#a78bfa',
            'hue_anim'         => 1,
            'hue_speed_sec'    => 12,
            'hue_range_deg'    => 24,
            'open_speed_ms'    => 600,
            'open_shape'       => 'circle',
            'z_index'          => 9999,
            'responsive_mode'  => 'off',
            'responsive_width' => 420,
        ];
    }
    private function get_options() {
        $opt = get_option(self::OPT_KEY, []);
        return wp_parse_args($opt, $this->defaults());
    }

    public function register_settings() {
        register_setting(self::OPT_KEY, self::OPT_KEY, function ($input) {
            $d = $this->defaults(); $out = [];
            $out['auto_inject']   = empty($input['auto_inject']) ? 0 : 1;
            $out['columns']       = max(1, min(6, intval($input['columns'] ?? $d['columns'])));
            $out['top_font_px']   = max(10, intval($input['top_font_px'] ?? $d['top_font_px']));
            $out['sub_font_px']   = max(8,  intval($input['sub_font_px'] ?? $d['sub_font_px']));
            $allowed_schemes = ['custom','blue','green','red','orange','black'];
            $scheme = $input['scheme'] ?? $d['scheme'];
            $out['scheme'] = in_array($scheme, $allowed_schemes, true) ? $scheme : 'custom';
            $out['color_start']   = sanitize_hex_color($input['color_start'] ?? $d['color_start']) ?: $d['color_start'];
            $out['color_end']     = sanitize_hex_color($input['color_end']   ?? $d['color_end'])   ?: $d['color_end'];
            $out['hue_anim']      = empty($input['hue_anim']) ? 0 : 1;
            $out['hue_speed_sec'] = max(3, intval($input['hue_speed_sec'] ?? $d['hue_speed_sec']));
            $out['hue_range_deg'] = max(0, min(360, intval($input['hue_range_deg'] ?? $d['hue_range_deg'])));
            $out['open_speed_ms'] = max(100, min(2000, intval($input['open_speed_ms'] ?? $d['open_speed_ms'])));
            $allowed_shapes = ['circle', 'linear'];
            $shape = $input['open_shape'] ?? $d['open_shape'];
            $out['open_shape'] = in_array($shape, $allowed_shapes, true) ? $shape : 'circle';
            $out['z_index']       = max(1000, intval($input['z_index'] ?? $d['z_index']));
            $allowed_modes = ['off', 'center', 'left_limit', 'right_limit'];
            $mode = $input['responsive_mode'] ?? $d['responsive_mode'];
            $out['responsive_mode'] = in_array($mode, $allowed_modes, true) ? $mode : 'off';
            $out['responsive_width'] = max(320, min(1200, intval($input['responsive_width'] ?? $d['responsive_width'])));
            return $out;
        });
    }

    public function add_settings_page() {
        add_options_page('NS Hamburger Menu','NS Hamburger Menu','manage_options','ns-hamburger-menu',[$this,'settings_page_html']);
    }
    public function admin_assets($hook) {
        if ($hook !== 'settings_page_ns-hamburger-menu') return;
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('ns-hmb-admin', plugin_dir_url(__FILE__) . 'assets/admin.js', ['jquery','wp-color-picker'], self::VER, true);
    }

    public function front_assets() {
        $opt = $this->get_options();
        $preset = $this->get_scheme_colors($opt['scheme']);
        $c_start = $preset ? $preset[0] : $opt['color_start'];
        $c_end   = $preset ? $preset[1] : $opt['color_end'];

        wp_enqueue_style('ns-hmb-style', plugin_dir_url(__FILE__) . 'assets/css/ns-hamburger.css', [], self::VER);
        $inline = sprintf(
            ':root{--ns-start:%1$s;--ns-end:%2$s;--ns-columns:%3$d;--ns-top-fz:%4$spx;--ns-sub-fz:%5$spx;--ns-hue-speed:%6$ss;--ns-hue-range:%7$s;--ns-open-speed:%8$sms;--ns-z:%9$d;--ns-resp-mode:%10$s;--ns-resp-width:%11$dpx;}',
            esc_html($c_start), esc_html($c_end),
            intval($opt['columns']), intval($opt['top_font_px']), intval($opt['sub_font_px']),
            intval($opt['hue_speed_sec']), intval($opt['hue_range_deg']).'deg', intval($opt['open_speed_ms']), intval($opt['z_index']),
            esc_html($opt['responsive_mode']), intval($opt['responsive_width'])
        );
        wp_add_inline_style('ns-hmb-style', $inline);

        wp_enqueue_script('ns-hmb-script', plugin_dir_url(__FILE__) . 'assets/js/ns-hamburger.js', [], self::VER, false);
        if (function_exists('wp_script_add_data')) wp_script_add_data('ns-hmb-script','strategy','defer');
        wp_localize_script('ns-hmb-script','NS_HMB',[ 'hueAnimDefault'=>(int)$opt['hue_anim'] ]);
    }

    private function get_scheme_colors($scheme) {
        switch ($scheme) {
            case 'blue':   return ['#0ea5e9','#60a5fa'];
            case 'green':  return ['#22c55e','#86efac'];
            case 'red':    return ['#ef4444','#f87171'];
            case 'orange': return ['#f59e0b','#fdba74'];
			case 'black':  return ['#0b0b0b','#575757'];
            default:       return null;
        }
    }

    public function settings_page_html() {
        if (!current_user_can('manage_options')) return;
        $opt = $this->get_options(); $name = self::OPT_KEY; ?>
        <div class="wrap"><h1>NS Hamburger Menu</h1>
        <form method="post" action="options.php"><?php settings_fields(self::OPT_KEY); ?>
        <table class="form-table" role="presentation">
          <tr><th><?php esc_html_e('Auto Insert', 'ns-hamburger-menu'); ?></th><td>
            <label><input type="checkbox" name="<?php echo esc_attr($name.'[auto_inject]');?>" value="1" <?php checked($opt['auto_inject'],1);?>> <?php esc_html_e('Automatically insert on all pages', 'ns-hamburger-menu'); ?></label>
          </td></tr>
          <tr><th><?php esc_html_e('Color Preset', 'ns-hamburger-menu'); ?></th><td>
            <select id="ns_scheme" name="<?php echo esc_attr($name.'[scheme]');?>"><?php
              $schemes = array(
                  'custom' => __('Custom', 'ns-hamburger-menu'),
                  'blue'   => __('Blue', 'ns-hamburger-menu'),
                  'green'  => __('Green', 'ns-hamburger-menu'),
                  'red'    => __('Red', 'ns-hamburger-menu'),
                  'orange' => __('Orange', 'ns-hamburger-menu'),
                  'black'  => __('Black', 'ns-hamburger-menu')
              );
              foreach($schemes as $k=>$label){
                printf('<option value="%s" %s>%s</option>', esc_attr($k), selected($opt['scheme'],$k,false), esc_html($label));
              } ?></select>
            <p class="description"><?php esc_html_e('Start and end colors can be fine-tuned below.', 'ns-hamburger-menu'); ?></p>
            <div style="margin-top:8px">
              開始色：<input type="text" class="ns-color" name="<?php echo esc_attr($name.'[color_start]');?>" value="<?php echo esc_attr($opt['color_start']);?>">
              終了色：<input type="text" class="ns-color" name="<?php echo esc_attr($name.'[color_end]');?>" value="<?php echo esc_attr($opt['color_end']);?>">
            </div>
            <div style="margin-top:8px">
              変化幅（度）：<input type="number" min="0" max="360" step="1" name="<?php echo esc_attr($name.'[hue_range_deg]');?>" value="<?php echo esc_attr($opt['hue_range_deg']);?>" style="width:90px">
              <span class="description">小さいほど“ほんのり”</span>
            </div>
          </td></tr>
          <tr><th>列数 / フォント</th><td>
            列数：<select name="<?php echo esc_attr($name.'[columns]');?>"><?php foreach (range(1,6) as $c){ printf('<option value="%1$s"%3$s>%2$s 列</option>',esc_attr((int)$c),esc_html((int)$c),selected($opt['columns'],(int)$c,false)); }?></select>
            <div style="margin-top:8px">
              親：<input type="number" min="10" name="<?php echo esc_attr($name.'[top_font_px]');?>" value="<?php echo esc_attr($opt['top_font_px']);?>" style="width:90px"> px　
              子：<input type="number" min="8"  name="<?php echo esc_attr($name.'[sub_font_px]');?>" value="<?php echo esc_attr($opt['sub_font_px']);?>" style="width:90px"> px
            </div>
          </td></tr>
          <tr><th>色相アニメ</th><td>
            <label><input type="checkbox" name="<?php echo esc_attr($name.'[hue_anim]');?>" value="1" <?php checked($opt['hue_anim'],1);?>> ON</label>
            <div style="margin-top:8px">
              速度：<input type="number" min="3" name="<?php echo esc_attr($name.'[hue_speed_sec]');?>" value="<?php echo esc_attr($opt['hue_speed_sec']);?>" style="width:90px"> 秒/周
            </div>
          </td></tr>
          <tr><th><?php esc_html_e('Menu Open Speed', 'ns-hamburger-menu'); ?></th><td>
            <input type="number" min="100" max="2000" step="50" name="<?php echo esc_attr($name.'[open_speed_ms]');?>" value="<?php echo esc_attr($opt['open_speed_ms']);?>" style="width:120px"> ms
            <p class="description"><?php esc_html_e('Menu opening/closing animation duration (100-2000ms, default: 600ms)', 'ns-hamburger-menu'); ?></p>
          </td></tr>
          <tr><th>Z-index</th><td><input type="number" min="1000" name="<?php echo esc_attr($name.'[z_index]');?>" value="<?php echo esc_attr($opt['z_index']);?>"></td></tr>
          <tr><th><?php esc_html_e('Responsive Position', 'ns-hamburger-menu'); ?></th><td>
            <select name="<?php echo esc_attr($name.'[responsive_mode]');?>">
              <option value="off" <?php selected($opt['responsive_mode'], 'off'); ?>><?php esc_html_e('Off (Default: Right Top)', 'ns-hamburger-menu'); ?></option>
              <option value="center" <?php selected($opt['responsive_mode'], 'center'); ?>><?php esc_html_e('Center Constrained', 'ns-hamburger-menu'); ?></option>
              <option value="left_limit" <?php selected($opt['responsive_mode'], 'left_limit'); ?>><?php esc_html_e('Left Edge Limit', 'ns-hamburger-menu'); ?></option>
              <option value="right_limit" <?php selected($opt['responsive_mode'], 'right_limit'); ?>><?php esc_html_e('Right Edge Limit', 'ns-hamburger-menu'); ?></option>
            </select>
            <div style="margin-top:8px">
              <?php esc_html_e('Breakpoint Width:', 'ns-hamburger-menu'); ?> <input type="number" min="320" max="1200" name="<?php echo esc_attr($name.'[responsive_width]');?>" value="<?php echo esc_attr($opt['responsive_width']);?>" style="width:90px"> px
            </div>
            <p class="description"><?php esc_html_e('Controls hamburger position on wider screens. Center Constrained prevents going beyond half the breakpoint width from center.', 'ns-hamburger-menu'); ?></p>
          </td></tr>
        </table><?php submit_button(); ?></form></div><?php
    }

    public function shortcode($atts = []) {
        return $this->render_markup(true, [], null, '');
    }

    public function register_block() {
        register_block_type(__DIR__ . '/blocks', ['render_callback' => [$this, 'render_block']]);
    }

    // ← ここがポイント：$block が無い環境でも $content からスロットを拾う
    public function render_block($attributes = [], $content = '', $block = null) {
        $attrs = [];
        foreach (['columns','topFontPx','subFontPx','colorStart','colorEnd','hueAnim','hueSpeedSec','zIndex'] as $k) {
            if (isset($attributes[$k])) $attrs[$k] = $attributes[$k];
        }
        return $this->render_markup(true, $attrs, $block, $content);
    }

    private function split_slots_from_block($block) {
        $before=''; $after='';
        if (!($block instanceof WP_Block)) return [$before,$after];
        foreach ($block->inner_blocks as $child) {
            if ($child->name !== 'ns/hamburger-slot') continue;
            $pos  = $child->attributes['position'] ?? 'before';
            $html = '';
            foreach ($child->inner_blocks as $ib) {
                if (!empty($ib->parsed_block))      $html .= render_block($ib->parsed_block);
                elseif (method_exists($ib,'render')) $html .= $ib->render();
            }
            if ($pos==='after') $after.=$html; else $before.=$html;
        }
        return [$before,$after];
    }

    private function split_slots_from_content($content) {
        $before=''; $after='';
        if (!$content) return [$before,$after];
        $blocks = parse_blocks($content);
        foreach ($blocks as $b) {
            if (($b['blockName']??'') !== 'ns/hamburger-slot') continue;
            $pos  = $b['attrs']['position'] ?? 'before';
            $html = '';
            if (!empty($b['innerBlocks'])) {
                foreach ($b['innerBlocks'] as $ib) $html .= render_block($ib);
            } elseif (!empty($b['innerHTML'])) {
                $html .= $b['innerHTML']; // 念のため
            }
            if ($pos==='after') $after.=$html; else $before.=$html;
        }
        return [$before,$after];
    }

    private function render_markup($return_string = true, $attrs = [], $block = null, $content = '') {
        $opt = $this->get_options();
        $columns = isset($attrs['columns']) ? max(1, min(6, intval($attrs['columns']))) : $opt['columns'];
        $top_fz  = isset($attrs['topFontPx']) ? max(10, intval($attrs['topFontPx'])) : $opt['top_font_px'];
        $sub_fz  = isset($attrs['subFontPx']) ? max(8,  intval($attrs['subFontPx'])) : $opt['sub_font_px'];
        $preset  = $this->get_scheme_colors($opt['scheme']);
        $c_start = isset($attrs['colorStart']) ? sanitize_hex_color($attrs['colorStart']) : ($preset ? $preset[0] : $opt['color_start']);
        $c_end   = isset($attrs['colorEnd'])   ? sanitize_hex_color($attrs['colorEnd'])   : ($preset ? $preset[1] : $opt['color_end']);
        $hue_on  = isset($attrs['hueAnim'])    ? (int)!empty($attrs['hueAnim']) : (int)$opt['hue_anim'];
        $hue_spd = isset($attrs['hueSpeedSec'])? max(3, intval($attrs['hueSpeedSec'])) : $opt['hue_speed_sec'];
        $z_index = isset($attrs['zIndex'])     ? max(1000, intval($attrs['zIndex']))    : $opt['z_index'];

        // ▼ スロット抽出（$block → $content の順で試行）
        [$slot_before,$slot_after] = $this->split_slots_from_block($block);
        if ($slot_before==='' && $slot_after==='') {
            [$slot_before,$slot_after] = $this->split_slots_from_content($content);
        }

        $open_spd = isset($attrs['openSpeedMs']) ? max(100, min(2000, intval($attrs['openSpeedMs']))) : $opt['open_speed_ms'];
        $open_shape = isset($attrs['openShape']) && in_array($attrs['openShape'], ['circle', 'linear'], true) ? $attrs['openShape'] : ($opt['open_shape'] ?? 'circle');
        $style_vars = sprintf('--ns-start:%1$s;--ns-end:%2$s;--ns-columns:%3$d;--ns-top-fz:%4$spx;--ns-sub-fz:%5$spx;--ns-hue-speed:%6$ss;--ns-open-speed:%7$sms;--ns-z:%8$d;--ns-resp-mode:%9$s;--ns-resp-width:%10$dpx;',
            esc_attr($c_start), esc_attr($c_end), $columns, $top_fz, $sub_fz, $hue_spd, $open_spd, $z_index,
            esc_attr($opt['responsive_mode']), intval($opt['responsive_width'])
        );

        $overlay_id = function_exists('wp_unique_id') ? wp_unique_id('ns-overlay-') : 'ns-overlay-'.uniqid();

        ob_start(); ?>
        <div data-open-shape="<?php echo esc_attr($open_shape); ?>" data-preset="<?php echo esc_attr($opt['design_preset'] ?? 'normal'); ?>">
        <button class="ns-hb" aria-controls="<?php echo esc_attr($overlay_id); ?>" aria-expanded="false" aria-label="<?php echo esc_attr(__('Open menu', 'ns-hamburger-menu')); ?>">
            <span class="ns-hb-box"><span class="ns-hb-bar"></span></span>
        </button>
        <div id="<?php echo esc_attr($overlay_id); ?>" class="ns-overlay<?php echo $hue_on ? '' : ' ns-hue-off'; ?>" hidden style="<?php echo esc_attr($style_vars); ?>">
            <div class="ns-overlay__inner">
                <nav class="ns-overlay__nav" aria-label="<?php esc_attr_e('Hamburger menu','ns-hamburger-menu'); ?>">
                    <?php
                    echo wp_kses_post( $slot_before ); // ULの上
                    $menu = wp_nav_menu([
                        'theme_location'=>'ns_hamburger_menu','container'=>false,'menu_class'=>'ns-menu','depth'=>2,'echo'=>false,
                    ]);
                    if ($menu) { echo wp_kses_post( $menu ); }
                    elseif (current_user_can('edit_theme_options')) {
                        echo '<p style="color:#fff;opacity:.9">※「外観→メニュー」で <strong>Hamburger Overlay Menu</strong> にメニューを割り当ててください。</p>';
                    }
                    echo wp_kses_post( $slot_after );  // ULの下
                    ?>
                </nav>
            </div>
        </div>
        </div>
        <?php
        $html = ob_get_clean();
        if ($return_string) return $html; echo wp_kses_post( $html );
    }
}

// Maintain backward compatibility
if (!class_exists('NSHM_Core')) {
    new NS_Hamburger_Menu();
}

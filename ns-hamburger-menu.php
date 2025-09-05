<?php
/**
 * Plugin Name:       NS Hamburger Overlay Menu
 * Description:       右上ハンバーガー → 全画面オーバーレイ。親子で文字サイズ差・2〜3列・斜め拡張＆色相アニメのシンプルメニュー。ブロック対応。
 * Version:           0.11
 * Author:            Netservice
 * Text Domain:       ns-hamburger-menu
 */

if (!defined('ABSPATH')) exit;

class NS_Hamburger_Menu {
    const OPT_KEY = 'ns_hamburger_options';
    const VER     = '0.11';

    public function __construct() {
        add_action('init', [$this, 'register_menu_location']);
        add_action('init', [$this, 'register_block']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'front_assets']);
        add_shortcode('ns_hamburger_menu', [$this, 'shortcode']);

        // 自動挿入（※スロットは対象外）
        add_action('wp_footer', function () {
            $opt = $this->get_options();
            if (!empty($opt['auto_inject'])) echo $this->render_markup(false, [], null, '');
        }, 99);
        add_action('wp_body_open', function () {
            $opt = $this->get_options();
            if (!empty($opt['auto_inject'])) echo $this->render_markup(false, [], null, '');
        }, 1);
    }

    public function register_menu_location() {
        register_nav_menus([
            'ns_hamburger_menu' => __('Hamburger Overlay Menu', 'ns-hamburger-menu'),
        ]);
    }

    private function defaults() {
        return [
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
            $out['z_index']       = max(1000, intval($input['z_index'] ?? $d['z_index']));
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

        wp_enqueue_style('ns-hmb-style', plugin_dir_url(__FILE__) . 'assets/ns-hamburger.css', [], self::VER);
        $inline = sprintf(
            ':root{--ns-start:%1$s;--ns-end:%2$s;--ns-columns:%3$d;--ns-top-fz:%4$spx;--ns-sub-fz:%5$spx;--ns-hue-speed:%6$ss;--ns-hue-range:%7$s;--ns-z:%8$d;}',
            esc_html($c_start), esc_html($c_end),
            intval($opt['columns']), intval($opt['top_font_px']), intval($opt['sub_font_px']),
            intval($opt['hue_speed_sec']), intval($opt['hue_range_deg']).'deg', intval($opt['z_index'])
        );
        wp_add_inline_style('ns-hmb-style', $inline);

        wp_enqueue_script('ns-hmb-script', plugin_dir_url(__FILE__) . 'assets/ns-hamburger.js', [], self::VER, false);
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
          <tr><th>自動挿入</th><td>
            <label><input type="checkbox" name="<?php echo esc_attr($name.'[auto_inject]');?>" value="1" <?php checked($opt['auto_inject'],1);?>> 全ページに自動挿入</label>
          </td></tr>
          <tr><th>色プリセット</th><td>
            <select id="ns_scheme" name="<?php echo esc_attr($name.'[scheme]');?>"><?php
              foreach(['custom'=>'カスタム','blue'=>'ブルー','green'=>'グリーン','red'=>'レッド','orange'=>'オレンジ','black'=>'ブラック'] as $k=>$label){
                printf('<option value="%s" %s>%s</option>', esc_attr($k), selected($opt['scheme'],$k,false), esc_html($label));
              } ?></select>
            <p class="description">開始/終了色は微調整できます。</p>
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
            列数：<select name="<?php echo esc_attr($name.'[columns]');?>"><?php foreach (range(1,6) as $c){ printf('<option value="%1$d" %2$s>%1$d 列</option>',$c,selected($opt['columns'],$c,false)); }?></select>
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
          <tr><th>Z-index</th><td><input type="number" min="1000" name="<?php echo esc_attr($name.'[z_index]');?>" value="<?php echo esc_attr($opt['z_index']);?>"></td></tr>
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

        $style_vars = sprintf('--ns-start:%1$s;--ns-end:%2$s;--ns-columns:%3$d;--ns-top-fz:%4$spx;--ns-sub-fz:%5$spx;--ns-hue-speed:%6$ss;--ns-z:%7$d;',
            esc_attr($c_start), esc_attr($c_end), $columns, $top_fz, $sub_fz, $hue_spd, $z_index
        );

        $overlay_id = function_exists('wp_unique_id') ? wp_unique_id('ns-overlay-') : 'ns-overlay-'.uniqid();

        ob_start(); ?>
        <button class="ns-hb" aria-controls="<?php echo esc_attr($overlay_id); ?>" aria-expanded="false" aria-label="<?php echo esc_attr('メニューを開く'); ?>">
            <span class="ns-hb-box"><span class="ns-hb-bar"></span></span>
        </button>
        <div id="<?php echo esc_attr($overlay_id); ?>" class="ns-overlay<?php echo $hue_on ? '' : ' ns-hue-off'; ?>" hidden style="<?php echo esc_attr($style_vars); ?>">
            <div class="ns-overlay__inner">
                <nav class="ns-overlay__nav" aria-label="<?php esc_attr_e('Hamburger menu','ns-hamburger-menu'); ?>">
                    <?php
                    echo $slot_before; // ULの上
                    $menu = wp_nav_menu([
                        'theme_location'=>'ns_hamburger_menu','container'=>false,'menu_class'=>'ns-menu','depth'=>2,'echo'=>false,
                    ]);
                    if ($menu) { echo $menu; }
                    elseif (current_user_can('edit_theme_options')) {
                        echo '<p style="color:#fff;opacity:.9">※「外観→メニュー」で <strong>Hamburger Overlay Menu</strong> にメニューを割り当ててください。</p>';
                    }
                    echo $slot_after;  // ULの下
                    ?>
                </nav>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        if ($return_string) return $html; echo $html;
    }
}
new NS_Hamburger_Menu();

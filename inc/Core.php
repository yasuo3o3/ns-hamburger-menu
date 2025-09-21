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

// コンソール出力でファイル読み込み確認
add_action('wp_footer', function() {
    echo '<script>console.log("NS Hamburger Menu Core.php loaded - v0.13.0");</script>';
});
add_action('admin_footer', function() {
    echo '<script>console.log("NS Hamburger Menu Core.php loaded - v0.13.0");</script>';
});


/**
 * Core plugin class
 */
class NSHM_Core {

    /**
     * Track if hamburger menu has been rendered to prevent duplicates
     * @var bool
     */
    private static $menu_rendered = false;

    /**
     * Initialize core functionality
     */
    public function __construct() {
        add_action('init', array($this, 'register_menu_location'));
        add_action('init', array($this, 'register_block'), 20); // Higher priority for block registration
        add_shortcode('ns_hamburger_menu', array($this, 'shortcode'));
        
        // Auto-inject functionality
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
        // Ensure the block directory exists and has block.json
        $block_path = NSHM_PLUGIN_PATH . 'blocks';
        if (!file_exists($block_path . '/block.json')) {
            return;
        }

        $result = register_block_type($block_path, array(
            'render_callback' => array($this, 'render_block')
        ));

        if (!$result) {
        }

        // Register child block for slots
        register_block_type('ns/hamburger-slot', array(
            'attributes' => array(
                'position' => array(
                    'type' => 'string',
                    'default' => 'before'
                )
            ),
            'render_callback' => array($this, 'render_slot_block')
        ));
    }
    
    /**
     * Auto-inject in footer (DISABLED - using wp_body_open instead)
     */
    /*
    public function auto_inject_footer() {
        $options = NSHM_Defaults::get_options();
        if (!empty($options['auto_inject'])) {
            echo wp_kses_post( $this->render_markup(false, array(), null, '') );
        }
    }
    */
    
    /**
     * Auto-inject after body open
     */
    public function auto_inject_body() {
        $options = NSHM_Defaults::get_options();
        if (!empty($options['auto_inject'])) {
            // Check if page has ns/hamburger block
            global $post;
            $current_post = get_post();
            $post_to_check = $post ?: $current_post;

            $has_block = false;
            if ($post_to_check) {
                $has_block = has_block('ns/hamburger', $post_to_check);
            }

            // Also check queried object for archive pages
            if (!$has_block) {
                $queried_object = get_queried_object();
                if ($queried_object && isset($queried_object->post_content)) {
                    $has_block = has_block('ns/hamburger', $queried_object);
                }
            }


            if ($has_block || self::$menu_rendered) {
                // Skip auto-inject if block exists or menu already rendered
                echo '<!-- NS Hamburger Menu: Auto-inject skipped - block found or already rendered -->';
                return;
            }

            // Mark as rendered to prevent future duplicates
            self::$menu_rendered = true;

            // Add debug comment to identify auto-inject source
            echo '<!-- NS Hamburger Menu: Auto-injected via wp_body_open -->';
            echo wp_kses_post( $this->render_markup(false, array(), null, '') );
        }
    }
    
    /**
     * Shortcode handler
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function shortcode($atts = array()) {
        return '<!-- NS Hamburger Menu: Shortcode [ns_hamburger_menu] -->' . $this->render_markup(true, array(), null, '');
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
        
        // Mark as rendered to prevent auto-inject duplicates
        self::$menu_rendered = true;

        return '<!-- NS Hamburger Menu: Block render -->' . $this->render_markup(true, $attrs, $block, $content);
    }

    /**
     * Slot block render callback
     *
     * @param array  $attributes Block attributes
     * @param string $content    Block content
     * @return string
     */
    public function render_slot_block($attributes = array(), $content = '') {
        // Wrap slot content with position attribute for parent block processing
        $position = isset($attributes['position']) ? $attributes['position'] : 'before';
        return sprintf(
            '<!-- slot-start position="%s" -->%s<!-- slot-end -->',
            esc_attr($position),
            $content
        );
    }
    
    
    /**
     * Get navigation menu from various sources (block theme compatible)
     *
     * @return string
     */
    private function get_navigation_menu() {
        $options = NSHM_Defaults::get_options();


        // 手動選択の場合
        if ($options['navigation_source'] === 'manual') {
            $manual_result = $this->get_manual_navigation($options['selected_navigation_id']);


            return $manual_result;
        }

        // 自動選択の場合（従来の動作）
        $is_block_theme = function_exists('wp_is_block_theme') && wp_is_block_theme();

        if (!$is_block_theme) {
            // クラシックテーマ：従来のwp_nav_menuを優先
            $traditional_menu = wp_nav_menu(array(
                'theme_location' => 'ns_hamburger_menu',
                'container'      => false,
                'menu_class'     => 'ns-menu',
                'depth'          => 2,
                'echo'           => false,
            ));

            if ($traditional_menu) {
                return $traditional_menu;
            }
        }

        // ブロックテーマまたはクラシックテーマでメニュー未設定：ナビゲーションブロックから取得
        $navigation_content = $this->get_navigation_from_blocks();
        if ($navigation_content) {
            return $navigation_content;
        }

        // フォールバック：ページ一覧の自動生成
        return $this->generate_fallback_menu();
    }

    /**
     * Get manually selected navigation
     *
     * @param string|int $navigation_id Selected navigation ID
     * @return string
     */
    private function get_manual_navigation($navigation_id) {

        // フォールバック（ページ一覧）の場合
        if ($navigation_id == 0) {
            return $this->generate_fallback_menu();
        }

        // classic_数値の形式（クラシックメニュー）
        if (is_string($navigation_id) && strpos($navigation_id, 'classic_') === 0) {
            $menu_id = (int) str_replace('classic_', '', $navigation_id);
            $menu = wp_nav_menu(array(
                'menu'           => $menu_id,
                'container'      => false,
                'menu_class'     => 'ns-menu',
                'depth'          => 2,
                'echo'           => false,
            ));
            return $menu ?: $this->generate_fallback_menu();
        }

        // block_数値の形式（ナビゲーションブロック）
        if (is_string($navigation_id) && strpos($navigation_id, 'block_') === 0) {
            $block_id = (int) str_replace('block_', '', $navigation_id);
            $content = $this->get_specific_navigation_block($block_id);


            return $content ?: $this->generate_fallback_menu();
        }

        // その他の場合はフォールバックを返す
        if (defined('WP_DEBUG') && WP_DEBUG) {
        }
        return $this->generate_fallback_menu();
    }

    /**
     * Get specific navigation block by ID
     *
     * @param int $block_id Navigation block post ID
     * @return string|null
     */
    private function get_specific_navigation_block($block_id) {

        if (defined('WP_DEBUG') && WP_DEBUG) {
        }

        $nav_post = get_post($block_id);

        if (!$nav_post || $nav_post->post_type !== 'wp_navigation' || $nav_post->post_status !== 'publish') {
            return null;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
        }

        $content = $this->parse_navigation_block($nav_post->post_content);
        if ($content) {
            return '<ul class="ns-menu">' . $content . '</ul>';
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
        }
        return null;
    }

    /**
     * Get navigation content from navigation blocks
     *
     * @return string|null
     */
    private function get_navigation_from_blocks() {

        // Navigation blocks using WP API
        $nav_posts = get_posts(array(
            'post_type' => 'wp_navigation',
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'numberposts' => 5
        ));

        if (empty($nav_posts)) {
            return null;
        }

        // 最初に見つかったナビゲーションブロックを使用
        foreach ($nav_posts as $nav_post) {
            $content = $this->parse_navigation_block($nav_post->post_content);
            if ($content) {
                return '<ul class="ns-menu">' . $content . '</ul>';
            }
        }

        return null;
    }

    /**
     * Parse navigation block content to extract menu items
     *
     * @param string $content Navigation block content
     * @return string
     */
    private function parse_navigation_block($content) {
        if (empty($content)) {
            return '';
        }


        $blocks = parse_blocks($content);
        $menu_items = array();

        foreach ($blocks as $block) {
            // 従来の構造：core/navigation ブロック内にナビゲーションリンクがある場合
            if ($block['blockName'] === 'core/navigation') {
                $extracted_items = $this->extract_nav_items($block);
                $menu_items = array_merge($menu_items, $extracted_items);

            }
            // 新しい構造：直接ナビゲーションリンクがある場合
            elseif ($block['blockName'] === 'core/navigation-link') {
                $attrs = $block['attrs'] ?? array();
                $title = $attrs['label'] ?? '';
                $url = $attrs['url'] ?? '#';

                if (!empty(trim($title))) {
                    $menu_items[] = array(
                        'title' => $title,
                        'url' => $url,
                        'children' => array()
                    );

                }
            }
            // サブメニュー構造の場合
            elseif ($block['blockName'] === 'core/navigation-submenu') {
                $attrs = $block['attrs'] ?? array();
                $title = $attrs['label'] ?? '';
                $url = $attrs['url'] ?? '#';

                if (!empty(trim($title))) {
                    // 子要素を処理
                    $children = array();
                    if (!empty($block['innerBlocks'])) {
                        foreach ($block['innerBlocks'] as $inner_block) {
                            if ($inner_block['blockName'] === 'core/navigation-link') {
                                $inner_attrs = $inner_block['attrs'] ?? array();
                                $inner_title = $inner_attrs['label'] ?? '';
                                $inner_url = $inner_attrs['url'] ?? '#';

                                if (!empty(trim($inner_title))) {
                                    $children[] = array(
                                        'title' => $inner_title,
                                        'url' => $inner_url,
                                        'children' => array()
                                    );
                                }
                            }
                        }
                    }

                    $menu_items[] = array(
                        'title' => $title,
                        'url' => $url,
                        'children' => $children
                    );

                }
            }
        }

        if (empty($menu_items)) {
            return '';
        }

        // HTMLを構築
        $output = '';
        foreach ($menu_items as $item) {
            if (empty($item['title'])) {
                continue;
            }

            $title = esc_html($item['title']);
            $url = esc_url($item['url']) ?: '#';

            $output .= '<li><a href="' . $url . '">' . $title . '</a>';

            if (!empty($item['children'])) {
                $output .= '<ul class="sub-menu">';
                foreach ($item['children'] as $child) {
                    if (!empty($child['title'])) {
                        $child_title = esc_html($child['title']);
                        $child_url = esc_url($child['url']) ?: '#';
                        $output .= '<li><a href="' . $child_url . '">' . $child_title . '</a></li>';
                    }
                }
                $output .= '</ul>';
            }

            $output .= '</li>';
        }


        return $output;
    }

    /**
     * Extract navigation items from navigation block
     *
     * @param array $block Navigation block
     * @return array
     */
    private function extract_nav_items($block) {
        $items = array();

        if (!empty($block['innerBlocks'])) {
            foreach ($block['innerBlocks'] as $inner_block) {
                if ($inner_block['blockName'] === 'core/navigation-link') {
                    $attrs = $inner_block['attrs'] ?? array();
                    $title = $attrs['label'] ?? '';
                    $url = $attrs['url'] ?? '#';

                    // 空のタイトルの場合はスキップ
                    if (empty(trim($title))) {
                        continue;
                    }

                    $items[] = array(
                        'title' => $title,
                        'url'   => $url,
                        'children' => $this->extract_nav_items($inner_block)
                    );
                }
            }
        }

        return $items;
    }

    /**
     * Extract single navigation item from navigation-link block
     *
     * @param array $block Navigation-link block
     * @return array|null
     */
    private function extract_single_nav_item($block) {
        if ($block['blockName'] !== 'core/navigation-link') {
            return null;
        }

        $attrs = $block['attrs'] ?? array();
        $title = $attrs['label'] ?? '';
        $url = $attrs['url'] ?? '#';

        // 空のタイトルの場合はスキップ
        if (empty(trim($title))) {
            return null;
        }

        return array(
            'title' => $title,
            'url' => $url,
            'children' => array()
        );
    }

    /**
     * Extract navigation submenu item
     *
     * @param array $block Navigation-submenu block
     * @return array|null
     */
    private function extract_submenu_item($block) {
        if ($block['blockName'] !== 'core/navigation-submenu') {
            return null;
        }

        $attrs = $block['attrs'] ?? array();
        $title = $attrs['label'] ?? '';
        $url = $attrs['url'] ?? '#';

        // 空のタイトルの場合はスキップ
        if (empty(trim($title))) {
            return null;
        }

        // 子要素を処理
        $children = array();
        if (!empty($block['innerBlocks'])) {
            foreach ($block['innerBlocks'] as $inner_block) {
                if ($inner_block['blockName'] === 'core/navigation-link') {
                    $child_item = $this->extract_single_nav_item($inner_block);
                    if ($child_item) {
                        $children[] = $child_item;
                    }
                }
            }
        }

        return array(
            'title' => $title,
            'url' => $url,
            'children' => $children
        );
    }

    /**
     * Format navigation item as HTML
     *
     * @param array $item Navigation item
     * @return string
     */
    private function format_nav_item($item) {
        // タイトルが空の場合は何も出力しない
        if (empty($item['title'])) {
            return '';
        }

        $title = esc_html($item['title']);
        $url = esc_url($item['url']) ?: '#';

        $output = '<li>';
        $output .= '<a href="' . $url . '">' . $title . '</a>';

        if (!empty($item['children'])) {
            $output .= '<ul class="sub-menu">';
            foreach ($item['children'] as $child) {
                $output .= $this->format_nav_item($child);
            }
            $output .= '</ul>';
        }

        $output .= '</li>';
        return $output;
    }

    /**
     * Generate fallback menu from pages
     *
     * @return string
     */
    private function generate_fallback_menu() {

        $pages = get_pages(array(
            'sort_order' => 'ASC',
            'sort_column' => 'menu_order',
            'hierarchical' => 1,
            'include' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'number' => 10,
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        ));

        if (defined('WP_DEBUG') && WP_DEBUG) {
            if (!empty($pages)) {
                $page_titles = array_map(function($page) { return $page->post_title; }, $pages);
            }
        }

        if (empty($pages)) {
            if (current_user_can('edit_theme_options')) {
                return '<p style="color:#fff;opacity:.9">' .
                       esc_html__('No navigation found. Please set up a menu in Appearance → Menus or create a Navigation block.', 'ns-hamburger-menu') .
                       '</p>';
            }
            return '';
        }

        $output = '<ul class="ns-menu">';
        foreach ($pages as $page) {
            $output .= '<li><a href="' . esc_url(get_permalink($page->ID)) . '">' . esc_html($page->post_title) . '</a></li>';
        }
        $output .= '</ul>';

        return $output;
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

        // Try to extract from HTML comments first (for rendered content)
        if (preg_match_all('/<!-- slot-start position="([^"]+)" -->(.*?)<!-- slot-end -->/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $position = $match[1];
                $slot_content = $match[2];

                if ($position === 'after') {
                    $after .= $slot_content;
                } else {
                    $before .= $slot_content;
                }
            }
            return array($before, $after);
        }

        // Fallback to block parsing
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
    public function render_markup($return_string = true, $attrs = array(), $block = null, $content = '') {
        $options = NSHM_Defaults::get_options();
        
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
        $open_shape = isset($attrs['openShape']) ? $attrs['openShape'] : $options['open_shape'];
        
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
        <div data-open-shape="<?php echo esc_attr($open_shape); ?>" data-preset="<?php echo esc_attr($options['design_preset'] ?? 'normal'); ?>">
        <button class="ns-hb" aria-controls="<?php echo esc_attr($overlay_id); ?>" aria-expanded="false" aria-label="<?php esc_attr_e('Open menu', 'ns-hamburger-menu'); ?>">
            <span class="ns-hb-box"><span class="ns-hb-bar"></span></span>
            <?php
            // メニューラベルの表示
            if ($options['nshm_menu_label_mode'] !== 'none') {
                $label_text = '';
                if ($options['nshm_menu_label_mode'] === 'ja') {
                    $label_text = 'メニュー';
                } elseif ($options['nshm_menu_label_mode'] === 'en') {
                    $label_text = 'Menu';
                }
                if (!empty($label_text)) {
                    echo '<span class="nshm-menu-label">' . esc_html($label_text) . '</span>';
                }
            }
            ?>
        </button>
        <div id="<?php echo esc_attr($overlay_id); ?>" class="ns-overlay<?php echo $hue_on ? '' : ' ns-hue-off'; ?>" hidden style="<?php echo esc_attr($style_vars); ?>">
            <div class="ns-overlay__inner">
                <nav class="ns-overlay__nav" aria-label="<?php esc_attr_e('Hamburger menu', 'ns-hamburger-menu'); ?>">
                    <?php
                    echo wp_kses_post( $slot_before );

                    // Use new navigation method that supports block themes
                    $menu_content = $this->get_navigation_menu();
                    echo wp_kses_post( $menu_content );

                    echo wp_kses_post( $slot_after );
                    ?>
                </nav>
            </div>
        </div>
        </div>
        <?php
        $html = ob_get_clean();
        
        if ($return_string) {
            return $html;
        }
        
        echo wp_kses_post( $html );
    }
}
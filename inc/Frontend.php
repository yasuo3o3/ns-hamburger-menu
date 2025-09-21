<?php
/**
 * Frontend functionality
 *
 * @package NS_Hamburger_Menu
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend functionality class
 */
class NSHM_Frontend {

	/**
	 * Flag to require assets from theme function
	 *
	 * @var bool
	 */
	private $require_assets_flag = false;

	/**
	 * Flag to track if assets were enqueued
	 *
	 * @var bool
	 */
	private $assets_enqueued = false;

	/**
	 * Initialize frontend functionality
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'nshm/require_assets', array( $this, 'set_require_assets_flag' ) );
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_assets() {
		// Only enqueue if menu is actually needed
		if ( ! $this->should_enqueue_assets() ) {
			return;
		}

		// Perform actual enqueue
		$this->do_enqueue_assets();
	}

	/**
	 * Force enqueue assets (for theme integration)
	 */
	public function force_enqueue_assets() {
		if ( ! $this->assets_enqueued ) {
			$this->do_enqueue_assets();
		}
	}

	/**
	 * Perform actual asset enqueue
	 */
	private function do_enqueue_assets() {
		// Prevent duplicate enqueuing
		if ( $this->assets_enqueued ) {
			return;
		}

		$this->assets_enqueued = true;

		$options = NSHM_Defaults::get_options();
		$preset  = $this->get_scheme_colors( $options['scheme'] );
		$c_start = $preset ? $preset[0] : $options['color_start'];
		$c_end   = $preset ? $preset[1] : $options['color_end'];

		// Enqueue stylesheet
		wp_enqueue_style(
			'ns-hmb-style',
			NSHM_PLUGIN_URL . 'assets/css/ns-hamburger.css',
			array(),
			NSHM_VERSION
		);

		// Add inline CSS variables
		$inline_css = sprintf(
			':root{--ns-start:%1$s;--ns-end:%2$s;--ns-columns:%3$d;--ns-top-fz:%4$spx;--ns-sub-fz:%5$spx;--ns-hue-speed:%6$ss;--ns-hue-range:%7$sdeg;--ns-open-speed:%8$sms;--ns-z:%9$d;--ns-hamburger-top:%10$s;--ns-hamburger-middle:%11$s;--ns-hamburger-bottom:%12$s;--ns-hamburger-cross1:%13$s;--ns-hamburger-cross2:%14$s;}',
			esc_html( $c_start ),
			esc_html( $c_end ),
			intval( $options['columns'] ),
			intval( $options['top_font_px'] ),
			intval( $options['sub_font_px'] ),
			intval( $options['hue_speed_sec'] ),
			intval( $options['hue_range_deg'] ),
			max( 0, min( 3000, absint( $options['open_speed_ms'] ) ) ),
			intval( $options['z_index'] ),
			esc_html( $options['hamburger_top_line'] ),
			esc_html( $options['hamburger_middle_line'] ),
			esc_html( $options['hamburger_bottom_line'] ),
			esc_html( $options['hamburger_cross_line1'] ),
			esc_html( $options['hamburger_cross_line2'] )
		);
		wp_add_inline_style( 'ns-hmb-style', $inline_css );

		// Add position-specific CSS
		$position_css = $this->get_position_css( $options );
		if ( $position_css ) {
			wp_add_inline_style( 'ns-hmb-style', $position_css );
		}

		// Enqueue JavaScript
		wp_enqueue_script(
			'ns-hmb-script',
			NSHM_PLUGIN_URL . 'assets/js/ns-hamburger.js',
			array(),
			NSHM_VERSION,
			false
		);

		// Set script loading strategy (WordPress 6.3+)
		if ( function_exists( 'wp_script_add_data' ) ) {
			wp_script_add_data( 'ns-hmb-script', 'strategy', 'defer' );
		}

		// Add gradient background override
		$c_mid = null;
		if ( $options['mid_enabled'] ) {
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
			'right'        => 'to left',
		);

		if ( $options['grad_type'] === 'radial' ) {
			$bg = "radial-gradient(circle at {$options['grad_pos']}, $colors)";
		} else {
			$direction = isset( $linear_directions[ $options['grad_pos'] ] ) ? $linear_directions[ $options['grad_pos'] ] : 'to bottom left';
			$bg        = "linear-gradient($direction, $colors)";
		}

		$gradient_css = ".ns-overlay::before{background: {$bg}!important;}";
		wp_add_inline_style( 'ns-hmb-style', $gradient_css );

		// Enqueue design preset CSS
		$this->enqueue_design_preset( $options );

		// Add custom CSS if provided
		$this->add_custom_css( $options );

		// Localize script
		wp_localize_script(
			'ns-hmb-script',
			'NS_HMB',
			array(
				'hueAnimDefault' => (int) $options['hue_anim'],
				'i18n'           => array(
					/* translators: Accessible label for hamburger menu button */
					'openMenu'  => __( 'Open menu', 'ns-hamburger-menu' ),
					/* translators: Accessible label for close menu button */
					'closeMenu' => __( 'Close menu', 'ns-hamburger-menu' ),
				),
			)
		);
	}

	/**
	 * Check if assets should be enqueued
	 *
	 * @return bool
	 */
	private function should_enqueue_assets() {
		$options = NSHM_Defaults::get_options();

		// Always enqueue if auto-inject is enabled
		if ( ! empty( $options['auto_inject'] ) ) {
			return true;
		}

		// Check if shortcode is present in current post
		global $post;
		if ( $post && has_shortcode( $post->post_content, 'ns_hamburger_menu' ) ) {
			return true;
		}

		// Check if block is present
		if ( $post && has_block( 'ns/hamburger', $post ) ) {
			return true;
		}

		// Check if assets are required by theme function
		if ( $this->require_assets_flag ) {
			return true;
		}

		// Allow themes/plugins to force enqueue
		return apply_filters( 'nshm_should_enqueue_assets', false );
	}


	/**
	 * Get scheme colors
	 *
	 * @param string $scheme Color scheme name
	 * @return array|null
	 */
	private function get_scheme_colors( $scheme ) {
		$schemes = array(
			'blue'   => array( '#0ea5e9', '#60a5fa' ),
			'green'  => array( '#22c55e', '#86efac' ),
			'red'    => array( '#ef4444', '#f87171' ),
			'orange' => array( '#f59e0b', '#fdba74' ),
			'black'  => array( '#0b0b0b', '#575757' ),
		);

		return isset( $schemes[ $scheme ] ) ? $schemes[ $scheme ] : null;
	}

	/**
	 * Enqueue design preset CSS
	 *
	 * @param array $options Plugin options
	 */
	private function enqueue_design_preset( $options ) {
		$preset = $options['design_preset'] ?? 'normal';

		// Skip if normal (no preset CSS)
		if ( $preset === 'normal' ) {
			return;
		}

		// Allow external override via filter
		$preset_urls = apply_filters(
			'nshm_menu_presets',
			array(
				'p1' => NSHM_PLUGIN_URL . 'assets/css/presets/p1.css',
				'p2' => NSHM_PLUGIN_URL . 'assets/css/presets/p2.css',
				'p3' => NSHM_PLUGIN_URL . 'assets/css/presets/p3.css',
			)
		);

		if ( isset( $preset_urls[ $preset ] ) ) {
			wp_enqueue_style(
				'ns-hmb-preset',
				$preset_urls[ $preset ],
				array( 'ns-hmb-style' ), // Depend on base CSS
				NSHM_VERSION
			);
		}
	}

	/**
	 * Add custom CSS inline
	 *
	 * @param array $options Plugin options
	 */
	private function add_custom_css( $options ) {
		$custom_css = trim( $options['design_custom_css'] ?? '' );

		if ( empty( $custom_css ) ) {
			return;
		}

		// Complete XSS protection: Convert to plain text first
		$custom_css = sanitize_textarea_field( $custom_css );

		// Remove all HTML tags completely (including variations like </stYle>, </STYLE>)
		$custom_css = wp_strip_all_tags( $custom_css );

		// Additional security: remove any remaining angle brackets
		$custom_css = str_replace( array( '<', '>' ), '', $custom_css );

		// Filter CSS properties with WordPress safecss_filter_attr
		$custom_css = preg_replace_callback(
			'/([a-zA-Z-]+)\s*:\s*([^;]+);?/',
			function( $matches ) {
				$property = trim( $matches[1] );
				$value    = trim( $matches[2] );
				// Use WordPress built-in CSS sanitization
				$filtered = safecss_filter_attr( $property . ':' . $value );
				return $filtered ? $filtered . ';' : '';
			},
			$custom_css
		);

		// Final cleanup: remove any remaining HTML-like content
		$custom_css = preg_replace( '/<[^>]*>/', '', $custom_css );

		if ( empty( trim( $custom_css ) ) ) {
			return;
		}

		// Safe output via wp_add_inline_style (automatically escapes)
		$target_handle = wp_style_is( 'ns-hmb-preset', 'enqueued' ) ? 'ns-hmb-preset' : 'ns-hmb-style';
		wp_add_inline_style( $target_handle, $custom_css );
	}

	/**
	 * Get position-specific CSS
	 *
	 * @param array $options Plugin options
	 * @return string|null
	 */
	private function get_position_css( $options ) {
		$position_mode = $options['position_mode'] ?? 'default';

		if ( $position_mode === 'default' ) {
			// Apply default position
			$position_default = $options['position_default'] ?? 'top-right';

			if ( $position_default === 'top-left' ) {
				return '.ns-hb { position: fixed; top: 16px; left: 16px; right: auto; }
                        @media (max-width:782px) { .admin-bar .ns-hb { top: calc(16px + 46px); } }
                        @media (min-width:783px) { .admin-bar .ns-hb { top: calc(16px + 32px); } }';
			}
			// top-right is already set in base CSS, no override needed
			return null;

		} elseif ( $position_mode === 'custom' ) {
			// Apply custom position
			$position_x = intval( $options['position_x'] ?? 0 );
			$position_y = intval( $options['position_y'] ?? 0 );

			// Calculate responsive X position with browser width constraints
			if ( $position_x >= 0 ) {
				// Right direction: ensure it doesn't go beyond right edge
				$left_value = sprintf( 'min(calc(50vw + %dpx), calc(100vw - 60px))', $position_x );
			} else {
				// Left direction: ensure it doesn't go beyond left edge
				$left_value = sprintf( 'max(calc(50vw + %dpx), 20px)', $position_x );
			}

			return sprintf(
				'.ns-hb { position: fixed; top: %dpx; left: %s; right: auto; transform: none; }
                @media (max-width:782px) { .admin-bar .ns-hb { top: calc(%dpx + 46px); } }
                @media (min-width:783px) { .admin-bar .ns-hb { top: calc(%dpx + 32px); } }',
				$position_y,
				$left_value,
				$position_y,
				$position_y
			);
		}

		return null;
	}

	/**
	 * Set flag to require assets (called from theme function)
	 */
	public function set_require_assets_flag() {
		$this->require_assets_flag = true;

		// Force enqueue immediately if wp_enqueue_scripts has already passed
		if ( did_action( 'wp_enqueue_scripts' ) ) {
			$this->force_enqueue_assets();
		}
	}
}

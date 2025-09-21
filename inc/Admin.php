<?php
/**
 * Admin functionality
 *
 * @package NS_Hamburger_Menu
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
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
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings() {
		register_setting(
			self::OPTION_KEY,
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( $this, 'sanitize_options' ),
			)
		);
	}

	/**
	 * Sanitize options
	 *
	 * @param array $input Raw input data
	 * @return array Sanitized options
	 */
	public function sanitize_options( $input ) {
		$defaults = NSHM_Defaults::get();
		$output   = array();

		// Auto inject (boolean)
		$output['auto_inject'] = ! empty( $input['auto_inject'] ) ? 1 : 0;

		// Columns (1-6)
		$output['columns'] = max( 1, min( 6, intval( $input['columns'] ?? $defaults['columns'] ) ) );

		// Font sizes (minimum validation)
		$output['top_font_px'] = max( 10, intval( $input['top_font_px'] ?? $defaults['top_font_px'] ) );
		$output['sub_font_px'] = max( 8, intval( $input['sub_font_px'] ?? $defaults['sub_font_px'] ) );

		// Color preset validation (new radio button system)
		$allowed_presets        = array( 'blue', 'green', 'red', 'orange', 'black', 'custom' );
		$preset                 = $input['color_preset'] ?? $defaults['color_preset'];
		$output['color_preset'] = in_array( $preset, $allowed_presets, true ) ? $preset : 'custom';

		// Legacy scheme for backward compatibility
		$output['scheme'] = $output['color_preset'];

		// Color validation (only save if custom preset is selected)
		if ( $output['color_preset'] === 'custom' ) {
			$output['color_start'] = sanitize_hex_color( $input['color_start'] ?? $defaults['color_start'] ) ?: $defaults['color_start'];
			$output['color_end']   = sanitize_hex_color( $input['color_end'] ?? $defaults['color_end'] ) ?: $defaults['color_end'];
		} else {
			// Use default colors for non-custom presets (will be overridden by scheme colors)
			$output['color_start'] = $defaults['color_start'];
			$output['color_end']   = $defaults['color_end'];
		}

		// Hue animation settings
		$output['hue_anim']      = ! empty( $input['hue_anim'] ) ? 1 : 0;
		$output['hue_speed_sec'] = max( 3, intval( $input['hue_speed_sec'] ?? $defaults['hue_speed_sec'] ) );
		$output['hue_range_deg'] = max( 0, min( 360, intval( $input['hue_range_deg'] ?? $defaults['hue_range_deg'] ) ) );

		// Mid color settings (only save if custom preset is selected)
		if ( $output['color_preset'] === 'custom' ) {
			$output['mid_enabled'] = ! empty( $input['mid_enabled'] ) ? 1 : 0;
			$output['color_mid']   = sanitize_hex_color( $input['color_mid'] ?? $defaults['color_mid'] ) ?: $defaults['color_mid'];
		} else {
			$output['mid_enabled'] = $defaults['mid_enabled'];
			$output['color_mid']   = $defaults['color_mid'];
		}

		// Gradient settings (only save if custom preset is selected)
		if ( $output['color_preset'] === 'custom' ) {
			$allowed_grad_types  = array( 'linear', 'radial' );
			$grad_type           = $input['grad_type'] ?? $defaults['grad_type'];
			$output['grad_type'] = in_array( $grad_type, $allowed_grad_types, true ) ? $grad_type : 'linear';

			$allowed_grad_pos   = array( 'right top', 'left top', 'left bottom', 'right bottom', 'top', 'bottom', 'left', 'right' );
			$grad_pos           = $input['grad_pos'] ?? $defaults['grad_pos'];
			$output['grad_pos'] = in_array( $grad_pos, $allowed_grad_pos, true ) ? $grad_pos : 'right top';
		} else {
			$output['grad_type'] = $defaults['grad_type'];
			$output['grad_pos']  = $defaults['grad_pos'];
		}

		// Open speed (100-2000ms)
		$output['open_speed_ms'] = max( 100, min( 2000, intval( $input['open_speed_ms'] ?? $defaults['open_speed_ms'] ) ) );

		// Open shape validation
		$allowed_shapes       = array( 'circle', 'linear' );
		$shape                = $input['open_shape'] ?? $defaults['open_shape'];
		$output['open_shape'] = in_array( $shape, $allowed_shapes, true ) ? $shape : 'circle';

		// Z-index
		$output['z_index'] = max( 1000, intval( $input['z_index'] ?? $defaults['z_index'] ) );

		// Position settings validation
		$allowed_modes           = array( 'default', 'custom' );
		$position_mode           = $input['position_mode'] ?? $defaults['position_mode'];
		$output['position_mode'] = in_array( $position_mode, $allowed_modes, true ) ? $position_mode : 'default';

		$allowed_defaults           = array( 'top-left', 'top-right' );
		$position_default           = $input['position_default'] ?? $defaults['position_default'];
		$output['position_default'] = in_array( $position_default, $allowed_defaults, true ) ? $position_default : 'top-right';

		$output['position_x'] = max( -960, min( 960, intval( $input['position_x'] ?? $defaults['position_x'] ) ) );
		$output['position_y'] = max( 0, min( 1080, intval( $input['position_y'] ?? $defaults['position_y'] ) ) );

		// Hamburger icon colors validation (5 individual colors)
		$output['hamburger_top_line']    = sanitize_hex_color( $input['hamburger_top_line'] ?? $defaults['hamburger_top_line'] ) ?: $defaults['hamburger_top_line'];
		$output['hamburger_middle_line'] = sanitize_hex_color( $input['hamburger_middle_line'] ?? $defaults['hamburger_middle_line'] ) ?: $defaults['hamburger_middle_line'];
		$output['hamburger_bottom_line'] = sanitize_hex_color( $input['hamburger_bottom_line'] ?? $defaults['hamburger_bottom_line'] ) ?: $defaults['hamburger_bottom_line'];
		$output['hamburger_cross_line1'] = sanitize_hex_color( $input['hamburger_cross_line1'] ?? $defaults['hamburger_cross_line1'] ) ?: $defaults['hamburger_cross_line1'];
		$output['hamburger_cross_line2'] = sanitize_hex_color( $input['hamburger_cross_line2'] ?? $defaults['hamburger_cross_line2'] ) ?: $defaults['hamburger_cross_line2'];

		// Design preset (whitelist validation)
		$allowed_presets         = array( 'normal', 'p1', 'p2', 'p3' );
		$output['design_preset'] = in_array( $input['design_preset'] ?? $defaults['design_preset'], $allowed_presets, true )
			? $input['design_preset'] : $defaults['design_preset'];

		// Custom CSS (truncate to 10KB)
		$custom_css                  = $input['design_custom_css'] ?? $defaults['design_custom_css'];
		$output['design_custom_css'] = substr( $custom_css, 0, 10240 ); // 10KB limit

		// ナビゲーション設定
		$allowed_nav_sources         = array( 'auto', 'manual' );
		$nav_source                  = $input['navigation_source'] ?? $defaults['navigation_source'];
		$output['navigation_source'] = in_array( $nav_source, $allowed_nav_sources, true ) ? $nav_source : 'auto';

		// 選択されたナビゲーションID（manual時のみ有効）
		if ( $output['navigation_source'] === 'manual' ) {
			$nav_id = $input['selected_navigation_id'] ?? $defaults['selected_navigation_id'];
			// IDの形式を検証（数値または "classic_数値" または "block_数値"）
			if ( is_numeric( $nav_id ) || preg_match( '/^(classic|block)_\d+$/', $nav_id ) ) {
				$output['selected_navigation_id'] = $nav_id;
			} else {
				$output['selected_navigation_id'] = $defaults['selected_navigation_id'];
			}
		} else {
			$output['selected_navigation_id'] = $defaults['selected_navigation_id'];
		}

		// メニューラベル設定の検証
		$allowed_label_modes            = array( 'none', 'ja', 'en' );
		$label_mode                     = $input['nshm_menu_label_mode'] ?? $defaults['nshm_menu_label_mode'];
		$output['nshm_menu_label_mode'] = in_array( $label_mode, $allowed_label_modes, true ) ? $label_mode : 'none';

		return $output;
	}

	/**
	 * Add settings page to admin menu
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'ハンバーガーメニュー', 'ns-hamburger-menu' ),
			__( 'ハンバーガーメニュー', 'ns-hamburger-menu' ),
			'manage_options',
			'ns-hamburger-menu',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Current admin page hook
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( $hook !== 'settings_page_ns-hamburger-menu' ) {
			return;
		}

		// WordPress color picker
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Plugin admin script
		wp_enqueue_script(
			'ns-hmb-admin',
			NSHM_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			NSHM_VERSION,
			true
		);

		// Add inline CSS for horizontal radio buttons and inline controls
		$admin_css = '
            .nshm-custom-settings {
                border: 1px solid #c3c4c7;
                padding: 12px;
                border-radius: 4px;
                background: #f9f9f9;
            }
            .nshm-radios-inline {
                display: flex;
                gap: 16px;
                align-items: center;
                flex-wrap: wrap;
            }
            .nshm-inline {
                display: flex;
                gap: 12px;
                align-items: center;
                flex-wrap: wrap;
            }
            .nshm-preset-custom {
                margin-top: 8px;
            }
            .nshm-color-panel {
                max-width: 740px;
                margin-inline: auto;
                margin-left:0;
            }
            .nshm-color-panel .nshm-color-row {
                display: block;
            }
            .nshm-field {
                margin-block: 8px;
            }
            .nshm-color-group {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .nshm-grad-row {
                display: flex;
                gap: 16px;
                flex-wrap: wrap;
            }
            @media (max-width: 782px) {
                .nshm-color-panel {
                    max-width: none;
                }
            }
            .nshm-font-sizes {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin-top: 8px;
            }
            .nshm-font-item {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .nshm-font-item label {
                min-width: 10em;
            }
            .nshm-font-item .unit {
                opacity: .7;
            }
            fieldset input[type="radio"] {
                margin-right: 6px;
            }
            fieldset label {
                margin-right: 15px;
                margin-bottom: 5px;
                display: inline-block;
            }
            .nshm-custom-css-label {
                display: block;
                font-weight: 600;
                margin-bottom: 6px;
            }
            .nshm-custom-css {
                width: 100%;
                max-width: 600px;
                font-family: monospace;
                font-size: 12px;
                margin-bottom: 6px;
            }
            .nshm-custom-css-note {
                margin-top: 0;
                margin-bottom: 0;
            }
        ';
		wp_add_inline_style( 'wp-color-picker', $admin_css );

		// Add inline JS for hue animation toggle and middle color control
		$admin_js = '
            jQuery(document).ready(function($) {
                function toggleHueSpeed() {
                    const checkbox = $("#nshm-hue-toggle");
                    const speedField = $("#nshm-hue-speed");
                    speedField.prop("disabled", !checkbox.is(":checked"));
                }
                
                function toggleMiddleColor() {
                    const checkbox = $("#nshm-mid-enabled");
                    const colorField = $("#nshm-color-mid");
                    colorField.prop("disabled", !checkbox.is(":checked"));
                    
                    // Also toggle the color picker visual state
                    if (checkbox.is(":checked")) {
                        colorField.closest(".wp-picker-container").removeClass("wp-picker-disabled");
                    } else {
                        colorField.closest(".wp-picker-container").addClass("wp-picker-disabled");
                    }
                }
                
                $("#nshm-hue-toggle").on("change", toggleHueSpeed);
                $("#nshm-mid-enabled").on("change", toggleMiddleColor);
                
                toggleHueSpeed(); // Initial state
                toggleMiddleColor(); // Initial state
            });
        ';
		wp_add_inline_script( 'ns-hmb-admin', $admin_js );
	}

	/**
	 * Render settings page HTML
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle form submission with CSRF protection
		if ( isset( $_POST['submit'] ) && check_admin_referer( 'ns_hamburger_settings', 'ns_hamburger_nonce' ) ) {
			// Settings are processed by WordPress settings API
			// Additional security validation can be added here if needed
		}

		$options     = NSHM_Defaults::get_options();
		$option_name = self::OPTION_KEY;
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<form method="post" action="options.php">
				<?php settings_fields( self::OPTION_KEY ); ?>
				<?php wp_nonce_field( 'ns_hamburger_settings', 'ns_hamburger_nonce' ); ?>
				
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Auto Insert', 'ns-hamburger-menu' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $option_name . '[auto_inject]' ); ?>" value="1" <?php checked( $options['auto_inject'], 1 ); ?>>
								<?php esc_html_e( 'Automatically insert on all pages', 'ns-hamburger-menu' ); ?>
							</label>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'Color Preset', 'ns-hamburger-menu' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'Color Preset Selection', 'ns-hamburger-menu' ); ?></legend>
								
								<div class="nshm-radios-inline nshm-preset-fixed">
								<?php
								$fixed_presets = array(
									'blue'   => esc_html__( 'Blue', 'ns-hamburger-menu' ),
									'green'  => esc_html__( 'Green', 'ns-hamburger-menu' ),
									'red'    => esc_html__( 'Red', 'ns-hamburger-menu' ),
									'orange' => esc_html__( 'Orange', 'ns-hamburger-menu' ),
									'black'  => esc_html__( 'Black', 'ns-hamburger-menu' ),
								);

								$current_preset = $options['color_preset'] ?? $options['scheme'] ?? 'custom';

								foreach ( $fixed_presets as $value => $label ) {
									printf(
										'<label style="display:flex; gap:6px; align-items:center;"><input type="radio" name="%s" value="%s" %s> %s</label>',
										esc_attr( $option_name . '[color_preset]' ),
										esc_attr( $value ),
										checked( $current_preset, $value, false ),
										esc_html( $label )
									);
								}
								?>
								</div>
								
								<div class="nshm-preset-custom">
									<label style="display:flex; gap:6px; align-items:center;">
										<input type="radio" name="<?php echo esc_attr( $option_name . '[color_preset]' ); ?>" value="custom" <?php checked( $current_preset, 'custom' ); ?>>
										<?php esc_html_e( 'Custom', 'ns-hamburger-menu' ); ?>
									</label>
								</div>
							</fieldset>
							
							<div class="nshm-color-panel" style="margin-top:12px;">
								<p class="description" id="nshm-custom-desc">
									<?php esc_html_e( 'カスタム色の設定（カスタム選択時に有効）', 'ns-hamburger-menu' ); ?>
								</p>
								
								<div class="nshm-color-row" aria-describedby="nshm-custom-desc">
									<div class="nshm-field">
										<div class="nshm-color-group">
											<label for="nshm-color-start">
												<?php esc_html_e( '開始色:', 'ns-hamburger-menu' ); ?>
											</label>
											<input type="text" class="nshm-color" id="nshm-color-start" name="<?php echo esc_attr( $option_name . '[color_start]' ); ?>" value="<?php echo esc_attr( $options['color_start'] ); ?>" data-default-color="<?php echo esc_attr( $options['color_start'] ); ?>">
										</div>
									</div>
									
									<div class="nshm-field">
										<div class="nshm-color-group">
											<label for="nshm-color-end">
												<?php esc_html_e( '終了色:', 'ns-hamburger-menu' ); ?>
											</label>
											<input type="text" class="nshm-color" id="nshm-color-end" name="<?php echo esc_attr( $option_name . '[color_end]' ); ?>" value="<?php echo esc_attr( $options['color_end'] ); ?>" data-default-color="<?php echo esc_attr( $options['color_end'] ); ?>">
										</div>
									</div>
									
									<div class="nshm-field">
										<div class="nshm-color-group">
											<label for="nshm-mid-enabled">
												<input type="checkbox" name="<?php echo esc_attr( $option_name . '[mid_enabled]' ); ?>" value="1" <?php checked( $options['mid_enabled'], 1 ); ?> id="nshm-mid-enabled">
												<?php esc_html_e( '中間色を使う', 'ns-hamburger-menu' ); ?>
											</label>
										</div>
									</div>
									
									<div class="nshm-field">
										<div class="nshm-color-group">
											<label for="nshm-color-mid">
												<?php esc_html_e( '中間色:', 'ns-hamburger-menu' ); ?>
											</label>
											<input type="text" class="nshm-color" id="nshm-color-mid" name="<?php echo esc_attr( $option_name . '[color_mid]' ); ?>" value="<?php echo esc_attr( $options['color_mid'] ); ?>" data-default-color="<?php echo esc_attr( $options['color_mid'] ); ?>">
										</div>
									</div>
									
									<div class="nshm-field">
										<label>
											<?php esc_html_e( '変化幅 (度数):', 'ns-hamburger-menu' ); ?>
											<input type="number" min="0" max="360" step="1" name="<?php echo esc_attr( $option_name . '[hue_range_deg]' ); ?>" value="<?php echo esc_attr( $options['hue_range_deg'] ); ?>" style="width:90px;">
										</label>
										<span class="description" style="display:block; font-size:12px; margin-top:4px;">
											<?php esc_html_e( '小さい値ほど微細な色変化になります', 'ns-hamburger-menu' ); ?>
										</span>
									</div>
									
									<div class="nshm-field">
										<div class="nshm-grad-row">
											<label>
												<?php esc_html_e( 'グラデーション種別:', 'ns-hamburger-menu' ); ?>
												<select name="<?php echo esc_attr( $option_name . '[grad_type]' ); ?>">
													<option value="linear" <?php selected( $options['grad_type'], 'linear' ); ?>><?php esc_html_e( '線形', 'ns-hamburger-menu' ); ?></option>
													<option value="radial" <?php selected( $options['grad_type'], 'radial' ); ?>><?php esc_html_e( '放射', 'ns-hamburger-menu' ); ?></option>
												</select>
											</label>
											
											<label>
												<?php esc_html_e( 'グラデーション位置:', 'ns-hamburger-menu' ); ?>
												<select name="<?php echo esc_attr( $option_name . '[grad_pos]' ); ?>">
													<option value="right top" <?php selected( $options['grad_pos'], 'right top' ); ?>><?php esc_html_e( '右上', 'ns-hamburger-menu' ); ?></option>
													<option value="left top" <?php selected( $options['grad_pos'], 'left top' ); ?>><?php esc_html_e( '左上', 'ns-hamburger-menu' ); ?></option>
													<option value="left bottom" <?php selected( $options['grad_pos'], 'left bottom' ); ?>><?php esc_html_e( '左下', 'ns-hamburger-menu' ); ?></option>
													<option value="right bottom" <?php selected( $options['grad_pos'], 'right bottom' ); ?>><?php esc_html_e( '右下', 'ns-hamburger-menu' ); ?></option>
													<option value="top" <?php selected( $options['grad_pos'], 'top' ); ?>><?php esc_html_e( '上', 'ns-hamburger-menu' ); ?></option>
													<option value="bottom" <?php selected( $options['grad_pos'], 'bottom' ); ?>><?php esc_html_e( '下', 'ns-hamburger-menu' ); ?></option>
													<option value="left" <?php selected( $options['grad_pos'], 'left' ); ?>><?php esc_html_e( '左', 'ns-hamburger-menu' ); ?></option>
													<option value="right" <?php selected( $options['grad_pos'], 'right' ); ?>><?php esc_html_e( '右', 'ns-hamburger-menu' ); ?></option>
												</select>
											</label>
										</div>
									</div>
								</div>
							</div>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'Menu Open Speed', 'ns-hamburger-menu' ); ?></th>
						<td>
							<input type="number" min="100" max="2000" step="50" name="<?php echo esc_attr( $option_name . '[open_speed_ms]' ); ?>" value="<?php echo esc_attr( $options['open_speed_ms'] ); ?>" style="width:120px;"> ms
							<p class="description">
								<?php esc_html_e( 'Menu opening/closing animation duration (100-2000ms, default: 600ms)', 'ns-hamburger-menu' ); ?>
							</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'Open Shape', 'ns-hamburger-menu' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'Open Shape Selection', 'ns-hamburger-menu' ); ?></legend>
								
								<div class="nshm-radios-inline nshm-open-shape">
								<?php
								$shapes = array(
									'circle' => esc_html__( 'Circle', 'ns-hamburger-menu' ),
									'linear' => esc_html__( 'Linear', 'ns-hamburger-menu' ),
								);

								$current_shape = $options['open_shape'] ?? 'circle';

								foreach ( $shapes as $value => $label ) {
									printf(
										'<label style="display:flex; gap:6px; align-items:center;"><input type="radio" name="%s" value="%s" %s> %s</label>',
										esc_attr( $option_name . '[open_shape]' ),
										esc_attr( $value ),
										checked( $current_shape, $value, false ),
										esc_html( $label )
									);
								}
								?>
								</div>
							</fieldset>
							<p class="description">
								<?php esc_html_e( 'Choose how the menu appears when opening', 'ns-hamburger-menu' ); ?>
							</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'Hue Animation', 'ns-hamburger-menu' ); ?></th>
						<td>
							<div class="nshm-inline nshm-hue">
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $option_name . '[hue_anim]' ); ?>" value="1" <?php checked( $options['hue_anim'], 1 ); ?> id="nshm-hue-toggle">
									<?php esc_html_e( 'Enable hue animation', 'ns-hamburger-menu' ); ?>
								</label>
								
								<label>
									<?php esc_html_e( 'Animation speed:', 'ns-hamburger-menu' ); ?>
									<input type="number" min="3" name="<?php echo esc_attr( $option_name . '[hue_speed_sec]' ); ?>" value="<?php echo esc_attr( $options['hue_speed_sec'] ); ?>" style="width:90px;" id="nshm-hue-speed">
									<?php esc_html_e( 'seconds per cycle', 'ns-hamburger-menu' ); ?>
								</label>
							</div>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'デザインプリセット', 'ns-hamburger-menu' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'デザインプリセット選択', 'ns-hamburger-menu' ); ?></legend>
								
								<div class="nshm-radios-inline nshm-design-presets">
								<?php
								$design_presets = array(
									'normal' => esc_html__( 'ノーマル（装飾なし）', 'ns-hamburger-menu' ),
									'p1'     => esc_html__( 'パターン1', 'ns-hamburger-menu' ),
									'p2'     => esc_html__( 'パターン2', 'ns-hamburger-menu' ),
									'p3'     => esc_html__( 'パターン3', 'ns-hamburger-menu' ),
								);

								$current_preset = $options['design_preset'] ?? 'normal';

								foreach ( $design_presets as $value => $label ) {
									printf(
										'<label style="display:flex; gap:6px; align-items:center;"><input type="radio" name="%s" value="%s" %s> %s</label>',
										esc_attr( $option_name . '[design_preset]' ),
										esc_attr( $value ),
										checked( $current_preset, $value, false ),
										esc_html( $label )
									);
								}
								?>
								</div>
							</fieldset>
							
							<div style="margin-top:12px;">
								<label for="nshm-custom-css" class="nshm-custom-css-label">
									<?php esc_html_e( '追加CSS（任意）', 'ns-hamburger-menu' ); ?>
								</label>
								<textarea id="nshm-custom-css" name="<?php echo esc_attr( $option_name . '[design_custom_css]' ); ?>" 
											class="nshm-custom-css" rows="6" cols="50"
											placeholder="/* カスタムCSSをここに入力 */"><?php echo esc_textarea( $options['design_custom_css'] ?? '' ); ?></textarea>
								<p class="description nshm-custom-css-note">
									<?php esc_html_e( '小規模な上書きに使用。outputはプラグインCSSの後、プリセットCSSの後に差し込み', 'ns-hamburger-menu' ); ?>
								</p>
							</div>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'Layout & Typography', 'ns-hamburger-menu' ); ?></th>
						<td>
							<label>
								<?php esc_html_e( 'Columns:', 'ns-hamburger-menu' ); ?>
								<select name="<?php echo esc_attr( $option_name . '[columns]' ); ?>">
									<?php
									for ( $i = 1; $i <= 6; $i++ ) {
										printf(
											'<option value="%1$s" %2$s>%3$s %4$s</option>',
											esc_attr( (int) $i ),
											selected( $options['columns'], $i, false ),
											esc_html( (int) $i ),
											esc_html( _n( 'column', 'columns', $i, 'ns-hamburger-menu' ) )
										);
									}
									?>
								</select>
							</label>
							
							<div class="nshm-font-sizes">
								<div class="nshm-font-item">
									<label for="top_font_px"><?php esc_html_e( '親フォントサイズ', 'ns-hamburger-menu' ); ?></label>
									<input type="number" min="10" id="top_font_px" name="<?php echo esc_attr( $option_name . '[top_font_px]' ); ?>" value="<?php echo esc_attr( $options['top_font_px'] ); ?>" style="width:90px;">
									<span class="unit">px</span>
								</div>
								<div class="nshm-font-item">
									<?php /* translators: The leading "┗" indicates this is a child value in the UI. */ ?>
									<label for="sub_font_px"><?php esc_html_e( '┗ 子フォントサイズ', 'ns-hamburger-menu' ); ?></label>
									<input type="number" min="8" id="sub_font_px" name="<?php echo esc_attr( $option_name . '[sub_font_px]' ); ?>" value="<?php echo esc_attr( $options['sub_font_px'] ); ?>" style="width:90px;">
									<span class="unit">px</span>
								</div>
							</div>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'Z-Index', 'ns-hamburger-menu' ); ?></th>
						<td>
							<input type="number" min="1000" name="<?php echo esc_attr( $option_name . '[z_index]' ); ?>" value="<?php echo esc_attr( $options['z_index'] ); ?>">
							<p class="description">
								<?php esc_html_e( 'Adjust if the menu appears behind other elements', 'ns-hamburger-menu' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( '位置設定', 'ns-hamburger-menu' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( '位置モード選択', 'ns-hamburger-menu' ); ?></legend>

								<div style="margin-bottom: 16px;">
									<label style="display:flex; gap:6px; align-items:center; margin-bottom:8px;">
										<input type="radio" name="<?php echo esc_attr( $option_name . '[position_mode]' ); ?>" value="default" <?php checked( $options['position_mode'], 'default' ); ?>>
										<?php esc_html_e( 'デフォルト位置', 'ns-hamburger-menu' ); ?>
									</label>

									<div id="default-position-options" style="margin-left: 24px; margin-bottom: 12px;">
										<label style="display:flex; gap:6px; align-items:center; margin-bottom:4px;">
											<input type="radio" name="<?php echo esc_attr( $option_name . '[position_default]' ); ?>" value="top-right" <?php checked( $options['position_default'], 'top-right' ); ?>>
											<?php esc_html_e( '右上', 'ns-hamburger-menu' ); ?>
										</label>
										<label style="display:flex; gap:6px; align-items:center;">
											<input type="radio" name="<?php echo esc_attr( $option_name . '[position_default]' ); ?>" value="top-left" <?php checked( $options['position_default'], 'top-left' ); ?>>
											<?php esc_html_e( '左上', 'ns-hamburger-menu' ); ?>
										</label>
									</div>
								</div>

								<div style="margin-bottom: 16px;">
									<label style="display:flex; gap:6px; align-items:center; margin-bottom:8px;">
										<input type="radio" name="<?php echo esc_attr( $option_name . '[position_mode]' ); ?>" value="custom" <?php checked( $options['position_mode'], 'custom' ); ?>>
										<?php esc_html_e( 'カスタム位置', 'ns-hamburger-menu' ); ?>
									</label>

									<div id="custom-position-options" style="margin-left: 24px;">
										<div style="margin-bottom: 8px;">
											<label for="position_x"><?php esc_html_e( 'X座標（画面中央基準）', 'ns-hamburger-menu' ); ?></label><br>
											<input type="range" id="position_x" name="<?php echo esc_attr( $option_name . '[position_x]' ); ?>" min="-960" max="960" step="10" value="<?php echo esc_attr( $options['position_x'] ); ?>" style="width: 300px;">
											<span id="position_x_value"><?php echo esc_html( $options['position_x'] ); ?>px</span>
										</div>
										<div style="margin-bottom: 8px;">
											<label for="position_y"><?php esc_html_e( 'Y座標（画面上端基準）', 'ns-hamburger-menu' ); ?></label><br>
											<input type="range" id="position_y" name="<?php echo esc_attr( $option_name . '[position_y]' ); ?>" min="0" max="1080" step="10" value="<?php echo esc_attr( $options['position_y'] ); ?>" style="width: 300px;">
											<span id="position_y_value"><?php echo esc_html( $options['position_y'] ); ?>px</span>
										</div>
										<p class="description">
											<?php esc_html_e( 'X座標：負の値は左方向、正の値は右方向　Y座標：0は画面上端、値が大きいほど下方向', 'ns-hamburger-menu' ); ?>
										</p>
									</div>
								</div>
							</fieldset>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'ハンバーガーアイコン色', 'ns-hamburger-menu' ); ?></th>
						<td>
							<fieldset style="margin-bottom: 16px;">
								<legend style="font-weight: 600; margin-bottom: 8px;"><?php esc_html_e( '閉じている時（3本線）', 'ns-hamburger-menu' ); ?></legend>

								<div style="display: flex; gap: 16px; flex-wrap: wrap;">
									<div style="min-width: 140px;">
										<label for="hamburger_top_line"><?php esc_html_e( '上の線', 'ns-hamburger-menu' ); ?></label><br>
										<input type="text" class="nshm-color" id="hamburger_top_line" name="<?php echo esc_attr( $option_name . '[hamburger_top_line]' ); ?>" value="<?php echo esc_attr( $options['hamburger_top_line'] ); ?>" data-default-color="<?php echo esc_attr( $options['hamburger_top_line'] ); ?>">
									</div>

									<div style="min-width: 140px;">
										<label for="hamburger_middle_line"><?php esc_html_e( '中の線', 'ns-hamburger-menu' ); ?></label><br>
										<input type="text" class="nshm-color" id="hamburger_middle_line" name="<?php echo esc_attr( $option_name . '[hamburger_middle_line]' ); ?>" value="<?php echo esc_attr( $options['hamburger_middle_line'] ); ?>" data-default-color="<?php echo esc_attr( $options['hamburger_middle_line'] ); ?>">
									</div>

									<div style="min-width: 140px;">
										<label for="hamburger_bottom_line"><?php esc_html_e( '下の線', 'ns-hamburger-menu' ); ?></label><br>
										<input type="text" class="nshm-color" id="hamburger_bottom_line" name="<?php echo esc_attr( $option_name . '[hamburger_bottom_line]' ); ?>" value="<?php echo esc_attr( $options['hamburger_bottom_line'] ); ?>" data-default-color="<?php echo esc_attr( $options['hamburger_bottom_line'] ); ?>">
									</div>
								</div>
							</fieldset>

							<fieldset style="margin-bottom: 16px;">
								<legend style="font-weight: 600; margin-bottom: 8px;"><?php esc_html_e( '開いている時（×マーク）', 'ns-hamburger-menu' ); ?></legend>

								<div style="display: flex; gap: 16px; flex-wrap: wrap;">
									<div style="min-width: 160px;">
										<label for="hamburger_cross_line1"><?php esc_html_e( '左上→右下の線', 'ns-hamburger-menu' ); ?></label><br>
										<input type="text" class="nshm-color" id="hamburger_cross_line1" name="<?php echo esc_attr( $option_name . '[hamburger_cross_line1]' ); ?>" value="<?php echo esc_attr( $options['hamburger_cross_line1'] ); ?>" data-default-color="<?php echo esc_attr( $options['hamburger_cross_line1'] ); ?>">
									</div>

									<div style="min-width: 160px;">
										<label for="hamburger_cross_line2"><?php esc_html_e( '右上→左下の線', 'ns-hamburger-menu' ); ?></label><br>
										<input type="text" class="nshm-color" id="hamburger_cross_line2" name="<?php echo esc_attr( $option_name . '[hamburger_cross_line2]' ); ?>" value="<?php echo esc_attr( $options['hamburger_cross_line2'] ); ?>" data-default-color="<?php echo esc_attr( $options['hamburger_cross_line2'] ); ?>">
									</div>
								</div>
							</fieldset>

							<p class="description">
								<?php esc_html_e( 'ハンバーガーメニューの各線の色を個別に設定できます。同じ色にしたい場合は同じ色を選択してください。', 'ns-hamburger-menu' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'メニュー表示', 'ns-hamburger-menu' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'メニューラベル表示設定', 'ns-hamburger-menu' ); ?></legend>

								<label for="nshm_menu_label_mode_none">
									<input type="radio" id="nshm_menu_label_mode_none" name="<?php echo esc_attr( $option_name . '[nshm_menu_label_mode]' ); ?>" value="none" <?php checked( $options['nshm_menu_label_mode'], 'none' ); ?>>
									<?php esc_html_e( '非表示', 'ns-hamburger-menu' ); ?>
								</label><br>

								<label for="nshm_menu_label_mode_ja">
									<input type="radio" id="nshm_menu_label_mode_ja" name="<?php echo esc_attr( $option_name . '[nshm_menu_label_mode]' ); ?>" value="ja" <?php checked( $options['nshm_menu_label_mode'], 'ja' ); ?>>
									<?php esc_html_e( 'メニュー（日本語）', 'ns-hamburger-menu' ); ?>
								</label><br>

								<label for="nshm_menu_label_mode_en">
									<input type="radio" id="nshm_menu_label_mode_en" name="<?php echo esc_attr( $option_name . '[nshm_menu_label_mode]' ); ?>" value="en" <?php checked( $options['nshm_menu_label_mode'], 'en' ); ?>>
									<?php esc_html_e( 'Menu（英語）', 'ns-hamburger-menu' ); ?>
								</label>
							</fieldset>

							<p class="description">
								<?php esc_html_e( 'ハンバーガーアイコンが閉じている状態のとき、3本線の下にラベルを表示できます。', 'ns-hamburger-menu' ); ?>
							</p>
						</td>
					</tr>

					<!-- ナビゲーション設定 -->
					<tr>
						<th scope="row"><?php esc_html_e( 'ナビゲーション設定', 'ns-hamburger-menu' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'ナビゲーション設定', 'ns-hamburger-menu' ); ?></legend>

								<label for="navigation_source">
									<input type="radio" id="navigation_source_auto" name="<?php echo esc_attr( $option_name . '[navigation_source]' ); ?>" value="auto" <?php checked( $options['navigation_source'], 'auto' ); ?>>
									<?php esc_html_e( '自動選択', 'ns-hamburger-menu' ); ?>
								</label><br>

								<label for="navigation_source_manual">
									<input type="radio" id="navigation_source_manual" name="<?php echo esc_attr( $option_name . '[navigation_source]' ); ?>" value="manual" <?php checked( $options['navigation_source'], 'manual' ); ?>>
									<?php esc_html_e( '手動選択', 'ns-hamburger-menu' ); ?>
								</label><br>

								<div id="navigation_manual_selection" style="margin-top: 10px; <?php echo $options['navigation_source'] === 'manual' ? '' : 'display: none;'; ?>">
									<label for="selected_navigation_id"><?php esc_html_e( '使用するナビゲーション：', 'ns-hamburger-menu' ); ?></label><br>
									<select id="selected_navigation_id" name="<?php echo esc_attr( $option_name . '[selected_navigation_id]' ); ?>">
										<option value="0"><?php esc_html_e( 'ページ一覧（フォールバック）', 'ns-hamburger-menu' ); ?></option>
										<?php
										// クラシックテーマのメニュー
										$menus = wp_get_nav_menus();
										if ( ! empty( $menus ) ) {
											echo '<optgroup label="' . esc_attr__( 'クラシックメニュー', 'ns-hamburger-menu' ) . '">';
											foreach ( $menus as $menu ) {
												echo '<option value="classic_' . esc_attr( $menu->term_id ) . '" ' . selected( $options['selected_navigation_id'], 'classic_' . $menu->term_id, false ) . '>' . esc_html( $menu->name ) . '</option>';
											}
											echo '</optgroup>';
										}

										// ナビゲーションブロック
										$nav_posts = get_posts(
											array(
												'post_type' => 'wp_navigation',
												'post_status' => 'publish',
												'orderby' => 'title',
												'order'   => 'ASC',
												'numberposts' => -1,
											)
										);
										if ( ! empty( $nav_posts ) ) {
											echo '<optgroup label="' . esc_attr__( 'ナビゲーションブロック', 'ns-hamburger-menu' ) . '">';
											foreach ( $nav_posts as $nav_post ) {
												/* translators: %d: Navigation block ID. */
												$title = $nav_post->post_title ? $nav_post->post_title : sprintf( __( 'ナビゲーション #%d', 'ns-hamburger-menu' ), $nav_post->ID );
												echo '<option value="block_' . esc_attr( $nav_post->ID ) . '" ' . selected( $options['selected_navigation_id'], 'block_' . $nav_post->ID, false ) . '>' . esc_html( $title ) . '</option>';
											}
											echo '</optgroup>';
										}
										?>
									</select>
								</div>
							</fieldset>

							<p class="description">
								<?php esc_html_e( '自動選択：テーマタイプに応じて最適なナビゲーションを自動選択します。手動選択：特定のメニューまたはナビゲーションブロックを指定できます。', 'ns-hamburger-menu' ); ?>
							</p>
						</td>
					</tr>
				</table>
				
				<?php submit_button(); ?>
			</form>
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const positionX = document.getElementById('position_x');
			const positionY = document.getElementById('position_y');
			const positionXValue = document.getElementById('position_x_value');
			const positionYValue = document.getElementById('position_y_value');

			if (positionX && positionXValue) {
				positionX.addEventListener('input', function() {
					positionXValue.textContent = this.value + 'px';
				});
			}

			if (positionY && positionYValue) {
				positionY.addEventListener('input', function() {
					positionYValue.textContent = this.value + 'px';
				});
			}

			// Enable/disable position options based on radio selection
			const positionModeInputs = document.querySelectorAll('input[name$="[position_mode]"]');
			const defaultOptions = document.getElementById('default-position-options');
			const customOptions = document.getElementById('custom-position-options');

			function updatePositionOptions() {
				const selectedMode = document.querySelector('input[name$="[position_mode]"]:checked');
				if (selectedMode) {
					if (selectedMode.value === 'default') {
						if (defaultOptions) defaultOptions.style.opacity = '1';
						if (customOptions) customOptions.style.opacity = '0.5';
					} else {
						if (defaultOptions) defaultOptions.style.opacity = '0.5';
						if (customOptions) customOptions.style.opacity = '1';
					}
				}
			}

			positionModeInputs.forEach(function(input) {
				input.addEventListener('change', updatePositionOptions);
			});

			updatePositionOptions();

			// ナビゲーション選択の表示制御
			const navigationSourceInputs = document.querySelectorAll('input[name$="[navigation_source]"]');
			const navigationManualSelection = document.getElementById('navigation_manual_selection');

			function updateNavigationOptions() {
				const selectedSource = document.querySelector('input[name$="[navigation_source]"]:checked');
				if (selectedSource && navigationManualSelection) {
					if (selectedSource.value === 'manual') {
						navigationManualSelection.style.display = 'block';
					} else {
						navigationManualSelection.style.display = 'none';
					}
				}
			}

			navigationSourceInputs.forEach(function(input) {
				input.addEventListener('change', updateNavigationOptions);
			});

			updateNavigationOptions();
		});
		</script>
		<?php
	}
}
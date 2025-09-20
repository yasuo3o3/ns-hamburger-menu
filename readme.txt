=== NS Hamburger Overlay Menu ===
Contributors: netservice
Tags: hamburger menu, overlay menu, responsive menu, accessibility, mobile menu
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.13.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accessible hamburger overlay menu with gradient animations, multi-column layout, and full keyboard navigation support.

== Description ==

NS Hamburger Overlay Menu is a modern, accessible hamburger menu plugin that provides a beautiful overlay menu experience with gradient animations, customizable layouts, and complete accessibility support.

= Key Features =

* **Fully Accessible** - Complete ARIA support, keyboard navigation, and focus management
* **Responsive Design** - Optimized for all screen sizes with mobile-first approach
* **Customizable** - Multiple color schemes, column layouts, typography options, and positioning
* **Individual Icon Colors** - 5 separate color controls for hamburger icon lines
* **Smart Positioning** - Custom positioning with browser-width responsive adjustments
* **Block Editor Ready** - Native Gutenberg block with live preview
* **Performance Optimized** - Lightweight CSS/JS with no jQuery dependency
* **Translation Ready** - Full i18n support with included POT file

= Usage Options =

* **Auto-Insert Mode** - Automatically display on all pages
* **Manual Placement** - Use the Gutenberg block or shortcode `[ns_hamburger_menu]`
* **Theme Integration** - Add directly to theme templates using `nshm_display_menu()`

= Customization =

* **Color Schemes & Design Presets** - Choose from built-in color presets (Blue, Green, Red, Orange, Black), visual design presets (Normal, Pattern 1-3), or create custom gradients with additional CSS
* **Layout Options** - 1-6 column grid layouts with separate font sizes for parent/child items
* **Animation Effects** - Optional hue rotation animation with speed control
* **Position & Icon Customization** - Default positions (top-left/right) or custom coordinates with responsive adjustments. Individual color controls for all 5 hamburger icon lines
* **Block Slots** - Add custom content above/below menu using slot blocks

= Accessibility Features =

* ARIA labels and states for screen readers
* Full keyboard navigation (Tab, Shift+Tab, Enter, Space, Escape)
* Focus trapping within open menu
* Focus restoration when menu closes  
* Respects `prefers-reduced-motion` setting

= Developer Friendly =

* Multiple hooks and filters for customization
* Template override support
* Clean, documented code following WordPress standards

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/ns-hamburger-menu/` or install through WordPress admin
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Appearance → Menus and assign a menu to "Hamburger Overlay Menu" location
4. Visit Settings → NS Hamburger Menu to customize appearance
5. Enable auto-insert or add the block/shortcode to your pages

== Frequently Asked Questions ==

= Can I use this with any theme? =

Yes, the plugin works with both classic and block themes. It's designed to integrate seamlessly with any properly coded WordPress theme.

= Does it work on mobile devices? =

Absolutely! The menu is optimized for touch devices with proper spacing and mobile-first responsive design.

= Can I translate the interface? =

Yes, the plugin uses standard WordPress internationalization with an included POT file for easy translation.

= Is it accessible? =

Yes, the plugin follows WCAG guidelines with full keyboard navigation and screen reader support.

= Can I customize the appearance? =

Yes, you can choose from built-in color presets, visual design presets with unique animations, adjust column layouts, typography, and even add custom CSS through the settings page for advanced customization.

= Does it require jQuery? =

No, the plugin uses vanilla JavaScript for better performance and compatibility.

== Screenshots ==

1. Hamburger menu button with gradient styling
2. Full-screen overlay menu with multi-column layout
3. Settings page with color and layout options
4. Gutenberg block with live preview
5. Mobile responsive menu display

== Changelog ==

= 0.12.0 =
* Added: Custom position settings with default (top-left/top-right) and custom positioning modes
* Added: Browser width-responsive X-axis positioning to prevent off-screen display
* Added: Individual color customization for all 5 hamburger icon lines
* Added: WordPress color picker integration for consistent UI experience
* Changed: Y-axis positioning from screen center to screen top reference
* Changed: Horizontal layout for hamburger icon color settings to reduce vertical space
* Technical: Enhanced CSS variable system and validation for new features

= 0.11.0 =
* Added: デザインプリセット（Pattern 1-3）でメニューの視覚効果を選択可能
* Added: 開閉形状設定（円形/線形）によるアニメーション制御
* Added: 追加CSS欄でカスタムスタイリングが可能
* Changed: 管理UIのレイアウト最適化とカラー設定UIの統一
* Fixed: 一部の出力エスケープ不足とオープンアニメーションが発火しないケースを修正

= 0.10.0 =
* Initial release
* Accessible hamburger overlay menu implementation
* Gutenberg block support
* Multi-column layout options
* Color scheme presets and visual design presets
* Design preset patterns with unique animations and effects
* Custom CSS integration for advanced styling
* Animation effects with hue rotation
* Responsive admin interface with improved color management
* i18n support
* Performance optimizations

== Upgrade Notice ==

= 0.10.0 =
Initial release of NS Hamburger Overlay Menu. Clean installation recommended.

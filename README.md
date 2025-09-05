# NS Hamburger Overlay Menu

![WordPress Plugin Version](https://img.shields.io/badge/WordPress-6.5+-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.4+-blue)
![License](https://img.shields.io/badge/License-GPL--2.0--or--later-green)

An accessible hamburger overlay menu plugin for WordPress with gradient animations, multi-column layout, and full keyboard navigation support.

## Features

- ✅ **Accessible**: Full ARIA support, keyboard navigation, focus management
- ✅ **Responsive**: Works on all screen sizes with optimized mobile experience  
- ✅ **Customizable**: Color schemes, column layouts, typography settings
- ✅ **Block Editor**: Native Gutenberg block with live preview
- ✅ **Performance**: Lightweight CSS/JS, no jQuery dependency
- ✅ **i18n Ready**: Full translation support with included POT file

## Requirements

- WordPress 6.5 or later
- PHP 7.4 or later

## Quick Start

1. **Install the plugin**: Upload and activate through WordPress admin
2. **Assign a menu**: Go to Appearance → Menus, assign menu to "Hamburger Overlay Menu" location
3. **Configure settings**: Visit Settings → NS Hamburger Menu to customize appearance
4. **Add to pages**: Use auto-insert or add the block manually in Gutenberg

## Usage

### Auto-Insert Mode
Enable "Auto Insert" in settings to display on all pages automatically.

### Manual Placement
Use the Gutenberg block `NS Hamburger Menu` or shortcode `[ns_hamburger_menu]`.

### Theme Integration
```php
// Add to your theme templates
if (function_exists('nshm_display_menu')) {
    nshm_display_menu();
}
```

## Customization

### Color Schemes
Choose from built-in presets (Blue, Green, Red, Orange, Black) or set custom gradient colors.

### Layout Options
- **Columns**: 1-6 column grid layout
- **Typography**: Separate font sizes for parent/child menu items
- **Animation**: Optional hue rotation animation with speed control

### Block Slots
Add custom content above/below the menu using slot blocks within the Gutenberg block.

## Accessibility Features

- ARIA labels and states for screen readers
- Keyboard navigation (Tab, Shift+Tab, Enter, Space, Escape)
- Focus trapping within open menu
- Focus restoration when menu closes
- Respects `prefers-reduced-motion`

## Developer

### Hooks & Filters
```php
// Customize menu markup
add_filter('nshm_menu_args', function($args) {
    $args['depth'] = 3;
    return $args;
});

// Modify CSS variables
add_filter('nshm_css_vars', function($vars) {
    $vars['--ns-z'] = 99999;
    return $vars;
});
```

### Template Override
Copy `/templates/hamburger-menu.php` to your theme's `/ns-hamburger-menu/` folder to customize markup.

## FAQ

**Q: Can I use this with any theme?**  
A: Yes, works with both classic and block themes.

**Q: Does it work on mobile?**  
A: Yes, optimized for touch devices with proper spacing.

**Q: Can I translate the interface?**  
A: Yes, uses standard WordPress i18n with included POT file.

**Q: Is it accessible?**  
A: Yes, follows WCAG guidelines with full keyboard and screen reader support.

## Support

For issues and feature requests, please use the [GitHub repository](https://github.com/netservice/ns-hamburger-menu).

## License

GPL-2.0-or-later. See LICENSE file for details.
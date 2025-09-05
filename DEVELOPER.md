# Developer Guide

## Architecture

### File Structure
```
ns-hamburger-menu/
├── ns-hamburger-menu.php    # Main plugin file
├── uninstall.php           # Cleanup on uninstall
├── inc/                    # PHP classes
│   ├── Core.php           # Core functionality
│   ├── Admin.php          # Admin interface
│   └── Frontend.php       # Frontend assets
├── assets/                 # Static assets
│   ├── css/ns-hamburger.css
│   ├── js/ns-hamburger.js
│   └── js/admin.js
├── blocks/                 # Gutenberg blocks
│   ├── block.json
│   └── index.js
├── templates/              # Template overrides
├── languages/              # Translation files
└── docs/                   # Documentation
```

### Class Structure

#### NSHM_Core
- Plugin initialization
- Menu location registration
- Block registration
- Shortcode handling
- Auto-inject functionality

#### NSHM_Admin  
- Settings page
- Option sanitization
- Admin asset enqueuing
- Color picker integration

#### NSHM_Frontend
- Frontend asset enqueuing
- CSS variable generation
- Script localization

## Hooks & Filters

### Actions
```php
// Modify menu arguments
add_filter('nshm_menu_args', function($args) {
    $args['depth'] = 3;
    $args['walker'] = new Custom_Walker();
    return $args;
});

// Customize CSS variables
add_filter('nshm_css_vars', function($vars) {
    $vars['--ns-z'] = 99999;
    $vars['--ns-columns'] = 3;
    return $vars;
});

// Modify button markup
add_filter('nshm_button_markup', function($markup) {
    return str_replace('ns-hb', 'ns-hb custom-class', $markup);
});

// Customize overlay attributes
add_filter('nshm_overlay_attributes', function($attrs) {
    $attrs['data-theme'] = 'dark';
    return $attrs;
});
```

### Available Hooks
- `nshm_enqueue_assets` - Before assets are enqueued
- `nshm_render_menu` - Before menu markup is rendered
- `nshm_menu_args` - Filter wp_nav_menu arguments
- `nshm_css_vars` - Filter CSS custom properties
- `nshm_js_config` - Filter JavaScript configuration
- `nshm_button_markup` - Filter hamburger button HTML
- `nshm_overlay_attributes` - Filter overlay element attributes

## Template Override System

### Override Template
1. Create folder in your theme: `{theme}/ns-hamburger-menu/`
2. Copy template: `templates/hamburger-menu.php` to theme folder
3. Customize as needed

### Template Variables
Available in template context:
- `$options` - Plugin settings array
- `$overlay_id` - Unique overlay ID  
- `$style_vars` - CSS custom properties string
- `$slot_before` - Content above menu
- `$slot_after` - Content below menu

## CSS Custom Properties

### Available Variables
```css
:root {
  --ns-start: #0ea5e9;        /* Gradient start color */
  --ns-end: #a78bfa;          /* Gradient end color */
  --ns-columns: 2;            /* Grid columns */
  --ns-top-fz: 24px;          /* Parent font size */
  --ns-sub-fz: 16px;          /* Child font size */
  --ns-hue-speed: 12s;        /* Animation duration */
  --ns-hue-range: 24deg;      /* Hue rotation range */
  --ns-z: 9999;               /* Z-index value */
}
```

### Custom Styling
```css
/* Custom button styles */
.ns-hb {
    border: 2px solid var(--ns-start);
    border-radius: 50%;
}

/* Custom overlay styles */
.ns-overlay {
    backdrop-filter: blur(10px);
}

/* Custom menu styles */
.ns-menu > li > a {
    text-transform: uppercase;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}
```

## JavaScript API

### Configuration Object
```javascript
// Available in NS_HMB global
{
    hueAnimDefault: true,
    i18n: {
        openMenu: "Open menu",
        closeMenu: "Close menu"
    }
}
```

### Custom Events
```javascript
// Listen for menu state changes
document.addEventListener('nshm:open', function(e) {
    console.log('Menu opened', e.detail.button);
});

document.addEventListener('nshm:close', function(e) {
    console.log('Menu closed', e.detail.button);
});
```

## Block Development

### Block Attributes
```javascript
{
    columns: { type: 'number', default: null },
    topFontPx: { type: 'number', default: null },
    subFontPx: { type: 'number', default: null },
    colorStart: { type: 'string', default: null },
    colorEnd: { type: 'string', default: null },
    hueAnim: { type: 'boolean', default: null },
    hueSpeedSec: { type: 'number', default: null },
    zIndex: { type: 'number', default: null }
}
```

### Slot Block
The `ns/hamburger-slot` block allows content insertion:
```javascript
// Slot positions
'before' // Above menu UL
'after'  // Below menu UL
```

## Database Schema

### Options
```php
// Option key: ns_hamburger_options
[
    'auto_inject'   => 1,           // Auto-insert boolean
    'columns'       => 2,           // Grid columns (1-6)
    'top_font_px'   => 24,          // Parent font size
    'sub_font_px'   => 16,          // Child font size  
    'scheme'        => 'custom',    // Color scheme
    'color_start'   => '#0ea5e9',   // Gradient start
    'color_end'     => '#a78bfa',   // Gradient end
    'hue_anim'      => 1,           // Hue animation boolean
    'hue_speed_sec' => 12,          // Animation speed
    'hue_range_deg' => 24,          // Hue rotation degrees
    'z_index'       => 9999         // CSS z-index
]
```

## Performance Considerations

### CSS Loading
- CSS loaded on all pages (minimal footprint ~3KB)
- Inline CSS variables for configuration
- No external font dependencies

### JavaScript Loading  
- Deferred loading for better performance
- No jQuery dependency
- Event delegation for efficiency
- Minimal DOM queries

### Caching Compatibility
- Uses `filemtime()` for asset versioning
- No server-side caching conflicts
- Works with all major caching plugins

## Security Guidelines

### Input Sanitization
- `sanitize_hex_color()` for color values
- `intval()` with min/max for numbers
- `in_array()` for enumerated options

### Output Escaping
- `esc_attr()` for HTML attributes
- `esc_html()` for text content
- `esc_url()` for URLs

### Capability Checks
- `current_user_can('manage_options')` for admin
- `current_user_can('edit_theme_options')` for menu hints

## Troubleshooting

### Common Issues
1. **Menu not appearing**: Check auto-insert setting or block placement
2. **JavaScript errors**: Ensure proper asset loading order
3. **Styling conflicts**: Check z-index and CSS specificity
4. **Focus issues**: Verify ARIA attributes are properly set

### Debug Mode
```php
// Enable WordPress debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Check for plugin conflicts
// Deactivate other plugins temporarily
```

### Performance Debugging
```javascript
// Monitor animation performance
performance.mark('nshm-animation-start');
// ... animation code ...
performance.mark('nshm-animation-end');
performance.measure('nshm-animation', 'nshm-animation-start', 'nshm-animation-end');
```
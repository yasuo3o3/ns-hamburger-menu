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
- Navigation block detection (block theme support)
- Fallback menu generation

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
- `nshm_navigation_menu` - Filter final navigation menu HTML
- `nshm_navigation_fallback` - Filter fallback menu generation
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

## Block Theme Support

### Navigation Detection
The plugin automatically detects navigation content from multiple sources:

1. **Traditional menus** (priority): Uses `wp_nav_menu` with location `ns_hamburger_menu`
2. **Navigation blocks**: Automatically detects `wp_navigation` post types
3. **Fallback generation**: Creates menu from published pages

### Navigation Block Integration
```php
// Get navigation content
$navigation = $this->get_navigation_menu();

// Hook into navigation detection
add_filter('nshm_navigation_menu', function($menu) {
    // Customize detected navigation
    return $menu;
});

// Hook into fallback generation
add_filter('nshm_navigation_fallback', function($pages) {
    // Modify page list for fallback menu
    return $pages;
});
```

### Block Theme Compatibility
- Automatically detects block themes
- Works without traditional menu management
- Supports hierarchical navigation blocks
- Preserves menu styling and structure

## Development Workflow

### Code Standards & Testing
```bash
# PHP syntax check
php -l $(git ls-files '*.php')

# WordPress Coding Standards (PHPCS)
vendor/bin/phpcs --standard=WordPress --report=summary .

# Plugin Check (WordPress.org compliance)
wp plugin check ns-hamburger-menu

# Auto-fix coding standards
vendor/bin/phpcbf --standard=WordPress .
```

### Build & Release Process
```bash
# 1. Update version numbers
# - Update version in ns-hamburger-menu.php header
# - Update NSHM_VERSION constant
# - Update readme.txt Stable tag
# - Update CHANGELOG.txt with new version

# 2. Run quality checks
php -l $(git ls-files '*.php')
vendor/bin/phpcs --standard=WordPress .
wp plugin check ns-hamburger-menu

# 3. Create distribution ZIP
git archive --format=zip --output=../ns-hamburger-menu.zip --prefix=ns-hamburger-menu/ HEAD

# 4. Test distribution package
unzip -l ../ns-hamburger-menu.zip | grep -E '\.(php|css|js|txt|json)$'
```

### Testing Checklist
- [ ] PHP syntax validation (`php -l`)
- [ ] PHPCS WordPress standards compliance
- [ ] Plugin Check tool passes
- [ ] Manual functionality testing
- [ ] Cross-browser compatibility
- [ ] Accessibility validation
- [ ] Performance profiling

## CI/CD Integration Notes

### GitHub Actions Workflow (Planned)
```yaml
# .github/workflows/quality-check.yml
name: Quality Check
on: [push, pull_request]
jobs:
  phpcs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
      - name: Install dependencies
        run: composer install
      - name: Run PHPCS
        run: vendor/bin/phpcs --standard=WordPress .

  plugin-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup WordPress environment
        # Use wordpress-develop or similar action
      - name: Run Plugin Check
        run: wp plugin check .
```

### WordPress.org SVN Deployment
```bash
# Prepare for WordPress.org submission
# 1. Ensure all files comply with Plugin Directory guidelines
# 2. Create release tag matching version
# 3. Upload to SVN trunk/
# 4. Create SVN tag for version

# SVN commands (when ready for WordPress.org)
svn checkout https://plugins.svn.wordpress.org/ns-hamburger-menu/
cd ns-hamburger-menu
# Copy files to trunk/
svn add trunk/*
svn commit -m "Version 0.13.0"
# Copy trunk/ to tags/0.13.0/
svn copy trunk/ tags/0.13.0/
svn commit -m "Tag version 0.13.0"
```

### Automated Testing Setup
```bash
# Install WP-CLI for local testing
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

# Setup local WordPress environment
wp core download
wp config create --dbname=test_db --dbuser=root --dbpass=password
wp core install --url=localhost --title="Test Site" --admin_user=admin --admin_password=password --admin_email=admin@example.com

# Install and activate plugin for testing
wp plugin activate ns-hamburger-menu
```

## Troubleshooting

### Common Issues
1. **Menu not appearing**: Check auto-insert setting or block placement
2. **Block theme menus**: Plugin automatically detects Navigation blocks
3. **JavaScript errors**: Ensure proper asset loading order
4. **Animation issues**: Check for CSS conflicts with hamburger button
5. **Styling conflicts**: Check z-index and CSS specificity
6. **Focus issues**: Verify ARIA attributes are properly set

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
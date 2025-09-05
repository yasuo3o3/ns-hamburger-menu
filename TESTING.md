# Testing Guide

## Manual Testing Checklist

### Basic Functionality
- [ ] Plugin activates without errors
- [ ] Menu location appears in Appearance → Menus
- [ ] Settings page accessible at Settings → NS Hamburger Menu
- [ ] Hamburger button appears on frontend (top-right)
- [ ] Menu opens/closes on button click
- [ ] Menu displays assigned navigation items

### Accessibility Testing

#### Keyboard Navigation
- [ ] Tab key navigates to hamburger button
- [ ] Enter/Space opens menu from button
- [ ] Tab cycles through menu items when open
- [ ] Shift+Tab cycles backward through menu items
- [ ] Escape closes menu from any focused element
- [ ] Focus returns to hamburger button after closing

#### Screen Reader Testing
- [ ] Button has proper ARIA label ("Open menu"/"Close menu")
- [ ] Menu has `aria-expanded` attribute that changes state
- [ ] Menu overlay has proper `aria-label`
- [ ] Menu items are announced correctly
- [ ] Navigation landmarks are properly identified

#### Focus Management
- [ ] Focus moves to first menu item when opening
- [ ] Focus is trapped within menu when open
- [ ] Focus returns to button or last focused element when closing
- [ ] Visual focus indicators are clear and visible

### Visual Testing

#### Desktop (1920x1080)
- [ ] Button positioned correctly (top-right with admin bar consideration)
- [ ] Menu expands with smooth clip-path animation
- [ ] Grid layout displays correctly for selected column count
- [ ] Typography renders at configured sizes
- [ ] Color gradient displays properly
- [ ] Hue animation works if enabled

#### Mobile (375x667)
- [ ] Button sized appropriately for touch
- [ ] Menu fills screen properly
- [ ] Touch interaction works smoothly
- [ ] Text remains readable at all sizes
- [ ] No horizontal scrolling issues

#### Tablet (768x1024)
- [ ] Layout adapts appropriately
- [ ] Touch targets remain accessible
- [ ] Menu scaling works correctly

### Browser Compatibility
- [ ] Chrome (latest)
- [ ] Firefox (latest)  
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Chrome Mobile
- [ ] Safari Mobile

### Theme Compatibility
- [ ] Twenty Twenty-Four: No visual conflicts
- [ ] Twenty Twenty-Three: Proper integration
- [ ] Popular third-party themes: Astra, GeneratePress, etc.

### Block Editor Testing
- [ ] Block appears in inserter
- [ ] Block settings panel works correctly
- [ ] Live preview updates in editor
- [ ] Slot blocks can be added/removed
- [ ] Block saves and loads correctly
- [ ] Block works in FSE themes

### Performance Testing
- [ ] No JavaScript errors in console
- [ ] CSS loads without 404 errors
- [ ] Page load time acceptable
- [ ] No memory leaks on repeated open/close
- [ ] Animation performs smoothly (60fps)

### Configuration Testing

#### Auto-Insert Mode
- [ ] Appears on homepage when enabled
- [ ] Appears on posts/pages when enabled  
- [ ] Does not duplicate when disabled
- [ ] Works with caching plugins

#### Color Schemes
- [ ] Preset colors apply correctly
- [ ] Custom colors save and display
- [ ] Hue animation respects settings
- [ ] Color picker works in admin

#### Layout Options
- [ ] Column count changes grid layout
- [ ] Font sizes apply to parent/child items
- [ ] Z-index setting prevents conflicts

### Reduced Motion Testing
- [ ] Animations disabled when `prefers-reduced-motion: reduce`
- [ ] Menu still functions without animations
- [ ] Transition states remain accessible

### Error Conditions
- [ ] Works when no menu assigned (shows admin notice)
- [ ] Handles malformed block content gracefully
- [ ] Continues working if CSS fails to load
- [ ] Degrades gracefully if JavaScript fails

## Automated Testing Commands

```bash
# Install development dependencies
composer install

# Run PHPCS
composer run phpcs

# Fix PHPCS issues
composer run phpcbf  

# Run PHPStan
composer run phpstan

# Run all linting
composer run lint
```

## Browser Testing Tools

- **axe DevTools**: Accessibility testing
- **Lighthouse**: Performance and accessibility audit
- **WAVE**: Web accessibility evaluation
- **Keyboard Navigation**: Built-in browser tools

## Minimum Passing Criteria

- ✅ Zero PHP errors/warnings
- ✅ Zero JavaScript console errors  
- ✅ All WCAG AA accessibility requirements met
- ✅ Works in all major browsers
- ✅ Functions on all device sizes
- ✅ Passes all automated code quality checks
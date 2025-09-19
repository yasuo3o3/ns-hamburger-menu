# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.12.0] - 2024-12-22

### Added
- Custom position settings for hamburger menu with default (top-left/top-right) and custom positioning modes
- Browser width-responsive X-axis positioning to prevent menu from going off-screen
- Individual color customization for all hamburger icon lines (5 separate colors)
  - Closed state: top line, middle line, bottom line
  - Open state (× mark): left-to-right diagonal, right-to-left diagonal
- WordPress color picker integration for consistent UI experience
- Horizontal layout for hamburger icon color settings to reduce vertical space

### Changed
- Y-axis positioning changed from screen center to screen top reference for more intuitive positioning
- Improved admin UI with compact horizontal layout for color pickers
- Enhanced CSS variable system to support individual line colors
- Updated validation system to handle new position and color settings

### Technical
- Added CSS min/max functions for responsive positioning
- Implemented 5-color CSS variable system for individual line control
- Enhanced settings sanitization and validation for all new options

## [0.11.1] - 2024-09-19

### Added
- Block theme support with automatic Navigation block detection
- Automatic fallback menu generation from published pages
- Multi-source navigation priority system (traditional menu → navigation blocks → page list)
- Enhanced hamburger button animation with improved visual clarity

### Fixed
- Hamburger button animation glitch where three lines remained visible when opened
- Improved CSS positioning for hamburger button transformation
- Better opacity control for menu state transitions

### Changed
- Enhanced navigation detection to work seamlessly with block themes
- Updated documentation for block theme compatibility
- Improved error handling for navigation menu generation

### Removed
- Removed obsolete responsive position test file from debug tools

## [0.11.0] - 2024-09-18

### Added
- Improved animation performance
- Enhanced accessibility features
- Better mobile responsiveness

## [0.10.0] - 2024-09-05

### Added
- Initial distribution-ready release
- Full accessibility support with ARIA labels and keyboard navigation
- Multi-column layout support (1-6 columns)
- Gradient color schemes with hue animation
- Gutenberg block with customizable slots
- Comprehensive internationalization (i18n) support
- PHPCS and PHPStan configuration for code quality
- Proper plugin architecture with separated classes
- Auto-insert functionality for site-wide deployment
- Focus management and focus trapping
- Reduced motion support for accessibility

### Changed
- Restructured codebase with proper OOP architecture
- Improved CSS with custom properties for better maintainability  
- Enhanced JavaScript with better error handling
- Updated to modern WordPress development practices

### Security
- Added proper input sanitization and output escaping
- Implemented nonce verification for admin forms
- Added capability checks for admin functionality

### Developer
- Added template override system
- Included hooks and filters for customization
- Comprehensive PHPDoc documentation
- Unit test ready structure

## [0.11.0] - 2024-09-13

### Added
- Design presets system with 4 preset options (Normal, Pattern 1-3)
- Custom CSS integration for advanced styling capabilities
- Individual design preset CSS files with unique animations and effects:
  - Pattern 1: Elegant rounded design with shadows and smooth transitions
  - Pattern 2: Modern minimal design with accent lines and dot decorations
  - Pattern 3: Playful animated design with color accents and floating effects
- Enhanced custom color settings panel with responsive grid layout
- Improved admin interface with better visual organization
- Japanese localization for all new admin interface elements

### Changed
- Redesigned custom color management interface with grid-based layout
- Enhanced admin UI responsiveness with mobile-friendly breakpoints
- Improved CSS cascade order: base styles → preset styles → custom CSS
- Updated color panel layout to accommodate larger screen sizes (max-width: 740px)
- Refined admin form styling with better spacing and visual hierarchy

### Technical
- Added `design_preset` and `design_custom_css` to plugin options schema
- Implemented conditional CSS enqueuing for design presets
- Enhanced sanitization with CSS content validation (10KB limit)
- Added proper nonce verification for new settings
- Improved CSS custom property management for consistent theming

## [Unreleased]

### Planned
- Menu item icons support  
- Advanced animation options
- Performance optimization dashboard
- Block theme template parts integration
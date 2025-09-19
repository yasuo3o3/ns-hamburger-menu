# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
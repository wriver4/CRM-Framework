# Summernote WYSIWYG Editor Integration

This document describes the comprehensive Summernote integration system implemented in the CRM application.

## Overview

The Summernote integration provides:
- **Flexible toolbar customization** per page and per textarea
- **Automatic configuration** based on page context
- **Plugin system** ready for awesome-summernote plugins
- **Email template support** with variable insertion
- **Bootstrap 5 compatibility**
- **CDN and local asset support**

## Architecture

### Core Components

1. **SummernoteManager** (`classes/Utilities/SummernoteManager.php`)
   - Main configuration and rendering engine
   - Handles toolbar presets and custom configurations
   - Manages JavaScript generation and asset loading

2. **EditorHelper** (`classes/Utilities/EditorHelper.php`)
   - Template system integration
   - Page-specific auto-configuration
   - Easy-to-use helper methods

3. **Configuration File** (`config/summernote-config.php`)
   - Centralized configuration management
   - Toolbar presets and page-specific settings
   - Plugin definitions and email template variables

4. **JavaScript Helper** (`public_html/assets/js/summernote-helper.js`)
   - Client-side functionality
   - Plugin loading and management
   - Email template features

## Quick Start

### Automatic Integration

The system automatically integrates with existing pages. For pages that contain textareas (like leads/edit, contacts/new, etc.), Summernote will be automatically loaded and configured.

### Manual Configuration

```php
// In your page controller
$editorHelper = EditorHelper::getInstance();

// Configure for current page
$editorHelper->configureForPage('leads', 'edit');

// Or configure specific textareas
$editorHelper->configureTextareas([
    '#notes' => [
        'toolbar' => 'basic',
        'height' => 150,
        'placeholder' => 'Enter notes...'
    ],
    '#description' => [
        'toolbar' => 'standard',
        'height' => 200
    ]
]);
```

### Template Integration

The system automatically integrates with the existing template system:

- **Header template** includes CSS automatically
- **Footer template** includes JavaScript and initialization automatically
- **Page detection** determines when to load Summernote

## Toolbar Presets

### Available Presets

- **minimal**: Basic formatting (bold, italic, lists)
- **basic**: Standard formatting with links
- **standard**: Full formatting with images and tables
- **advanced**: Complete feature set with code view
- **email**: Email-optimized toolbar
- **notes**: Simplified for note-taking

### Custom Toolbars

```php
// Add custom toolbar preset
$summernote = SummernoteManager::getInstance();
$summernote->addToolbarPreset('custom', [
    ['style', ['bold', 'italic']],
    ['color', ['color']],
    ['para', ['ul', 'ol']],
    ['misc', ['undo', 'redo']]
]);
```

## Page-Specific Configuration

### Leads Pages

- **new.php**: 
  - `structure_additional`: Minimal toolbar
  - `notes`: Basic toolbar
- **edit.php**:
  - `project_description`: Standard toolbar
  - `notes`: Basic toolbar
- **compare_notes.php**:
  - Both textareas: Basic toolbar with larger height

### Contacts Pages

- **new.php** / **edit.php**:
  - `notes`: Basic toolbar for contact notes

### Systems Pages

- **edit.php**:
  - Replaces CKEditor with Summernote advanced toolbar

## Email Template Features

### Template Variables

The system supports email template variables:

```javascript
// Setup template variables
const variables = [
    { name: 'Customer Name', value: '{{customer_name}}' },
    { name: 'Lead Number', value: '{{lead_number}}' },
    { name: 'Project Name', value: '{{project_name}}' }
];

summernoteHelper.setupTemplateVariables('#email-editor', variables);
```

### Email Export

```javascript
// Export email template
const template = summernoteHelper.exportAsEmailTemplate('#email-editor');
console.log(template.html); // HTML version
console.log(template.text); // Plain text version
```

## Plugin System

### Available Plugins

The system supports awesome-summernote plugins:

- **summernote-ext-emoji**: Emoji support
- **summernote-ext-mention**: @mention functionality
- **summernote-ext-template**: Template management
- **summernote-ext-print**: Print functionality
- **summernote-ext-specialchars**: Special characters
- **summernote-ext-highlight**: Code highlighting
- **summernote-ext-rtl**: Right-to-left text support

### Loading Plugins

```javascript
// Load specific plugins
await summernoteHelper.loadPlugins([
    { name: 'emoji', url: 'https://cdn.jsdelivr.net/npm/summernote-ext-emoji@latest/dist/summernote-ext-emoji.min.js' },
    { name: 'template', url: 'https://cdn.jsdelivr.net/npm/summernote-ext-template@latest/dist/summernote-ext-template.min.js' }
]);

// Load email template plugins
await summernoteHelper.loadEmailTemplatePlugins();
```

## Configuration Options

### Global Settings

```php
// In config/summernote-config.php
'global' => [
    'height' => 200,
    'minHeight' => 100,
    'maxHeight' => 500,
    'useLocalAssets' => false, // Use CDN by default
    'focus' => false,
    'tabsize' => 2
]
```

### Page-Specific Settings

```php
'pages' => [
    'leads' => [
        'edit' => [
            'notes' => [
                'toolbar' => 'basic',
                'height' => 120,
                'placeholder' => 'Lead notes...'
            ]
        ]
    ]
]
```

## Security Features

### Content Sanitization

The configuration includes security settings:

```php
'security' => [
    'allowedTags' => ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a', 'img', 'ul', 'ol', 'li'],
    'allowedAttributes' => ['href', 'src', 'alt', 'title', 'class', 'style'],
    'stripTags' => ['script', 'style', 'iframe', 'object', 'embed']
]
```

## Testing

### Test Page

Visit `/test-summernote.php` to test the integration:

- Multiple editor configurations
- Plugin loading
- Export functionality
- Custom configurations

### Existing Tests

The integration follows the smart testing workflow:

**Existing Test Coverage:**
- Authentication tests cover protected pages
- Form validation tests cover textarea functionality
- No specific Summernote tests needed initially

**Future Test Enhancement:**
If specific Summernote functionality needs testing, add to:
- `tests/playwright/` for UI testing
- `tests/phpunit/Feature/` for integration testing

## Usage Examples

### Basic Usage

```php
// Automatic - just include textareas in your forms
<textarea name="notes" class="form-control" placeholder="Enter notes..."></textarea>
```

### Custom Configuration

```php
// Configure specific editor
$editorHelper = EditorHelper::getInstance();
$editorHelper->configureTextarea('#special-notes', [
    'toolbar' => 'advanced',
    'height' => 300,
    'placeholder' => 'Special notes with full features...'
]);
```

### Email Templates

```php
// Setup email template editor
$editorHelper->setupEmailTemplate('#email-content');
```

### JavaScript Customization

```javascript
// Custom editor setup
$('#my-editor').summernote({
    toolbar: [
        ['style', ['bold', 'italic']],
        ['color', ['color']],
        ['misc', ['undo', 'redo']]
    ],
    height: 200,
    callbacks: {
        onInit: function() {
            console.log('Editor ready');
        }
    }
});
```

## Troubleshooting

### Common Issues

1. **Editor not loading**
   - Check if page is in the `shouldLoadEditor()` list
   - Verify jQuery is loaded before Summernote
   - Check browser console for JavaScript errors

2. **Toolbar not showing correctly**
   - Verify toolbar preset exists
   - Check CSS conflicts with Bootstrap
   - Ensure proper configuration syntax

3. **Plugins not working**
   - Check plugin URLs are accessible
   - Verify plugins are loaded after Summernote
   - Check for plugin compatibility

### Debug Mode

```javascript
// Enable debug logging
window.summernoteHelper.debug = true;
```

## Future Enhancements

### Planned Features

1. **Image Upload Integration**
   - Server-side image handling
   - Image resizing and optimization
   - Gallery integration

2. **Advanced Email Templates**
   - Template library
   - Drag-and-drop components
   - Preview functionality

3. **Collaboration Features**
   - Real-time editing
   - Comment system
   - Version history

4. **Mobile Optimization**
   - Touch-friendly toolbar
   - Responsive editor
   - Mobile-specific features

## Support

For issues or questions:

1. Check the test page: `/test-summernote.php`
2. Review configuration: `config/summernote-config.php`
3. Check browser console for errors
4. Refer to [Summernote documentation](https://summernote.org/)
5. Browse [awesome-summernote plugins](https://github.com/summernote/awesome-summernote)
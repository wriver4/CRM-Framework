# PHP Web Application Framework

This framework provides a foundation for building PHP web applications with database connectivity, user management, and table display functionality.

## Directory Structure

```
framework/
├── classes/           # Core framework classes
├── config/           # Configuration files
├── templates/        # HTML templates and components
├── assets/          # CSS, JS, and other static assets
├── examples/        # Example implementations
└── docs/           # Documentation
```

## Core Classes

- **Database**: Database connection management with multiple database support
- **Table**: Base table display functionality
- **ActionTable**: Extended table with action buttons (view, edit, delete)
- **ViewTable**: Read-only table display
- **EditDeleteTable**: Table with edit/delete functionality
- **FormComponents**: Form building utilities
- **Security**: Authentication and authorization
- **Helpers**: Utility functions
- **Nonce**: CSRF protection

## Quick Start

1. Configure your database settings in `config/database.php`
2. Include the autoloader: `require_once 'framework/autoload.php'`
3. Extend the base classes to create your application-specific functionality
4. Use the template system for consistent UI

## Example Usage

```php
// Create a new data model
class Products extends Database {
    // Your product-specific methods
}

// Create a list view
class ProductsList extends ActionTable {
    // Your product list display logic
}

// Use in your application
$products = new Products();
$results = $products->get_all_active();
$list = new ProductsList($results, $lang);
$list->create_table();
```

See the `examples/` directory for complete implementation examples.
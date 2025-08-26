# CRM Framework - Claude Code Context

## Project Summary
PHP-based CRM (Customer Relationship Management) framework for managing leads, contacts, users, and sales pipelines. Built with PHP 8.4.8, MySQL, Bootstrap 5, and jQuery.

## Key Features
- Lead and contact management with CRUD operations
- Role-based access control and permissions system
- Multilingual support (English/Spanish)
- Audit trails and comprehensive logging
- Reporting module with various report types
- Notes system with migration capabilities
- Bootstrap 5 + Font Awesome UI

## Architecture
- **MVC Pattern**: Controllers in `public_html/`, Models in `classes/`, Views in `templates/`
- **Entity Organization**: Separate directories for leads, contacts, users, security
- **Database Layer**: All models extend `classes/Database.php`
- **Security**: Centralized authentication, CSRF protection, session management

## Development Guidelines

### Code Style
- **Classes**: PascalCase (e.g., `Leads.php`, `ContactsList.php`)
- **Controllers**: action.php pattern (`list.php`, `edit.php`, `new.php`, `delete.php`)
- **Templates**: snake_case (`nav_item_leads.php`)
- **Database**: PDO with prepared statements
- **Error Handling**: Whoops for development, Monolog for production logging

### Key Directories
- `classes/` - Core business logic and database models
- `public_html/` - Web-accessible controllers and entry points
- `templates/` - Reusable HTML components
- `config/` - System configuration files
- `sql/` - Database schemas and migration scripts
- `logs/` - Application logs (audit, PHP errors, internal)

### Database
- **Type**: MySQL with utf8mb4 charset
- **Connection**: PDO via `classes/Database.php`
- **Tables**: leads, contacts, users, roles, permissions, notes, audit

### Security
- CSRF protection via `classes/Nonce.php`
- Role-based permissions system
- Session management in `classes/Sessions.php`
- HTTPS enforcement
- Input sanitization and prepared statements

### Frontend
- **CSS Framework**: Bootstrap 5
- **Icons**: Bootstrap Icons + Font Awesome
- **JavaScript**: jQuery, DataTables, Validator.js
- **Responsive**: Mobile-friendly design

### Multilingual Support
- Language files in `public_html/admin/languages/`
- Helper class for internationalization
- Currently supports English and Spanish

## Common Tasks

### Running Tests
```bash
php scripts/test_migration.php
php public_html/admin/test_notes_migration.php
```

### Database Operations
- Schema: `sql/democrm_democrm.sql`
- Migrations: Files in `sql/` directory
- Connection: Configured in `classes/Database.php`

### Adding New Entities
1. Create model class in `classes/`
2. Create controller directory in `public_html/`
3. Add CRUD controllers (`list.php`, `new.php`, `edit.php`, `delete.php`)
4. Update navigation in `templates/nav_*.php`
5. Add permissions in security system

### Logging
- **Audit Trail**: `classes/Audit.php`
- **PHP Errors**: `logs/php_errors.log`
- **Application Logs**: Monolog integration

## Entry Points
- **Main**: `public_html/index.php`
- **Dashboard**: `public_html/dashboard.php`
- **Login**: `public_html/login.php`
- **Admin Tools**: `public_html/admin/`

## Dependencies
- **monolog/monolog**: Logging framework
- **filp/whoops**: Error handling and debugging
- **Composer**: Dependency management

## Notes Migration System
The application includes a sophisticated notes migration system:
- Migration scripts in `scripts/`
- Test utilities in `public_html/admin/`
- Comparison tools for data validation

When working on this codebase:
1. Follow the established MVC pattern
2. Use the existing security classes for authentication
3. Maintain the CRUD controller naming convention
4. Utilize the template system for consistent UI
5. Follow the multilingual support patterns
6. Use the logging system for debugging and audit trails
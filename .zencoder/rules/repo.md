---
description: Repository Information Overview
alwaysApply: true
---

# CRM Framework Information

## Summary
A PHP-based CRM (Customer Relationship Management) framework providing functionality for managing leads, contacts, users, and sales pipelines. The application follows a traditional PHP web application structure with database connectivity, user management, and table display functionality. The application is multilingual with language files stored in arrays in the admin/languages folder, and helper classes to support iternationalization. The application also includes a reporting module that generates various types of reports based on different criteria. It includes features like CRUD operations, role-based access control, and audit trails. It uses Boostrap 5 and Bootstrap Icons for frontend design as well as Font awesome for icons. There are other javascript tools used such validator.js and  jquery and datatable.js.

## Structure
- **classes/**: Core framework classes for database, security, and business logic
- **config/**: Configuration files for system settings
- **public_html/**: Web-accessible files including controllers and views
- **scripts/**: Utility scripts for data migration
- **sql/**: SQL scripts for database setup and migrations
- **vendor/**: Composer dependencies
- **logs/**: Application logs
- **templates/**: HTML templates and components

## Language & Runtime
**Language**: PHP
**Version**: 8.4.8
**Build System**: None (direct PHP execution)
**Package Manager**: Composer

## Dependencies
**Main Dependencies**:
- monolog/monolog (^3.9): Logging library
- filp/whoops (^2.18): Error handling

**Development Dependencies**:
- Same as main dependencies

## Database
**Type**: MySQL
**Connection**: PDO
**Database Name**: democrm_democrm
**Character Set**: utf8mb4

### Database Constraints & Limitations
- **No information_schema queries**: Due to root@localhost permission issues, avoid using `information_schema` tables in SQL scripts
- **Use alternative approaches**: Use `SHOW CREATE TABLE`, `SHOW TABLES`, `DESCRIBE table_name` instead
- **Foreign key management**: Use `SHOW CREATE TABLE` to view constraints before modification

## Core Components
**Models**:
- Database: Base database connection class
- Users: User management
- Leads: Lead management
- Contacts: Contact management
- Notes: Notes functionality
- Roles/Permissions: Security management

**Views**:
- Templates system with header, footer, navigation components
- Table display classes (ViewTable, Table)

**Controllers**:
- Module-specific controllers in public_html directories (users/, leads/, contacts/)

## Error Handling
**Framework**: Whoops (^2.18)
**Logging**: Monolog (^3.9)
**Log Location**: logs/php_errors.log

## Security
- HTTPS enforcement
- Session management
- Role-based permissions system
- CSRF protection via Nonce class

## Entry Points
**Main Entry**: public_html/index.php
**Dashboard**: public_html/dashboard.php
**Authentication**: public_html/login.php, public_html/logout.php

## Configuration
**System Config**: config/system.php
**Database Config**: Embedded in classes/Database.php
**Path Constants**: Defined in config/system.php

## Testing
**Testing Directory**: `tests/`
**Lead Tests**: `tests/leads/` - Contains debugging and testing utilities for lead functionality

No formal testing framework is implemented; testing is done through manual execution of test scripts and debugging utilities.

## Directory Structure

```
.
├── classes/                         # Core framework classes
│   ├── ActionTable.php              # Extended table with action buttons
│   ├── Audit.php                    # Audit logging functionality
│   ├── AuditList.php                # Audit trail list display
│   ├── Communications.php           # Communication management
│   ├── Contacts.php                 # Contact management
│   ├── ContactsList.php             # Contact list display
│   ├── Database.php                 # Base database connection class
│   ├── EditDeleteTable.php          # Table with edit/delete functionality
│   ├── FormComponents.php           # Form building utilities
│   ├── Helpers.php                  # Utility functions (multilingual support)
│   ├── InternalErrors.php           # Internal error handling
│   ├── Leads.php                    # Lead management
│   ├── LeadsListTable.php           # Leads list table display
│   ├── Logit.php                    # Logging utilities
│   ├── Nonce.php                    # CSRF protection
│   ├── Notes.php                    # Notes functionality
│   ├── Permissions.php              # Permission management
│   ├── PermissionsList.php          # Permissions list display
│   ├── PhpErrorLog.php              # PHP error logging
│   ├── Roles.php                    # Role management
│   ├── RolesList.php                # Roles list display
│   ├── RolesPermissions.php         # Role-permission management
│   ├── RolesPermissionsList.php     # Role-permission mapping display
│   ├── Sales.php                    # Sales functionality
│   ├── Security.php                 # Authentication and authorization
│   ├── Sessions.php                 # Session management
│   ├── Table.php                    # Base table display functionality
│   ├── Users.php                    # User management
│   ├── UsersList.php                # Users list display
│   └── ViewTable.php                # Read-only table display
├── config/                          # Configuration files
│   ├── cronconfig.php               # Cron job configuration
│   ├── ftpconfig.php                # FTP configuration
│   ├── helpers.php                  # Helper configuration
│   └── system.php                   # System configuration
├── logs/                            # Application logs
│   ├── delete_note_errors.log       # Note deletion error logs
│   ├── minimal_test.log             # Minimal test logs
│   └── php_errors.log               # PHP error logs
├── public_html/                     # Web-accessible files
│   ├── admin/                       # Administrative tools
│   │   ├── languages/               # Language files (multilingual support)
│   │   │   ├── login/               # Login-specific translations
│   │   │   │   ├── en.php           # English login translations
│   │   │   │   ├── es.php           # Spanish login translations
│   │   │   │   └── template.php     # Translation template
│   │   │   ├── en.php               # English translations
│   │   │   ├── _es.php              # Spanish translations
│   │   │   └── _es_complete.php     # Complete Spanish translations
│   │   ├── leads/                   # Admin lead management
│   │   │   ├── delete_note.php      # Note deletion handler
│   │   │   ├── edit.php             # Admin lead editing
│   │   │   ├── get.php              # Admin lead retrieval
│   │   │   ├── list.php             # Admin lead listing
│   │   │   └── post.php             # Admin lead processing
│   │   └── logs/                    # Admin log viewers
│   │       ├── audit.php            # Audit log viewer
│   │       ├── internal.php         # Internal log viewer
│   │       └── phperror.php         # PHP error log viewer
│   ├── assets/                      # Static assets
│   │   ├── css/                     # Stylesheets
│   │   │   ├── webfonts/            # Font Awesome fonts
│   │   │   │   ├── fa-brands-400.woff2
│   │   │   │   ├── fa-regular-400.woff2
│   │   │   │   ├── fa-solid-900.woff2
│   │   │   │   └── fa-v4compatibility.woff2
│   │   │   ├── all.css              # Font Awesome CSS
│   │   │   ├── bootstrap.min.css    # Bootstrap framework
│   │   │   ├── forms.css            # Form styling
│   │   │   ├── login.css            # Login page styling
│   │   │   └── style.css            # Main application styles
│   │   ├── help/                    # Help documentation
│   │   │   ├── administration.php   # Admin help
│   │   │   ├── common_elements.php  # Common elements help
│   │   │   ├── contacts.php         # Contacts help
│   │   │   ├── get.php              # Help retrieval
│   │   │   ├── index.php            # Help index
│   │   │   ├── properties.php       # Properties help
│   │   │   ├── reports.php          # Reports help
│   │   │   ├── status.php           # Status help
│   │   │   ├── systems.php          # Systems help
│   │   │   ├── testing.php          # Testing help
│   │   │   ├── this_app.php         # Application help
│   │   │   ├── tickets.php          # Tickets help
│   │   │   └── users.php            # Users help
│   │   ├── img/                     # Images and icons
│   │   │   ├── browserconfig.xml    # Browser configuration
│   │   │   ├── logo.svg             # Application logo
│   │   │   ├── safari-pinned-tab.svg # Safari icon
│   │   │   └── site.webmanifest     # Web manifest
│   │   └── js/                      # JavaScript files
│   │       ├── user/                # User-specific scripts
│   │       │   ├── edit.js          # User edit scripts
│   │       │   └── new.js           # User creation scripts
│   │       ├── conditional-forms.js # Dynamic form behavior
│   │       ├── contact-selector.js  # Contact selection functionality
│   │       ├── countdown.js         # Countdown functionality
│   │       ├── edit-leads.js        # Lead editing scripts
│   │       ├── general.js           # General utilities
│   │       ├── hide-empty-structure.js # UI optimization
│   │       ├── user.js              # User functionality
│   │       ├── validator.min.js     # Form validation
│   │       └── validator.min.js.map # Source map
│   ├── contacts/                    # Contact management
│   │   ├── delete.php               # Contact deletion
│   │   ├── edit.php                 # Contact editing
│   │   ├── get.php                  # Contact retrieval
│   │   ├── list.php                 # Contact listing
│   │   ├── new.php                  # New contact creation
│   │   ├── post.php                 # Contact form processing
│   │   └── view.php                 # Contact viewing
│   ├── customers/                   # Customer management
│   │   └── list.php                 # Customer listing
│   ├── leads/                       # Lead management
│   │   ├── communications/          # Lead communications
│   │   │   ├── list.php             # Communication listing
│   │   │   └── log.php              # Communication logging
│   │   ├── compare_notes.php        # Notes comparison
│   │   ├── compare_notes_save.php   # Notes comparison saving
│   │   ├── delete.php               # Lead deletion
│   │   ├── edit.php                 # Lead editing
│   │   ├── get.php                  # Lead retrieval
│   │   ├── list.php                 # Lead listing
│   │   ├── new.php                  # New lead creation
│   │   ├── notes_ajax.php           # AJAX notes handling
│   │   ├── post.php                 # Lead form processing
│   │   ├── post_with_contact_integration.php # Lead processing with contact integration
│   │   └── view.php                 # Lead viewing
│   ├── reports/                     # Reporting system
│   │   ├── contacts/                # Contact reports
│   │   │   └── all.php              # All contacts report
│   │   ├── reports/                 # Specific reports
│   │   │   ├── customer_activity.php # Customer activity report
│   │   │   └── sales_performance.php # Sales performance report
│   │   ├── get.php                  # Report retrieval
│   │   ├── index.php                # Reports dashboard
│   │   └── list.php                 # Report listing
│   ├── sales/                       # Sales management
│   │   └── pipeline.php             # Sales pipeline
│   ├── security/                    # Security management
│   │   ├── permissions/             # Permission management
│   │   │   ├── assign_role_permissions.php # Role permission assignment
│   │   │   ├── delete.php           # Permission deletion
│   │   │   ├── get.php              # Permission retrieval
│   │   │   ├── list.php             # Permission listing
│   │   │   ├── new.php              # New permission creation
│   │   │   └── post.php             # Permission processing
│   │   ├── roles/                   # Role management
│   │   │   ├── delete.php           # Role deletion
│   │   │   ├── edit_role.php        # Role editing
│   │   │   ├── get.php              # Role retrieval
│   │   │   ├── list.php             # Role listing
│   │   │   ├── new.php              # New role creation
│   │   │   ├── post.php             # Role processing
│   │   │   └── view.php             # Role viewing
│   │   └── roles_permissions/       # Role-permission mapping
│   │       ├── delete.php           # Role-permission deletion
│   │       ├── get.php              # Role-permission retrieval
│   │       ├── list.php             # Role-permission listing
│   │       └── post.php             # Role-permission processing
│   ├── templates/                   # HTML templates
│   │   ├── body.php                 # Page body template
│   │   ├── footer.php               # Page footer
│   │   ├── header.php               # Page header
│   │   ├── list_buttons.php         # List button components
│   │   ├── list_clock.php           # Clock component
│   │   ├── list_close.php           # List close component
│   │   ├── list_open.php            # List open component
│   │   ├── nav.php                  # Main navigation
│   │   ├── nav_end.php              # Navigation end
│   │   ├── nav_item_contacts.php    # Contacts navigation
│   │   ├── nav_item_leads_list.php  # Leads list navigation
│   │   ├── nav_item_leads_new.php   # New lead navigation
│   │   ├── nav_item_profile.php     # Profile navigation
│   │   ├── nav_item_reports.php     # Reports navigation
│   │   ├── nav_item_users.php       # Users navigation
│   │   ├── nav_start.php            # Navigation start
│   │   ├── section_close.php        # Section close
│   │   ├── section_footer.php       # Section footer
│   │   ├── section_header.php       # Section header
│   │   └── section_open.php         # Section open
│   ├── users/                       # User management
│   │   ├── delete.php               # User deletion
│   │   ├── edit.php                 # User editing
│   │   ├── get.php                  # User retrieval
│   │   ├── list.php                 # User listing
│   │   ├── new.php                  # New user creation
│   │   ├── post.php                 # User form processing
│   │   └── view.php                 # User viewing
│   ├── profile/                     # User profile management
│   ├── dashboard.php                # Main dashboard
│   ├── index.html                   # Static index page
│   ├── index.php                    # Application entry point
│   ├── INSTALLATION.md              # Installation documentation
│   ├── LICENSE.txt                  # License file
│   ├── login.php                    # Login page
│   ├── logout.php                   # Logout handler
│   ├── php.ini                      # PHP configuration
│   ├── post.php                     # General form processor
│   └── README.md                    # Public HTML documentation
├── sql/                            # Database scripts
│   └── democrm_democrm.sql          # Main database schema
├── tests/                           # Test files and debugging utilities
│   ├── leads/                       # Lead-related tests
│   │   ├── debug_delete_note.php    # Debug note deletion
│   │   ├── delete_note_fixed.php    # Fixed note deletion
│   │   ├── minimal_delete.php       # Minimal deletion test
│   │   ├── simple_test.php          # Simple test file
│   │   ├── test_delete_simple.php   # Simple deletion test
│   │   ├── test_endpoint.php        # Endpoint testing
│   │   ├── test_minimal.html        # Minimal test HTML
│   │   └── test_note_delete.php     # Note deletion test
│   └── README.md                    # Test documentation
├── vendor/                          # Composer dependencies (ignored in git)
│   ├── composer/                    # Composer files
│   ├── filp/                        # Whoops error handling
│   ├── monolog/                     # Monolog logging
│   ├── psr/                         # PSR standards
│   └── autoload.php                 # Composer autoloader
├── .gitignore                       # Git ignore rules
├── .htaccess                        # Apache configuration
├── composer.json                    # Composer dependencies
├── composer.lock                    # Dependency lock file
└── README.md                        # Project documentation
```

### Ignored Files and Directories (per .gitignore)
The following are excluded from version control:
- `.vscode/` (except specific settings files)
- `.conf/`, `.pki/`, `.trash/`, `.zencoder/` (dot folders)
- `.bash*`, `.cloud*` (dot files)
- `cwp_stats/`, `backupcwp/`, `tmp/`, `ssl/`, `ftp/`, `Archive/` (system folders)
- `error.log`, `php_errors.log`, `request.log`, `request_2.log` (log files)
- `conf.json`, `passwd` (configuration files)

## File Naming Conventions

- **Controllers**: `action.php` (e.g., `edit.php`, `list.php`, `new.php`)
- **Classes**: `PascalCase.php` (e.g., `Leads.php`, `ContactsList.php`)
- **Templates**: `snake_case.php` (e.g., `nav_item_leads.php`)
- **Assets**: Standard web naming (e.g., `style.css`, `edit-leads.js`)

## Project Preferences

### Database
- Prefer integers over varchar for ID fields and numeric data
- Use phpMyAdmin for database migrations and schema changes
- Always create backup tables before major alterations
- Use MariaDB 10 compatible syntax
- Maintain foreign key constraints for data integrity

### PHP Development
- Always use individual PDO bindValue() calls instead of execute() with parameter arrays
- Cast to integers in PHP when binding ID parameters
- Use appropriate PDO parameter types (PDO::PARAM_INT for integers, PDO::PARAM_STR for strings)

### Code Standards
- Use `int(11)` over `varchar()` for ID fields
- Implement proper foreign key relationships
- Follow consistent CRUD operation patterns

## Key Architectural Patterns

- **MVC Structure**: Controllers in `public_html/`, Models in `classes/`, Views in `templates/`
- **Entity Organization**: Each entity (leads, contacts, users) has its own directory
- **CRUD Operations**: Consistent `list.php`, `new.php`, `edit.php`, `view.php`, `delete.php` pattern
- **Security Layer**: Centralized in `classes/Security.php` and `classes/Nonce.php`
- **Database Layer**: All models extend `classes/Database.php`
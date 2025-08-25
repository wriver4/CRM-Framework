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
**Testing Files**: 
- scripts/test_migration.php
- public_html/admin/test_notes_migration.php

No formal testing framework is implemented; testing appears to be done through manual execution of test scripts.

## Directory Structure

```
.
├── classes/                          # Core framework classes
│   ├── ActionTable.php              # Extended table with action buttons
│   ├── AuditList.php                # Audit trail list display
│   ├── Audit.php                    # Audit logging functionality
│   ├── Communications.php           # Communication management
│   ├── ContactsList.php             # Contact list display
│   ├── Contacts.php                 # Contact management
│   ├── Database.php                 # Base database connection class
│   ├── EditDeleteTable.php          # Table with edit/delete functionality
│   ├── FormComponents.php           # Form building utilities
│   ├── Helpers.php                  # Utility functions
│   ├── InternalErrors.php           # Internal error handling
│   ├── LeadsList_orginal.php        # Original leads list (backup)
│   ├── LeadsListTable.php           # Leads list table display
│   ├── Leads.php                    # Lead management
│   ├── Logit.php                    # Logging utilities
│   ├── Nonce.php                    # CSRF protection
│   ├── Notes.php                    # Notes functionality
│   ├── PermissionsList.php          # Permissions list display
│   ├── Permissions.php              # Permission management
│   ├── PhpErrorLog.php              # PHP error logging
│   ├── RolesList.php                # Roles list display
│   ├── RolesPermissionsList.php     # Role-permission mapping display
│   ├── RolesPermissions.php         # Role-permission management
│   ├── Roles.php                    # Role management
│   ├── Sales.php                    # Sales functionality
│   ├── Security.php                 # Authentication and authorization
│   ├── Sessions.php                 # Session management
│   ├── Table.php                    # Base table display functionality
│   ├── UsersList.php                # Users list display
│   ├── Users.php                    # User management
│   └── ViewTable.php                # Read-only table display
├── config/                          # Configuration files
│   ├── cronconfig.php               # Cron job configuration
│   ├── ftpconfig.php                # FTP configuration
│   ├── helpers.php                  # Helper configuration
│   └── system.php                   # System configuration
├── examples/                        # Example implementations
│   └── notes_integration_examples.php # Notes system examples
├── public_html/                     # Web-accessible files
│   ├── admin/                       # Administrative tools
│   │   ├── languages/               # Language files
│   │   │   ├── login/               # Login-specific translations
│   │   │   │   ├── en.php           # English login translations
│   │   │   │   ├── es.php           # Spanish login translations
│   │   │   │   └── template.php     # Translation template
│   │   │   ├── en.php               # English translations
│   │   │   └── _es.php              # Spanish translations
│   │   ├── original/                # Original/backup files
│   │   │   ├── leads/               # Original lead files
│   │   │   └── logo files           # Original logos
│   │   ├── run_notes_migration.php  # Notes migration runner
│   │   └── test_notes_migration.php # Notes migration tester
│   ├── assets/                      # Static assets
│   │   ├── css/                     # Stylesheets
│   │   │   ├── webfonts/            # Font Awesome fonts
│   │   │   ├── all.css              # Font Awesome CSS
│   │   │   ├── bootstrap.min.css    # Bootstrap framework
│   │   │   ├── forms.css            # Form styling
│   │   │   ├── login.css            # Login page styling
│   │   │   └── style.css            # Main application styles
│   │   ├── help/                    # Help documentation
│   │   │   ├── administration.php   # Admin help
│   │   │   ├── contacts.php         # Contacts help
│   │   │   ├── properties.php       # Properties help
│   │   │   ├── reports.php          # Reports help
│   │   │   └── [other help files]   # Various help topics
│   │   ├── img/                     # Images and icons
│   │   │   ├── logo.svg             # Application logo
│   │   │   └── [favicon files]      # Browser icons
│   │   └── js/                      # JavaScript files
│   │       ├── user/                # User-specific scripts
│   │       ├── conditional-forms.js # Dynamic form behavior
│   │       ├── countdown.js         # Countdown functionality
│   │       ├── edit-leads.js        # Lead editing scripts
│   │       ├── general.js           # General utilities
│   │       ├── hide-empty-structure.js # UI optimization
│   │       └── validator.min.js     # Form validation
│   ├── contacts/                    # Contact management
│   │   ├── call_order_list.php      # Call order listing
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
│   │   └── view.php                 # Lead viewing
│   ├── reports/                     # Reporting system
│   │   ├── contacts/                # Contact reports
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
│   │   ├── roles/                   # Role management
│   │   └── roles_permissions/       # Role-permission mapping
│   ├── templates/                   # HTML templates
│   │   ├── body.php                 # Page body template
│   │   ├── footer.php               # Page footer
│   │   ├── header.php               # Page header
│   │   ├── nav*.php                 # Navigation components
│   │   ├── list_*.php               # List display components
│   │   └── section_*.php            # Section components
│   ├── users/                       # User management
│   │   ├── delete.php               # User deletion
│   │   ├── edit.php                 # User editing
│   │   ├── get.php                  # User retrieval
│   │   ├── list.php                 # User listing
│   │   ├── new.php                  # New user creation
│   │   ├── post.php                 # User form processing
│   │   └── view.php                 # User viewing
│   ├── dashboard.php                # Main dashboard
│   ├── index.php                    # Application entry point
│   ├── login.php                    # Login page
│   ├── logout.php                   # Logout handler
│   └── post.php                     # General form processor
├── scripts/                         # Utility scripts
│   ├── migrate_notes.php            # Notes migration script
│   └── test_migration.php           # Migration testing
├── sql/                            # Database scripts
│   ├── create_leads_notes_table.sql # Notes table creation
│   ├── democrm_democrm.sql          # Main database schema
│   ├── leads_comparison.sql         # Leads comparison queries
│   ├── leads.sql                    # Leads table schema
│   └── notes.sql                    # Notes table schema
├── composer.json                    # Composer dependencies
├── composer.lock                    # Dependency lock file
├── .gitignore                       # Git ignore rules
├── .htaccess                        # Apache configuration
├── project_context.txt              # Project context documentation
└── README.md                        # Project documentation
```

## File Naming Conventions

- **Controllers**: `action.php` (e.g., `edit.php`, `list.php`, `new.php`)
- **Classes**: `PascalCase.php` (e.g., `Leads.php`, `ContactsList.php`)
- **Templates**: `snake_case.php` (e.g., `nav_item_leads.php`)
- **Assets**: Standard web naming (e.g., `style.css`, `edit-leads.js`)

## Key Architectural Patterns

- **MVC Structure**: Controllers in `public_html/`, Models in `classes/`, Views in `templates/`
- **Entity Organization**: Each entity (leads, contacts, users) has its own directory
- **CRUD Operations**: Consistent `list.php`, `new.php`, `edit.php`, `view.php`, `delete.php` pattern
- **Security Layer**: Centralized in `classes/Security.php` and `classes/Nonce.php`
- **Database Layer**: All models extend `classes/Database.php`
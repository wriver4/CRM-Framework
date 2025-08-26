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

## File Tree
```
/workspace/
├── CLAUDE.md                          # Project documentation and guidelines
├── README.md                          # Project overview
├── composer.json                      # PHP dependencies
├── composer.lock                      # Dependency lock file
├── classes/                           # Core business logic and models
│   ├── Database.php                   # PDO database connection class
│   ├── Leads.php                      # Lead management model
│   ├── LeadsEnhanced.php             # Enhanced lead functionality
│   ├── LeadsList.php                 # Lead listing functionality
│   ├── Contacts.php                  # Contact management model
│   ├── ContactsEnhanced.php          # Enhanced contact functionality
│   ├── ContactsList.php              # Contact listing functionality
│   ├── Users.php                     # User management model
│   ├── UsersList.php                 # User listing functionality
│   ├── Notes.php                     # Notes system model
│   ├── Security.php                  # Authentication and security
│   ├── Sessions.php                  # Session management
│   ├── Audit.php                     # Audit trail logging
│   ├── AuditList.php                 # Audit log listing
│   ├── Permissions.php               # Permission management
│   ├── PermissionsList.php           # Permission listing
│   ├── Roles.php                     # Role management
│   ├── RolesList.php                 # Role listing
│   ├── RolesPermissions.php          # Role-permission mapping
│   ├── RolesPermissionsList.php      # Role-permission listing
│   ├── Nonce.php                     # CSRF protection
│   ├── Table.php                     # Base table class
│   ├── ViewTable.php                 # Read-only table display
│   ├── EditDeleteTable.php           # Editable table with actions
│   ├── ActionTable.php               # Action buttons for tables
│   ├── FormComponents.php            # Reusable form elements
│   ├── Helpers.php                   # Utility functions
│   ├── Communications.php            # Communication logging
│   ├── Sales.php                     # Sales pipeline management
│   ├── Logit.php                     # General logging utility
│   ├── InternalErrors.php            # Internal error handling
│   └── PhpErrorLog.php               # PHP error log management
├── config/                           # System configuration
│   ├── system.php                    # Core system settings
│   ├── helpers.php                   # Configuration helpers
│   ├── cronconfig.php               # Cron job configuration
│   └── ftpconfig.php                # FTP configuration
├── public_html/                      # Web-accessible files
│   ├── index.php                     # Main entry point
│   ├── dashboard.php                 # User dashboard
│   ├── login.php                     # Login controller
│   ├── logout.php                    # Logout handler
│   ├── post.php                      # General POST handler
│   ├── leads/                        # Lead management controllers
│   │   ├── list.php                  # Lead listing
│   │   ├── new.php                   # Create new lead
│   │   ├── edit.php                  # Edit lead
│   │   ├── delete.php                # Delete lead
│   │   ├── view.php                  # View lead details
│   │   ├── get.php                   # Lead data retrieval
│   │   ├── post.php                  # Lead form processing
│   │   ├── notes_ajax.php            # AJAX notes handling
│   │   ├── compare_notes.php         # Notes comparison tool
│   │   └── communications/           # Lead communications
│   │       ├── list.php              # Communication history
│   │       └── log.php               # Communication logging
│   ├── contacts/                     # Contact management controllers
│   │   ├── list.php                  # Contact listing
│   │   ├── new.php                   # Create new contact
│   │   ├── edit.php                  # Edit contact
│   │   ├── delete.php                # Delete contact
│   │   ├── view.php                  # View contact details
│   │   ├── get.php                   # Contact data retrieval
│   │   ├── post.php                  # Contact form processing
│   │   └── call_order_list.php       # Contact call ordering
│   ├── users/                        # User management controllers
│   │   ├── list.php                  # User listing
│   │   ├── new.php                   # Create new user
│   │   ├── edit.php                  # Edit user
│   │   ├── delete.php                # Delete user
│   │   ├── view.php                  # View user details
│   │   ├── get.php                   # User data retrieval
│   │   └── post.php                  # User form processing
│   ├── security/                     # Security management
│   │   ├── roles/                    # Role management
│   │   │   ├── list.php              # Role listing
│   │   │   ├── new.php               # Create role
│   │   │   ├── edit_role.php         # Edit role
│   │   │   ├── delete.php            # Delete role
│   │   │   ├── view.php              # View role details
│   │   │   ├── get.php               # Role data retrieval
│   │   │   └── post.php              # Role form processing
│   │   ├── permissions/              # Permission management
│   │   │   ├── list.php              # Permission listing
│   │   │   ├── new.php               # Create permission
│   │   │   ├── delete.php            # Delete permission
│   │   │   ├── get.php               # Permission data retrieval
│   │   │   ├── post.php              # Permission form processing
│   │   │   └── assign_role_permissions.php # Role-permission assignment
│   │   └── roles_permissions/        # Role-permission mapping
│   │       ├── list.php              # Mapping listing
│   │       ├── delete.php            # Delete mapping
│   │       ├── get.php               # Mapping data retrieval
│   │       └── post.php              # Mapping form processing
│   ├── reports/                      # Reporting system
│   │   ├── index.php                 # Report dashboard
│   │   ├── list.php                  # Report listing
│   │   ├── get.php                   # Report data retrieval
│   │   ├── contacts/                 # Contact reports
│   │   │   └── all.php               # All contacts report
│   │   └── reports/                  # Specific report types
│   │       ├── customer_activity.php # Customer activity report
│   │       └── sales_performance.php # Sales performance report
│   ├── sales/                        # Sales management
│   │   └── pipeline.php              # Sales pipeline view
│   ├── customers/                    # Customer management
│   │   └── list.php                  # Customer listing
│   ├── profile/                      # User profile management
│   │   ├── cleanup_duplicate_notes.php # Note cleanup utility
│   │   └── cleanup_duplicate_notes_do.php # Note cleanup execution
│   ├── templates/                    # Reusable HTML components
│   │   ├── header.php                # Page header template
│   │   ├── footer.php                # Page footer template
│   │   ├── body.php                  # Body container template
│   │   ├── nav.php                   # Main navigation
│   │   ├── nav_start.php             # Navigation opening
│   │   ├── nav_end.php               # Navigation closing
│   │   ├── nav_item_leads_list.php   # Leads navigation item
│   │   ├── nav_item_leads_new.php    # New lead navigation item
│   │   ├── nav_item_contacts.php     # Contacts navigation item
│   │   ├── nav_item_users.php        # Users navigation item
│   │   ├── nav_item_reports.php      # Reports navigation item
│   │   ├── nav_item_profile.php      # Profile navigation item
│   │   ├── section_header.php        # Section header template
│   │   ├── section_footer.php        # Section footer template
│   │   ├── section_open.php          # Section opening
│   │   ├── section_close.php         # Section closing
│   │   ├── list_open.php             # List container opening
│   │   ├── list_close.php            # List container closing
│   │   ├── list_clock.php            # Clock display
│   │   └── list_buttons.php          # Action buttons
│   ├── assets/                       # Static assets
│   │   ├── css/                      # Stylesheets
│   │   │   ├── bootstrap.min.css     # Bootstrap framework
│   │   │   ├── style.css             # Custom styles
│   │   │   ├── forms.css             # Form-specific styles
│   │   │   ├── login.css             # Login page styles
│   │   │   ├── all.css               # Combined stylesheet
│   │   │   └── webfonts/             # Font files
│   │   ├── js/                       # JavaScript files
│   │   │   ├── general.js            # General utilities
│   │   │   ├── edit-leads.js         # Lead editing functionality
│   │   │   ├── conditional-forms.js  # Dynamic form behavior
│   │   │   ├── countdown.js          # Countdown timers
│   │   │   ├── hide-empty-structure.js # UI optimization
│   │   │   ├── validator.min.js      # Form validation
│   │   │   └── user/                 # User-specific scripts
│   │   │       ├── edit.js           # User editing
│   │   │       └── new.js            # User creation
│   │   ├── img/                      # Images and icons
│   │   │   ├── logo.svg              # Application logo
│   │   │   ├── browserconfig.xml     # Browser configuration
│   │   │   ├── site.webmanifest      # Web app manifest
│   │   │   └── safari-pinned-tab.svg # Safari icon
│   │   └── help/                     # Help documentation
│   │       ├── index.php             # Help index
│   │       ├── this_app.php          # Application help
│   │       ├── administration.php    # Admin help
│   │       ├── contacts.php          # Contacts help
│   │       ├── users.php             # Users help
│   │       ├── reports.php           # Reports help
│   │       ├── properties.php        # Properties help
│   │       ├── systems.php           # Systems help
│   │       ├── testing.php           # Testing help
│   │       ├── tickets.php           # Tickets help
│   │       ├── status.php            # Status help
│   │       ├── common_elements.php   # Common UI help
│   │       └── get.php               # Help data retrieval
│   ├── admin/                        # Administrative tools
│   │   ├── languages/                # Internationalization
│   │   │   ├── en.php                # English translations
│   │   │   ├── _es.php               # Spanish translations
│   │   │   └── login/                # Login-specific translations
│   │   │       ├── en.php            # English login text
│   │   │       ├── es.php            # Spanish login text
│   │   │       └── template.php      # Translation template
│   │   ├── leads/                    # Lead admin tools
│   │   │   ├── delete_note.php       # Note deletion tool
│   │   │   ├── debug_delete_note.php # Debug deletion issues
│   │   │   ├── minimal_delete.php    # Minimal deletion test
│   │   │   ├── test_delete_simple.php # Simple deletion test
│   │   │   ├── test_note_delete.php  # Note deletion test
│   │   │   ├── test_endpoint.php     # Endpoint testing
│   │   │   ├── simple_test.php       # Simple functionality test
│   │   │   ├── edit.php              # Lead editing admin
│   │   │   ├── get.php               # Lead data admin
│   │   │   ├── list.php              # Lead listing admin
│   │   │   ├── post.php              # Lead processing admin
│   │   │   ├── test_minimal.html     # Minimal test page
│   │   ├── logs/                     # Log management
│   │   │   ├── audit.php             # Audit log viewer
│   │   │   ├── internal.php          # Internal log viewer
│   │   │   └── phperror.php          # PHP error log viewer
│   │   ├── run_notes_migration.php   # Notes migration runner
│   │   └── test_notes_migration.php  # Notes migration test
│   └── INSTALLATION.md               # Installation instructions
├── scripts/                          # Command-line scripts
│   ├── migrate_leads_to_contacts.php # Lead-to-contact migration
│   ├── migrate_notes.php             # Notes migration script
│   └── test_migration.php            # Migration testing
├── sql/                              # Database schemas and migrations
│   ├── leads.sql                     # Leads table schema
│   ├── notes.sql                     # Notes table schema
│   ├── leads_notes.sql               # Lead-notes relationship
│   ├── lead_contact_integration_migration.sql # Integration migration
│   ├── analyze_and_solve_duplicate_notes.sql # Duplicate analysis
│   ├── cleanup_duplicate_notes_simple.sql # Cleanup script
│   ├── create_duplicate_notes_table.sql # Duplicate tracking
│   ├── duplicate_notes_per_lead.sql  # Lead duplicate analysis
│   ├── lead_duplicate_notes_analysis.sql # Detailed analysis
│   ├── lead_duplicate_notes_solution.sql # Solution script
│   └── 25-08-15_10-43_Leads Entry.csv # Sample data
├── logs/                             # Application logs
│   ├── delete_note_errors.log        # Note deletion errors
│   ├── minimal_test.log              # Minimal test output
│   └── php_errors.log                # PHP error log
├── examples/                         # Code examples
│   └── notes_integration_examples.php # Notes integration examples
├── vendor/                           # Composer dependencies
│   └── [composer packages]          # Third-party libraries
├── debug_delete_note.php             # Standalone debug script
├── project_context.txt               # Additional project context
├── test_audit_fix.php                # Audit system test
├── test_all_audit_fixes.php          # Comprehensive audit test
├── test_delete_fix.html              # Delete functionality test
├── test_delete_note_ajax.php         # AJAX deletion test
└── test_delete_note_js.html          # JavaScript deletion test
```

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
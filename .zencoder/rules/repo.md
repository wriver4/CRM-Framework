---
description: Repository Information Overview
alwaysApply: true
---

# CRM Framework Information

## Summary
A PHP-based CRM (Customer Relationship Management) framework providing functionality for managing leads, contacts, users, and sales pipelines. The application follows a traditional PHP web application structure with database connectivity, user management, and table display functionality. The application is multilingual with language files stored in arrays in the admin/languages folder, and helper classes to support iternationalization. The application also includes a reporting module that generates various types of reports based on different criteria. It includes features like CRUD operations, role-based access control, and audit trails. It uses Boostrap 5 and Bootstrap Icons for frontend design as well as Font awesome for icons. There are other javascript tools used such validator.js and  jquery and datatable.js.

## Server & Access Configuration

### SSH Configuration
This project is hosted on a remote server accessible via SSH with certificate-based authentication.

**SSH Config Entry** (in `~/.ssh/config`):
```
Host wswg
    HostName 159.203.116.150
    Port 222
    User root
    IdentityFile ~/.ssh/wswg_key
    IdentitiesOnly yes
```

**SSH Key Setup:**
```bash
# Generate SSH key pair (if not already done)
ssh-keygen -t rsa -b 4096 -f ~/.ssh/wswg_key -C "your-email@example.com"

# Copy public key to server (replace with your actual public key)
# Public key content should be added to server's ~/.ssh/authorized_keys
cat ~/.ssh/wswg_key.pub
```

**Public Key for Server Setup:**
```
# Copy this public key to the server's ~/.ssh/authorized_keys file
# Replace with your actual public key content:
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIOtBZ5F327lNczQ76KxK1ibJ8wl/cMh1R8DvZh/uB3LP mark@king
```

**Access Commands:**
```bash
# SSH to server (no password required with key)
ssh wswg

# Copy files from server
scp wswg:/path/to/file ./local/path

# Copy files to server  
scp ./local/file wswg:/path/to/destination

# Run commands on server
ssh wswg "command to run"
```

**Troubleshooting SSH:**
```bash
# Test SSH connection
ssh -v wswg

# Check key permissions (should be 600)
chmod 600 ~/.ssh/wswg_key
chmod 644 ~/.ssh/wswg_key.pub

# Add key to SSH agent
ssh-add ~/.ssh/wswg_key
```

### SSL Certificates
The project uses SSL certificates located in the `ssl/` directory:
- `ssl/autossl/` - Auto SSL certificates
- `ssl/commercial/` - Commercial SSL certificates

**Live URL:** https://democrm.waveguardco.net

### Multi-Project Server Notes
This server hosts multiple projects. When working with this repository:
- Always use absolute paths when referencing files
- Be aware of shared resources and dependencies
- Check ownership/permissions if encountering access issues
- Use the SSH alias `wswg` for consistent access

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

### Database Class Architecture

The `Database` class differs from standard database implementations in several key ways:

**Unique Characteristics:**
- **Singleton Pattern with Static Connection**: Uses a static `$DBCRM` variable to maintain a single database connection across all instances
- **Embedded Configuration**: Database credentials are hardcoded in the constructor rather than using external config files
- **Direct PDO Access**: The `dbcrm()` method returns the PDO instance directly for use by extending classes
- **Inheritance-Based**: All model classes extend `Database` to inherit the connection method

**Connection Details:**
```php
// Database credentials (embedded in Database.php)
$this->crm_host = 'localhost';
$this->crm_database = 'democrm_democrm';
$this->crm_username = 'democrm_democrm';
$this->crm_password = 'b3J2sy5T4JNm60';
```

**PDO Configuration:**
- **Error Mode**: `PDO::ERRMODE_EXCEPTION` - Throws exceptions on errors
- **Fetch Mode**: `PDO::FETCH_ASSOC` - Returns associative arrays by default
- **Prepared Statements**: `PDO::ATTR_EMULATE_PREPARES => false` - Uses native prepared statements

**Usage Pattern:**
```php
class ExampleModel extends Database {
    public function getData() {
        $pdo = $this->dbcrm(); // Get PDO instance
        $stmt = $pdo->prepare("SELECT * FROM table");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
```

### Database Constraints & Limitations
- **No information_schema queries**: Due to root@localhost permission issues, avoid using `information_schema` tables in SQL scripts
- **Use alternative approaches**: Use `SHOW CREATE TABLE`, `SHOW TABLES`, `DESCRIBE table_name` instead
- **Foreign key management**: Use `SHOW CREATE TABLE` to view constraints before modification
- **Single Database**: The system is designed for a single database connection only

## Multilingual System

The application features a comprehensive multilingual system supporting multiple languages with centralized translation management.

### Language Architecture

**Language Files Location**: `public_html/admin/languages/`

**Supported Languages**:
- **English** (`en.php`) - Primary language
- **Spanish** (`es.php`, `_es_complete.php`) - Complete Spanish translations
- **Login-specific translations** (`login/en.php`, `login/es.php`) - Specialized login translations

**Language File Structure**:
```php
<?php
$lang = [
    // Navbar translations
    'navbar_tooltip_title' => "Home",
    'navbar_contacts' => "Contacts",
    'navbar_leads_new' => "New Lead Entry",
    
    // Form elements
    'full_name' => 'Full Name',
    'username' => 'Username',
    'password' => 'Password',
    
    // Role translations
    'role_id_1' => 'Administrator',
    'role_id_2' => 'Manager',
    
    // System states
    'state_id_1' => 'Active',
    'state_id_2' => 'Inactive',
    
    // Lead-specific translations
    'lead_service_wildfire_spray' => 'Exterior Wildfire Spray System',
    'lead_structure_rambler' => 'Rambler - One Story',
    
    // Geographic translations
    'US-CA' => 'California',
    'US-TX' => 'Texas',
];
```

### Helpers Class - Multilingual Support

The `Helpers` class (`classes/Helpers.php`) extends `Database` and provides comprehensive multilingual support utilities:

**Key Multilingual Methods**:

1. **Role Management**:
   - `get_role_array($lang)` - Returns role ID to translated name mapping
   - `select_role($lang, $rid)` - Generates HTML select options for roles

2. **System States**:
   - `get_system_state_array($lang)` - System state translations
   - `select_system_state($lang, $state_id)` - HTML select for system states

3. **Contact Types**:
   - `get_contact_type_array($lang)` - Contact type translations
   - `select_contact_type($lang, $contact_id)` - HTML select for contact types

4. **Geographic Data**:
   - `get_us_states_array($lang)` - US state translations
   - `get_countries_array($lang)` - Country translations
   - `select_us_state($lang, $state)` - HTML select for US states

5. **Lead-Specific Data**:
   - `get_lead_services_array($lang)` - Lead service translations
   - `get_lead_structure_description_array($lang)` - Structure descriptions
   - `get_lead_hear_about_array($lang)` - "How did you hear about us" options
   - `get_lead_source_array($lang)` - Lead source translations

**Usage Pattern**:
```php
// Load language file
$lang = include 'public_html/admin/languages/en.php';

// Use Helpers class for multilingual data
$helpers = new Helpers();
$roles = $helpers->get_role_array($lang);
$helpers->select_role($lang, $current_role_id);
```

**Multilingual Features**:
- **Centralized translations**: All text stored in language arrays
- **Dynamic HTML generation**: Form elements generated with proper translations
- **Consistent data structures**: Standardized array formats across all multilingual data
- **Template integration**: Language variables passed to templates for rendering

### Language File Organization

**Main Language Files**:
- `en.php` - Complete English translations
- `_es_complete.php` - Complete Spanish translations
- `_es.php` - Partial Spanish translations (legacy)

**Specialized Language Files**:
- `login/en.php` - Login-specific English translations
- `login/es.php` - Login-specific Spanish translations
- `login/template.php` - Translation template for new languages

**Translation Categories**:
- Navigation and UI elements
- Form labels and placeholders
- System states and roles
- Geographic data (states, countries)
- Lead management terminology
- Error messages and notifications
- Button labels and actions

## Core Components
**Models**:
- Database: Base database connection class with singleton pattern
- Helpers: Multilingual support and utility functions
- Users: User management
- Leads: Lead management
- Contacts: Contact management
- Notes: Notes functionality
- Roles/Permissions: Security management

**Views**:
- Templates system with header, footer, navigation components
- Table display classes (ViewTable, Table)
- Multilingual form components

**Controllers**:
- Module-specific controllers in public_html directories (users/, leads/, contacts/)
- Language-aware form processing

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

The project includes two comprehensive testing systems: **Playwright** for end-to-end web interface testing and **PHPUnit** for unit, integration, and feature testing.

### Testing Directory Structure

```
tests/
├── bootstrap.php                    # PHPUnit bootstrap configuration
├── README.md                        # Testing documentation
├── check_users.php                  # User verification utility
├── create_test_users.php            # Test user creation utility
├── test_summary.php                 # Test results summary
├── verify_test_login.php            # Login verification utility
├── web_test.sh                      # Web testing shell script
├── playwright/                      # Playwright E2E tests
│   ├── accessibility.spec.js        # Accessibility testing
│   ├── auth-helper.js               # Authentication helper utilities
│   ├── authenticated-tests.spec.js  # Tests requiring authentication
│   ├── example.spec.js              # Example test patterns
│   ├── login.spec.js                # Login functionality tests
│   ├── navigation.spec.js           # Navigation testing
│   ├── remote-crm.spec.js           # Remote CRM specific tests
│   ├── responsive.spec.js           # Responsive design tests
│   └── test-credentials.js          # Test credential management
├── phpunit/                         # PHPUnit structured tests
│   ├── TestCase.php                 # Base test case class
│   ├── Unit/                        # Unit tests
│   │   ├── HelpersTest.php          # Helpers class unit tests
│   │   └── SimpleTest.php           # Simple unit test examples
│   ├── Integration/                 # Integration tests
│   │   └── DatabaseTest.php         # Database integration tests
│   ├── Feature/                     # Feature tests
│   │   └── LoginTest.php            # Login feature tests
│   └── Remote/                      # Remote server tests
│       └── RemoteServerTest.php     # Remote server connectivity tests
└── leads/                           # Lead-specific debugging utilities
    ├── debug_delete_note.php        # Note deletion debugging
    ├── delete_note_fixed.php        # Fixed note deletion implementation
    ├── minimal_delete.php           # Minimal deletion test
    ├── simple_test.php              # Simple lead test
    ├── test_classes_only.php        # Class-only testing
    ├── test_delete_simple.php       # Simple deletion test
    ├── test_endpoint.php            # Endpoint testing
    ├── test_minimal.html            # Minimal HTML test
    ├── test_note_delete.php         # Note deletion test
    └── test_note_delete_fixed.php   # Fixed note deletion test
```

### 1. Playwright Testing System

**Purpose**: End-to-end web interface testing across multiple browsers and devices.

**Configuration Files:**
- `playwright.config.js` - Main configuration for remote testing
- `playwright-local.config.js` - Local testing configuration (if exists)
- `run-tests-nixos.sh` - Test runner script for NixOS

**Key Features:**
- **Multi-browser testing**: Chrome, Firefox, Safari, Mobile Chrome, Mobile Safari
- **Remote testing**: Configured to test against `https://democrm.waveguardco.net`
- **Authentication helpers**: Reusable login utilities in `auth-helper.js`
- **Responsive testing**: Mobile and desktop viewport testing
- **Accessibility testing**: WCAG compliance verification
- **Visual regression**: Screenshot and video capture on failures

**Running Playwright Tests:**
```bash
# On server (NixOS)
./run-tests-nixos.sh test

# Local development
npm install @playwright/test
npx playwright test

# Copy configuration from server
scp wswg:/home/democrm/playwright-local.config.js ./playwright.config.js
scp wswg:/home/democrm/tests/playwright/*.js ./tests/
```

### 2. PHPUnit Testing System

**Purpose**: Unit, integration, and feature testing of PHP classes and functionality.

**Configuration Files:**
- `phpunit.xml` - PHPUnit configuration with test suites
- `tests/bootstrap.php` - Bootstrap file with autoloading and environment setup

**Test Suites:**
- **Unit Tests** (`tests/phpunit/Unit/`): Test individual classes and methods
- **Integration Tests** (`tests/phpunit/Integration/`): Test component interactions
- **Feature Tests** (`tests/phpunit/Feature/`): Test complete user workflows
- **Remote Tests** (`tests/phpunit/Remote/`): Test remote server connectivity

**Key Features:**
- **Environment isolation**: Testing environment variables
- **Custom TestCase base class**: Shared testing utilities
- **Database testing**: Integration with live database
- **Remote server testing**: Connectivity and functionality verification
- **Multilingual testing**: Helper class internationalization testing

**Running PHPUnit Tests:**
```bash
# Run all test suites
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration
./vendor/bin/phpunit --testsuite Feature

# Run specific test file
./vendor/bin/phpunit tests/phpunit/Unit/HelpersTest.php
```

### 3. Manual Testing Utilities

**Lead Testing Utilities** (`tests/leads/`):
- Debugging utilities for lead functionality
- Note deletion testing and fixes
- Endpoint testing utilities
- Minimal test implementations for troubleshooting

**User Management Utilities**:
- `create_test_users.php` - Creates test users for testing
- `check_users.php` - Verifies user accounts
- `verify_test_login.php` - Tests login functionality

**Test Environment Setup:**
- Environment variables configured in `phpunit.xml`
- Bootstrap file handles autoloading and test environment setup
- Base URL configured for remote testing: `https://democrm.waveguardco.net`

## Directory Structure

```
.
├── archive/                         # Archived files
│   └── leads/                       # Archived lead files
│       ├── post.php                 # Original lead processing (archived)
│       └── post_with_contact_integration.php # Contact integration version (archived)
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
│   │   ├── post.php                 # Lead form processing with contact integration
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
- **Never use closing PHP tags (`?>`)** at the end of PHP-only files - this is bad practice and can cause whitespace issues

### File Operations
- **File Ownership**: 
  - **Local Machine**: Files should be owned by `mark:users` user and group
  - **Remote Server**: Files should be owned by `democrm:democrm` user and group
- After creating files:
  - Local: Run `chown mark:users filename` if needed
  - Remote: Run `chown democrm:democrm filename` if needed
- Use `chmod 644` for regular files and `chmod 755` for executable files
- Ensure proper permissions for web server access

## Key Architectural Patterns

**Note**: This is **NOT** a traditional MVC framework. It follows a more direct, procedural approach with object-oriented components.

### Architecture Overview
- **Direct File Structure**: Controllers are individual PHP files in `public_html/` directories
- **Class-Based Models**: Business logic classes in `classes/` directory extending `Database`
- **Template System**: Reusable HTML components in `templates/` directory
- **Multilingual Support**: Centralized language files with `Helpers` class integration

### Core Patterns
- **Entity Organization**: Each entity (leads, contacts, users) has its own directory with consistent file naming
- **CRUD Operations**: Standardized `list.php`, `new.php`, `edit.php`, `view.php`, `delete.php` pattern
- **Database Inheritance**: All models extend `classes/Database.php` for connection access
- **Security Layer**: Centralized authentication in `classes/Security.php` and CSRF protection via `classes/Nonce.php`
- **Multilingual Integration**: `Helpers` class provides translation-aware form generation and data handling

### File Processing Flow
1. **Request Routing**: Direct file access (e.g., `/leads/list.php`)
2. **Authentication**: Security checks via `Security` class
3. **Language Loading**: Include appropriate language file from `admin/languages/`
4. **Data Processing**: Use model classes extending `Database`
5. **Template Rendering**: Include template components with language variables
6. **Response**: Direct HTML output with multilingual content

### Unique Characteristics
- **No Framework Dependencies**: Pure PHP with minimal external libraries
- **Direct Database Access**: PDO connections through inheritance rather than dependency injection
- **Template Inclusion**: PHP `include` statements rather than template engines
- **Language Arrays**: Simple PHP arrays for translations rather than complex i18n systems
- **Static Connections**: Singleton database pattern for connection reuse
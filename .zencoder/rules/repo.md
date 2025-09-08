---
description: Repository Information Overview
alwaysApply: true
---

# CRM Framework Information

## Summary
A PHP-based CRM (Customer Relationship Management) framework providing functionality for managing leads, contacts, users, and sales pipelines. **This is NOT a traditional MVC framework** - it follows a direct, procedural approach with object-oriented components. The architecture uses direct file routing (no URL rewriting), database inheritance patterns (all models extend a singleton Database class), and template inclusion rather than dependency injection or modern framework patterns. The application features a comprehensive multilingual system with language arrays stored in `admin/languages/` and a specialized `Helpers` class that generates translation-aware HTML components and form elements. It includes CRUD operations, role-based access control, audit trails, and reporting capabilities. The frontend uses Bootstrap 5, Bootstrap Icons, Font Awesome, jQuery, DataTables, and Validator.js for a modern user interface.

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

## Structure
- **classes/**: Core framework classes for database, security, and business logic
- **config/**: Configuration files for system settings
- **public_html/**: Web-accessible files including controllers and views
- **scripts/**: Utility scripts for data migration
- **sql/**: Database schema (`democrm_democrm_structure.sql`) and migration scripts
- **vendor/**: Composer dependencies
- **logs/**: Application logs
- **templates/**: HTML templates and components

## Entry Points
**Main Entry**: public_html/index.php
**Dashboard**: public_html/dashboard.php
**Authentication**: public_html/login.php, public_html/logout.php

## Configuration
**System Config**: config/system.php
**Database Config**: Embedded in classes/Database.php
**Path Constants**: Defined in config/system.php
**Network Utilities**: Helpers class (IP detection, geolocation, session validation)
**Session Security**: Prepared settings in config/system.php (commented out for safety)
**Sessions Management**: Enhanced Sessions class in classes/Core/Sessions.php

### System Constants Reference
All constants are defined in `config/system.php` for performance and consistency:

#### **File System Paths**
- `DOCROOT` - Application root directory
- `DOCPUBLIC` - Public HTML directory (`$_SERVER['DOCUMENT_ROOT']`)
- `DOCTEMPLATES` - Templates directory path
- `HEADER`, `BODY`, `NAV`, `FOOTER` - Template component paths
- `LISTOPEN`, `LISTBUTTONS`, `LISTCLOSE` - List template components
- `SECTIONOPEN`, `SECTIONCLOSE` - Section template components

#### **URL Constants**
- `URL` - Base application URL (`https://domain.com`)
- `TEMPLATES` - Templates URL path
- `ASSETS` - Static assets URL path
- `IMG`, `CSS`, `JS` - Asset-specific URL paths
- `SECURITY` - Security module URL

#### **Module URL Constants** *(Performance Optimized)*
- `LEADS` - Leads module URL (`/leads`)
- `CONTACTS` - Contacts module URL (`/contacts`) 
- `ADMIN` - Admin module URL (`/admin`)
- `REPORTS` - Reports module URL (`/reports`)

#### **Application Settings**
- `LANG` - Language files directory
- `LOGINLANG` - Login-specific language files
- `VALIDEMAIL` - Email validation regex pattern
- `NONCE_SECRET` - CSRF protection secret key

## Database Architecture & Rules

### Database Connection
- **Host**: localhost
- **Database**: democrm_democrm  
- **Connection**: PDO with UTF-8 charset
- **Pattern**: Singleton connection reuse via `Database` class inheritance
- **Schema**: Complete structure in `/sql/democrm_democrm_structure.sql`

### Critical Database Rules & Constraints

**⚠️ IMPORTANT**: This database environment has specific permission restrictions that affect troubleshooting and development approaches.

#### **Metadata Query Restrictions**
```php
// ❌ FORBIDDEN: These queries will fail with #1044 Access Denied
$stmt = $pdo->query("SHOW TABLES");                    // Access denied
$stmt = $pdo->query("SHOW TABLES LIKE 'table_name'");  // Access denied  
$stmt = $pdo->query("SELECT * FROM information_schema.tables"); // Access denied
$stmt = $pdo->query("DESCRIBE table_name");            // May fail
```

#### **✅ APPROVED: Direct Table Access Method**
```php
// ✅ CORRECT: Test table existence by direct query
foreach ($required_tables as $table) {
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM `$table` LIMIT 1");
        $stmt->execute();
        // Table exists and is accessible
    } catch (Exception $e) {
        // Table missing or inaccessible - parse error message
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            // Table does not exist
        } else {
            // Other access/permission issue
        }
    }
}
```

#### **Troubleshooting Guidelines**
- **❌ DO NOT use `SHOW TABLES`** for debugging - it will always fail regardless of table existence
- **❌ DO NOT use `information_schema`** queries - access is restricted
- **✅ USE direct table queries** (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) to test accessibility
- **✅ PARSE exception messages** to distinguish between "table doesn't exist" vs permission errors
- **✅ REFER to `/sql/democrm_democrm_structure.sql`** for authoritative table definitions

#### **Development Impact**
- **Table Existence Checks**: Must use direct query approach, not metadata queries
- **Schema Inspection**: Rely on structure file, not runtime schema queries  
- **Debugging**: Focus on actual data operations rather than metadata exploration
- **Error Handling**: Parse PDO exception messages for specific error types

This constraint pattern ensures that all database interactions use direct, efficient queries while avoiding potentially expensive metadata operations.

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
- **Session Management**: Enhanced `Sessions` class provides convenient static methods for session operations
- **Multilingual Integration**: `Helpers` class provides translation-aware form generation and data handling
- **Routing Variables**: Page-specific variables control conditional resource loading in templates

### Critical Framework Routing Pattern
**REQUIRED**: All PHP files in `public_html/` must include the system configuration using this exact pattern:
```php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
```

**Why This Pattern:**
- **Path Independence**: Works from any subdirectory depth (`/admin/`, `/admin/email/`, etc.)
- **Server Compatibility**: Uses `$_SERVER['DOCUMENT_ROOT']` which Apache sets to `/home/democrm/public_html`
- **Framework Foundation**: Loads autoloaders, constants, and core classes required by all pages
- **Security**: Ensures proper class loading and authentication systems are available

**Common Mistake**: Using relative paths like `../../config/system.php` breaks when directory structure changes

### File Processing Flow
1. **Request Routing**: Direct file access (e.g., `/leads/list.php`)
2. **Authentication**: Security checks via `Security` class
3. **Routing Variables**: Set page-specific variables for conditional resource loading
4. **Language Loading**: Include appropriate language file from `admin/languages/`
5. **Data Processing**: Use model classes extending `Database`
6. **Template Rendering**: Include template components with language variables
7. **Response**: Direct HTML output with multilingual content

### Unique Characteristics
- **No Framework Dependencies**: Pure PHP with minimal external libraries
- **Direct Database Access**: PDO connections through inheritance rather than dependency injection
- **Template Inclusion**: PHP `include` statements rather than template engines
- **Language Arrays**: Simple PHP arrays for translations rather than complex i18n systems
- **Static Connections**: Singleton database pattern for connection reuse

## Direct Page Routing System

This framework uses a **direct file routing system** instead of traditional MVC URL rewriting. Each PHP file in `public_html/` directories corresponds directly to a URL endpoint, with routing controlled by standardized variables that manage template inclusion, resource loading, and UI behavior.

### Core Architecture Principles

**Direct File Access Pattern:**
- `/users/list.php` → `https://domain.com/users/list`
- `/leads/edit.php` → `https://domain.com/leads/edit`
- `/security/roles/new.php` → `https://domain.com/security/roles/new`

**No URL Rewriting:** Files are accessed directly without `.htaccess` rewriting or routing controllers.

### Page Types & Routing Variables

The system recognizes **two distinct page types** with different routing patterns:

#### 1. **List Pages** (`$page = 'list'`)
**Purpose:** Display tabular data with search, pagination, and action buttons.

**Required Variables:**
```php
$dir = 'users';              // Primary module directory
$subdir = '';                // Subdirectory (empty for main modules)
$page = 'list';              // Page type identifier
$table_page = true;          // Enable DataTables resources
$table_header = true;        // Enable table header functionality

// Button Controls
$search = true;              // Enable search functionality
$paginate = true;            // Enable pagination
$button_new = true;          // Show "New" button
$button_showall = false;     // Show "Show All" button
$button_back = false;        // Show "Back" button
$button_refresh = true;      // Show "Refresh" button

// Language & Display
$title = $lang['users'];     // Page title from language file
$new_button = $lang['user_new']; // New button text
$title_icon = '<i class="fa fa-users"></i>'; // Title icon HTML
$new_icon = '<i class="fa fa-user"></i>';    // New button icon
```

**Template Inclusion Pattern:**
```php
require HEADER;              // Load header with conditional CSS/JS
require BODY;                // Load body template
require NAV;                 // Load navigation
require LISTOPEN;            // Load list container opening
require 'get.php';           // Load data and render table
require LISTCLOSE;           // Load list container closing
require FOOTER;              // Load footer with conditional JS
```

#### 2. **Standard Pages** (`$page = 'new'|'edit'|'view'|'delete'`)
**Purpose:** Forms, detail views, and single-record operations.

**Required Variables:**
```php
$dir = 'users';              // Primary module directory
$subdir = '';                // Subdirectory (if applicable)
$page = 'edit';              // Page type (new, edit, view, delete)
$table_page = false;         // Disable DataTables resources

// Language & Display
$title = $lang['user_edit']; // Page title from language file
$title_icon = '<i class="fa-solid fa-user-pen"></i>'; // Title icon
```

**Template Inclusion Pattern:**
```php
require HEADER;              // Load header
require BODY;                // Load body template
require NAV;                 // Load navigation
require SECTIONOPEN;         // Load section container opening
// Custom form/content HTML here
require SECTIONCLOSE;        // Load section container closing
require FOOTER;              // Load footer
```

### Multi-Level Directory Routing

**Extended Directory Structure Support:**
The routing system supports up to 4 levels of directory nesting:

```php
// Level 1: /users/list.php
$dir = 'users';
$page = 'list';

// Level 2: /security/roles/list.php  
$dir = 'security';
$subdir = 'roles';
$page = 'list';

// Level 3: /admin/email/system_status.php
$dir = 'admin';
$subdir = 'email';
$sub_subdir = '';
$page = 'system_status';

// Level 4: /admin/languages/login/en.php
$dir = 'admin';
$subdir = 'languages';
$sub_subdir = 'login';
$sub_sub_subdir = '';
$page = 'en';
```

**URL Generation in Templates:**
```php
// Dynamic URL generation supporting all levels
function generateUrl($dir, $subdir = '', $sub_subdir = '', $sub_sub_subdir = '', $page = '') {
    $url = '/' . $dir;
    
    if (!empty($subdir)) {
        $url .= '/' . $subdir;
    }
    
    if (!empty($sub_subdir)) {
        $url .= '/' . $sub_subdir;
    }
    
    if (!empty($sub_sub_subdir)) {
        $url .= '/' . $sub_sub_subdir;
    }
    
    if (!empty($page)) {
        $url .= '/' . $page;
    }
    
    return $url;
}

// Button generation with multi-level support
echo '<a href="' . generateUrl($dir, $subdir, $sub_subdir, $sub_sub_subdir, 'new') . '" class="btn btn-success">New</a>';
```

**Template Button Logic Update:**
```php
// Updated list_buttons.php logic for multi-level support
if (isset($button_new) && $button_new == true) {
    echo '<a href="/' . $dir;
    if(isset($subdir) && !empty($subdir)){
        echo '/' . $subdir;
    }
    if(isset($sub_subdir) && !empty($sub_subdir)){
        echo '/' . $sub_subdir;
    }
    if(isset($sub_sub_subdir) && !empty($sub_sub_subdir)){
        echo '/' . $sub_sub_subdir;
    }
    echo '/new" class="btn btn-success">' . $new_icon . '&ensp;' . $new_button . '</a>';
}
```

### Conditional Resource Loading

The routing variables control **conditional inclusion** of CSS, JavaScript, and functionality:

#### CSS Loading (header.php)
```php
// Login-specific styling
<?php if ($page == "login") { ?>
<link rel="stylesheet" href="<?php echo CSS . "/login.css"; ?>">
<?php } ?>

// DataTables CSS for list pages
<?php if ($table_page == true) { ?>
<link rel="stylesheet" href="https://cdn.datatables.net/.../datatables.min.css" />
<?php } ?>

// Auto-refresh for status monitoring
<?php if (isset($refresh) && $refresh == true && $dir == "status") { ?>
<meta http-equiv="refresh" content="<?php echo $_SESSION['refresh_time']; ?>">
<?php } ?>
```

#### JavaScript Loading (footer.php)
```php
// Form validation for new/edit pages
if ($page == 'new' || $page == 'edit') {
    echo '<script src="/assets/js/validator.min.js"></script>';
}

// Lead-specific conditional forms
if ($dir == 'leads' && $page == 'new') {
    echo '<script src="/assets/js/conditional-forms.js"></script>';
}

// DataTables for list pages
if ($table_page == true) {
    echo '<script src="https://cdn.datatables.net/.../datatables.min.js"></script>';
    
    // Module-specific DataTable configurations
    if ($dir == 'users') {
        echo 'order: [[1, "asc"]], columnDefs: [{ orderable: false, targets: [0] }]';
    }
    if ($dir == 'leads') {
        echo '"ordering": false, columnDefs: [{ orderable: false, targets: [0] }]';
    }
}
```

#### Button Controls (list_buttons.php)
```php
// Back button
if (isset($button_back) && $button_back == true) {
    echo '<a href="/' . $dir . '/list" class="btn btn-success">' . $lang['back'] . '</a>';
}

// New button with subdirectory support
if (isset($button_new) && $button_new == true) {
    echo '<a href="/' . $dir;
    if(isset($subdir) && !empty($subdir)){
        echo "/$subdir";
    }
    echo '/new" class="btn btn-success">' . $new_icon . '&ensp;' . $new_button . '</a>';
}

// Show All button
if (isset($button_showall) && $button_showall == true) {
    echo '<a href="/' . $dir . (isset($subdir) ? '/' . $subdir : '') . '/list" class="btn btn-info">';
    echo '<i class="fa fa-list"></i>&ensp;' . $lang['showall'] . '</a>';
}
```

### Standard Implementation Pattern

**Every framework page must follow this pattern:**

```php
<?php
// 1. REQUIRED: Load system configuration
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// 2. REQUIRED: Authentication check
$not->loggedin();

// 3. REQUIRED: Set routing variables
$dir = 'module_name';        // Primary directory
$subdir = '';                // Subdirectory (if applicable)
$sub_subdir = '';            // Sub-subdirectory (if applicable)
$sub_sub_subdir = '';        // Sub-sub-subdirectory (if applicable)
$page = 'page_type';         // list, new, edit, view, delete

// 4. REQUIRED: Set page behavior flags
$table_page = true;          // true for list pages, false for others

// 5. List pages only: Set button controls
$search = true;              // Enable search
$paginate = true;            // Enable pagination
$button_new = true;          // Show new button
$button_refresh = false;     // Show refresh button
// ... other button controls

// 6. REQUIRED: Load language file
require LANG . '/en.php';

// 7. REQUIRED: Set display variables
$title = $lang['page_title'];
$title_icon = '<i class="fa fa-icon"></i>';

// 8. List pages only: Set button text and icons
$new_button = $lang['new_item'];
$new_icon = '<i class="fa fa-plus"></i>';

// 9. REQUIRED: Include templates in correct order
require HEADER;              // Always first
require BODY;                // Always second
require NAV;                 // Always third

// 10. Page-specific template inclusion
if ($page == 'list') {
    require LISTOPEN;
    require 'get.php';       // Data loading and table rendering
    require LISTCLOSE;
} else {
    require SECTIONOPEN;
    // Custom form/content HTML
    require SECTIONCLOSE;
}

require FOOTER;              // Always last
```

### Helper Functions & Template Integration

**Routing Helper Functions:**
The framework includes `templates/routing_helpers.php` with utility functions:

```php
// Multi-level URL generation
buildUrl($dir, $subdir, $sub_subdir, $sub_sub_subdir, $page)

// Navigation breadcrumb generation
buildBreadcrumb($dir, $subdir, $sub_subdir, $sub_sub_subdir, $page)

// Parent URL for back navigation
getParentUrl($dir, $subdir, $sub_subdir, $sub_sub_subdir)

// CRUD action URLs with ID support
buildActionUrl($action, $dir, $subdir, $sub_subdir, $sub_sub_subdir, $id)
```

**Automatic Template Integration:**
- Helper functions are automatically included in `header.php`
- `list_buttons.php` uses `buildUrl()` for all button generation
- DataTables configurations support multi-level routing in `footer.php`

### Special Cases & Exceptions

**Non-Standard Pages:**
- `dashboard.php` - Includes dashboard content directly without standard routing
- `login.php` - Uses special login language files and templates
- `index.php` - Uses dynamic routing variable calculation

**Dynamic Variable Calculation (index.php):**
```php
$dir = basename(dirname(__FILE__));  // Auto-detect directory
$page = substr(basename(__FILE__), 0, strrpos(basename(__FILE__), '.'));  // Auto-detect page
```

**Fixed Admin Email System:**
All admin email pages now follow standard routing:
- `/admin/email/system_status.php` → `$dir='admin', $subdir='email', $page='system_status'`
- `/admin/email/accounts_config.php` → `$dir='admin', $subdir='email', $page='accounts_config'`
- `/admin/email/processing_log.php` → `$dir='admin', $subdir='email', $page='processing_log'`
- `/admin/email/sync_queue.php` → `$dir='admin', $subdir='email', $page='sync_queue'`

### Performance Benefits

**Optimized Resource Loading:**
- Only loads CSS/JS needed for specific page types
- DataTables resources only loaded for list pages
- Form validation only loaded for new/edit pages
- Module-specific JavaScript loaded conditionally

**Template Reuse:**
- Single header/footer templates serve all pages
- Conditional logic eliminates duplicate template files
- Consistent UI/UX across all modules

**Development Efficiency:**
- Predictable file structure and naming
- Standardized variable patterns
- Clear separation between list and form pages
- Easy to add new modules following established patterns

This direct routing system provides the simplicity of procedural PHP with the organization and reusability of template-based architecture, optimized for performance and developer productivity.

## Email Processing System

### Overview
Automated email form processing system that monitors email accounts and converts form submissions into CRM leads. Supports three form types:
- **Estimate Forms** - Fire protection system quotes
- **LTR Forms** - Long-term retardant applications  
- **Contact Forms** - General inquiries

### Features
- **Automated Processing**: IMAP monitoring with 5-minute cron intervals
- **Form Parsing**: Structured data extraction from email content
- **Lead Integration**: Direct integration with existing leads system
- **Duplicate Detection**: Prevents duplicate lead creation
- **Admin Interface**: Management dashboard at `/admin/email/system_status`
- **REST API**: External integration support
- **Multilingual Support**: Follows framework language patterns

### Installation & Setup
```bash
# Install system
php scripts/install_email_system.php

# Configure cron job
*/5 * * * * php /home/democrm/scripts/email_cron.php >> logs/email_cron.log 2>&1
```

### Configuration
- **Email Accounts**: Configured in `email_accounts_config` table
- **Form Mapping**: Defined in `EmailFormMapper` class
- **Processing Logic**: `EmailFormProcessor` class handles IMAP and lead creation
- **Admin Access**: `/admin/email/email_import` for manual processing

### System Status Monitoring

**System Status Page**: `/admin/email/system_status`

Comprehensive monitoring dashboard that checks all critical email processing components:

#### **Status Checks Performed**
1. **Database Connection** - Tests PDO connectivity
2. **Required Tables** - Verifies email processing tables exist and are accessible
3. **Email Accounts Configuration** - Monitors active vs total configured accounts
4. **Recent Processing Activity** - Shows 24-hour email processing statistics
5. **Processing Failures** - Tracks failed processing attempts over 7 days
6. **CRM Sync Queue Status** - Monitors pending and failed sync operations
7. **File Permissions** - Verifies log directory and cron script accessibility
8. **System Information** - PHP version, database version, required extensions

#### **Database Table Checking Implementation**
**Critical Implementation Note**: The system status page uses a **direct table access approach** to check table existence, avoiding both `SHOW TABLES` and `information_schema` queries due to database permission restrictions.

```php
// ✅ CORRECT: Direct table access method
foreach ($required_tables as $table) {
    try {
        // Try a simple SELECT to see if table exists and is accessible
        $stmt = $pdo->prepare("SELECT 1 FROM `$table` LIMIT 1");
        $stmt->execute();
        // If we get here, table exists and is accessible
    } catch (Exception $e) {
        $missing_tables[] = $table;
        // Parse error to determine if table missing vs other issues
    }
}
```

**Why This Approach:**
- ✅ **Avoids `SHOW TABLES`** - No metadata query privileges required
- ✅ **Avoids `information_schema`** - Bypasses #1044 access denied errors
- ✅ **Tests Real Accessibility** - Verifies actual table query permissions
- ✅ **Follows Database Rules** - Complies with database constraint patterns
- ✅ **Provides Specific Errors** - Shows exact error messages for debugging

#### **Required Email Processing Tables**
- `email_form_processing` - Tracks processed email forms and their status
- `crm_sync_queue` - Manages CRM synchronization queue and retry logic  
- `email_accounts_config` - Stores email account configurations and credentials

#### **Status Indicators**
- **🟢 Green (OK)**: All systems operational
- **🟡 Yellow (Warning)**: Issues requiring attention but system functional
- **🔴 Red (Error)**: Critical issues requiring immediate attention

#### **Quick Actions Available**
- View Processing Log
- Manage Email Accounts
- Check Sync Queue
- Manual Email Import
- System Configuration

## phpList Marketing Integration

### Overview
Automated email marketing list management system that syncs lead data with phpList when users opt-in for updates. Uses a hybrid approach combining immediate flagging with batch processing for reliable, scalable marketing automation.

### Architecture
**Hybrid Processing Model:**
1. **Immediate Flagging**: When lead created with `get_updates = 1`, subscriber record created in `phplist_subscribers` table with `sync_status = 'pending'`
2. **Batch Processing**: Cron job runs every 15 minutes to sync pending subscribers with phpList via API

### Database Tables
- **`phplist_subscribers`**: Main tracking table with sync status, attempts, and segmentation data
- **`phplist_config`**: Configuration for API credentials, sync settings, and list mappings
- **`phplist_sync_log`**: Detailed logging for sync operations and debugging

### Key Features
- **Automatic List Segmentation**: Geographic (state), service type, and lead source segmentation
- **Error Handling & Retry Logic**: Maximum retry attempts with detailed error logging
- **Web-based Admin Interface**: Configuration, monitoring, and manual retry capabilities
- **Graceful Degradation**: Lead creation never fails due to phpList issues

### Installation & Setup
```bash
# 1. Run database migration
php sql/migrations/run_phplist_migration.php

# 2. Configure via admin interface
# Access: /admin/phplist/config.php
# - Set API credentials (URL, username, password)
# - Configure list mappings for segmentation
# - Enable sync and set frequency

# 3. Set up cron job (every 15 minutes)
*/15 * * * * php /home/democrm/scripts/phplist_sync.php

# 4. Test integration
# - Create lead with get_updates = 1
# - Verify subscriber record created
# - Run sync script manually
# - Check subscriber appears in phpList
```

### Configuration Options
**API Settings:**
- `phplist_api_url`: Full URL to phpList admin directory
- `phplist_api_username/password`: API credentials (password encrypted)
- `api_timeout_seconds`: Request timeout (default: 30)

**Sync Settings:**
- `sync_enabled`: Enable/disable sync (1/0)
- `sync_frequency_minutes`: Cron frequency (default: 15)
- `max_sync_attempts`: Retry limit (default: 3)
- `batch_size`: Records per sync batch (default: 50)

**List Mapping (JSON):**
```json
{
  "phplist_geographic_lists": {"US-CA": 2, "US-TX": 3, "US-CO": 4},
  "phplist_service_lists": {"1": 10, "2": 11},
  "phplist_source_lists": {"Internet search": 20, "Referral": 21}
}
```

### Admin Management
- **Subscribers**: `/admin/phplist/subscribers.php` - View, filter, search, retry failed syncs
- **Configuration**: `/admin/phplist/config.php` - API settings, connection testing, statistics
- **Sync Logs**: `/admin/phplist/sync_log.php` - Detailed operation logs and debugging

### API Integration
- **Requirements**: phpList 3.x with REST API enabled, HTTPS recommended
- **Operations**: Add/update subscriber, get subscriber details, list management
- **Custom Attributes**: Maps CRM data (name, location, source, business) to phpList attributes

### Monitoring & Troubleshooting
**Sync Status Types:**
- `pending`: Waiting for sync
- `synced`: Successfully synced to phpList
- `failed`: Sync failed (check error message)
- `skipped`: Intentionally skipped (invalid email, etc.)

**Common Issues:**
- API connection failures: Verify credentials and connectivity
- Sync failures: Check error messages and API rate limits
- Performance issues: Adjust batch size and timeout settings

**Debug Mode**: Enable `debug_mode = 1` for detailed logging and API communication tracking

### Security & Maintenance
- API passwords encrypted in database
- HTTPS recommended for API communication
- Subscriber data only synced with explicit opt-in
- Regular monitoring of sync status and error logs
- Include phpList tables in database backups

## SQL Error Logging System

### Overview
Comprehensive error logging and debugging system for tracking database-related issues across all operations.

### Components
- **SqlErrorLogger**: Main logging class (`/classes/Logging/SqlErrorLogger.php`)
- **Enhanced Database**: Integrated logging in `Database` class
- **Admin Interface**: Web-based log viewer (`/admin/system/sql-logs.php`)
- **Configuration**: Debug settings in `config/system.php`

### Features
- **Error Tracking**: SQL errors, parameter mismatches, query failures
- **Context Capture**: User info, request context, stack traces, timing data
- **Security**: Sensitive data protection and access control
- **Admin Dashboard**: Real-time log viewing with filtering and statistics

### Usage
```php
// Automatic logging in Database class
$this->logSqlError($e, $sql, $params, $context);

// Manual logging
$logger = new SqlErrorLogger();
$logger->logError($exception, $sql, $params, $additionalContext);
```

## Testing Framework

### Overview
Comprehensive testing system with two complementary frameworks for different testing needs.

### Testing Tools
- **PHPUnit**: Unit, integration, and feature testing of PHP classes
- **Playwright**: End-to-end web interface testing with browser automation

### PHPUnit Testing
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test types
./vendor/bin/phpunit tests/phpunit/Unit/          # Unit tests
./vendor/bin/phpunit tests/phpunit/Integration/   # Integration tests
./vendor/bin/phpunit tests/phpunit/Feature/       # Feature tests

# Run specific test
./vendor/bin/phpunit tests/phpunit/Unit/EmailFormProcessorTest.php
```

### Playwright Testing
```bash
# Install dependencies
npm install

# Run all tests
npx playwright test

# Run specific tests
npx playwright test --grep "login"           # Test login functionality
npx playwright test --grep "leads"           # Test leads-related UI
npx playwright test --project=chromium       # Chrome-specific testing

# Debug mode
npx playwright test --debug                  # Step through tests
npx playwright test --headed                 # See browser actions
```

### Test Structure
- **Unit Tests**: Test individual classes and methods in isolation
- **Integration Tests**: Test component interactions and database operations
- **Feature Tests**: Test complete workflows and business logic
- **E2E Tests**: Test full user workflows through browser interface

### Development Workflow
1. **Write Unit Tests**: For new classes and methods
2. **Integration Tests**: For database operations and component interactions
3. **Feature Tests**: For complete business workflows
4. **E2E Tests**: For critical user paths and UI functionality

## Development & Operations

### File Ownership
- **Application Files**: Must be owned by `democrm:democrm`
- **Web Server**: Runs as `nobody` user (standard Apache configuration)
- **Permissions**: `644` for files, `755` for directories

### File Management Best Practices
- **Prefer file tools** (`WriteFile`, `EditFile`) over shell commands for proper ownership
- **Verify ownership** after shell-based file operations: `chown democrm:democrm /path/to/file`
- **Check ownership**: `find /home/democrm -user root -name '*.php' | grep -v '.git'` (should return 0)

### Performance Optimizations
- **Constants Usage**: Use pre-compiled constants (`LEADS`, `CONTACTS`) instead of string concatenation
- **Database Connections**: Singleton pattern for connection reuse
- **Template Caching**: Direct PHP includes for optimal performance
- **Opcache**: PHP opcache enabled for constant and class caching

### Security Considerations
- **CSRF Protection**: `Nonce` class for form security
- **Permission Checks**: Role-based access control via `Security` class
- **SQL Injection Prevention**: PDO prepared statements throughout
- **Sensitive Data**: Automatic sanitization in logging systems
- **Admin Access**: Separate admin permissions for system management tools

### Maintenance Tasks
- **Log Rotation**: SQL error logs rotate automatically when large
- **Email Processing**: Cron job runs every 5 minutes for email import
- **Database Cleanup**: Regular cleanup of processed email records
- **Performance Monitoring**: SQL error logs track query performance

## Helper Functions Architecture

### Current Implementation (Post-Refactoring)
All utility functions are now consolidated in the `Helpers` class for better organization and maintainability:

**Location**: `classes/Utilities/Helpers.php`

**Network & Security Utilities**:
- `$helper->get_client_ip()` - IP detection with proxy support
- `$helper->country_by_ip()` - Geolocation using fallback services  
- `$helper->isValidSessionId($id)` - Session ID validation

**Usage Pattern**:
```php
// Available globally after system.php is loaded
$clientIp = $helper->get_client_ip();
$countryCode = $helper->country_by_ip();
$sessionValid = $helper->isValidSessionId($_SESSION['id']);
```

### Migration Notes
- **Old global functions removed**: `config/helpers.php` no longer required
- **Class-based approach**: Better testing, dependency injection, and IDE support
- **Backward compatibility**: Function names unchanged, only calling syntax updated
- **Performance improvement**: Lazy loading instead of always-loaded globals

## Sessions Management Architecture

### Enhanced Sessions Class
The `Sessions` class provides convenient static methods for session management while working with the global session started in `config/system.php`.

**Location**: `classes/Core/Sessions.php`

**Key Features**:
- **Static convenience methods** for common session operations
- **Backward compatible** with existing `$_SESSION` usage
- **Security helpers** for login validation and timeout checking
- **Clean session creation/destruction** methods

**Core Methods**:
```php
// Authentication checks
Sessions::isLoggedIn()           // Check if user is authenticated
Sessions::getUserId()            // Get current user ID
Sessions::getUserName()          // Get user's full name
Sessions::getPermissions()       // Get user's permission array
Sessions::getLanguage()          // Get user's language preference

// Session management
Sessions::create($userData, $permissions)  // Set session after login
Sessions::destroyClean()         // Clean logout with cookie cleanup
Sessions::isValid($timeout_min)  // Check session timeout
Sessions::updateActivity()       // Update last activity timestamp

// Generic session operations
Sessions::get($key, $default)    // Get session value
Sessions::set($key, $value)      // Set session value
Sessions::has($key)              // Check if key exists
Sessions::remove($key)           // Remove session key
```

### Session Security Configuration
Comprehensive session security settings are prepared in `config/system.php` but commented out for safety.

**Available Security Settings**:
- `session.use_strict_mode` - Prevents session fixation attacks
- `session.sid_length` - Increases session ID entropy (32 chars)
- `session.sid_bits_per_character` - More randomness per character (6 bits)
- `session.cookie_httponly` - Prevents XSS cookie theft
- `session.cookie_secure` - HTTPS-only cookies (requires full HTTPS)
- `session.use_only_cookies` - Prevents URL session exposure
- `session.cookie_samesite` - CSRF protection (Strict mode)

**Implementation Strategy**:
1. **Phase 1**: Enable low-risk settings (httponly, use_only_cookies, sid_length)
2. **Phase 2**: Test medium-risk settings (use_strict_mode, cookie_samesite)
3. **Phase 3**: Enable high-risk settings when HTTPS is fully implemented (cookie_secure)

**Documentation**: See `SESSION_SECURITY_GUIDE.md` for detailed implementation guide

### Session Architecture Notes
- **Global session start**: `session_start()` called in `config/system.php` for all pages
- **No session initialization conflicts**: Sessions class works with existing session
- **Autoloaded**: Available immediately after `system.php` is loaded
- **Mixed usage supported**: Can use both `Sessions::method()` and `$_SESSION[]` approaches
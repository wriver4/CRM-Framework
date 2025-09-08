# CRM Framework - Developer Documentation

## Quick Start

### Prerequisites
- PHP 8.4.8+
- MySQL/MariaDB
- Composer
- Web server (Apache/Nginx)

### Local Development Setup
1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd democrm
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure database**
   - Edit database credentials in `classes/Core/Database.php`
   - Update the connection details in the constructor

4. **Import database schema**
   ```bash
   mysql -u username -p database_name < sql/democrm_democrm.sql
   ```

5. **Set up web server**
   - Point document root to `public_html/` directory
   - Ensure proper file permissions (644 for files, 755 for directories)

6. **Verify installation**
   - Access the application through your web server
   - Check `logs/php_errors.log` for any issues

### Running Tests

**PHPUnit Tests:**
```bash
# Local development
./vendor/bin/phpunit

# Remote server
php phpunit.phar
```

**Playwright E2E Tests:**
```bash
# Install Playwright
npm install @playwright/test

# Run tests
npx playwright test
```

### Development Notes
- This is **NOT a traditional MVC framework** - it uses direct file routing
- Database credentials are hardcoded in `classes/Core/Database.php`
- All models extend the `Database` class for connection access
- Language files are stored in `public_html/admin/languages/`
- Templates are included directly, not rendered through a template engine

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

# Development Patterns & Conventions

## Database Operations
- **Always use individual `bindValue()` calls** instead of `execute()` with parameter arrays
- **Cast to integers when binding ID parameters**: `$stmt->bindValue(':id', (int)$id, PDO::PARAM_INT)`
- **Use appropriate PDO parameter types**: `PDO::PARAM_INT` for integers, `PDO::PARAM_STR` for strings
- **Prevent unbuffered query errors**: Use `fetchAll()` to consume results and `$stmt = null` to close statements
- **Prefer integers over varchar** for ID fields and numeric data
- **Use phpMyAdmin** for database migrations and schema changes
- **Always create backup tables** before major alterations
- **Use MariaDB 10 compatible syntax**
- **Maintain foreign key constraints** for data integrity

## Code Standards
- **Never use closing PHP tags (`?>`)** at the end of PHP-only files - this prevents whitespace issues
- **Use `int(11)` over `varchar()`** for ID fields
- **Implement proper foreign key relationships**
- **Follow consistent CRUD operation patterns**
- **Include language file at the top**: `$lang = include 'admin/languages/en.php';`
- **Pass language arrays to templates**: Templates expect `$lang` variable

## Form Processing Flow
- **Load language file first**: `$lang = include 'admin/languages/en.php';`
- **Validate CSRF tokens**: Use `Nonce` class for protection
- **Sanitize input data**: Always validate and sanitize user input
- **Use Helpers class for multilingual forms**: `$helpers->select_role($lang, $role_id)`
- **Include templates with language context**: `include 'templates/header.php';`

## Error Handling Patterns
- **Check logs location**: `logs/php_errors.log` for PHP errors
- **Server error monitoring**: Check line 1 in `.tail` for server-level errors
- **Use Whoops for development**: Detailed error pages in development
- **Log to Monolog**: Use structured logging for application events
- **Audit trail everything**: Use `Audit` class for user actions

## Error Handling
**Framework**: Whoops (^2.18)
**Logging**: Monolog (^3.9)
**Log Location**: logs/php_errors.log
**Server Error Log**: Server errors for this domain can be found at line 1 in `.tail` (server-specific error monitoring)

## Security
- HTTPS enforcement
- Session management
- Role-based permissions system
- CSRF protection via Nonce class

## Common Issues & Solutions

### File Permissions
- **Web server access**: Ensure `public_html/` has read permissions for web server
- **Log file permissions**: `logs/` directory needs write permissions for error logging
- **File ownership**: 
  - **Local Machine**: Files should be owned by `mark:users`
  - **Remote Server**: Files should be owned by `democrm:democrm`
- **Fix permissions**: `chmod 644` for files, `chmod 755` for directories

### Database Connection Issues
- **Connection details are hardcoded** in `classes/Database.php` - no external config files
- **Check credentials**: Verify database name, username, password in Database constructor
- **PDO errors**: Enable error reporting to see detailed PDO connection issues
- **Character set**: Ensure `utf8mb4` charset is supported by database

### Multilingual Issues
- **Language files must return arrays**: Use `return $lang;` not `echo` in language files
- **Helper class array formats**: Ensure language arrays have expected key formats (e.g., `role_id_1`, `state_id_1`)
- **Missing translations**: Check for undefined array keys causing PHP notices
- **Template variables**: Ensure `$lang` variable is passed to all templates
- **Array offset errors in templates**: Use defensive programming with `isset()` checks for language keys

### Common PHP Errors
- **Class not found**: Check autoloader paths and class file locations
- **Database connection failed**: Verify credentials in `classes/Database.php`
- **Session errors**: Ensure session handling is properly initialized
- **Include path issues**: Use absolute paths or proper relative paths for includes
- **PDO unbuffered query errors**: Use `fetchAll()` to consume results before executing new queries
- **Array offset on int errors**: Check that `$lang` variable is properly loaded as array in templates

### Development Environment Issues
- **PHP version compatibility**: Requires PHP 8.4.8+ for proper functionality
- **Missing extensions**: Ensure PDO, PDO_MySQL extensions are installed
- **Composer dependencies**: Run `composer install` to install required packages
- **Web server configuration**: Point document root to `public_html/` directory

### Testing Issues
- **PHPUnit not found**: Use `php phpunit.phar` on remote server, `./vendor/bin/phpunit` locally
- **Playwright setup**: Ensure Node.js and Playwright dependencies are installed
- **Test database**: Verify test environment has access to database
- **Authentication in tests**: Use test credentials from `tests/playwright/test-credentials.js`

## Development Workflow

### Making Changes
1. **Always test on development environment first** - Never make changes directly on production
2. **Check error logs after changes** - Monitor `logs/php_errors.log` for issues
3. **Verify multilingual functionality works** - Test with different language files
4. **Test with different user roles** - Ensure permissions work correctly
5. **Run tests before deployment** - Execute PHPUnit and Playwright tests

### File Organization Rules
- **Controllers**: Place in `public_html/[entity]/` directories (e.g., `public_html/leads/edit.php`)
- **Models**: Place in `classes/Models/` directory (e.g., `classes/Models/Leads.php`)
- **Views/Lists**: Place in `classes/Views/` directory (e.g., `classes/Views/LeadsList.php`)
- **Utilities**: Place in `classes/Utilities/` directory (e.g., `classes/Utilities/Helpers.php`)
- **Core Classes**: Place in `classes/Core/` directory (e.g., `classes/Core/Database.php`)
- **Templates**: Place in `public_html/templates/` directory

### Code Review Checklist
- **Database operations use proper binding** - Individual `bindValue()` calls with correct types
- **Language files are included** - `$lang = include 'admin/languages/en.php';`
- **CSRF protection is implemented** - Use `Nonce` class for forms
- **Error handling is present** - Proper try/catch blocks and logging
- **No closing PHP tags** - Files should not end with `?>`
- **Proper file permissions** - Correct ownership and permissions set

### Deployment Process
1. **Update repository documentation** - Run `php scripts/generate_readme.php` after changes to `.zencoder/rules/repo.md`
2. **Test locally first** - Verify all functionality works in development
3. **Backup database** - Create backup before schema changes
4. **Deploy files** - Copy files to server with proper permissions
5. **Verify deployment** - Check logs and test critical functionality
6. **Monitor for issues** - Watch error logs after deployment

### Documentation Updates
- **When modifying repo.md**: Always run `php scripts/generate_readme.php` to update README.md
- **When adding new classes**: Update the class organization documentation
- **When changing database schema**: Update the Database Schema Reference section
- **When adding new features**: Update relevant sections in repo.md

## Project Preferences

### Database
- Prefer integers over varchar for ID fields and numeric data
- Use phpMyAdmin for database migrations and schema changes
- Always create backup tables before major alterations
- Use MariaDB 10.11.9 compatible syntax
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

## File Naming Conventions

- **Controllers**: `action.php` (e.g., `edit.php`, `list.php`, `new.php`)
- **Classes**: `PascalCase.php` (e.g., `Leads.php`, `ContactsList.php`)
- **Templates**: `snake_case.php` (e.g., `nav_item_leads.php`)
- **Assets**: Standard web naming (e.g., `style.css`, `edit-leads.js`)

# Database Configuration

## Database
**Type**: MySQL
**Connection**: PDO
**Database Name**: democrm_democrm
**Character Set**: utf8mb4

## Database Setup & Schema

### Database Structure Reference
**Primary Schema File**: `sql/democrm_democrm_structure.sql`
- Contains complete database schema (tables, indexes, constraints)
- Structure-only file (33K) - no data included
- Use this file for development reference and schema understanding

### Available SQL Files
- **`sql/democrm_democrm_structure.sql`** - Database structure only (33K, recommended for development)
- **`sql/democrm_democrm.sql`** - Full database with structure and data (7.2M, for production restore)
- **`sql/democrm_democrm_no create.sql`** - Data only, no CREATE statements (33K, for data migration)

### Usage Guidelines
- **For Documentation**: Always reference `democrm_democrm_structure.sql`
- **For Development**: Use `democrm_democrm_structure.sql` to understand schema
- **For Production Restore**: Use `democrm_democrm.sql` (contains actual data)
- **For Data Migration**: Use `democrm_democrm_no create.sql` when schema exists

### Database Setup Process
```bash
# For development/testing (structure only)
mysql -u username -p democrm_democrm < sql/democrm_democrm_structure.sql

# For complete setup with data (production restore)
mysql -u username -p democrm_democrm < sql/democrm_democrm.sql
```

**Note**: Always use the structure file (`democrm_democrm_structure.sql`) for development reference and documentation purposes.

## Database Class Architecture

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
[PASSWORD_REDACTED]
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

## Database Constraints & Limitations

### ⚠️ CRITICAL: information_schema Access Denied (Error #1044)
**RECURRING ISSUE**: `#1044 - Access denied for user 'root'@'localhost' to database 'information_schema'`

**ROOT CAUSE**: The database user lacks SELECT privileges on the `information_schema` database, preventing queries that check table/constraint existence.

**MANDATORY SOLUTION PATTERNS**:

**❌ NEVER USE information_schema queries**:
```sql
-- This WILL FAIL with #1044 error
SELECT COUNT(*) FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'table_name';

SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'constraint_name';
```

**✅ USE ALTERNATIVE APPROACHES**:

1. **Check Table Existence**:
```sql
-- Use SHOW TABLES instead of information_schema.TABLES
SET @table_exists = (SELECT COUNT(*) FROM (SHOW TABLES LIKE 'table_name') AS t);
```

2. **Check Constraint Existence**:
```sql
-- Use SHOW CREATE TABLE to check constraints
SET @sql = 'SHOW CREATE TABLE table_name';
-- Parse the result to check for constraint existence
```

3. **Get Column Information**:
```sql
-- Use DESCRIBE instead of information_schema.COLUMNS
DESCRIBE table_name;
-- or
SHOW COLUMNS FROM table_name;
```

4. **List All Tables**:
```sql
-- Use SHOW TABLES instead of information_schema.TABLES
SHOW TABLES;
```

**PREVENTION CHECKLIST**:
- **Never query information_schema** in any SQL scripts
- **Use SHOW commands** for metadata queries
- **Test all SQL scripts** on the actual server environment
- **Document alternative approaches** for common information_schema queries
- **Use direct table operations** instead of metadata-dependent logic

**COMMON REPLACEMENTS**:
- `information_schema.TABLES` → `SHOW TABLES LIKE 'pattern'`
- `information_schema.COLUMNS` → `DESCRIBE table_name` or `SHOW COLUMNS FROM table_name`
- `information_schema.TABLE_CONSTRAINTS` → `SHOW CREATE TABLE table_name`
- `information_schema.KEY_COLUMN_USAGE` → `SHOW INDEX FROM table_name`

### ⚠️ CRITICAL: Cannot Change Column Used in Foreign Key (Error #1833)
**RECURRING ISSUE**: `#1833 - Cannot change column 'id': used in a foreign key constraint 'fk_constraint_name'`

**ROOT CAUSE**: Attempting to modify a column that is referenced by existing foreign key constraints.

**MANDATORY SOLUTION PATTERN**:
```sql
-- ✅ CORRECT: Drop foreign keys, modify column, recreate constraints
-- Step 1: Drop existing foreign key constraints
ALTER TABLE referencing_table DROP FOREIGN KEY fk_constraint_name;

-- Step 2: Modify the referenced column
ALTER TABLE referenced_table MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT;

-- Step 3: Update referencing columns to match
ALTER TABLE referencing_table MODIFY COLUMN foreign_key_column int(11) DEFAULT NULL;

-- Step 4: Recreate foreign key constraints
ALTER TABLE referencing_table ADD CONSTRAINT fk_constraint_name 
  FOREIGN KEY (foreign_key_column) REFERENCES referenced_table(id) ON DELETE SET NULL;
```

**❌ NEVER DO THIS**:
```sql
-- This WILL FAIL with #1833 error if foreign keys exist
ALTER TABLE contacts MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT;
```

**PREVENTION CHECKLIST**:
- **Always check for foreign keys** before modifying columns
- **Use `SHOW CREATE TABLE`** to see existing constraints
- **Drop constraints first**, then modify, then recreate
- **Handle multiple referencing tables** - drop all foreign keys that reference the column
- **Use proper error handling** for missing constraints

### ⚠️ CRITICAL: Cannot Drop Foreign Key (Error #1091)
**RECURRING ISSUE**: `#1091 - Can't DROP FOREIGN KEY 'constraint_name'; check that it exists`

**ROOT CAUSE**: Attempting to drop a foreign key constraint that doesn't exist or has a different name than expected.

**MANDATORY SOLUTION PATTERN**:
```sql
-- ✅ CORRECT: Use stored procedures with error handling
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS SafeDropForeignKey(
    IN table_name VARCHAR(64),
    IN constraint_name VARCHAR(64)
)
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
    
    SET @sql = CONCAT('ALTER TABLE ', table_name, ' DROP FOREIGN KEY ', constraint_name);
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //
DELIMITER ;

-- Try multiple possible constraint names
CALL SafeDropForeignKey('leads', 'fk_leads_contact_id');
CALL SafeDropForeignKey('leads', 'leads_ibfk_1');
CALL SafeDropForeignKey('leads', 'leads_contact_id_fk');

DROP PROCEDURE IF EXISTS SafeDropForeignKey;
```

**❌ NEVER DO THIS**:
```sql
-- This WILL FAIL with #1091 error if constraint doesn't exist
ALTER TABLE leads DROP FOREIGN KEY fk_leads_contact_id;
```

**PREVENTION CHECKLIST**:
- **Check actual constraint names** using `SHOW CREATE TABLE table_name`
- **Use stored procedures** with error handling for safe constraint dropping
- **Try multiple naming patterns** - MySQL auto-generates names like `table_ibfk_1`
- **Always use `IF EXISTS` patterns** where possible
- **Test constraint operations** on development environment first

### ⚠️ CRITICAL: MariaDB Version Mismatch (Error #1558)
**RECURRING ISSUE**: `#1558 - Column count of mysql.proc is wrong. Expected 21, found 20. Created with MariaDB 100243, now running 101109. Please use mariadb-upgrade to fix this error.`

**ROOT CAUSE**: The database system tables were created with an older version of MariaDB and are incompatible with the current version.

**MANDATORY SOLUTION PATTERN**:
```bash
# ✅ CORRECT: Run MariaDB upgrade to fix system tables
# This must be run as root or database administrator

# Stop MariaDB service
sudo systemctl stop mariadb

# Run the upgrade utility
sudo mariadb-upgrade

# Start MariaDB service
sudo systemctl start mariadb

# Verify the upgrade worked
mysql -u root -p -e "SELECT VERSION();"
```

**ALTERNATIVE APPROACH** (if mariadb-upgrade is not available):
```bash
# Use mysql_upgrade (older systems)
sudo mysql_upgrade -u root -p

# Or force upgrade with specific options
sudo mysql_upgrade -u root -p --force --upgrade-system-tables
```

**❌ NEVER DO THIS**:
```sql
-- Don't try to manually fix system tables
-- Don't ignore this error - it will cause stored procedures to fail
```

**PREVENTION CHECKLIST**:
- **Run mariadb-upgrade after any MariaDB version updates**
- **Always backup databases before upgrading MariaDB**
- **Test stored procedures after MariaDB upgrades**
- **Monitor system logs for version mismatch warnings**
- **Document current MariaDB version in deployment notes**

**IMPACT ON APPLICATION**:
- **Stored procedures will fail** - affects our SafeDropForeignKey procedures
- **Database migrations may fail** - especially those using stored procedures
- **System stability issues** - inconsistent behavior across database operations

**WORKAROUND SOLUTION** (when mariadb-upgrade cannot be run):
```sql
-- ✅ ALTERNATIVE: Avoid stored procedures entirely
-- Use direct SQL statements instead of stored procedures
-- Accept that some statements may fail (this is expected behavior)

-- Instead of stored procedures, use direct statements:
ALTER TABLE leads DROP FOREIGN KEY fk_leads_contact_id;
ALTER TABLE leads DROP FOREIGN KEY leads_ibfk_1;
ALTER TABLE leads DROP FOREIGN KEY leads_ibfk_2;
-- Continue with migration even if some statements fail

-- This approach works around the MariaDB version mismatch
-- by avoiding stored procedures completely
```

### **🎯 SIMPLEST & SAFEST SQL Migration Pattern**

**For maximum reliability, use this ultra-simple approach**:

```sql
-- ✅ ULTRA-SAFE MIGRATION PATTERN
-- 1. Show current structure (for debugging)
SHOW CREATE TABLE table_name;

-- 2. Try to drop common foreign key constraint names (errors expected)
ALTER TABLE table_name DROP FOREIGN KEY fk_constraint_name;
ALTER TABLE table_name DROP FOREIGN KEY table_name_ibfk_1;
ALTER TABLE table_name DROP FOREIGN KEY table_name_ibfk_2;

-- 3. Modify columns (should work after constraint removal attempts)
ALTER TABLE table_name MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT;

-- 4. Recreate foreign key with standard name
ALTER TABLE table_name ADD CONSTRAINT fk_standard_name 
  FOREIGN KEY (column_id) REFERENCES other_table(id) ON DELETE SET NULL;

-- 5. Verify final structure
SHOW CREATE TABLE table_name;
```

**KEY PRINCIPLES**:
- ✅ **Use SHOW commands** instead of information_schema queries
- ✅ **Try multiple constraint names** - some will fail (expected)
- ✅ **Use direct ALTER TABLE** statements (no stored procedures)
- ✅ **Standardize to int(11)** for all ID fields
- ✅ **Show structures** before and after for verification
- ✅ **Accept errors gracefully** - migration continues despite #1091 errors

### ⚠️ CRITICAL: Foreign Key Constraint Issues (Error #1005)
**RECURRING ISSUE**: Foreign key constraints fail with "errno: 150 'Foreign key constraint is incorrectly formed'"

**ROOT CAUSE**: Creating tables with foreign key constraints in the same statement when referenced tables may not exist or have different column types.

**MANDATORY SOLUTION PATTERN**:
```sql
-- ✅ CORRECT: Create tables WITHOUT foreign keys first
CREATE TABLE IF NOT EXISTS `new_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL COMMENT 'Foreign key to parent table',
  -- ... other columns
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`)
  -- NO FOREIGN KEY CONSTRAINTS HERE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ✅ CORRECT: Add foreign keys AFTER tables exist
-- Check if constraint already exists to avoid duplicate errors
SET @constraint_exists = (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS 
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'new_table'
    AND CONSTRAINT_NAME = 'fk_new_table_parent'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql = IF(@constraint_exists = 0,
  'ALTER TABLE new_table ADD CONSTRAINT fk_new_table_parent FOREIGN KEY (parent_id) REFERENCES parent_table(id) ON DELETE CASCADE',
  'SELECT "Foreign key already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
```

**❌ NEVER DO THIS**:
```sql
-- This WILL FAIL with errno: 150
CREATE TABLE `new_table` (
  `parent_id` int(11) NOT NULL,
  CONSTRAINT `fk_new_table_parent` FOREIGN KEY (`parent_id`) REFERENCES `parent_table` (`id`)
) ENGINE=InnoDB;
```

**PREVENTION CHECKLIST**:
1. Always use `CREATE TABLE IF NOT EXISTS`
2. Create tables without foreign key constraints first
3. Add foreign key constraints in separate ALTER TABLE statements
4. Check if constraints already exist before adding them
5. **CRITICAL**: Use consistent column types across all tables (standardize to `int(11)` for all ID fields)
6. **STANDARDIZATION**: Before creating foreign keys, ensure referenced tables have matching data types
7. Test migration scripts before deployment

**DATA TYPE STANDARDIZATION**:
- **All ID fields**: Use `int(11) NOT NULL AUTO_INCREMENT`
- **All foreign key fields**: Use `int(11)` (nullable or NOT NULL as appropriate)
- **Never mix**: `int(10) UNSIGNED` with `int(11)` in foreign key relationships
- **Migration pattern**: Always standardize existing tables before adding new foreign key relationships

### Other Database Limitations
- **Foreign key management**: Use `SHOW CREATE TABLE` to view constraints before modification
- **Single Database**: The system is designed for a single database connection only
- **Character set**: Ensure `utf8mb4` charset is supported by database

## Database Schema Reference

**Core Entity Tables:**
- `users` - User accounts and authentication (id, username, full_name, email, role_id, state_id)
- `leads` - Lead tracking and management (id, contact_id, service_id, source_id, structure_id, notes)
- `contacts` - Contact information and relationships (id, full_name, email, phone, address, city, state, zip)
- `notes` - Notes linked to leads/contacts (id, lead_id, contact_id, user_id, note_text, created_at)
- `communications` - Communication history (id, lead_id, contact_id, user_id, type, content, created_at)
- `sales` - Sales pipeline and transactions (id, lead_id, amount, status, close_date)

**Security & Access Control:**
- `roles` - User role definitions (id, role_name, description, permissions)
- `permissions` - System permission definitions (id, permission_name, description, module)
- `roles_permissions` - Role-permission mapping (role_id, permission_id) - **Bridge Table**
- `user_sessions` - Session management (id, user_id, session_token, expires_at)

**System & Audit:**
- `audit` - System audit trail (id, user_id, action, table_name, record_id, old_values, new_values, ip_address, created_at)
- `internal_errors` - Error logging (id, error_message, file_path, line_number, user_id, created_at)
- `php_error_log` - PHP error tracking (id, error_type, message, file, line, created_at)

**Lookup/Reference Tables:**
- `lead_sources` - Lead source options (id, source_name, description)
- `lead_services` - Available services (id, service_name, description, active)
- `lead_structures` - Structure type classifications (id, structure_name, description)
- `contact_types` - Contact type classifications (id, type_name, description)
- `system_states` - Active/inactive states (id, state_name, description)

**Bridge/Junction Tables:**
- `roles_permissions` - Many-to-many: roles ↔ permissions
- `lead_contacts` - Links leads to contacts (if multiple contacts per lead)
- `user_permissions` - Direct user permissions override (user_id, permission_id)

**Key Foreign Key Relationships:**
- `users.role_id` → `roles.id`
- `users.state_id` → `system_states.id`
- `leads.contact_id` → `contacts.id`
- `leads.service_id` → `lead_services.id`
- `leads.source_id` → `lead_sources.id`
- `leads.structure_id` → `lead_structures.id`
- `notes.lead_id` → `leads.id`
- `notes.contact_id` → `contacts.id`
- `notes.user_id` → `users.id`
- `audit.user_id` → `users.id`
- `roles_permissions.role_id` → `roles.id`
- `roles_permissions.permission_id` → `permissions.id`

# Multilingual System

The application features a comprehensive multilingual system supporting multiple languages with centralized translation management.

## Language Architecture

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

## Helpers Class - Multilingual Support

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

## Language File Organization

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

## Network & Geolocation Services

The application includes IP geolocation functionality for audit logging and user tracking. The system uses free geolocation services with fallback support.

**Configuration Location**: `classes/Utilities/Helpers.php`

**Services Used**:
- **ip-api.com** (primary) - Free tier with 1000 requests/month
- **freeiplookupapi.com** (fallback) - Free service

**Key Methods**:
- `$helper->get_client_ip()` - Detects client IP from various sources (proxy-aware)
- `$helper->country_by_ip()` - Returns country code from IP address using fallback services
- `$helper->isValidSessionId($id)` - Validates session ID format according to PHP configuration

**Features**:
- **Multiple fallback services**: Ensures reliability if one service fails
- **Timeout protection**: 3-second timeout per service prevents delays
- **Graceful degradation**: Returns "Unknown" if all services fail
- **Proxy support**: Properly handles forwarded IPs from proxies and load balancers

## Routing Variables System

The application uses a set of standard variables to control conditional resource loading and template behavior. These variables must be defined before including templates:

**Core Routing Variables:**
- **`$dir`** - Primary directory/module (e.g., 'users', 'leads', 'admin')
- **`$subdir`** - Subdirectory within module (e.g., 'phplist' for admin/phplist)
- **`$page`** - Specific page/action (e.g., 'list', 'edit', 'new', 'login')
- **`$table_page`** - Boolean controlling DataTables CSS/JS inclusion
- **`$title`** - Page title displayed in browser tab and page header
- **`$title_icon`** - Icon HTML displayed with page title

**Conditional Resource Loading Examples:**
```php
// Login-specific CSS loading (header.php line 81)
<?php if ($page == "login") { ?>
<link rel="stylesheet" href="<?php echo CSS . "/login.css"; ?>">
<?php } ?>

// Auto-refresh for status pages (header.php line 85)
<?php if (isset($refresh) && $refresh == true && $dir == "status") { ?>
<meta http-equiv="refresh" content="<?php echo $_SESSION['refresh_time']; ?>">
<?php } ?>

// DataTables CSS for list pages (header.php line 89)
<?php if ($table_page == true) { ?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/..." />
<?php } ?>
```

**Standard Variable Pattern**:
```php
// Required in every page before including templates
$dir = 'users';           // Module directory
$subdir = '';             // Subdirectory (if applicable)
$page = 'list';           // Page type
$table_page = true;       // Enable DataTables resources
$title = 'User List';     // Page title
$title_icon = '<i class="fa fa-users"></i>'; // Title icon
```

This system allows the framework to load only the CSS and JavaScript resources needed for each specific page, optimizing performance and avoiding resource conflicts.

# Testing

The project includes two comprehensive testing systems: **Playwright** for end-to-end web interface testing and **PHPUnit** for unit, integration, and feature testing.

## Testing Directory Structure

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

scripts/testing/                    # Test runner scripts and utilities
├── run_tests_simple.sh             # Simple test runner
├── run_email_tests.sh              # Email processing test runner
├── run-note-deletion-tests.sh      # Note deletion specific tests
├── run-phpunit-nixos.sh            # PHPUnit runner for NixOS
├── run-tests-nixos.sh              # Complete test suite for NixOS
├── run-tests.php                   # PHP-based test runner
├── setup-local-playwright.sh       # Playwright local setup
├── setup-local-tests.sh            # Local testing environment setup
├── install-playwright-nixos.sh     # Playwright installation for NixOS
└── simple-test.php                 # Simple PHP test utility
```

## Test Runner Scripts

**Convenience Wrapper**: Use `./run-tests [type]` from project root for easy access to all test runners.

**Available Test Runners:**
- `./run-tests simple` - Basic functionality tests
- `./run-tests email` - Email processing system tests  
- `./run-tests notes` - Note deletion functionality tests
- `./run-tests phpunit` - PHPUnit test suite
- `./run-tests all` - Complete test suite
- `./run-tests setup-playwright` - Setup Playwright environment
- `./run-tests setup-tests` - Setup local testing environment

## 1. Playwright Testing System

**Purpose**: End-to-end web interface testing across multiple browsers and devices.

**Configuration Files:**
- `playwright.config.js` - Main configuration for remote testing
- `playwright-local.config.js` - Local testing configuration (if exists)
- `run-tests-nixos.sh` - Test runner script for NixOS

**Key Features:**
- **Multi-browser testing**: Chrome, Firefox, Safari, Mobile Chrome, Mobile Safari
- **Remote testing**: Configured to test against `https://[REDACTED]`
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

## 2. PHPUnit Testing System

**Purpose**: Unit, integration, and feature testing of PHP classes and functionality.

### PHPUnit Installation

**Remote Server (Production):**
The remote server uses a standalone PHPUnit PHAR file for testing:

```bash
# PHPUnit is installed as phpunit.phar in the project root
# Location: /home/democrm/phpunit.phar
# Version: PHPUnit 10.5.53

# Run tests on remote server
php phpunit.phar [test-file-or-options]

# Example: Run specific test
php phpunit.phar tests/phpunit/Integration/ClassOrganizationTest.php
```

**Local Development:**
For local development, PHPUnit can be installed via Composer:

```bash
# Install PHPUnit via Composer (local development)
composer require --dev phpunit/phpunit

# Run tests locally
./vendor/bin/phpunit [test-file-or-options]
```

**Configuration Files:**
- `phpunit.xml` - PHPUnit configuration with test suites
- `tests/bootstrap.php` - Bootstrap file with autoloading and environment setup
- `phpunit.phar` - Standalone PHPUnit executable (remote server)

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
- **Class organization testing**: Validates proper class structure and inheritance

**Running PHPUnit Tests:**

**On Remote Server:**
```bash
# Run all test suites
php phpunit.phar

# Run specific test suite
php phpunit.phar --testsuite Unit
php phpunit.phar --testsuite Integration
php phpunit.phar --testsuite Feature

# Run specific test file
php phpunit.phar tests/phpunit/Unit/HelpersTest.php
php phpunit.phar tests/phpunit/Integration/ClassOrganizationTest.php
```

**Local Development:**
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

**Test Bootstrap Configuration:**
The `tests/bootstrap.php` file includes:
- Custom autoloader for `/classes` directory structure
- Composer autoloader for vendor packages
- Test environment variables and constants
- Required constants like `NONCE_SECRET` for testing

## 3. Manual Testing Utilities

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
- Base URL configured for remote testing: `https://[REDACTED]`

# Lead Management Workflow

The lead management system follows a specific information flow from creation to editing, with distinct phases for data collection and management.

## Lead Creation Flow (new.php → post.php)

**Purpose**: Collect initial lead information and create contact integration

**Data Collection Phases**:
1. **Lead Source & Identification**
   - Lead Source (required dropdown)
   - Lead ID (required text input)

2. **Contact Information** (creates/links contact record)
   - First Name, Family Name (required)
   - Email (required), Cell Phone
   - Contact Type (dropdown)
   - Business Name (optional)

3. **Property Address** (stored in contacts table)
   - Street Address 1 & 2
   - City, State, Postal Code, Country
   - All address fields are editable inputs

4. **Structure Information** (lead-specific data)
   - Structure Type (required dropdown)
   - Structure Description (checkboxes, conditional)
   - Structure Other (text input, conditional)

5. **File Submissions** (URLs for client uploads)
   - Picture Upload Links (3 text inputs)
   - Plans Upload Links (3 text inputs)

6. **Communication Preferences**
   - Get Updates (Yes/No dropdown)
   - How Did You Hear About Us (dropdown + conditional text)

7. **Upload Links** (generated for client)
   - Pictures Upload Link
   - Plans Upload Link

**Processing**: All data is validated, contact record is created/updated, lead record is created with contact linkage.

## Lead Editing Flow (edit.php → post.php)

**Purpose**: Manage existing leads with mixed editable/display-only information

**Information Display Structure**:

### 1. **Lead Header** (Display Only)
- Lead Number (from database)
- Current Stage (badge display, editable via dropdown at bottom)

### 2. **Contact Information** (Display Only + Contact Selector)
- **Contact Selector**: Dropdown to switch between multiple contacts for same property
- **Display Fields**: Full Name, Email, Cell Phone (read-only, populated from selected contact)
- **New Contact Button**: Link to create additional contacts for this property

### 3. **Property Address** (Display Only)
- Street Address, City, State, Postal Code, Country
- All address information is read-only (edit via contact record)

### 4. **Project Information** (Editable)
- **Project Name**: Text input (editable)
- **Project Description**: Textarea (editable)

### 5. **Structure Information** (Display Only - Collapsible)
- **Structure Type**: Building type classification (e.g., "Rambler - One Story")
- **Structure Description**: Selected building features/characteristics (conditional display)
- **Structure Other**: Custom structure description text (conditional display)
- **Structure Additional**: Additional buildings or notes (conditional display, supports multi-line)
- **Note**: Structure information is captured during lead creation and not editable in lead edit

### 6. **File Upload Links** (Display Only - Collapsible)
- Plans Upload Link (read-only display with copy functionality)
- Pictures Upload Link (read-only display with copy functionality)
- **Purpose**: Shows client-facing upload URLs generated during lead creation

### 7. **Notes Management** (Fully Interactive)
- **Add New Notes**: Textarea with contact association
- **Notes List**: Searchable, sortable list of all notes
- **Note Actions**: Edit, delete individual notes
- **Contact Association**: Notes can be linked to specific contacts

### 8. **Stage Management** (Editable)
- Stage dropdown at bottom of form
- Updates lead progression status

## Key Information Flow Principles

### Data Ownership & Editing Rules:
1. **Contact Data**: Owned by contact record, read-only in lead edit
2. **Property Address**: Owned by contact record, read-only in lead edit
3. **Structure Information**: Captured at lead creation, read-only in lead edit
4. **Upload Links**: Generated at creation, read-only in lead edit
5. **Project Details**: Lead-specific, fully editable
6. **Notes**: Lead-specific, fully interactive (CRUD operations)
7. **Stage**: Lead-specific, editable

### Contact Integration:
- **One Property, Multiple Contacts**: Property can have multiple associated contacts
- **Contact Selector**: Switch between contacts to view different contact information
- **Shared Property Data**: All contacts share the same property address
- **Independent Contact Data**: Each contact has unique name, email, phone

### User Experience Design:
- **Collapsible Sections**: Structure Information and File Upload Links collapse to reduce visual clutter
- **Clear Visual Distinction**: Editable fields use form inputs, read-only data uses styled display boxes
- **Contextual Actions**: New Contact button in contact section, note management in notes section
- **Progressive Disclosure**: Collapsible sections show detailed information only when needed

## File Structure & Processing:

**Creation Files**:
- `leads/new.php` - Lead creation form (all fields editable)
- `leads/post.php` - Form processing with contact integration
- `leads/get.php` - Data retrieval and validation

**Management Files**:
- `leads/edit.php` - Lead management interface (mixed editable/display)
- `leads/view.php` - Read-only lead viewing
- `leads/list.php` - Lead listing with search/filter
- `leads/delete.php` - Lead deletion with cascade handling

This workflow ensures clear separation between initial data collection (creation) and ongoing lead management (editing), with appropriate data ownership and editing permissions for each phase.

## Core Components
**Business Logic Classes** (extend Database for inheritance-based connection access):
- Database: Singleton database connection class with embedded credentials
- Helpers: Multilingual support and translation-aware HTML generation
- Users, Leads, Contacts, Notes: Entity management classes
- Roles, Permissions, RolesPermissions: Security management classes
- Audit, Security, Sessions: System functionality classes

**Display Classes** (table rendering and list management):
- Table, ViewTable, EditDeleteTable, ActionTable: Base table display classes
- UsersList, LeadsList, ContactsList: Entity-specific list displays
- FormComponents: Dynamic form generation utilities

**Direct File Controllers** (no routing framework):
- Individual PHP files in `public_html/` directories (users/, leads/, contacts/)
- Direct file access pattern: `/leads/list.php`, `/users/edit.php`
- Language-aware form processing with template inclusion

# Server & Access Configuration

## SSH Configuration
This project is hosted on a remote server accessible via SSH with certificate-based authentication.

**SSH Config Entry** (in `~/.ssh/config`):
```
[REDACTED]
```

**SSH Key Setup:**
```bash
# Generate SSH key pair (if not already done)
ssh-keygen -t rsa -b 4096 -f ~/.ssh/[REDACTED] -C "your-email@example.com"

# Copy public key to server (replace with your actual public key)
# Public key content should be added to server's ~/.ssh/authorized_keys
cat ~/.ssh/[REDACTED].pub
```

**Public Key for Server Setup:**
```
# Copy this public key to the server's ~/.ssh/authorized_keys file
# Replace with your actual public key content:
[REDACTED]
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
chmod 600 ~/.ssh/[REDACTED]
chmod 644 ~/.ssh/[REDACTED].pub

# Add key to SSH agent
ssh-add ~/.ssh/[REDACTED]
```

## SSL Certificates
The project uses SSL certificates located in the `[REDACTED] directory:
- `[REDACTED] - Auto SSL certificates
- `[REDACTED] - Commercial SSL certificates

[REDACTED]
## Multi-Project Server Notes
This server hosts multiple projects. When working with this repository:
- Always use absolute paths when referencing files
- Be aware of shared resources and dependencies
- Check ownership/permissions if encountering access issues
- Use the SSH alias `wswg` for consistent access

## File Operations

### File Ownership
- **File Ownership**: 
  - **Local Machine**: Files should be owned by `mark:users` user and group
  - **Remote Server**: Files should be owned by `democrm:democrm` user and group
- **CRITICAL**: After creating ANY file, ALWAYS set proper ownership:
  - Local: `chown mark:users filename`
  - Remote: `chown democrm:democrm filename`
- **File Permissions**:
  - Regular files: `chmod 644`
  - Executable files: `chmod 755`
  - Directories: `chmod 755`
- **Web Server Access**: Ensure proper permissions for web server access

### 🔧 MANDATORY File Creation Workflow

**🚀 FASTEST METHOD - Fix All Ownership at Once**:
```bash
# Fix ownership for entire project (run after creating multiple files)
# NOTE: This must be run on the actual server, not through SFTP mount
# The server is a shared hosting environment with specific ownership requirements
chown -R democrm:democrm /home/democrm/
chgrp nobody /home/democrm/public_html
```

**📁 Individual File Method** (for single files):
```bash
# 1. Create the file (via WriteFile tool or other method)
# 2. IMMEDIATELY set proper ownership
chown democrm:democrm /path/to/new/file

# 3. Set proper permissions
chmod 644 /path/to/new/file  # for regular files
chmod 755 /path/to/new/file  # for executable files

# 4. Verify ownership and permissions
ls -la /path/to/new/file
```

**🎯 RECOMMENDED WORKFLOW**:
1. **Create all needed files** using WriteFile tool
2. **SSH to server and run the fast ownership fix**: `ssh wswg "chown -R democrm:democrm /home/democrm/ && chgrp nobody /home/democrm/public_html"`
3. **Verify critical files**: `ls -la /home/democrm/sql/migrations/`

**⚠️ SHARED SERVER NOTES**:
- **Ownership commands must be run on the actual server** (not through SFTP mount)
- **Use SSH to execute ownership commands**: `ssh wswg "chown command"`
- **Files created through SFTP mount** will show as `mark:users` locally but are `democrm:democrm` on server
- **Web server requires specific ownership** for proper access and security

**Example for SQL migration files**:
```bash
# After creating multiple migration files
chown -R democrm:democrm /home/democrm/
chgrp nobody /home/democrm/public_html
ls -la /home/democrm/sql/migrations/  # verify ownership
```


# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Critical: Remote Development Environment

**This is a remote coding project via SFTP at `sftp://159.203.116.150:222/home/democrm`**

### Remote Development Rules
- Full remote path: `/run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm`
- Changes are immediately live on production server
- File ownership MUST be `democrm:democrm` - use `ssh wswg "chown democrm:democrm /path/to/file"` after creating files
- SSH alias: `wswg` (configured in SSH config)

### Command Execution: SSH vs SFTP
**ALL execution commands MUST run via SSH, not through SFTP mount:**

```bash
# CORRECT: Run tests via SSH
ssh wswg "cd /home/democrm && vendor/bin/phpunit"

# WRONG: Running through SFTP mount will hang/timeout
cd /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm && vendor/bin/phpunit
```

**Commands requiring SSH:**
- `vendor/bin/phpunit` - All PHPUnit test execution
- `php script.php` - Any PHP script execution
- `composer install/update` - Composer operations
- Database operations

**File operations via SFTP mount are OK** (read, edit, cp, mv, rm)

## Development Commands

### Testing
```bash
# Full test suite (827 tests, ~2 minutes)
ssh wswg "cd /home/democrm && vendor/bin/phpunit"

# Core classes only (100% passing - Nonce, Sessions)
ssh wswg "cd /home/democrm && vendor/bin/phpunit --filter='Nonce|Sessions'"

# Specific test suite
ssh wswg "cd /home/democrm && vendor/bin/phpunit --testsuite=LeadsModule"

# Single test file
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/NonceTest.php"

# With detailed output
ssh wswg "cd /home/democrm && vendor/bin/phpunit --testdox"

# With coverage
ssh wswg "cd /home/democrm && composer test-coverage"
```

### Playwright Browser Tests
```bash
npm run test              # Run all Playwright tests
npm run test:headed       # Run with visible browser
npm run test:ui           # Interactive UI mode
npm run test:debug        # Debug mode
npm run test:report       # Show test report
```

### Composer
```bash
ssh wswg "cd /home/democrm && composer install"
ssh wswg "cd /home/democrm && composer update"
ssh wswg "cd /home/democrm && composer test"
```

## Architecture Overview

### Non-Traditional PHP Architecture
DemoCRM does NOT follow MVC. It uses:
- **Template-based rendering** with sequential includes
- **Direct file routing** (module/action.php structure)
- **Database singleton pattern** with class inheritance
- **Session-centric security** for performance

### Directory Structure
```
public_html/          # Web-accessible files (modules live here)
├── admin/           # Admin panel modules
│   ├── email/      # Email management
│   ├── leads/      # Lead operations
│   ├── languages/  # Translation files
│   ├── security/   # Security utilities
│   └── tools/      # Admin tools
├── contacts/        # Contact module
├── leads/          # Lead module
├── templates/      # Shared template components
└── assets/         # CSS, JS, images

classes/             # Application classes (NOT web-accessible)
├── Core/           # Database, Security, Sessions, Nonce
├── Models/         # Leads, Contacts, Users, etc.
├── Utilities/      # Helpers (200+ methods), FormComponents
├── Views/          # LeadsList, ContactsList (DataTables)
└── Logging/        # Audit, SqlErrorLogger

sql/                # Database schemas and migrations
tests/              # PHPUnit and Playwright tests
```

### Database Connection Pattern - CRITICAL

**All model classes MUST use this exact pattern:**

```php
class YourModel extends Database {
    public function __construct() {
        parent::__construct($this->dbcrm());
    }

    public function yourMethod() {
        // CORRECT: Use $this->dbcrm() to get PDO connection
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
```

**Critical rules:**
1. Always extend Database class
2. Call `parent::__construct($this->dbcrm())`
3. Use `$this->dbcrm()` for PDO connection
4. NEVER use `$this->conn()` or `$this->connection()` (these don't exist)

### Template System (Required Pattern)

Every page MUST follow this exact template sequence:

```php
// Set page variables first
$dir = 'module_name';     // Module name for routing
$page = 'action_name';    // Action type (new, edit, list, view)
$table_page = true;       // Enable DataTables (for list pages)
$table_header = true;     // Show table headers

// Then include templates in this order
require HEADER;           // HTML head, CSS, meta tags
require BODY;             // Body opening, navigation setup
require NAV;              // Dynamic navigation based on permissions
require SECTIONOPEN;      // Content area opening
// Page-specific content here
require SECTIONCLOSE;     // Content area closing
require FOOTER;           // JavaScript loading, body closing
```

## Internationalization - Mandatory

**NO hardcoded text allowed** - all text must use translation keys.

```php
// Language files location
public_html/admin/languages/
├── en.php              # English (primary)
├── es.php              # Spanish (complete)
└── login/
    ├── en.php
    └── es.php

// Using translations
$helpers = new Helpers();
$text = $helpers->getTranslation('key_name', $current_language);

// Translation key format in language files
$lang = [
    'navbar_contacts' => "Contacts",
    'full_name' => 'Full Name',
    'role_id_1' => 'Administrator',
];
```

**Regional requirements:**
- UTF-8 (utf8mb4) everywhere in database and code
- Date/time with timezone support
- International phone validation
- Currency formatting (ISO 4217)

## Database Operations

### SQL-First Migration Strategy - CRITICAL

**NEVER create PHP migration scripts. ALWAYS use SQL files.**

```sql
-- Location: /sql/migrations/YYYY_MM_DD_description.sql
-- Or: /scripts/migration_name.sql (for immediate use)

-- =====================================================
-- Migration: [Description]
-- Date: YYYY-MM-DD
-- =====================================================

-- Pre-migration verification
SELECT 'Starting migration...' as status;

-- Migration steps here
ALTER TABLE table_name ADD COLUMN new_column VARCHAR(255);

-- Post-migration verification
SELECT 'Migration completed successfully' as status;
```

### Schema Reference Files
- `sql/democrm_democrm_structure.sql` - Structure only (33K, development reference)
- `sql/democrm_democrm.sql` - Full database (7.2M, production restore)

### Always Verify Schema First
Before writing INSERT/UPDATE statements, always run:
```sql
DESCRIBE table_name;
```

Field names may differ from documentation. For example:
- `email_templates.active` (NOT `is_active`)
- `email_template_content.subject` (NOT in `email_templates`)

## Security Requirements

### Input Validation
- Sanitize ALL user input
- Use prepared statements (NEVER string concatenation in SQL)
- Escape output appropriately
- Implement CSRF token validation

### Authentication
```php
// Security class handles authentication
$security = new Security();
$security->checkLogin();  // Verify user is logged in
$security->checkPermission($permission_name);  // Check specific permission
```

### Audit Logging
```php
// Standard audit pattern
$audit = new Audit();
$audit->logAction($user_id, $action, $table_name, $record_id, $details);
```

## Key Utilities

### Helpers Class
200+ utility methods for translation-aware HTML generation:
- `getTranslation()` - Retrieve translations
- `generateSelectOptions()` - Translated dropdowns
- `formatDate()`, `formatCurrency()` - Locale-aware formatting
- Form element generation with automatic translation

### FormComponents Class
Generates consistent form elements with validation.

### LeadsList / ContactsList
DataTables integration for list views with server-side processing.

## Testing Framework

### Current Status (Phase 1 Complete)
- **Total Tests**: 827 tests, 2,247 assertions
- **Core Classes**: 100% passing (Nonce, Sessions)
- **Execution Time**: ~2 minutes via SSH
- **Test Database**: `democrm_test` (configured in phpunit.xml)

### Test Organization
```
tests/phpunit/
├── Unit/              # Unit tests
│   ├── Core/         # Database, Nonce, Sessions
│   ├── Models/       # Leads, Contacts, Users
│   └── Utilities/    # Helpers, FormComponents
├── Integration/       # Cross-component tests
└── Feature/          # End-to-end workflows
```

### Testing Environment
- Test mode enabled via `phpunit.xml` env vars
- Persistent test database (not ephemeral)
- Base URL: `https://democrm.waveguardco.net`

## Common Patterns

### Creating a New Module
1. Create directory in `public_html/[module]/`
2. Create model class extending Database in `classes/Models/`
3. Add translation keys to `public_html/admin/languages/en.php` and `es.php`
4. Follow template sequence pattern in all pages
5. Add navigation items to appropriate nav template
6. Create tests in `tests/phpunit/Unit/` and `Integration/`

### Handling Form Submissions
```php
// POST handling pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $nonce = new Nonce();
    if (!$nonce->validate($_POST['csrf_token'], 'form_action')) {
        // Handle invalid token
    }

    // Sanitize input
    $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

    // Process with model
    $model = new YourModel();
    $result = $model->create($data);

    // Log action
    $audit = new Audit();
    $audit->logAction($_SESSION['rid'], 'create', 'table_name', $result['id'], json_encode($data));
}
```

## File Organization

### Module Structure
```
public_html/[module]/
├── new.php           # Create new record
├── edit.php          # Edit existing record
├── view.php          # View record details
├── list.php          # List/DataTable view
└── delete.php        # Delete record
```

### API Endpoints
```
public_html/admin/api/
├── leads_api.php
├── contacts_api.php
└── [module]_api.php
```

## Additional Resources

For comprehensive documentation, see `.zencoder/rules/`:
- `repo.md` - Complete project overview and remote development setup
- `architecture-complete.md` - Detailed architecture patterns
- `development-workflow.md` - Coding standards and integration patterns
- `testing-complete.md` - Complete testing framework documentation
- `database-operations.md` - Database schema and migration guidelines
- `internationalization-complete.md` - Multilingual system details

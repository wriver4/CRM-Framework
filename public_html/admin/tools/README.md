# DemoCRM Framework Compliance Tools

## Overview

This directory contains CLI tools designed to help developers maintain framework compliance and catch common errors before they reach production. These tools validate database schemas, translation keys, framework patterns, generate optimized SQL queries, create UI components, check security vulnerabilities, and add error handling.

**Tool Tiers:**
- **Tier 1** (Critical): Database schema, translation keys, framework compliance, query generation
- **Tier 2** (High Value): UI components, security validation, AJAX endpoints, naming conventions
- **Tier 3** (Quality of Life): Error handling, testing checklists, documentation

## 🚨 CRITICAL: Execution Requirements

**These tools MUST be executed via SSH, not from the SFTP mount:**

```bash
ssh wswg "cd /home/democrm/public_html && php admin/tools/[tool_name].php [arguments]"
```

**Why SSH is required:**
- The SFTP mount cannot establish database connections
- PHP CLI from the mount path will fail with "Access denied" errors
- All database operations require SSH execution

## Available Tools

### 1. get_database_schema.php
**Purpose**: Retrieve and validate database table schemas

**Usage**:
```bash
# Display schema for a table
ssh wswg "cd /home/democrm/public_html && php admin/tools/get_database_schema.php email_queue"

# Get JSON output
ssh wswg "cd /home/democrm/public_html && php admin/tools/get_database_schema.php email_queue --json"

# Validate specific columns exist
ssh wswg "cd /home/democrm/public_html && php admin/tools/get_database_schema.php users --validate=id,full_name,email"
```

**Features**:
- Lists all columns with types, nullable status, and keys
- Shows foreign key relationships
- Validates column existence
- JSON output for programmatic use
- Can be included in other PHP scripts

**Example Output**:
```
═══════════════════════════════════════════════════════════════
  TABLE: email_queue
═══════════════════════════════════════════════════════════════

COLUMNS:
───────────────────────────────────────────────────────────────
Name                      Type                 Nullable Key
───────────────────────────────────────────────────────────────
id                        int(11)              NO       PRI
template_id               int(11)              NO       MUL
status                    enum(...)            YES      MUL
created_by                int(11)              YES      
...
```

### 2. check_translation_keys.php
**Purpose**: Validate translation keys exist in language files

**Usage**:
```bash
# Check if a key exists
ssh wswg "cd /home/democrm/public_html && php admin/tools/check_translation_keys.php email_queue_title"

# Check multiple keys
ssh wswg "cd /home/democrm/public_html && php admin/tools/check_translation_keys.php email_queue_title,email_queue_status"

# Get JSON output
ssh wswg "cd /home/democrm/public_html && php admin/tools/check_translation_keys.php email_queue_title --json"
```

**Features**:
- Checks both English and Spanish language files
- Identifies missing translations
- Suggests similar existing keys
- JSON output for automation
- Can be included in other PHP scripts

**Example Output**:
```
═══════════════════════════════════════════════════════════════
  TRANSLATION KEY CHECK
═══════════════════════════════════════════════════════════════

Checking key: email_queue_title

✓ FOUND in English (en.php)
✓ FOUND in Spanish (es.php)

STATUS: Valid - Key exists in all languages
```

### 3. validate_framework_compliance.php
**Purpose**: Analyze PHP files for framework compliance issues

**Usage**:
```bash
# Validate a file
ssh wswg "cd /home/democrm/public_html && php admin/tools/validate_framework_compliance.php admin/system_email_management/queue/list.php"

# Get JSON output
ssh wswg "cd /home/democrm/public_html && php admin/tools/validate_framework_compliance.php admin/system_email_management/queue/list.php --json"
```

**Features**:
- Detects hardcoded strings that should use translations
- Validates translation key usage
- Checks for proper UI component usage
- Identifies security issues (SQL injection risks, XSS vulnerabilities)
- Calculates compliance score
- Provides actionable suggestions

**Checks Performed**:
- ✓ Translation key validation
- ✓ Hardcoded string detection
- ✓ SQL injection prevention
- ✓ XSS vulnerability detection
- ✓ Framework pattern compliance
- ✓ UI component usage

**Example Output**:
```
═══════════════════════════════════════════════════════════════
  FRAMEWORK COMPLIANCE VALIDATION
═══════════════════════════════════════════════════════════════

File: admin/system_email_management/queue/list.php

ISSUES FOUND: 15 (8 errors, 7 warnings)

ERRORS:
───────────────────────────────────────────────────────────────
Line 45: Missing translation key 'email_queue_status'
  → Add to language files: en.php and es.php

Line 67: Hardcoded string "Email Queue"
  → Use translation: $lang['email_queue_title']

...

COMPLIANCE SCORE: 45/100
```

### 4. generate_query.php
**Purpose**: Generate optimized SQL queries with schema validation

**Usage**:
```bash
# Generate SELECT query
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_query.php email_queue --select=id,subject,status,created_at --join=users:created_by:id:full_name"

# Generate with WHERE clause
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_query.php email_queue --select=* --where='status=pending'"

# Get JSON output
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_query.php email_queue --select=* --json"
```

**Features**:
- Validates all column names against actual schema
- Generates optimized JOIN statements
- Creates pagination-ready queries
- Prevents SQL injection with prepared statement placeholders
- Detects invalid column references
- JSON output for programmatic use

**Example Output**:
```
═══════════════════════════════════════════════════════════════
  SQL QUERY GENERATOR
═══════════════════════════════════════════════════════════════

Table: email_queue

GENERATED QUERY:
───────────────────────────────────────────────────────────────
SELECT 
    q.id,
    q.subject,
    q.status,
    q.created_at,
    u.full_name as created_by_name
FROM email_queue q
LEFT JOIN users u ON q.created_by = u.id
WHERE q.status = ?
ORDER BY q.created_at DESC
LIMIT ? OFFSET ?

PARAMETERS: ['pending', 50, 0]

COUNT QUERY (for pagination):
───────────────────────────────────────────────────────────────
SELECT COUNT(*) as total
FROM email_queue q
WHERE q.status = ?
```

### 5. generate_ui_component.php (Tier 2)
**Purpose**: Generate framework-compliant UI components

**Usage**:
```bash
# Generate a data table
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_ui_component.php --type=data_table --columns=id,name,email,status --prefix=user"

# Generate a form
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_ui_component.php --type=form --fields=name:text:required,email:email:required,status:select --prefix=user"

# Generate filters
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_ui_component.php --type=filters --filters=status,date_range --prefix=email_queue"

# Generate page header
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_ui_component.php --type=page_header --title='Email Queue' --prefix=email_queue"

# Generate action buttons
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_ui_component.php --type=action_buttons --actions=create,export --prefix=email_template"
```

**Features**:
- Generates Bootstrap 5 compliant HTML
- Creates proper PHP loops for data display
- Includes JavaScript for validation
- Lists all required translation keys
- Supports: data_table, form, filters, page_header, action_buttons, alert

### 6. validate_security.php (Tier 2)
**Purpose**: Check PHP files for security vulnerabilities

**Usage**:
```bash
# Run all security checks
ssh wswg "cd /home/democrm/public_html && php admin/tools/validate_security.php admin/users/edit.php"

# Run specific checks only
ssh wswg "cd /home/democrm/public_html && php admin/tools/validate_security.php admin/users/post.php --checks=sql_injection,xss"

# JSON output
ssh wswg "cd /home/democrm/public_html && php admin/tools/validate_security.php admin/users/list.php --json"
```

**Features**:
- Detects SQL injection vulnerabilities
- Finds XSS (Cross-Site Scripting) issues
- Checks CSRF protection
- Validates input sanitization
- Checks authentication requirements
- Validates file upload security
- Calculates security score (0-100)
- Categorizes by severity: Critical, High, Medium, Low

**Checks Performed**:
- ✓ SQL injection prevention (prepared statements)
- ✓ XSS prevention (htmlspecialchars usage)
- ✓ CSRF token validation
- ✓ Input validation and sanitization
- ✓ Authentication checks
- ✓ File upload security
- ✓ Session security

### 7. generate_ajax_endpoint.php (Tier 2)
**Purpose**: Generate complete AJAX endpoints with backend and frontend code

**Usage**:
```bash
# Generate GET endpoint
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_ajax_endpoint.php --type=get --table=users --operations=validate,log"

# Generate POST endpoint
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_ajax_endpoint.php --type=post --table=email_queue --operations=validate,save,log"

# Generate DELETE endpoint
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_ajax_endpoint.php --type=delete --table=email_templates --operations=validate,log"

# Generate SEARCH endpoint
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_ajax_endpoint.php --type=search --table=leads --operations=validate"
```

**Features**:
- Generates backend PHP endpoint
- Generates frontend JavaScript code
- Includes authentication checks
- Adds CSRF protection
- Implements error handling
- Uses database schema for validation
- Returns JSON responses
- Lists required translation keys

**Endpoint Types**:
- **get**: Fetch single record by ID
- **post**: Create or update record
- **delete**: Delete record with confirmation
- **search**: Search records with autocomplete support

### 8. suggest_naming.php (Tier 2)
**Purpose**: Suggest framework-compliant naming conventions based on actual Contacts/Leads patterns

**Usage**:
```bash
# Suggest translation key
ssh wswg "cd /home/democrm/public_html && php admin/tools/suggest_naming.php --type=translation_key --text='First Name' --module=contact"

# Suggest CSS class
ssh wswg "cd /home/democrm/public_html && php admin/tools/suggest_naming.php --type=css_class --context='primary save button'"

# Suggest variable name
ssh wswg "cd /home/democrm/public_html && php admin/tools/suggest_naming.php --type=variable --context='database results for leads'"

# Suggest database column
ssh wswg "cd /home/democrm/public_html && php admin/tools/suggest_naming.php --type=db_column --context='personal street address'"

# Suggest file name
ssh wswg "cd /home/democrm/public_html && php admin/tools/suggest_naming.php --type=file --context='list page' --module=opportunities"

# JSON output
ssh wswg "cd /home/democrm/public_html && php admin/tools/suggest_naming.php --json --type=translation_key --text='Create New' --module=opportunity"
```

**Features**:
- Based on actual Contacts and Leads module patterns
- Suggests translation keys following {module}_{element} pattern (NOT {module}_{page}_{element}_{type})
- Recommends Bootstrap 5 CSS classes
- Suggests variable names with proper prefixes
- Recommends database columns with address prefixes (p_, b_, m_, form_)
- Provides alternatives and pattern explanations
- JSON output for programmatic use

**Naming Types**:
- **translation_key**: Translation key suggestions (no _label, _button suffixes)
- **css_class**: Bootstrap 5 class combinations
- **variable**: Variable naming patterns ($results, $row, class instances)
- **db_column**: Database column names with prefixes
- **file**: File naming conventions (list.php, edit.php, get.php, etc.)

**Key Insights**:
- Framework uses simple {module}_{element} pattern for translation keys
- No suffixes like _label, _button, _title are used
- Address fields use consistent prefixes: p_ (personal), b_ (business), m_ (mailing), form_ (form data)
- Class instances use plural names: $contacts, $leads, $users
- Bootstrap 5 patterns: btn btn-primary, form-control, form-select

### 9. add_error_handling.php (Tier 3)
**Purpose**: Add framework-compliant error handling to code

**Usage**:
```bash
# Wrap a file with database error handling
ssh wswg "cd /home/democrm/public_html && php admin/tools/add_error_handling.php --file=admin/users/post.php --types=database,validation --context=page"

# Wrap code snippet for AJAX endpoint
ssh wswg "cd /home/democrm/public_html && php admin/tools/add_error_handling.php --code='\$stmt->execute();' --types=database --context=ajax"

# Generate error display component
ssh wswg "cd /home/democrm/public_html && php admin/tools/add_error_handling.php --component --context=page"
```

**Features**:
- Wraps code in try-catch blocks
- Adds proper error logging
- Generates user-friendly error messages
- Context-aware (page, ajax, api)
- Creates error display components
- Lists required translation keys

**Error Types**:
- **database**: PDO exception handling
- **validation**: Form validation errors
- **api**: General exception handling

**Contexts**:
- **page**: Session messages with redirects
- **ajax**: JSON responses
- **api**: JSON with HTTP status codes

## Programmatic Usage

All tools can be included and used programmatically in PHP scripts:

```php
<?php
// Include the tool
require_once __DIR__ . '/get_database_schema.php';

// Use the validator
$validator = new DatabaseSchemaValidator($pdo);
$schema = $validator->getTableSchema('email_queue');

// Check if column exists
if (isset($schema['columns']['created_by'])) {
    echo "Column exists!";
}
```

## Integration Examples

### Pre-Commit Hook
```bash
#!/bin/bash
# Validate all modified PHP files
for file in $(git diff --cached --name-only --diff-filter=ACM | grep '\.php$'); do
    ssh wswg "cd /home/democrm/public_html && php admin/tools/validate_framework_compliance.php $file"
done
```

### CI/CD Pipeline
```yaml
- name: Validate Framework Compliance
  run: |
    ssh wswg "cd /home/democrm/public_html && php admin/tools/validate_framework_compliance.php admin/module/file.php --json" > compliance.json
    # Parse JSON and fail if score < 80
```

## Common Use Cases

### 1. Before Writing a Query
```bash
# Check what columns exist
ssh wswg "cd /home/democrm/public_html && php admin/tools/get_database_schema.php email_queue"

# Generate the query
ssh wswg "cd /home/democrm/public_html && php admin/tools/generate_query.php email_queue --select=id,subject,status"
```

### 2. Adding New Translations
```bash
# Check if key already exists
ssh wswg "cd /home/democrm/public_html && php admin/tools/check_translation_keys.php new_feature_title"

# If missing, add to language files, then verify
ssh wswg "cd /home/democrm/public_html && php admin/tools/check_translation_keys.php new_feature_title"
```

### 3. Code Review
```bash
# Validate compliance before submitting PR
ssh wswg "cd /home/democrm/public_html && php admin/tools/validate_framework_compliance.php admin/new_module/list.php"
```

## Error Prevention

These tools would have prevented common production errors:

**Example 1: Wrong Column Name**
```
ERROR: Unknown column 'q.queued_by' in 'on clause'
```
✓ `get_database_schema.php` shows the correct column is `created_by`
✓ `generate_query.php` validates column names before generating queries

**Example 2: Missing Translation**
```
WARNING: Undefined array key 'email_queue_status'
```
✓ `check_translation_keys.php` detects missing keys
✓ `validate_framework_compliance.php` finds hardcoded strings

**Example 3: Wrong Table Name**
```
ERROR: Table 'email_logs' doesn't exist
```
✓ `get_database_schema.php` shows the correct table is `email_send_log`

## Technical Details

### Database Connection
- Uses minimal Database class loading
- Credentials: democrm_democrm@localhost
- Avoids full framework initialization for performance
- Error suppression during class loading to prevent service dependency errors

### Language Files
- Location: `/home/democrm/public_html/admin/languages/`
- Supported: `en.php` (English), `es.php` (Spanish)
- Variable: `$lang` array

### Execution Environment
- PHP 8.3+
- MariaDB 10+
- Must run via SSH (not SFTP mount)
- Working directory: `/home/democrm/public_html`

## Known Limitations

1. **Validation Tool False Positives**: May flag old template patterns (e.g., `require HEADER`) as errors when they're actually valid legacy code
2. **Security Tool False Positives**: May flag `$lang[]` output as XSS when it's actually safe (translation keys are controlled)
3. **SSH Required**: Cannot run from SFTP mount due to database connection restrictions
4. **Language Support**: Currently only validates English and Spanish
5. **Performance**: Large files may take longer to validate
6. **AJAX Generator Schema Dependency**: Works best when database schema is accessible

## Future Enhancements

### Tier 2 (Remaining)
- **suggest_naming.php**: Naming convention suggester for variables, keys, classes, files

### Tier 3 (Remaining)
- **generate_test_checklist.php**: Generate testing checklists for features
- **add_permission_checks.php**: Add role-based access control
- **generate_documentation.php**: Auto-generate documentation from code

### Advanced Features
- Automated fix suggestions (auto-correct common issues)
- Integration with IDE (VSCode extension)
- Real-time validation during development
- Support for additional languages
- Pre-commit hook installer
- CI/CD pipeline templates

## Support

For issues or questions:
1. Check this README
2. Review `.zencoder/rules/` documentation
3. Test with `--json` flag for detailed output
4. Verify SSH execution method is being used

## Version History

- **v2.0** (2025-01-12): Tier 2 & 3 tools added
  - UI component generator (Tier 2)
  - Security validator (Tier 2)
  - AJAX endpoint generator (Tier 2)
  - Error handling wrapper (Tier 3)
  - Enhanced README with comprehensive examples
  
- **v1.0** (2025-01-12): Initial release of Tier 1 tools
  - Database schema validator
  - Translation key checker
  - Framework compliance validator
  - SQL query generator

## Tool Summary

| Tool                                | Tier | Purpose                         | Key Feature                      |
| ----------------------------------- | ---- | ------------------------------- | -------------------------------- |
| `get_database_schema.php`           | 1    | Database schema validation      | Prevents column name errors      |
| `check_translation_keys.php`        | 1    | Translation key validation      | Ensures all keys exist           |
| `validate_framework_compliance.php` | 1    | Framework pattern validation    | Catches compliance issues        |
| `generate_query.php`                | 1    | SQL query generation            | Validates queries before writing |
| `generate_ui_component.php`         | 2    | UI component generation         | Creates compliant components     |
| `validate_security.php`             | 2    | Security vulnerability scanning | Finds SQL injection, XSS, CSRF   |
| `generate_ajax_endpoint.php`        | 2    | AJAX endpoint generation        | Full backend + frontend code     |
| `add_error_handling.php`            | 3    | Error handling wrapper          | Adds try-catch and logging       |
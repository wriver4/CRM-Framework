# Scripts Directory

This directory contains all utility, automation, testing, and database scripts for the DemoCRM project.

## Directory Structure

```
scripts/
├── database/           # Database-related scripts
├── testing/            # All testing scripts
├── automation/         # Cron jobs and automated tasks
├── utilities/          # General utility scripts
└── README.md          # This file
```

## Database Scripts

### `database/migrations/`
Database migration scripts for schema changes and data transformations.

**Files:**
- `apply_executive_migration.php` - Creates executive role hierarchy
- `apply-migration.php` - Applies rid→role_id migration to prod/test DBs
- `migrate-direct.php` - Direct migration executor for RBAC changes
- `direct_migration.php` - Alternative direct migration tool
- `migrate_bridge_tables.php` - Migrates bridge table structures
- `migrate_stage_numbering.php` - Stage numbering system migration
- `run_migrations.php` - General migration runner
- `show_migration_preview.php` - Preview migration changes before applying
- `stage_migration_complete.sql` - Complete stage migration SQL
- `stage_migration.sql` - Stage system migration SQL
- `stage_remapping.php` - Stage remapping logic

**Usage:**
```bash
# Run a specific migration
php scripts/database/migrations/apply-migration.php

# Preview migration changes
php scripts/database/migrations/show_migration_preview.php
```

### `database/utilities/`
Database utility and maintenance scripts.

**Files:**
- `check_tables.php` - Verify table existence and email system tables
- `create-test-user.php` - Create test database user with proper permissions

**Usage:**
```bash
# Check database tables
php scripts/database/utilities/check_tables.php

# Create test user
php scripts/database/utilities/create-test-user.php
```

## Testing Scripts

### `testing/runners/`
Test execution scripts for PHPUnit, Playwright, and feature-specific tests.

**Files:**
- `run-core-tests.sh` - Runs Phase 1 Core Foundation tests
- `run-tests.sh` - General test runner (formerly run-tests)
- `run_tests_simple.sh` - Simple test runner
- `run_email_tests.sh` - Email system tests
- `run-note-deletion-tests.sh` - Note deletion tests
- `run-phpunit-nixos.sh` - PHPUnit runner for NixOS
- `run-tests.php` - PHP-based test runner
- `run-edit-workflow-tests.sh` - Edit workflow tests
- `run-calendar-tests.sh` - Calendar feature tests
- `run-calendar-comprehensive-tests.sh` - Comprehensive calendar tests
- `run-tests-nixos.sh` - General NixOS test runner
- `run-local-tests.sh` - Local environment test runner
- `run-leads-tests.sh` - Leads module tests

**Usage:**
```bash
# Run core tests
bash scripts/testing/runners/run-core-tests.sh

# Run calendar tests
bash scripts/testing/runners/run-calendar-tests.sh
```

### `testing/setup/`
Scripts for setting up test environments and installing dependencies.

**Files:**
- `setup-local-playwright.sh` - Setup Playwright for local testing
- `setup-local-tests.sh` - Setup local test environment
- `setup-test-environment.sh` - General test environment setup
- `install-playwright-nixos.sh` - Install Playwright on NixOS

**Usage:**
```bash
# Setup local test environment
bash scripts/testing/setup/setup-local-tests.sh

# Install Playwright
bash scripts/testing/setup/setup-local-playwright.sh
```

### `testing/verification/`
Scripts for verifying test framework and system configuration.

**Files:**
- `verify-testing-setup.php` - Comprehensive testing framework verification
- `test-mcp-server.sh` - MCP server configuration test
- `simple-test.php` - Simple test verification

**Usage:**
```bash
# Verify testing framework
php scripts/testing/verification/verify-testing-setup.php

# Test MCP server
bash scripts/testing/verification/test-mcp-server.sh
```

### `testing/feature-tests/`
Feature-specific test scripts for manual testing and verification.

**Files:**
- `test_email_template_system.php` - Email template system verification
- `test_action_buttons.php` - Action button functionality tests
- `test_edit_workflow.php` - Edit workflow tests
- `test_leads_list.php` - Leads list functionality tests
- `test_project_name_column.php` - Project name column tests
- `test_stage_mapping_only.php` - Stage mapping verification
- `test_stage_migration.php` - Stage migration tests
- `test_stage_notification.php` - Stage notification tests
- `test_stage_system.php` - Complete stage system tests

**Usage:**
```bash
# Test email template system
php scripts/testing/feature-tests/test_email_template_system.php

# Test stage system
php scripts/testing/feature-tests/test_stage_system.php
```

## Automation Scripts

### `automation/`
Cron jobs and scheduled automation tasks.

**Files:**
- `delegation_expiration_cron.php` - Checks and processes expired delegations
- `email_cron.php` - Processes email queue and sends pending emails
- `marketing_automation.php` - Marketing automation workflows
- `phplist_sync.php` - Syncs contacts with PHPList

**Usage:**
```bash
# Run email queue processor
php scripts/automation/email_cron.php

# Process delegations
php scripts/automation/delegation_expiration_cron.php
```

**Cron Setup:**
```cron
# Email queue processing (every 5 minutes)
*/5 * * * * php /home/democrm/scripts/automation/email_cron.php

# Delegation expiration check (daily at 1 AM)
0 1 * * * php /home/democrm/scripts/automation/delegation_expiration_cron.php
```

## Utility Scripts

### `utilities/`
General-purpose utility scripts.

**Files:**
- `generate_readme.php` - Generates README files
- `update_directory_tree.php` - Updates directory tree documentation
- `debug_button_html.php` - Debug HTML button rendering

**Usage:**
```bash
# Generate README
php scripts/utilities/generate_readme.php

# Update directory tree
php scripts/utilities/update_directory_tree.php
```

## Best Practices

1. **Always run from project root**: Most scripts expect to be run from `/home/democrm/`
2. **Check permissions**: Ensure scripts are executable (`chmod +x script.sh`)
3. **Review before running**: Migration scripts can modify production data
4. **Use test database**: Test migrations on test database first
5. **Check dependencies**: Ensure required PHP extensions and tools are installed

## Remote Development Notes

This is a **remote SFTP project**. When creating or modifying scripts:
- Set proper file ownership: `ssh wswg "chown democrm:democrm /path/to/script"`
- Test in development environment first
- Changes are immediately live on production server

## Need Help?

- See [/docs/QUICK_START.md](../docs/QUICK_START.md) for general documentation
- See [/docs/PLAYWRIGHT_LOCAL_TESTING.md](../docs/PLAYWRIGHT_LOCAL_TESTING.md) for Playwright testing
- See `.zencoder/rules/testing-complete.md` for comprehensive testing guide

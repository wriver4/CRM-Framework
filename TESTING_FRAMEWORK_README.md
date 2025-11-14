# Testing Framework Documentation

## Overview

This document describes the enhanced testing framework for DemoCRM, including PHPUnit for backend testing and Playwright for E2E testing, with full support for RBAC testing.

---

## 📋 Table of Contents

1. [Quick Start](#quick-start)
2. [Test Database Setup](#test-database-setup)
3. [PHPUnit Testing](#phpunit-testing)
4. [Playwright Testing](#playwright-testing)
5. [RBAC Testing](#rbac-testing)
6. [Configuration](#configuration)
7. [Best Practices](#best-practices)

---

## 🚀 Quick Start

### Prerequisites

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Install Playwright browsers
npx playwright install
```

### Setup Test Database

```bash
# Create test database with standard dataset
php tests/setup-test-database.php --mode=persistent --seed=standard

# Or use minimal dataset for faster tests
php tests/setup-test-database.php --mode=persistent --seed=minimal

# Reset test database
php tests/setup-test-database.php --reset

# Destroy test database
php tests/setup-test-database.php --destroy
```

### Run Tests

```bash
# Run all PHPUnit tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Integration
vendor/bin/phpunit --testsuite=Feature

# Run Playwright tests
npx playwright test

# Run specific Playwright test
npx playwright test rbac-permissions.spec.js

# Run with UI mode
npx playwright test --ui
```

---

## 🗄️ Test Database Setup

### Database Modes

#### 1. Persistent Mode (Recommended)
- Database persists between test runs
- Faster for repeated testing
- Data is reset between test suites
- Good for development

```bash
php tests/setup-test-database.php --mode=persistent
```

#### 2. Ephemeral Mode
- Database created and destroyed per test run
- Ensures clean state
- Slower but more isolated
- Good for CI/CD

```bash
php tests/setup-test-database.php --mode=ephemeral
```

### Seed Datasets

#### Minimal Dataset
- 2 users (admin + regular)
- 3 roles
- 10 basic permissions
- Fast setup, good for unit tests

```bash
php tests/setup-test-database.php --seed=minimal
```

#### Standard Dataset (Default)
- 5 users with different roles
- 5 roles with hierarchy
- 50 permissions (module + action level)
- 20 leads, 30 contacts
- Good for integration tests

```bash
php tests/setup-test-database.php --seed=standard
```

#### Full Dataset
- 20 users
- 10 roles with complex hierarchy
- 100 permissions (all levels)
- 100 leads, 150 contacts, 200 notes
- Good for performance testing

```bash
php tests/setup-test-database.php --seed=full
```

### Database Snapshots

```bash
# Create snapshot
php tests/create-snapshot.php --name=before_rbac_test

# Restore snapshot
php tests/restore-snapshot.php --name=before_rbac_test

# List snapshots
php tests/list-snapshots.php
```

---

## 🧪 PHPUnit Testing

### Test Structure

```
tests/phpunit/
├── Unit/              # Unit tests (isolated, no DB)
├── Integration/       # Integration tests (with DB)
├── Feature/          # Feature tests (full workflows)
├── Helpers/          # Test helpers
├── Fixtures/         # Test data fixtures
└── TestCase.php      # Base test class
```

### Writing Tests

#### Unit Test Example

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;

class LeadValidationTest extends TestCase
{
    public function testEmailValidation()
    {
        $lead = new Lead();
        $this->assertTrue($lead->isValidEmail('test@example.com'));
        $this->assertFalse($lead->isValidEmail('invalid-email'));
    }
}
```

#### Database Test Example

```php
<?php

namespace Tests\Integration;

use Tests\DatabaseTestCase;

class LeadCrudTest extends DatabaseTestCase
{
    protected $useTransactions = true;
    protected $seedData = true;
    
    public function testCreateLead()
    {
        $leadId = $this->insert('leads', [
            'first_name' => 'John',
            'family_name' => 'Doe',
            'email' => 'john@example.com'
        ]);
        
        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'email' => 'john@example.com'
        ]);
    }
    
    public function testUpdateLead()
    {
        $leadId = $this->insert('leads', [
            'first_name' => 'John',
            'family_name' => 'Doe'
        ]);
        
        $this->update('leads', 
            ['first_name' => 'Jane'],
            ['id' => $leadId]
        );
        
        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'first_name' => 'Jane'
        ]);
    }
}
```

#### RBAC Test Example

```php
<?php

namespace Tests\Integration;

use Tests\DatabaseTestCase;
use Tests\Helpers\RbacTestHelper;

class RbacPermissionTest extends DatabaseTestCase
{
    protected $rbacHelper;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->rbacHelper = new RbacTestHelper($this->getDb());
        $this->rbacHelper->seedStandardRbacData();
    }
    
    public function testSalesRepCanViewOwnLeads()
    {
        $userId = $this->rbacHelper->createUserWithRole('test_rep', 'sales_rep');
        $rbac = new RbacService();
        
        $this->assertTrue($rbac->hasPermission($userId, 'leads.view.own'));
        $this->assertFalse($rbac->hasPermission($userId, 'leads.view.all'));
    }
    
    public function testManagerCanViewAllLeads()
    {
        $userId = $this->rbacHelper->createUserWithRole('test_manager', 'sales_manager');
        $rbac = new RbacService();
        
        $this->assertTrue($rbac->hasPermission($userId, 'leads.view.all'));
    }
}
```

### Database Test Helpers

```php
// Insert data
$id = $this->insert('leads', ['first_name' => 'John']);

// Update data
$this->update('leads', ['stage' => 'Qualified'], ['id' => $id]);

// Delete data
$this->delete('leads', ['id' => $id]);

// Fetch data
$lead = $this->fetchOne('leads', ['id' => $id]);
$leads = $this->fetchAll('leads', ['stage' => 'New']);

// Count records
$count = $this->count('leads', ['stage' => 'New']);

// Assertions
$this->assertDatabaseHas('leads', ['email' => 'test@example.com']);
$this->assertDatabaseMissing('leads', ['email' => 'deleted@example.com']);
$this->assertDatabaseCount('leads', 5);
```

---

## 🎭 Playwright Testing

### Test Structure

```
tests/playwright/
├── rbac-permissions.spec.js    # RBAC E2E tests
├── rbac-helper.js              # RBAC test utilities
├── auth-helper.js              # Authentication utilities
├── calendar.spec.js            # Calendar tests
└── leads-edit.spec.js          # Leads workflow tests
```

### Writing E2E Tests

#### Basic Test

```javascript
const { test, expect } = require('@playwright/test');

test('User can create lead', async ({ page }) => {
  await page.goto('/login');
  await page.fill('input[name="username"]', 'test_user');
  await page.fill('input[name="password"]', 'test_password');
  await page.click('button[type="submit"]');
  
  await page.goto('/leads/new');
  await page.fill('input[name="first_name"]', 'John');
  await page.fill('input[name="family_name"]', 'Doe');
  await page.fill('input[name="email"]', 'john@example.com');
  await page.click('button[type="submit"]');
  
  await expect(page).toHaveURL(/\/leads\/view\/\d+/);
});
```

#### RBAC Test

```javascript
const { test, expect } = require('@playwright/test');
const { loginAs, canAccessModule, assertAccessDenied } = require('./rbac-helper');

test('Sales Rep cannot access admin module', async ({ page }) => {
  await loginAs(page, 'salesRep');
  await page.goto('/admin');
  await assertAccessDenied(page);
});

test('Sales Manager can access leads module', async ({ page }) => {
  await loginAs(page, 'salesManager');
  const hasAccess = await canAccessModule(page, 'leads', 'salesManager');
  expect(hasAccess).toBe(true);
});
```

---

## 🔐 RBAC Testing

### Test Users

The framework provides pre-configured test users with different permission levels:

| User                 | Role          | Permissions             |
| -------------------- | ------------- | ----------------------- |
| `test_super_admin`   | Super Admin   | All permissions         |
| `test_sales_manager` | Sales Manager | Sales module management |
| `test_sales_rep`     | Sales Rep     | Own leads + team leads  |
| `test_viewer`        | Viewer        | Read-only access        |
| `test_restricted`    | Restricted    | Minimal access          |

### Permission Levels

#### 1. Module Level
```php
// PHPUnit
$rbac->canAccessModule($userId, 'leads');

// Playwright
await canAccessModule(page, 'leads', 'salesRep');
```

#### 2. Action Level
```php
// PHPUnit
$rbac->canPerformAction($userId, 'leads', 'delete');

// Playwright
await canPerformAction(page, 'leads', 'delete', 'salesRep');
```

#### 3. Field Level
```php
// PHPUnit
$rbac->canAccessField($userId, 'leads', 'email', 'view');

// Playwright
await canViewField(page, 'email', 'salesRep');
await canEditField(page, 'email', 'salesRep');
```

#### 4. Record Level
```php
// PHPUnit
$rbac->canAccessRecord($userId, 'leads', $recordId, 'edit');

// Playwright
await canAccessRecord(page, 'leads', recordId, 'salesRep');
```

### RBAC Test Scenarios

```javascript
// Run all RBAC permission tests
npx playwright test rbac-permissions.spec.js

// Test specific permission level
npx playwright test rbac-permissions.spec.js -g "Module Level"
npx playwright test rbac-permissions.spec.js -g "Action Level"
npx playwright test rbac-permissions.spec.js -g "Field Level"
npx playwright test rbac-permissions.spec.js -g "Record Level"
```

---

## ⚙️ Configuration

### PHPUnit Configuration

**File:** `phpunit.xml`

```xml
<php>
  <env name="APP_ENV" value="testing"/>
  <env name="TESTING_MODE" value="true"/>
  <env name="TEST_DB_NAME" value="democrm_test"/>
  <env name="TESTING_MODE_TYPE" value="persistent"/>
</php>
```

### Playwright Configuration

**File:** `playwright.config.js`

```javascript
module.exports = defineConfig({
  testDir: './tests/playwright',
  use: {
    baseURL: 'https://democrm.waveguardco.net',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
});
```

### Testing Configuration

**File:** `config/testing.php`

```php
return [
    'enabled' => true,
    'mode' => 'persistent',
    'database' => [
        'persistent' => [
            'name' => 'democrm_test',
            'auto_reset' => true,
        ],
    ],
    'seeding' => [
        'enabled' => true,
        'default_dataset' => 'standard',
    ],
];
```

---

## 📚 Best Practices

### 1. Test Isolation

```php
// Use transactions for automatic rollback
protected $useTransactions = true;

// Or manually clean up
protected function tearDown(): void
{
    $this->truncate('leads');
    parent::tearDown();
}
```

### 2. Test Data

```php
// Use factories for consistent test data
$lead = LeadFactory::create([
    'first_name' => 'John',
    'stage' => 'New'
]);

// Use fixtures for complex scenarios
$this->loadFixture('leads_with_contacts');
```

### 3. Assertions

```php
// Be specific with assertions
$this->assertDatabaseHas('leads', [
    'email' => 'test@example.com',
    'stage' => 'Qualified'
]);

// Use custom assertions
$this->assertLeadHasStage($leadId, 'Qualified');
```

### 4. Performance

```php
// Use database snapshots for expensive setups
$this->createSnapshot('complex_rbac_setup');
// ... run tests ...
$this->restoreSnapshot('complex_rbac_setup');

// Disable transactions for read-only tests
protected $useTransactions = false;
```

### 5. RBAC Testing

```php
// Test all permission levels
public function testCompleteRbacScenario()
{
    // Module level
    $this->assertTrue($rbac->canAccessModule($userId, 'leads'));
    
    // Action level
    $this->assertTrue($rbac->canPerformAction($userId, 'leads', 'view'));
    
    // Field level
    $this->assertTrue($rbac->canAccessField($userId, 'leads', 'email', 'view'));
    
    // Record level
    $this->assertTrue($rbac->canAccessRecord($userId, 'leads', $recordId, 'edit'));
}
```

---

## 🐛 Debugging

### PHPUnit Debugging

```bash
# Run with verbose output
vendor/bin/phpunit --verbose

# Run specific test
vendor/bin/phpunit --filter testUserCanCreateLead

# Stop on failure
vendor/bin/phpunit --stop-on-failure

# Generate coverage report
vendor/bin/phpunit --coverage-html coverage/
```

### Playwright Debugging

```bash
# Run with headed browser
npx playwright test --headed

# Run with debug mode
npx playwright test --debug

# Run with UI mode
npx playwright test --ui

# Generate trace
npx playwright test --trace on
npx playwright show-trace trace.zip
```

### Database Debugging

```php
// Check current database
echo $this->getDb()->getCurrentDatabase();

// Verify test mode
var_dump($this->getDb()->isTestMode());

// Inspect test data
var_dump($this->fetchAll('leads'));
```

---

## 📊 CI/CD Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      
      - name: Install dependencies
        run: composer install
      
      - name: Setup test database
        run: php tests/setup-test-database.php --mode=ephemeral --seed=standard
      
      - name: Run PHPUnit tests
        run: vendor/bin/phpunit
      
      - name: Setup Node.js
        uses: actions/setup-node@v2
        with:
          node-version: '18'
      
      - name: Install Playwright
        run: |
          npm install
          npx playwright install --with-deps
      
      - name: Run Playwright tests
        run: npx playwright test
```

---

## 📝 Summary

### Quick Commands

```bash
# Setup
php tests/setup-test-database.php --mode=persistent --seed=standard

# Run all tests
vendor/bin/phpunit && npx playwright test

# Run specific suites
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Integration
npx playwright test rbac-permissions.spec.js

# Debug
vendor/bin/phpunit --filter testSpecificTest --verbose
npx playwright test --debug

# Cleanup
php tests/setup-test-database.php --destroy
```

### Key Features

✅ Automatic test database switching  
✅ Transaction-based test isolation  
✅ Database snapshots and restoration  
✅ Comprehensive RBAC testing utilities  
✅ PHPUnit and Playwright integration  
✅ Multiple seed datasets  
✅ Performance optimizations  
✅ CI/CD ready  

---

**Last Updated:** January 2025  
**Version:** 2.0
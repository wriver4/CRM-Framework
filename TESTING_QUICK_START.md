# Testing Framework - Quick Start Guide

## 🚀 5-Minute Setup

### Step 1: Create Test Database User
```bash
mysql -u root -p < tests/create-test-db-user.sql
```

### Step 2: Setup Test Database
```bash
php tests/setup-test-database.php --mode=persistent --seed=standard
```

### Step 3: Run Tests
```bash
# PHPUnit
vendor/bin/phpunit

# Playwright
npx playwright test
```

**Done!** ✅

---

## 📝 Common Commands

### Database Management
```bash
# Reset test database
php tests/setup-test-database.php --reset

# Destroy test database
php tests/setup-test-database.php --destroy

# Switch to minimal dataset (faster)
php tests/setup-test-database.php --reset --seed=minimal

# Switch to full dataset (comprehensive)
php tests/setup-test-database.php --reset --seed=full
```

### Running Tests
```bash
# All PHPUnit tests
vendor/bin/phpunit

# Specific test suite
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Integration
vendor/bin/phpunit --testsuite=Feature

# Specific test file
vendor/bin/phpunit tests/phpunit/Unit/MyTest.php

# All Playwright tests
npx playwright test

# Specific Playwright test
npx playwright test tests/playwright/rbac-permissions.spec.js
```

---

## 🎯 Test Data Available

### Users (Standard Dataset)
| Username           | Password      | Role          |
| ------------------ | ------------- | ------------- |
| test_super_admin   | test_password | Super Admin   |
| test_sales_manager | test_password | Sales Manager |
| test_sales_rep     | test_password | Sales Rep     |
| test_viewer        | test_password | Viewer        |
| test_restricted    | test_password | Restricted    |

### Database Contents
- **5 users** with different permission levels
- **5 roles** (Super Admin, Admin, Sales Manager, Sales Rep, Viewer)
- **50 permissions** (RBAC-compliant)
- **20 leads** with realistic data
- **30 contacts** with realistic data
- **40 notes** linked to leads/contacts

---

## 💡 Writing Tests

### PHPUnit Test Example
```php
<?php

namespace Tests\Unit;

use Tests\DatabaseTestCase;

class MyTest extends DatabaseTestCase
{
    public function testCreateLead()
    {
        // Insert test data
        $leadId = $this->insert('leads', [
            'company_name' => 'Test Company',
            'contact_id' => 1,
            'source_id' => 1
        ]);
        
        // Assert it exists
        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'company_name' => 'Test Company'
        ]);
        
        // Data automatically cleaned up after test!
    }
}
```

### Playwright Test Example
```javascript
const { test, expect } = require('@playwright/test');
const { loginAsUser, checkModuleAccess } = require('./rbac-helper');

test('Sales manager can access leads', async ({ page }) => {
    // Login as test user
    await loginAsUser(page, 'test_sales_manager', 'test_password');
    
    // Check module access
    const hasAccess = await checkModuleAccess(page, 'leads');
    expect(hasAccess).toBe(true);
});
```

---

## 🔧 Configuration

### Test Database Credentials
Located in `phpunit.xml`:
```xml
<env name="TEST_DB_HOST" value="localhost"/>
<env name="TEST_DB_NAME" value="democrm_test"/>
<env name="TEST_DB_USER" value="democrm_test"/>
<env name="TEST_DB_PASS" value="TestDB_2025_Secure!"/>
```

### Dataset Sizes
Located in `config/testing.php`:
- **minimal**: 2 users, 3 roles, 10 permissions
- **standard**: 5 users, 5 roles, 50 permissions, 20 leads, 30 contacts
- **full**: 20 users, 10 roles, 100 permissions, 100 leads, 150 contacts

---

## ⚡ Performance Tips

### Use Transactions (Default)
```php
class MyTest extends DatabaseTestCase
{
    protected $useTransactions = true; // Automatic rollback
}
```
**Speed:** ~0.1ms overhead per test

### Use Minimal Dataset for Unit Tests
```bash
php tests/setup-test-database.php --reset --seed=minimal
```
**Speed:** 1 second setup vs 3 seconds

### Use Snapshots for Complex Setups
```php
public function testComplexWorkflow()
{
    $this->createSnapshot('before_workflow');
    
    // Run complex operations
    
    $this->restoreSnapshot('before_workflow');
}
```

---

## 🛡️ Production Safety

### ✅ Guaranteed Safe
- Production `Database` class has **zero test code**
- Test `TestDatabase` class **never loaded in production**
- **Impossible** to accidentally use test database in production
- **Zero performance overhead** in production

### Production vs Test
```php
// Production code (unchanged)
$db = new Database();  // Uses democrm_democrm

// Test code (new)
$db = new TestDatabase();  // Uses democrm_test
```

---

## 📚 Full Documentation

- **[TEST_DATA_GENERATION_GUIDE.md](TEST_DATA_GENERATION_GUIDE.md)** - Complete data generation guide
- **[PRODUCTION_ZERO_OVERHEAD_SUMMARY.md](PRODUCTION_ZERO_OVERHEAD_SUMMARY.md)** - Zero overhead explanation
- **[TESTING_FRAMEWORK_README.md](TESTING_FRAMEWORK_README.md)** - Full testing documentation
- **[RBAC_MIGRATION_PLAN.md](RBAC_MIGRATION_PLAN.md)** - RBAC implementation plan

---

## 🆘 Troubleshooting

### "Test database not found"
```bash
php tests/setup-test-database.php --mode=persistent --seed=standard
```

### "Access denied for user 'democrm_test'"
```bash
mysql -u root -p < tests/create-test-db-user.sql
```

### "Tests are slow"
```bash
# Use minimal dataset
php tests/setup-test-database.php --reset --seed=minimal

# Ensure transactions are enabled
protected $useTransactions = true;
```

### "Tests affecting each other"
```bash
# Reset database
php tests/setup-test-database.php --reset

# Verify transactions in test class
protected $useTransactions = true;
```

---

## 🎉 Next Steps

1. ✅ Setup test database (done above)
2. ✅ Run existing tests to verify setup
3. 📝 Write your first test
4. 🚀 Start RBAC implementation (see RBAC_MIGRATION_PLAN.md)

---

**Last Updated:** January 2025  
**Status:** Ready to Use  
**Setup Time:** 5 minutes
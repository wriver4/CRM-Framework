# Test Data Generation Guide

## Overview

The testing framework includes **automatic test data generation** with three dataset sizes to support different testing needs.

---

## 🎯 Quick Start

### 1. Create Test Database User (One-time setup)

```bash
# Run as MySQL root
mysql -u root -p < tests/create-test-db-user.sql
```

This creates:
- User: `democrm_test`
- Password: `TestDB_2025_Secure!`
- Permissions: Full access to `democrm_test*` databases

### 2. Setup Test Database with Data

```bash
# Standard dataset (recommended for most tests)
php tests/setup-test-database.php --mode=persistent --seed=standard

# Minimal dataset (fast, for unit tests)
php tests/setup-test-database.php --mode=persistent --seed=minimal

# Full dataset (comprehensive, for integration tests)
php tests/setup-test-database.php --mode=persistent --seed=full
```

### 3. Run Tests

```bash
# PHPUnit tests
vendor/bin/phpunit

# Playwright E2E tests
npx playwright test
```

---

## 📊 Dataset Sizes

### Minimal Dataset
**Use for:** Unit tests, quick validation

| Entity      | Count |
| ----------- | ----- |
| Users       | 2     |
| Roles       | 3     |
| Permissions | 10    |
| Leads       | 0     |
| Contacts    | 0     |
| Notes       | 0     |

**Setup time:** ~1 second

### Standard Dataset (Default)
**Use for:** Integration tests, feature tests

| Entity      | Count |
| ----------- | ----- |
| Users       | 5     |
| Roles       | 5     |
| Permissions | 50    |
| Leads       | 20    |
| Contacts    | 30    |
| Notes       | 40    |

**Setup time:** ~3 seconds

### Full Dataset
**Use for:** Performance tests, comprehensive E2E tests

| Entity      | Count |
| ----------- | ----- |
| Users       | 20    |
| Roles       | 10    |
| Permissions | 100   |
| Leads       | 100   |
| Contacts    | 150   |
| Notes       | 200   |

**Setup time:** ~10 seconds

---

## 🔧 Test Data Details

### Test Users

All test users have password: `test_password`

**Standard Dataset Users:**
1. `test_super_admin` - Full system access
2. `test_sales_manager` - Sales team management
3. `test_sales_rep` - Basic sales access
4. `test_viewer` - Read-only access
5. `test_restricted` - Minimal permissions

### Test Roles

1. **Super Admin** - All permissions
2. **Admin** - Administrative permissions
3. **Sales Manager** - Team management + sales
4. **Sales Rep** - Basic sales operations
5. **Viewer** - Read-only access

### Test Permissions

Permissions follow the RBAC structure:
- `{module}.access` - Module-level access
- `{module}.{action}` - Action-level (view, create, edit, delete)
- `{module}.{action}.{field}` - Field-level
- `{module}.{action}.{scope}` - Record-level (own, team, all)

**Example permissions:**
```
leads.access
leads.view
leads.create
leads.edit
leads.delete
leads.view.email
leads.edit.stage
leads.view.own
leads.view.team
leads.view.all
```

### Test Leads

Generated with realistic data:
- Random company names
- Random contact associations
- Various lead sources (Web, Referral, Cold Call, etc.)
- Different lead stages (New, Qualified, Proposal, etc.)
- Random service types
- Timestamps spread over last 90 days

### Test Contacts

Generated with realistic data:
- Random names (first + last)
- Email addresses: `{firstname}.{lastname}@test.com`
- Phone numbers: `555-0100` to `555-0999`
- Random addresses and cities
- Timestamps spread over last 90 days

---

## 🔄 Database Modes

### Persistent Mode (Recommended)
```bash
php tests/setup-test-database.php --mode=persistent
```

**Characteristics:**
- Database persists between test runs
- Data is reset via transactions (fast)
- Ideal for development and debugging
- Can inspect database after tests

**When to use:**
- Local development
- Debugging failed tests
- Integration tests
- Feature tests

### Ephemeral Mode
```bash
php tests/setup-test-database.php --mode=ephemeral
```

**Characteristics:**
- Database created fresh for each run
- Database destroyed after tests
- Slower but guarantees clean state
- Ideal for CI/CD pipelines

**When to use:**
- CI/CD environments
- Automated test suites
- When you need guaranteed isolation

---

## 🛠️ Management Commands

### Reset Test Database
```bash
# Drops and recreates database with fresh data
php tests/setup-test-database.php --reset
```

### Destroy Test Database
```bash
# Completely removes test database
php tests/setup-test-database.php --destroy
```

### Change Dataset
```bash
# Switch to minimal dataset
php tests/setup-test-database.php --reset --seed=minimal

# Switch to full dataset
php tests/setup-test-database.php --reset --seed=full
```

---

## 📸 Database Snapshots

The framework supports database snapshots for complex test setups:

### Create Snapshot
```php
// In your test
$this->createSnapshot('before_complex_operation');
```

### Restore Snapshot
```php
// In your test
$this->restoreSnapshot('before_complex_operation');
```

**Use cases:**
- Testing multi-step workflows
- Performance testing with consistent data
- Testing rollback scenarios
- Debugging complex test failures

---

## 🎨 Custom Test Data

### In PHPUnit Tests

```php
use Tests\DatabaseTestCase;

class MyTest extends DatabaseTestCase
{
    public function testSomething()
    {
        // Insert custom test data
        $userId = $this->insert('users', [
            'username' => 'custom_user',
            'email' => 'custom@test.com',
            'role_id' => 1
        ]);
        
        // Use the data
        $this->assertDatabaseHas('users', [
            'username' => 'custom_user'
        ]);
        
        // Data automatically cleaned up after test
    }
}
```

### Using RbacTestHelper

```php
use Tests\Helpers\RbacTestHelper;

class RbacTest extends DatabaseTestCase
{
    protected $rbacHelper;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->rbacHelper = new RbacTestHelper(self::$pdo);
    }
    
    public function testPermissions()
    {
        // Create test user with specific permissions
        $userId = $this->rbacHelper->createTestUserWithRole(
            'test_user',
            'Sales Manager'
        );
        
        // Assign specific permissions
        $this->rbacHelper->assignPermissionToUser(
            $userId,
            'leads.edit'
        );
        
        // Test permissions
        $this->assertTrue(
            $this->rbacHelper->userHasPermission($userId, 'leads.edit')
        );
    }
}
```

---

## 🚀 Performance Optimization

### Transaction-Based Isolation (Fast)

```php
class MyTest extends DatabaseTestCase
{
    protected $useTransactions = true; // Default
    
    // Each test runs in a transaction
    // Automatic rollback after test
    // ~0.1ms overhead per test
}
```

### Snapshot-Based Isolation (Medium)

```php
class MyTest extends DatabaseTestCase
{
    protected $useTransactions = false;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->restoreSnapshot('clean_state');
    }
    
    // ~100ms overhead per test
}
```

### Full Reset (Slow)

```php
// Only use in CI/CD or when absolutely necessary
// ~3000ms overhead per test
```

---

## 🔍 Debugging Test Data

### View Current Database

```php
public function testDebug()
{
    echo "\nCurrent database: " . self::$db->getCurrentDatabase();
    echo "\nTest mode: " . self::$db->getTestMode();
    
    // Count records
    $count = $this->count('users');
    echo "\nUsers in database: $count";
}
```

### Inspect Test Database

```bash
# Connect to test database
mysql -u democrm_test -p democrm_test

# View tables
SHOW TABLES;

# View test users
SELECT id, username, email, role_id FROM users;

# View test permissions
SELECT * FROM permissions LIMIT 10;
```

---

## ⚠️ Important Notes

### Production Safety

1. **TestDatabase class** is used for all tests
2. **Production Database class** has ZERO test-related code
3. **No performance overhead** in production
4. **Impossible to accidentally use test DB** in production

### Test Database Credentials

- **Never use production credentials** for test database
- **Test database user** should only have access to `democrm_test*` databases
- **Credentials are in phpunit.xml** - keep this file secure

### Data Isolation

- Each test runs in a **transaction** (automatic rollback)
- Tests **cannot affect each other**
- Tests **cannot affect production data**
- **Parallel test execution** is safe

---

## 📚 Related Documentation

- [TESTING_FRAMEWORK_README.md](TESTING_FRAMEWORK_README.md) - Complete testing guide
- [RBAC_MIGRATION_PLAN.md](RBAC_MIGRATION_PLAN.md) - RBAC implementation plan
- [IMPLEMENTATION_CHECKLIST.md](IMPLEMENTATION_CHECKLIST.md) - Implementation progress

---

## 🆘 Troubleshooting

### "Test database not found"
```bash
php tests/setup-test-database.php --mode=persistent --seed=standard
```

### "Access denied for user 'democrm_test'"
```bash
# Recreate test user
mysql -u root -p < tests/create-test-db-user.sql
```

### "Too slow to run tests"
```bash
# Use minimal dataset
php tests/setup-test-database.php --reset --seed=minimal

# Or use transactions (default)
protected $useTransactions = true;
```

### "Tests affecting each other"
```bash
# Verify transactions are enabled
protected $useTransactions = true;

# Or reset database
php tests/setup-test-database.php --reset
```

---

**Last Updated:** January 2025  
**Status:** Production Ready
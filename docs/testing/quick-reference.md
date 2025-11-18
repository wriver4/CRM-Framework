# Testing Framework - Quick Reference Card

## 🚀 Quick Commands

### Run All Core Tests
```bash
./run-core-tests.sh
```

### Run Specific Test Suite
```bash
vendor/bin/phpunit --testsuite=Core
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Integration
vendor/bin/phpunit --testsuite=Feature
```

### Run Individual Test File
```bash
vendor/bin/phpunit tests/phpunit/Unit/Core/DatabaseTest.php
vendor/bin/phpunit tests/phpunit/Unit/Core/SecurityTest.php
vendor/bin/phpunit tests/phpunit/Unit/Core/SessionsTest.php
vendor/bin/phpunit tests/phpunit/Unit/Core/NonceTest.php
```

### Run with Detailed Output
```bash
vendor/bin/phpunit --testsuite=Core --testdox
vendor/bin/phpunit --testsuite=Core --testdox --colors=always
```

### Run Specific Test Method
```bash
vendor/bin/phpunit --filter test_method_name tests/phpunit/Unit/Core/DatabaseTest.php
```

### Generate Coverage Report
```bash
vendor/bin/phpunit --coverage-html coverage/ tests/phpunit/Unit/Core/
# Open: coverage/index.html
```

---

## 📊 Test Statistics

| Test File        | Tests   | What It Tests                                  |
| ---------------- | ------- | ---------------------------------------------- |
| DatabaseTest.php | 25      | Database connection, queries, transactions     |
| SecurityTest.php | 30      | Password hashing, authentication, RBAC         |
| SessionsTest.php | 35      | Session management, user state, timeouts       |
| NonceTest.php    | 35      | CSRF protection, token generation/verification |
| **TOTAL**        | **125** | **Core Infrastructure**                        |

---

## 📁 File Locations

### Test Files
```
tests/phpunit/Unit/Core/
├── DatabaseTest.php    (25 tests)
├── SecurityTest.php    (30 tests)
├── SessionsTest.php    (35 tests)
└── NonceTest.php       (35 tests)
```

### Documentation
```
/
├── TESTING_FRAMEWORK_BUILDOUT_SUMMARY.md    (Complete overview)
├── TESTING_FRAMEWORK_PHASE1_COMPLETE.md     (Phase 1 details)
├── TESTING_FRAMEWORK_EXPANSION_PLAN.md      (6-phase roadmap)
├── TESTING_QUICK_REFERENCE.md               (This file)
└── run-core-tests.sh                        (Test runner script)
```

### Configuration
```
/
├── phpunit.xml              (PHPUnit configuration)
└── tests/bootstrap.php      (Test bootstrap)
```

---

## 🎯 What's Tested

### Database (25 tests)
- ✅ Singleton pattern
- ✅ PDO connection
- ✅ UTF-8MB4 charset
- ✅ Prepared statements
- ✅ Transactions
- ✅ Error handling

### Security (30 tests)
- ✅ Password hashing (bcrypt)
- ✅ Password verification
- ✅ Authentication
- ✅ RBAC permissions
- ✅ Special characters
- ✅ Unicode support

### Sessions (35 tests)
- ✅ Login state
- ✅ User data
- ✅ Permissions
- ✅ Language preferences
- ✅ Session timeout
- ✅ Activity tracking

### Nonce (35 tests)
- ✅ CSRF token generation
- ✅ Token verification
- ✅ Expiration handling
- ✅ Tampering detection
- ✅ Multi-form support
- ✅ Session storage

---

## 🔧 Troubleshooting

### Tests Hang
```bash
# Check database connection
php -r "require 'classes/Core/Database.php'; \$db = new Database(); var_dump(\$db->dbcrm());"
```

### Tests Fail
```bash
# Run with verbose output
vendor/bin/phpunit --testsuite=Core --testdox --verbose
```

### Check Syntax
```bash
php -l tests/phpunit/Unit/Core/DatabaseTest.php
```

---

## 📚 Documentation

| Document                                | Purpose                        |
| --------------------------------------- | ------------------------------ |
| `TESTING_FRAMEWORK_BUILDOUT_SUMMARY.md` | Complete overview of Phase 1   |
| `TESTING_FRAMEWORK_PHASE1_COMPLETE.md`  | Detailed Phase 1 documentation |
| `TESTING_FRAMEWORK_EXPANSION_PLAN.md`   | 6-phase roadmap (12 weeks)     |
| `TESTING_QUICK_REFERENCE.md`            | This quick reference           |
| `.zencoder/rules/testing-complete.md`   | Testing rules and guidelines   |

---

## 🎓 Test Groups

Tests are organized with `@group` tags:

```bash
# Run by group
vendor/bin/phpunit --group Core
vendor/bin/phpunit --group Critical
vendor/bin/phpunit --group Security
vendor/bin/phpunit --group CSRF
```

---

## 📈 Next Steps

### Phase 2: Business Logic
Create tests for:
- UsersTest.php
- ContactsTest.php
- RolesTest.php
- PermissionsTest.php
- RolesPermissionsTest.php

### Phase 3: Models
Create tests for:
- NotesTest.php
- CommunicationsTest.php
- SalesTest.php
- ProspectsTest.php
- CalendarEventTest.php

---

## 💡 Tips

### Run Tests Before Committing
```bash
./run-core-tests.sh && git commit
```

### Watch for Changes
```bash
# Install phpunit-watcher
composer require --dev spatie/phpunit-watcher

# Run watcher
vendor/bin/phpunit-watcher watch
```

### Generate Coverage Badge
```bash
vendor/bin/phpunit --coverage-text
```

---

## 🎯 Coverage Goals

| Category         | Target | Current       |
| ---------------- | ------ | ------------- |
| Core Classes     | 90%+   | ✅ 90%+        |
| Security Classes | 95%+   | ✅ 95%+        |
| Business Logic   | 80%+   | 🔨 In Progress |
| Overall          | 80%+   | 🔨 In Progress |

---

## 🚨 Important Notes

1. **Always run tests before deploying**
2. **Add tests for new features**
3. **Update tests when refactoring**
4. **Review coverage reports regularly**
5. **Keep tests fast (< 5 minutes total)**

---

**Quick Start**: `./run-core-tests.sh`  
**Full Docs**: `TESTING_FRAMEWORK_BUILDOUT_SUMMARY.md`  
**Roadmap**: `TESTING_FRAMEWORK_EXPANSION_PLAN.md`

---

*Last Updated: 2025-01-12*
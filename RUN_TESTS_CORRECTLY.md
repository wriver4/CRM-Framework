# 🚀 How to Run Tests Correctly

## ⚠️ CRITICAL: Use SSH, Not SFTP Mount

**All test commands MUST be run via SSH on the remote server.**

---

## 🎯 Quick Start

### 1. Run All Tests
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit"
```

### 2. Run Core Tests Only
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/"
```

### 3. Run Specific Test File
```bash
# Nonce tests
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/NonceTest.php"

# Security tests
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/SecurityTest.php"

# Sessions tests
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/SessionsTest.php"

# Database tests
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/DatabaseTest.php"
```

### 4. Run with Better Output
```bash
# Testdox format (readable test names)
ssh wswg "cd /home/democrm && vendor/bin/phpunit --testdox --no-coverage"

# Verbose output
ssh wswg "cd /home/democrm && vendor/bin/phpunit --verbose"

# Stop on first failure
ssh wswg "cd /home/democrm && vendor/bin/phpunit --stop-on-failure"
```

### 5. Run Specific Test Method
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit --filter=it_can_verify_valid_nonce"
```

---

## 📋 Test Suites

### Run by Test Suite
```bash
# Core tests (Database, Security, Sessions, Nonce)
ssh wswg "cd /home/democrm && vendor/bin/phpunit --testsuite=Core"

# Unit tests
ssh wswg "cd /home/democrm && vendor/bin/phpunit --testsuite=Unit"

# Integration tests
ssh wswg "cd /home/democrm && vendor/bin/phpunit --testsuite=Integration"

# Feature tests
ssh wswg "cd /home/democrm && vendor/bin/phpunit --testsuite=Feature"
```

---

## 🔍 Debugging Tests

### Run with Debug Output
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit --debug --verbose"
```

### Run Single Test with Full Output
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/NonceTest.php --filter=it_can_verify_valid_nonce --debug"
```

### Check PHP Version and Modules
```bash
# PHP version
ssh wswg "php -v"

# PHP modules
ssh wswg "php -m"

# Check specific module
ssh wswg "php -m | grep -i mbstring"
```

---

## 🗄️ Database Setup (First Time Only)

### 1. Create Test Database User
```bash
ssh wswg "mysql -u rootremote -p'HTG3rfd_ugd1pwq.mzc' < /home/democrm/tests/create-test-db-user.sql"
```

### 2. Verify Test Database
```bash
ssh wswg "mysql -u democrm_test -p'TestDB_2025_Secure!' -e 'SELECT 1;'"
```

### 3. Create Test Database
```bash
ssh wswg "mysql -u rootremote -p'HTG3rfd_ugd1pwq.mzc' -e 'CREATE DATABASE IF NOT EXISTS democrm_test;'"
```

### 4. Grant Permissions
```bash
ssh wswg "mysql -u rootremote -p'HTG3rfd_ugd1pwq.mzc' -e \"GRANT ALL PRIVILEGES ON democrm_test.* TO 'democrm_test'@'localhost';\""
```

---

## 📊 Generate Coverage Report

### HTML Coverage Report
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit --coverage-html coverage/"
```

### Text Coverage Summary
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit --coverage-text"
```

---

## 🎨 Output Formats

### Testdox (Readable)
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit --testdox"
```

### TAP Format
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit --tap"
```

### JUnit XML (for CI/CD)
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit --log-junit results.xml"
```

---

## 🚫 Common Mistakes

### ❌ WRONG: Running via SFTP Mount
```bash
# This will HANG - DO NOT USE
cd /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm
vendor/bin/phpunit
```

### ❌ WRONG: Running on Local Machine
```bash
# This will FAIL - DO NOT USE
phpunit tests/phpunit/Unit/Core/NonceTest.php
```

### ✅ CORRECT: Running via SSH
```bash
# This works correctly
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/NonceTest.php"
```

---

## 🔧 Troubleshooting

### Tests Hang or Timeout
**Problem**: Running through SFTP mount
**Solution**: Use SSH as shown above

### Database Connection Errors
**Problem**: Test database doesn't exist
**Solution**: Create test database (see Database Setup section)

### Permission Errors
**Problem**: Test database user doesn't have permissions
**Solution**: Grant permissions (see Database Setup section)

### Class Not Found Errors
**Problem**: Composer autoload not updated
**Solution**: 
```bash
ssh wswg "cd /home/democrm && composer dump-autoload"
```

---

## 📈 Performance Tips

### Skip Coverage for Faster Tests
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit --no-coverage"
```

### Run Tests in Parallel (if supported)
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit --parallel 4"
```

### Stop on First Failure
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit --stop-on-failure"
```

---

## 🎯 Recommended Workflow

### 1. Edit Files Locally (via SFTP)
```bash
# Use your local editor through SFTP mount
nano /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/classes/Core/Nonce.php
```

### 2. Run Tests Remotely (via SSH)
```bash
# Run tests on remote server
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/NonceTest.php"
```

### 3. Debug if Needed (via SSH)
```bash
# Run debug script on remote server
ssh wswg "cd /home/democrm && php test_debug.php"
```

---

## 📚 Additional Resources

- **Full Command Guide**: `CRITICAL_SSH_VS_SFTP_COMMANDS.md`
- **Bug Fixes**: `TESTING_PHASE1_FIXES.md`
- **Summary**: `TESTING_PHASE1_SUMMARY.md`
- **Testing Plan**: `TESTING_FRAMEWORK_EXPANSION_PLAN.md`
- **Testing Rules**: `.zencoder/rules/testing-complete.md`

---

## 🆘 Quick Help

### Check SSH Connection
```bash
ssh wswg "echo 'Connection works!'"
```

### Check Project Directory
```bash
ssh wswg "ls -la /home/democrm"
```

### Check PHPUnit Installation
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit --version"
```

### Check PHP Configuration
```bash
ssh wswg "php -i | grep -E '(memory_limit|max_execution_time)'"
```

---

**Last Updated**: 2025-01-12
**Status**: ✅ Ready to run tests via SSH
**Next Step**: Run tests and verify all fixes work correctly

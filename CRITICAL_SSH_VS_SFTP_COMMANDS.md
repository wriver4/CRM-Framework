# 🚨 CRITICAL: SSH vs SFTP Command Execution

## ⚠️ Current Environment Issue

**Problem**: Commands are being executed on the **local NixOS machine** (`king`) through an SFTP filesystem mount at:
```
/run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm
```

**Impact**: 
- PHPUnit tests hang/timeout because they're running locally but accessing files over SFTP
- PHP execution is extremely slow through SFTP mount
- Database connections fail (trying to connect to localhost from wrong machine)
- Session handling doesn't work properly

---

## 🎯 Solution: Use SSH for Command Execution

### SSH Connection Details
- **Host**: `159.203.116.150`
- **Port**: `222`
- **User**: `root` (or appropriate user)
- **Alias**: `wswg` (if configured in SSH config)
- **Project Path**: `/home/democrm`

---

## 📋 Command Categories

### ✅ MUST Run via SSH (on Remote Server)

These commands MUST be executed on the remote server via SSH:

#### 1. PHP Execution
```bash
# ❌ WRONG (via SFTP mount - will hang/fail)
cd /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm
php test.php

# ✅ CORRECT (via SSH)
ssh -p 222 root@159.203.116.150 "cd /home/democrm && php test.php"

# ✅ CORRECT (using alias)
ssh wswg "cd /home/democrm && php test.php"
```

#### 2. PHPUnit Tests
```bash
# ❌ WRONG (will hang)
vendor/bin/phpunit tests/phpunit/Unit/Core/NonceTest.php

# ✅ CORRECT
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/NonceTest.php"

# ✅ CORRECT (with output)
ssh wswg "cd /home/democrm && vendor/bin/phpunit --testdox --no-coverage"
```

#### 3. Composer Commands
```bash
# ❌ WRONG
composer install

# ✅ CORRECT
ssh wswg "cd /home/democrm && composer install"

# ✅ CORRECT
ssh wswg "cd /home/democrm && composer update"
```

#### 4. Database Operations
```bash
# ❌ WRONG (connects to local database)
mysql -u democrm_test -p

# ✅ CORRECT (connects to remote database)
ssh wswg "mysql -u democrm_test -p'TestDB_2025_Secure!' democrm_test"

# ✅ CORRECT (MariaDB as root)
ssh wswg "mariadb -u rootremote -p'HTG3rfd_ugd1pwq.mzc'"
```

#### 5. Service Management
```bash
# ❌ WRONG
systemctl restart php-fpm

# ✅ CORRECT
ssh wswg "systemctl restart php-fpm"
```

#### 6. Package Installation
```bash
# ❌ WRONG
apt install php-mbstring

# ✅ CORRECT
ssh wswg "apt install php-mbstring"
```

#### 7. Process Management
```bash
# ❌ WRONG (shows local processes)
ps aux | grep php

# ✅ CORRECT (shows remote processes)
ssh wswg "ps aux | grep php"
```

---

### ✅ Can Run Locally (via SFTP mount)

These commands work fine through the SFTP mount:

#### 1. File Viewing
```bash
# ✅ OK (read-only operations)
cat /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/config.php
less /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/README.md
```

#### 2. File Editing
```bash
# ✅ OK (using local editor)
nano /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/test.php
vim /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/config.php
```

#### 3. File Operations
```bash
# ✅ OK (file system operations)
cp file1.php file2.php
mv old.php new.php
rm temp.php
mkdir new_directory
```

#### 4. Search Operations
```bash
# ✅ OK (but slower than SSH)
grep -r "function_name" /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/
find /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/ -name "*.php"
```

---

## 🔧 Practical Examples

### Running PHPUnit Tests (CORRECT WAY)

```bash
# Run all tests
ssh wswg "cd /home/democrm && vendor/bin/phpunit"

# Run specific test file
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/NonceTest.php"

# Run with testdox output
ssh wswg "cd /home/democrm && vendor/bin/phpunit --testdox --no-coverage"

# Run specific test suite
ssh wswg "cd /home/democrm && vendor/bin/phpunit --testsuite=Core"

# Run with filter
ssh wswg "cd /home/democrm && vendor/bin/phpunit --filter=it_can_verify_valid_nonce"
```

### Testing PHP Scripts (CORRECT WAY)

```bash
# Run test script
ssh wswg "cd /home/democrm && php test_nonce_simple.php"

# Run with error reporting
ssh wswg "cd /home/democrm && php -d display_errors=1 test.php"

# Check PHP version
ssh wswg "php -v"

# Check PHP modules
ssh wswg "php -m"
```

### Database Operations (CORRECT WAY)

```bash
# Connect to test database
ssh wswg "mysql -u democrm_test -p'TestDB_2025_Secure!' democrm_test"

# Run SQL file
ssh wswg "mysql -u democrm_test -p'TestDB_2025_Secure!' democrm_test < /home/democrm/tests/setup.sql"

# Export database
ssh wswg "mysqldump -u democrm_test -p'TestDB_2025_Secure!' democrm_test > /home/democrm/backup.sql"

# Create test database
ssh wswg "mysql -u rootremote -p'HTG3rfd_ugd1pwq.mzc' -e 'CREATE DATABASE IF NOT EXISTS democrm_test;'"
```

### Composer Operations (CORRECT WAY)

```bash
# Install dependencies
ssh wswg "cd /home/democrm && composer install"

# Update dependencies
ssh wswg "cd /home/democrm && composer update"

# Require new package
ssh wswg "cd /home/democrm && composer require vendor/package"

# Dump autoload
ssh wswg "cd /home/democrm && composer dump-autoload"
```

---

## 🎯 Quick Reference Table

| Operation        | Local (SFTP) | Remote (SSH) | Notes                     |
| ---------------- | ------------ | ------------ | ------------------------- |
| View files       | ✅ OK         | ✅ OK         | SFTP is fine for viewing  |
| Edit files       | ✅ OK         | ✅ OK         | Use local editor via SFTP |
| Run PHP          | ❌ NO         | ✅ YES        | Must run on server        |
| Run PHPUnit      | ❌ NO         | ✅ YES        | Must run on server        |
| Database queries | ❌ NO         | ✅ YES        | Must connect from server  |
| Composer         | ❌ NO         | ✅ YES        | Must run on server        |
| File operations  | ✅ OK         | ✅ OK         | Both work                 |
| Search files     | ⚠️ SLOW       | ✅ FAST       | SSH is much faster        |

---

## 🚀 Recommended Workflow

### 1. File Editing (Use SFTP)
```bash
# Edit files using local tools through SFTP mount
nano /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/classes/Core/Nonce.php
```

### 2. Testing (Use SSH)
```bash
# Run tests on remote server
ssh wswg "cd /home/democrm && vendor/bin/phpunit tests/phpunit/Unit/Core/NonceTest.php"
```

### 3. Debugging (Use SSH)
```bash
# Run debug scripts on remote server
ssh wswg "cd /home/democrm && php test_debug.php"
```

### 4. Database Work (Use SSH)
```bash
# Database operations on remote server
ssh wswg "mysql -u democrm_test -p'TestDB_2025_Secure!' democrm_test"
```

---

## 🐛 Common Issues and Solutions

### Issue 1: PHPUnit Hangs/Timeouts
**Symptom**: Tests never complete, timeout after 120+ seconds
**Cause**: Running PHPUnit through SFTP mount
**Solution**: Use SSH to run tests on remote server

```bash
# ❌ WRONG
cd /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm
vendor/bin/phpunit

# ✅ CORRECT
ssh wswg "cd /home/democrm && vendor/bin/phpunit"
```

### Issue 2: Database Connection Errors
**Symptom**: "Can't connect to MySQL server on 'localhost'"
**Cause**: PHP running locally trying to connect to local database
**Solution**: Run PHP on remote server via SSH

```bash
# ❌ WRONG
php test_database.php

# ✅ CORRECT
ssh wswg "cd /home/democrm && php test_database.php"
```

### Issue 3: Session Errors
**Symptom**: "Session cannot be started after headers have already been sent"
**Cause**: PHP running locally with SFTP filesystem delays
**Solution**: Run PHP on remote server via SSH

```bash
# ❌ WRONG
php test_sessions.php

# ✅ CORRECT
ssh wswg "cd /home/democrm && php test_sessions.php"
```

### Issue 4: Composer Errors
**Symptom**: Composer hangs or fails
**Cause**: Composer running locally accessing files over SFTP
**Solution**: Run Composer on remote server via SSH

```bash
# ❌ WRONG
composer install

# ✅ CORRECT
ssh wswg "cd /home/democrm && composer install"
```

---

## 📝 SSH Configuration

### Setting Up SSH Alias

Add to `~/.ssh/config`:
```
Host wswg
    HostName 159.203.116.150
    Port 222
    User root
    IdentityFile ~/.ssh/id_rsa
    ServerAliveInterval 60
    ServerAliveCountMax 3
```

Then you can use:
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit"
```

Instead of:
```bash
ssh -p 222 root@159.203.116.150 "cd /home/democrm && vendor/bin/phpunit"
```

---

## 🎓 Best Practices

### 1. Always Use SSH for Execution
- PHP scripts
- PHPUnit tests
- Composer commands
- Database operations
- Service management

### 2. Use SFTP Mount for Editing
- View files
- Edit code
- File operations
- Quick searches (if not too large)

### 3. Combine Both for Efficiency
```bash
# Edit file locally via SFTP
nano /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/test.php

# Run test remotely via SSH
ssh wswg "cd /home/democrm && php test.php"
```

### 4. Use Screen/Tmux for Long Operations
```bash
# Start screen session on remote server
ssh wswg
screen -S tests

# Run long-running tests
cd /home/democrm
vendor/bin/phpunit

# Detach: Ctrl+A, D
# Reattach: screen -r tests
```

---

## 🔍 Verification Commands

### Check Where You Are
```bash
# Check hostname (should show 'king' if local, server name if remote)
hostname

# Check current directory
pwd

# If you see '/run/user/1000/gvfs/sftp:...' you're on LOCAL machine via SFTP
# If you see '/home/democrm' you're on REMOTE server via SSH
```

### Test SSH Connection
```bash
# Test connection
ssh wswg "echo 'SSH connection works!'"

# Check remote PHP version
ssh wswg "php -v"

# Check remote database connection
ssh wswg "mysql -u democrm_test -p'TestDB_2025_Secure!' -e 'SELECT 1;'"
```

---

## 📊 Performance Comparison

| Operation        | SFTP Mount | SSH Direct | Speed Difference |
| ---------------- | ---------- | ---------- | ---------------- |
| View file        | ~100ms     | ~50ms      | 2x faster        |
| Edit file        | ~200ms     | ~100ms     | 2x faster        |
| Run PHP script   | HANGS      | ~1s        | ∞ faster         |
| PHPUnit test     | HANGS      | ~5-30s     | ∞ faster         |
| Database query   | FAILS      | ~100ms     | ∞ faster         |
| Composer install | HANGS      | ~30s       | ∞ faster         |

---

**Last Updated**: 2025-01-12
**Status**: 🚨 CRITICAL - Must follow these guidelines for successful testing
**Next Review**: After implementing SSH-based test execution

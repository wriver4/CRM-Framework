# Test Database Setup - Quick Start Guide

## 🚨 Current Status: BLOCKED

The test database user cannot be created until we identify the correct MySQL host configuration.

## 📋 What You Need to Do

### Step 1: Identify MySQL Host Configuration ⭐ START HERE

**Read this file first:** [`MYSQL_HOST_SETUP_GUIDE.md`](./MYSQL_HOST_SETUP_GUIDE.md)

This guide will help you:
1. Find out what host your MySQL users are configured with
2. Create the test user with the correct host
3. Update configuration if needed

### Step 2: Create Test User

Once you know the correct host from Step 1, follow:
- [`create-test-user-instructions.md`](./create-test-user-instructions.md) (but use the correct host!)

### Step 3: Verify Setup

Run the verification script:

```bash
php tests/verify-test-user.php
```

### Step 4: Initialize Test Database

```bash
php tests/setup-test-database.php --mode=persistent --seed=standard
```

## 🔍 Why This is Happening

Your MySQL server has **host-based access restrictions** (a security best practice). The users are configured to only accept connections from specific hosts/contexts:

- ✅ **Web context works** - Production database connects fine from web applications
- ❌ **CLI context fails** - Cannot connect from command-line PHP

This is because:
1. MySQL users are created with a specific `@'host'` value
2. When PHP CLI tries to connect, it connects from 'localhost'
3. If the user is NOT configured with `@'localhost'`, access is denied
4. The production user works from web but not CLI, proving it's configured with a different host

## 🛠️ Diagnostic Tools

We've created several diagnostic tools to help:

| Tool                        | Purpose                               | How to Run                            |
| --------------------------- | ------------------------------------- | ------------------------------------- |
| `check-connection-host.php` | Shows what host PHP CLI connects from | `php tests/check-connection-host.php` |
| `verify-test-user.php`      | Verifies test user can connect        | `php tests/verify-test-user.php`      |
| `check-mysql-users.php`     | Compares production vs test user      | `php tests/check-mysql-users.php`     |

## 📊 What We've Discovered

From `check-connection-host.php`:

```
System Information:
  Hostname: king
  PHP SAPI: cli
  User: mark
  localhost resolves to: 127.0.0.1

Testing production credentials (democrm_democrm)...
  ❌ Connection failed!
  Error: Access denied for user 'democrm_democrm'@'localhost'

Testing test credentials (democrm_test)...
  ❌ Connection failed!
  Error: Access denied for user 'democrm_test'@'localhost'
```

**Key Finding:** Both users are denied when connecting from 'localhost', which means they're configured with a different host value.

## 🎯 Next Steps

1. **You need to:** Run the SQL query in phpMyAdmin to find the production user's host:
   ```sql
   SELECT User, Host FROM mysql.user WHERE User LIKE 'democrm%';
   ```

2. **Then:** Create the test user with the SAME host value

3. **Finally:** Run the verification and setup scripts

## 📞 Questions?

If you're stuck or need clarification:
1. Share the output of: `SELECT User, Host FROM mysql.user WHERE User LIKE 'democrm%';`
2. Share the output of: `php tests/check-connection-host.php`

This will help us determine the exact configuration needed.

## 🔐 Security Note

This host restriction is a **security feature**, not a bug! It ensures:
- Database access is restricted to authorized connection sources
- Port 3306 doesn't need to be exposed to the internet
- Even with correct credentials, unauthorized hosts cannot connect

Your server is more secure because of this configuration!
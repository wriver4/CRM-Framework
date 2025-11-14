# Test Database Setup - Documentation Index

## 🚀 Quick Start

**👉 START HERE:** [`NEXT_STEPS.md`](./NEXT_STEPS.md)

This is your action plan with step-by-step instructions to get the test database working.

---

## 📚 Documentation Files

### Core Guides

| File                                                                       | Description                           | When to Use                                         |
| -------------------------------------------------------------------------- | ------------------------------------- | --------------------------------------------------- |
| **[NEXT_STEPS.md](./NEXT_STEPS.md)**                                       | Your action plan and decision tree    | **Start here** - tells you exactly what to do       |
| **[README_TEST_SETUP.md](./README_TEST_SETUP.md)**                         | Overview of the problem and solutions | Understanding the context                           |
| **[MYSQL_HOST_SETUP_GUIDE.md](./MYSQL_HOST_SETUP_GUIDE.md)**               | Detailed host configuration guide     | When you need to understand MySQL host restrictions |
| **[create-test-user-instructions.md](./create-test-user-instructions.md)** | SQL commands to create test user      | Reference for SQL commands (check host first!)      |

### Quick Reference

- **Problem:** MySQL users have host-based restrictions
- **Symptom:** CLI connections fail with "Access denied for user 'democrm_democrm'@'localhost'"
- **Root Cause:** Users are NOT configured with `@'localhost'` as the host
- **Solution:** Find the correct host, create test user with same host

---

## 🛠️ Diagnostic & Setup Tools

### Interactive Tools

| Tool                      | Command                                | Purpose                                                        |
| ------------------------- | -------------------------------------- | -------------------------------------------------------------- |
| **SQL Generator**         | `php tests/generate-test-user-sql.php` | 🌟 **Recommended** - Interactive wizard to generate correct SQL |
| **Connection Diagnostic** | `php tests/check-connection-host.php`  | Shows what host PHP CLI connects from                          |
| **User Verification**     | `php tests/verify-test-user.php`       | Tests if test user can connect successfully                    |
| **User Comparison**       | `php tests/check-mysql-users.php`      | Compares production vs test user connectivity                  |

### Setup Scripts

| Script             | Command                                                               | Purpose                                              |
| ------------------ | --------------------------------------------------------------------- | ---------------------------------------------------- |
| **Database Setup** | `php tests/setup-test-database.php --mode=persistent --seed=standard` | Initialize test database (run after user is created) |
| **Test Config**    | `php tests/test-config.php`                                           | View current test configuration                      |

---

## 📋 Workflow Overview

```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│  1. Find MySQL Host                                         │
│     └─> Run query in phpMyAdmin                            │
│         SELECT User, Host FROM mysql.user                   │
│         WHERE User LIKE 'democrm%';                         │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  2. Generate SQL Commands                                   │
│     └─> Run: php tests/generate-test-user-sql.php          │
│         (or manually create SQL with correct host)          │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  3. Create Test User                                        │
│     └─> Execute SQL in phpMyAdmin                          │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  4. Update Config (if host ≠ localhost)                     │
│     └─> Create .env file OR edit config/testing.php        │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  5. Verify Setup                                            │
│     └─> Run: php tests/verify-test-user.php                │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  6. Initialize Test Database                                │
│     └─> Run: php tests/setup-test-database.php             │
│              --mode=persistent --seed=standard              │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔍 Understanding the Problem

### What We Discovered

1. **Production user works from web** ✅
   - Web applications connect successfully
   - Database operations work fine

2. **Production user fails from CLI** ❌
   - Command-line PHP cannot connect
   - Error: "Access denied for user 'democrm_democrm'@'localhost'"

3. **Root Cause Identified** 🎯
   - MySQL users are NOT configured with `@'localhost'`
   - They use a different host specification
   - This is a security feature, not a bug!

### Why This Happens

MySQL users are created with a specific host pattern:
```sql
CREATE USER 'username'@'host' IDENTIFIED BY 'password';
```

The `host` part determines WHERE the user can connect from:
- If host = `'localhost'` → Can only connect via local socket
- If host = `'127.0.0.1'` → Can only connect via TCP to localhost
- If host = `'192.168.1.100'` → Can only connect from that IP
- If host = `'%'` → Can connect from anywhere (not recommended)

Your production user is configured with a specific host that matches web server connections but NOT CLI connections.

---

## 🎯 Success Criteria

You'll know everything is working when:

1. ✅ `php tests/verify-test-user.php` shows all green checkmarks
2. ✅ `php tests/setup-test-database.php` completes successfully  
3. ✅ `democrm_test` database exists in phpMyAdmin with tables
4. ✅ Test framework can connect and run tests

---

## 🆘 Troubleshooting

### Common Issues

| Issue                        | Solution                                                 |
| ---------------------------- | -------------------------------------------------------- |
| "Access denied" error        | Check host value matches production user's host          |
| Can't query mysql.user table | Use CWP interface or ask hosting provider for host value |
| Config not working           | Ensure .env file exists OR config/testing.php is updated |
| Verification fails           | Re-run SQL commands in phpMyAdmin, check for typos       |

### Getting Help

If stuck, provide:
1. Output of: `SELECT User, Host FROM mysql.user WHERE User LIKE 'democrm%';`
2. Output of: `php tests/check-connection-host.php`
3. Any error messages

---

## 📁 File Structure

```
tests/
├── INDEX.md                              # This file - navigation hub
├── NEXT_STEPS.md                         # Action plan (START HERE)
├── README_TEST_SETUP.md                  # Problem overview
├── MYSQL_HOST_SETUP_GUIDE.md            # Detailed host guide
├── create-test-user-instructions.md      # SQL reference
│
├── generate-test-user-sql.php           # 🌟 Interactive SQL generator
├── check-connection-host.php            # Connection diagnostic
├── verify-test-user.php                 # User verification
├── check-mysql-users.php                # User comparison
│
├── setup-test-database.php              # Database initialization
└── test-config.php                      # Config viewer
```

---

## 🔐 Security Notes

The host-based restrictions are a **security feature**:

✅ **Benefits:**
- Database access restricted to authorized sources
- Port 3306 doesn't need to be open to the internet
- Even with correct credentials, unauthorized hosts are blocked
- Prevents brute force attacks from external sources

⚠️ **Implications:**
- CLI tools need proper host configuration
- Test users must match production user's host pattern
- May need multiple user entries for different contexts

Your server is **more secure** because of this configuration!

---

## 📞 Support

**Next Action:** Read [`NEXT_STEPS.md`](./NEXT_STEPS.md) and follow the workflow.

**Recommended Tool:** Run `php tests/generate-test-user-sql.php` for guided setup.

**Documentation:** All guides are in the `tests/` directory.

---

*Last Updated: 2024-10-03*
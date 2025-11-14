# MySQL Test User Setup Guide - Host Configuration Issue

## 🔍 Problem Identified

The MySQL users on this server are **NOT configured with `@'localhost'`** as the host. This is why:
- ✅ Web applications can connect (they match the configured host)
- ❌ CLI/command-line PHP cannot connect (it tries to connect from 'localhost')

## 📋 Step-by-Step Solution

### Step 1: Identify the Production User's Host

In **phpMyAdmin**, run this query:

```sql
SELECT User, Host, plugin 
FROM mysql.user 
WHERE User LIKE 'democrm%' 
ORDER BY User, Host;
```

**Expected output example:**
```
| User            | Host          | plugin                |
| --------------- | ------------- | --------------------- |
| democrm_democrm | 192.168.1.100 | mysql_native_password |
```

**Important:** Note the exact value in the `Host` column for `democrm_democrm`. This is what we need!

### Step 2: Create Test User with Matching Host

Replace `PRODUCTION_HOST` below with the actual host value from Step 1:

```sql
-- Create the test user with the SAME host as production
CREATE USER 'democrm_test'@'PRODUCTION_HOST' IDENTIFIED BY 'TestDB_2025_Secure!';

-- Grant all privileges on the persistent test database
GRANT ALL PRIVILEGES ON `democrm_test`.* TO 'democrm_test'@'PRODUCTION_HOST';

-- Grant all privileges on ephemeral test databases (with pattern democrm_test_*)
GRANT ALL PRIVILEGES ON `democrm_test_%`.* TO 'democrm_test'@'PRODUCTION_HOST';

-- Grant CREATE and DROP privileges for creating/destroying ephemeral databases
GRANT CREATE, DROP ON *.* TO 'democrm_test'@'PRODUCTION_HOST';

-- Apply changes
FLUSH PRIVILEGES;
```

### Step 3: Create the Test Database

```sql
CREATE DATABASE IF NOT EXISTS `democrm_test` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### Step 4: Update Configuration (if needed)

If the host is NOT 'localhost', you have two options:

#### Option A: Use Environment Variables (Recommended)

Create a `.env` file in the project root:

```bash
TEST_DB_HOST=PRODUCTION_HOST
TEST_DB_USER=democrm_test
TEST_DB_PASS=TestDB_2025_Secure!
TEST_DB_NAME=democrm_test
```

#### Option B: Update config/testing.php

Edit the configuration file to use the correct host:

```php
'ephemeral' => [
    'host' => getenv('TEST_DB_HOST') ?: 'PRODUCTION_HOST',  // Change localhost to actual host
    // ... rest of config
],

'persistent' => [
    'host' => getenv('TEST_DB_HOST') ?: 'PRODUCTION_HOST',  // Change localhost to actual host
    // ... rest of config
],
```

### Step 5: Verify the Setup

Run the verification script:

```bash
php tests/verify-test-user.php
```

**Expected output:**
```
✅ Test database connection successful!
✅ Test database exists
✅ Can create tables
✅ Can insert data
✅ Can query data
✅ Can drop tables
```

### Step 6: Initialize Test Database

Once verification passes, run:

```bash
php tests/setup-test-database.php --mode=persistent --seed=standard
```

## 🔧 Common Host Values

The `Host` column in MySQL can have various values:

| Host Value     | Meaning                     | Example                            |
| -------------- | --------------------------- | ---------------------------------- |
| `localhost`    | Local socket connection     | `democrm_test@'localhost'`         |
| `127.0.0.1`    | TCP connection to localhost | `democrm_test@'127.0.0.1'`         |
| `192.168.x.x`  | Specific IP address         | `democrm_test@'192.168.1.100'`     |
| `%.domain.com` | Wildcard for domain         | `democrm_test@'%.waveguardco.net'` |
| `%`            | Any host (not recommended)  | `democrm_test@'%'`                 |

## 🚨 Security Note

The host restriction is a **security feature**. It ensures that:
- Database users can only connect from authorized sources
- Even with correct credentials, connections from unauthorized hosts are blocked
- Port 3306 doesn't need to be open to the world

This is why your setup is more secure than typical configurations!

## 📝 Alternative: Create User for Both Hosts

If you want the test user to work from both web and CLI contexts, you can create it for multiple hosts:

```sql
-- For web context (use the production host)
CREATE USER 'democrm_test'@'PRODUCTION_HOST' IDENTIFIED BY 'TestDB_2025_Secure!';
GRANT ALL PRIVILEGES ON `democrm_test`.* TO 'democrm_test'@'PRODUCTION_HOST';
GRANT ALL PRIVILEGES ON `democrm_test_%`.* TO 'democrm_test'@'PRODUCTION_HOST';
GRANT CREATE, DROP ON *.* TO 'democrm_test'@'PRODUCTION_HOST';

-- For CLI context (if different)
CREATE USER 'democrm_test'@'localhost' IDENTIFIED BY 'TestDB_2025_Secure!';
GRANT ALL PRIVILEGES ON `democrm_test`.* TO 'democrm_test'@'localhost';
GRANT ALL PRIVILEGES ON `democrm_test_%`.* TO 'democrm_test'@'localhost';
GRANT CREATE, DROP ON *.* TO 'democrm_test'@'localhost';

FLUSH PRIVILEGES;
```

**Note:** This creates two separate user entries in MySQL, one for each host.

## ❓ Troubleshooting

### If you get "Access denied" errors:

1. **Check the host value** - Run the query from Step 1 again
2. **Verify password** - Ensure it matches exactly: `TestDB_2025_Secure!`
3. **Check grants** - Run: `SHOW GRANTS FOR 'democrm_test'@'PRODUCTION_HOST';`
4. **Flush privileges** - Run: `FLUSH PRIVILEGES;`

### If you can't query mysql.user table:

The production user might not have permission to view user tables. In that case:
- Use CWP's MySQL user management interface to check the host
- Or ask your hosting provider/server admin

### If CLI still can't connect:

The MySQL server might be configured to only accept connections from specific sources. You may need to:
- Create the user with `@'localhost'` specifically for CLI access
- Or configure MySQL to allow CLI connections (requires server admin access)

## 📞 Need Help?

If you're stuck, provide the output of:

```bash
php tests/check-connection-host.php
```

This will help diagnose the exact connection context and host requirements.
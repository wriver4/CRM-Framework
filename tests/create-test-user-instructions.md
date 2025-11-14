# Create Test Database User - CORRECTED INSTRUCTIONS

## ⚠️ CRITICAL: Read This First!

**NEW DISCOVERY:** The MySQL users are NOT configured with `@'localhost'` as the host!

**👉 Please read `MYSQL_HOST_SETUP_GUIDE.md` first** - it contains the correct procedure to identify and use the proper host value.

The instructions below assume `@'localhost'` but this may NOT be correct for your server!

---

## Problem Identified

The MySQL users on this server have **host-based restrictions**. Even the production user `democrm_democrm@localhost` cannot connect from PHP CLI, which means the users might be restricted to specific connection contexts.

## Solution

You need to create the test user using **phpMyAdmin SQL tab** with the complete CREATE USER + GRANT statements.

---

## Step 1: Open phpMyAdmin

1. Log into your CWP
2. Open **phpMyAdmin**
3. Click on the **SQL** tab

---

## Step 2: Run This Complete SQL Script

Copy and paste this ENTIRE script into the SQL tab and click "Go":

```sql
-- Drop user if it exists (cleanup from previous attempts)
DROP USER IF EXISTS 'democrm_test'@'localhost';
DROP USER IF EXISTS 'democrm_test'@'%';

-- Create the test user for localhost connections
CREATE USER 'democrm_test'@'localhost' IDENTIFIED BY 'TestDB_2025_Secure!';

-- Grant all privileges on democrm_test database (persistent mode)
GRANT ALL PRIVILEGES ON `democrm_test`.* TO 'democrm_test'@'localhost';

-- Grant all privileges on democrm_test_* databases (ephemeral mode)
GRANT ALL PRIVILEGES ON `democrm_test_%`.* TO 'democrm_test'@'localhost';

-- Grant CREATE and DROP privileges for creating/destroying test databases
GRANT CREATE, DROP ON *.* TO 'democrm_test'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify the user was created
SELECT User, Host FROM mysql.user WHERE User = 'democrm_test';

-- Show the grants
SHOW GRANTS FOR 'democrm_test'@'localhost';
```

---

## Step 3: Verify Output

After running the script, you should see:

1. **User created** message
2. **Grants applied** messages  
3. **SELECT result** showing: `democrm_test | localhost`
4. **SHOW GRANTS result** showing all the permissions

---

## Step 4: Test the Connection

After creating the user, run this command to verify it works:

```bash
php tests/verify-test-user.php
```

You should see ✅ success messages!

---

## Alternative: If CWP Prefix is Required

If your CWP requires a prefix (like `democrm_` for all users), you might need to:

1. Create user as: `democrm_democrm_test` (with the account prefix)
2. Update `config/testing.php` to use username: `democrm_democrm_test`

To check if this is the case, look at the production username: `democrm_democrm`
- If it's `democrm_democrm`, then test user should be `democrm_test` (no double prefix)
- If CWP adds prefixes automatically, it might become `democrm_democrm_test`

---

## Why This Approach?

- **Direct SQL** bypasses CWP's user interface limitations
- **Explicit host specification** (`@'localhost'`) ensures proper host permissions
- **Complete GRANT statements** give all necessary privileges in one go
- **Verification queries** confirm the user was created correctly

---

## Next Steps After User Creation

Once the user is created and verified:

1. Run: `php tests/setup-test-database.php --mode=persistent --seed=standard`
2. This will create the test database and seed it with test data
3. Then you can run tests with: `vendor/bin/phpunit`
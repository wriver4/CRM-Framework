# Create Test Database User

## Issue
The test database user `democrm_test` needs to be created with proper permissions.

## Solution Options

### Option 1: Using MySQL Root (Recommended)
If you have MySQL root access:

```bash
mysql -u root -p < tests/create-test-db-user.sql
```

### Option 2: Using Production User (If it has GRANT privileges)
Try using the production database user:

```bash
mysql -u democrm_democrm -p'b3J2sy5T4JNm60' < tests/create-test-db-user.sql
```

### Option 3: Manual Creation via cPanel/phpMyAdmin
If you use cPanel or phpMyAdmin:

1. Log into your database management interface
2. Go to MySQL Users section
3. Create new user:
   - **Username:** `democrm_test`
   - **Password:** `TestDB_2025_Secure!`
4. Create new database:
   - **Database name:** `democrm_test`
5. Add user to database with ALL PRIVILEGES
6. Also grant privileges to pattern: `democrm_test_%` (for ephemeral databases)

### Option 4: Run SQL Commands Manually
Connect to MySQL with admin privileges and run:

```sql
-- Create test database user
CREATE USER IF NOT EXISTS 'democrm_test'@'localhost' IDENTIFIED BY 'TestDB_2025_Secure!';

-- Grant permissions for test databases
GRANT ALL PRIVILEGES ON `democrm_test`.* TO 'democrm_test'@'localhost';
GRANT ALL PRIVILEGES ON `democrm_test_%`.* TO 'democrm_test'@'localhost';

-- Grant CREATE/DROP permissions for ephemeral databases
GRANT CREATE, DROP ON *.* TO 'democrm_test'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify user was created
SELECT User, Host FROM mysql.user WHERE User = 'democrm_test';

-- Show granted permissions
SHOW GRANTS FOR 'democrm_test'@'localhost';
```

## Verification

After creating the user, test the connection:

```bash
mysql -u democrm_test -p'TestDB_2025_Secure!' -e "SELECT 'Connection successful' as status;"
```

You should see:
```
+----------------------+
| status               |
+----------------------+
| Connection successful|
+----------------------+
```

## Next Steps

Once the user is created successfully, continue with:

```bash
# Step 3: Setup test database
php tests/setup-test-database.php --mode=persistent --seed=standard

# Step 4: Verify setup
php verify-testing-setup.php

# Step 5: Run tests
vendor/bin/phpunit
```

## Need Help?

If you're unsure how to access MySQL with admin privileges on your server, please let me know:
- What hosting control panel do you use? (cPanel, Plesk, DirectAdmin, etc.)
- Do you have SSH root access?
- Do you have phpMyAdmin access?

I can provide specific instructions based on your setup.
-- Create Test Database User
-- Run this as MySQL root or admin user

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
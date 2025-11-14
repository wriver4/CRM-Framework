#!/usr/bin/env php
<?php
/**
 * Generate Test User SQL Script
 * 
 * This script helps generate the correct SQL commands to create the test user
 * based on the production user's host configuration.
 * 
 * Since we can't connect from CLI, this provides instructions for running
 * the query in phpMyAdmin to get the host, then generates the SQL.
 */

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║           TEST USER SQL GENERATOR                           ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "This script will help you create the test user with the correct host.\n\n";

echo "STEP 1: Find the Production User's Host\n";
echo "════════════════════════════════════════\n\n";

echo "In phpMyAdmin, run this query:\n\n";
echo "  SELECT User, Host FROM mysql.user WHERE User LIKE 'democrm%';\n\n";

echo "You should see output like:\n";
echo "  User              | Host\n";
echo "  ------------------|------------------\n";
echo "  democrm_democrm   | <SOME_HOST_VALUE>\n\n";

echo "The <SOME_HOST_VALUE> could be:\n";
echo "  • localhost\n";
echo "  • 127.0.0.1\n";
echo "  • An IP address (e.g., 192.168.1.100)\n";
echo "  • A hostname (e.g., king.waveguardco.net)\n";
echo "  • A wildcard (e.g., %.waveguardco.net or %)\n\n";

echo "═══════════════════════════════════════════════════════════════\n\n";

echo "STEP 2: Enter the Host Value\n";
echo "═════════════════════════════\n\n";

echo "Enter the Host value from the query above: ";
$host = trim(fgets(STDIN));

if (empty($host)) {
    echo "\n❌ No host provided. Exiting.\n";
    exit(1);
}

echo "\n✅ Using host: '$host'\n\n";

echo "═══════════════════════════════════════════════════════════════\n\n";

echo "STEP 3: Generated SQL Commands\n";
echo "═══════════════════════════════\n\n";

echo "Copy and paste these commands into phpMyAdmin SQL tab:\n\n";
echo "```sql\n";
echo "-- ============================================\n";
echo "-- Create Test Database User\n";
echo "-- Host: $host\n";
echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
echo "-- ============================================\n\n";

echo "-- Drop user if exists (cleanup)\n";
echo "DROP USER IF EXISTS 'democrm_test'@'$host';\n\n";

echo "-- Create the test user\n";
echo "CREATE USER 'democrm_test'@'$host' IDENTIFIED BY 'TestDB_2025_Secure!';\n\n";

echo "-- Grant privileges on persistent test database\n";
echo "GRANT ALL PRIVILEGES ON \`democrm_test\`.* TO 'democrm_test'@'$host';\n\n";

echo "-- Grant privileges on ephemeral test databases (pattern: democrm_test_*)\n";
echo "GRANT ALL PRIVILEGES ON \`democrm_test_%\`.* TO 'democrm_test'@'$host';\n\n";

echo "-- Grant CREATE and DROP for database management\n";
echo "GRANT CREATE, DROP ON *.* TO 'democrm_test'@'$host';\n\n";

echo "-- Apply changes\n";
echo "FLUSH PRIVILEGES;\n\n";

echo "-- Create the test database\n";
echo "CREATE DATABASE IF NOT EXISTS \`democrm_test\` \n";
echo "CHARACTER SET utf8mb4 \n";
echo "COLLATE utf8mb4_unicode_ci;\n\n";

echo "-- Verify the user was created\n";
echo "SELECT User, Host FROM mysql.user WHERE User = 'democrm_test';\n\n";

echo "-- Show grants for the new user\n";
echo "SHOW GRANTS FOR 'democrm_test'@'$host';\n";
echo "```\n\n";

echo "═══════════════════════════════════════════════════════════════\n\n";

echo "STEP 4: Update Configuration (if needed)\n";
echo "═════════════════════════════════════════\n\n";

if ($host !== 'localhost') {
    echo "⚠️  The host is NOT 'localhost', so you need to update the configuration.\n\n";
    
    echo "Option A: Create .env file (recommended)\n";
    echo "─────────────────────────────────────────\n\n";
    echo "Create a file named '.env' in the project root with:\n\n";
    echo "TEST_DB_HOST=$host\n";
    echo "TEST_DB_USER=democrm_test\n";
    echo "TEST_DB_PASS=TestDB_2025_Secure!\n";
    echo "TEST_DB_NAME=democrm_test\n\n";
    
    echo "Option B: Edit config/testing.php\n";
    echo "──────────────────────────────────\n\n";
    echo "Change both occurrences of:\n";
    echo "  'host' => getenv('TEST_DB_HOST') ?: 'localhost',\n\n";
    echo "To:\n";
    echo "  'host' => getenv('TEST_DB_HOST') ?: '$host',\n\n";
} else {
    echo "✅ The host is 'localhost', so no configuration changes needed.\n";
    echo "   The default configuration should work.\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n\n";

echo "STEP 5: Verify the Setup\n";
echo "═════════════════════════\n\n";

echo "After running the SQL commands, verify with:\n\n";
echo "  php tests/verify-test-user.php\n\n";

echo "If successful, initialize the test database:\n\n";
echo "  php tests/setup-test-database.php --mode=persistent --seed=standard\n\n";

echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Done! Follow the steps above to complete the setup.\n\n";
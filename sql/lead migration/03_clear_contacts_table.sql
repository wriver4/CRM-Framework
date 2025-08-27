-- ===============================================
-- STEP 3: CLEAR CONTACTS TABLE
-- File: 03_clear_contacts_table.sql
-- Purpose: Clear existing contacts data before migration
-- ===============================================

-- WARNING: This will delete all existing contact data!
-- Ensure backup is completed before running this query!

-- Check current contacts count
SELECT 
    COUNT(*) as contacts_before_clear,
    'WILL_BE_DELETED' as warning
FROM contacts;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Clear all existing contacts data
DELETE FROM contacts;

-- Reset auto-increment counter
ALTER TABLE contacts AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verify table is empty
SELECT 
    COUNT(*) as contacts_after_clear,
    CASE 
        WHEN COUNT(*) = 0 THEN 'SUCCESS: Table cleared'
        ELSE 'ERROR: Table not empty'
    END as status
FROM contacts;

-- Verify table structure is intact
DESCRIBE contacts;

-- INSTRUCTIONS:
-- 1. Ensure database backup is completed
-- 2. Run this query to clear contacts table
-- 3. Verify count = 0 before proceeding to migration
-- 4. If errors occur, restore from backup immediately
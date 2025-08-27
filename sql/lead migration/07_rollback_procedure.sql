-- ===============================================
-- STEP 7: ROLLBACK PROCEDURE (EMERGENCY ONLY!)
-- File: 07_rollback_procedure.sql  
-- Purpose: Rollback migration if critical issues found
-- ===============================================

-- WARNING: Only use this if migration has critical failures!
-- This will restore the database to pre-migration state

-- STEP 1: Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- STEP 2: Clear migration data
-- Remove bridge table data
DELETE FROM leads_contacts;

-- Clear contact_id from leads table  
UPDATE leads SET contact_id = NULL;

-- Clear all contacts created during migration
DELETE FROM contacts;
ALTER TABLE contacts AUTO_INCREMENT = 1;

-- STEP 3: Remove schema changes
-- Drop bridge table
DROP TABLE IF EXISTS leads_contacts;

-- Remove indexes
DROP INDEX IF EXISTS idx_contacts_lead_id ON contacts;
DROP INDEX IF EXISTS idx_leads_contact_id ON leads;
DROP INDEX IF EXISTS idx_contacts_email ON contacts;
DROP INDEX IF EXISTS idx_contacts_phone ON contacts;
DROP INDEX IF EXISTS idx_leads_email ON leads;
DROP INDEX IF EXISTS idx_leads_phone ON leads;

-- Remove added columns (MariaDB compatible)
-- Check and drop lead_id column from contacts
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns 
    WHERE table_schema = DATABASE() AND table_name = 'contacts' AND column_name = 'lead_id');
SET @sql = IF(@col_exists > 0, 'ALTER TABLE contacts DROP COLUMN lead_id', 'SELECT "lead_id column does not exist" as status');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and drop contact_id column from leads
SET @col_exists2 = (SELECT COUNT(*) FROM information_schema.columns 
    WHERE table_schema = DATABASE() AND table_name = 'leads' AND column_name = 'contact_id');
SET @sql2 = IF(@col_exists2 > 0, 'ALTER TABLE leads DROP COLUMN contact_id', 'SELECT "contact_id column does not exist" as status');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Check and drop timestamp columns from contacts
SET @ts_exists = (SELECT COUNT(*) FROM information_schema.columns 
    WHERE table_schema = DATABASE() AND table_name = 'contacts' AND column_name = 'created_at');
SET @sql3 = IF(@ts_exists > 0, 'ALTER TABLE contacts DROP COLUMN created_at, DROP COLUMN updated_at', 'SELECT "timestamp columns do not exist" as status');
PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- STEP 4: Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- STEP 5: Verification
SELECT 'Rollback completed' as status;

-- Verify leads table unchanged
SELECT COUNT(*) as leads_count FROM leads;
DESCRIBE leads;

-- Verify contacts table cleared
SELECT COUNT(*) as contacts_count FROM contacts;
DESCRIBE contacts;

-- Verify bridge table removed
SHOW TABLES LIKE 'leads_contacts';

-- INSTRUCTIONS FOR MANUAL RESTORE:
-- 1. After running this rollback script
-- 2. Restore contacts table from backup file using PHPMyAdmin Import
-- 3. Verify all data restored correctly
-- 4. Check application functionality
-- 5. Investigate root cause of migration failure before retry

SELECT '
=== ROLLBACK COMPLETED ===
Next steps:
1. Restore contacts table from backup via PHPMyAdmin Import
2. Verify data integrity
3. Test application functionality
4. Investigate migration failure cause
5. Fix issues before attempting migration again
' as instructions;
-- ============================================================================
-- ROLLBACK SCRIPT FOR LEADS TABLE MIGRATION
-- ============================================================================
-- Use this script ONLY if you need to completely undo the migration
-- and restore the leads table to its original state.
-- 
-- WARNING: This will completely replace the current leads table!
-- ============================================================================

-- Verify backup table exists before proceeding
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN CONCAT('Backup table found with ', COUNT(*), ' records. Safe to proceed.')
        ELSE 'ERROR: Backup table not found! Cannot rollback safely.'
    END as backup_check
FROM information_schema.tables 
WHERE table_name = 'leads_backup_migration' 
AND table_schema = DATABASE();

-- Show current vs backup record counts
SELECT 
    'Current leads table:' as table_name,
    COUNT(*) as record_count 
FROM leads
UNION ALL
SELECT 
    'Backup table:' as table_name,
    COUNT(*) as record_count 
FROM leads_backup_migration;

-- ============================================================================
-- ROLLBACK PROCESS
-- ============================================================================

SELECT 'Starting rollback process...' as Status;

-- Start transaction for safety
START TRANSACTION;

-- Drop current leads table
DROP TABLE IF EXISTS leads_rollback_temp;
RENAME TABLE leads TO leads_rollback_temp;

-- Restore from backup
CREATE TABLE leads AS SELECT * FROM leads_backup_migration;

-- Verify restoration
SELECT 
    CASE 
        WHEN (SELECT COUNT(*) FROM leads) = (SELECT COUNT(*) FROM leads_backup_migration)
        THEN 'SUCCESS: Table restored successfully'
        ELSE 'ERROR: Restoration failed - record counts do not match'
    END as restoration_status;

-- If verification passed, commit the rollback
COMMIT;

SELECT 'ROLLBACK COMPLETED: Database restored to original state' as Status;
SELECT 'The modified table has been saved as leads_rollback_temp for reference' as Status;

-- ============================================================================
-- CLEANUP AFTER SUCCESSFUL ROLLBACK
-- ============================================================================
-- Uncomment these lines if you want to clean up after successful rollback:

-- DROP TABLE IF EXISTS leads_rollback_temp;
-- SELECT 'Cleanup completed: Temporary rollback table removed' as Status;

-- ============================================================================
-- ALTERNATIVE MANUAL ROLLBACK
-- ============================================================================
-- If you prefer to do this step by step:
--
-- 1. First, verify your backup:
--    SELECT COUNT(*) FROM leads_backup_migration;
--
-- 2. Create a backup of current modified table:
--    CREATE TABLE leads_modified_backup AS SELECT * FROM leads;
--
-- 3. Drop current table and restore:
--    DROP TABLE leads;
--    CREATE TABLE leads AS SELECT * FROM leads_backup_migration;
--
-- 4. Verify restoration:
--    SELECT COUNT(*) FROM leads;

SELECT 'Rollback script completed.' as Status;
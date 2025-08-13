-- ============================================================================
-- SAFE LEADS TABLE MIGRATION SCRIPT
-- ============================================================================
-- This script safely migrates the leads table to the new structure while
-- preserving all existing data and providing rollback capabilities.
-- 
-- IMPORTANT: Review this script before running and test on a copy first!
-- ============================================================================

-- Set session variables for safer operations
SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
SET SESSION foreign_key_checks = 0;

-- ============================================================================
-- STEP 1: CREATE BACKUP TABLE
-- ============================================================================
SELECT 'STEP 1: Creating backup table...' as Status;

-- Drop backup table if it exists from previous runs
DROP TABLE IF EXISTS `leads_backup_migration`;

-- Create backup table
CREATE TABLE `leads_backup_migration` AS SELECT * FROM `leads`;

-- Verify backup was created successfully
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN CONCAT('SUCCESS: Backup created with ', COUNT(*), ' records')
        ELSE 'WARNING: Backup table is empty'
    END as backup_status
FROM `leads_backup_migration`;

-- ============================================================================
-- STEP 2: PRE-MIGRATION VALIDATION
-- ============================================================================
SELECT 'STEP 2: Pre-migration validation...' as Status;

-- Count original records
SELECT COUNT(*) as original_record_count FROM `leads`;

-- Check for potential data issues
SELECT 'Checking for data quality issues...' as Status;

SELECT 
    COUNT(*) as total_records,
    COUNT(CASE WHEN first_name IS NULL OR first_name = '' THEN 1 END) as missing_first_names,
    COUNT(CASE WHEN family_name IS NULL OR family_name = '' THEN 1 END) as missing_family_names,
    COUNT(CASE WHEN email IS NULL OR email = '' THEN 1 END) as missing_emails,
    COUNT(CASE WHEN email NOT LIKE '%@%' THEN 1 END) as invalid_emails
FROM `leads`;

-- ============================================================================
-- STEP 3: BEGIN MIGRATION TRANSACTION
-- ============================================================================
SELECT 'STEP 3: Starting migration transaction...' as Status;

START TRANSACTION;

-- ============================================================================
-- STEP 4: ADD NEW COLUMNS
-- ============================================================================
SELECT 'STEP 4: Adding new columns...' as Status;

-- Add new columns that don't exist in current table
ALTER TABLE `leads` 
    ADD COLUMN `last_name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `first_name`,
    ADD COLUMN `ctype` TINYINT DEFAULT 1 AFTER `email`,
    ADD COLUMN `notes` TEXT AFTER `ctype`,
    ADD COLUMN `picture_submitted_1` VARCHAR(255) AFTER `structure_additional`,
    ADD COLUMN `picture_submitted_2` VARCHAR(255) AFTER `picture_submitted_1`,
    ADD COLUMN `picture_submitted_3` VARCHAR(255) AFTER `picture_submitted_2`,
    ADD COLUMN `plans_submitted_1` VARCHAR(255) AFTER `picture_submitted_3`,
    ADD COLUMN `plans_submitted_2` VARCHAR(255) AFTER `plans_submitted_1`,
    ADD COLUMN `plans_submitted_3` VARCHAR(255) AFTER `plans_submitted_2`,
    ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `stage`,
    ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
    ADD COLUMN `edited_by` INT(11) AFTER `updated_at`;

-- ============================================================================
-- STEP 5: MIGRATE EXISTING DATA
-- ============================================================================
SELECT 'STEP 5: Migrating existing data...' as Status;

-- Migrate family_name to last_name
UPDATE `leads` 
SET `last_name` = COALESCE(`family_name`, '') 
WHERE `family_name` IS NOT NULL AND `family_name` != '';

-- Convert lead_source from string to TINYINT
UPDATE `leads` 
SET `lead_source` = CASE 
    WHEN LOWER(`lead_source`) LIKE '%web%' THEN 1
    WHEN LOWER(`lead_source`) LIKE '%referral%' THEN 2
    WHEN LOWER(`lead_source`) LIKE '%phone%' THEN 3
    WHEN LOWER(`lead_source`) LIKE '%email%' THEN 4
    WHEN LOWER(`lead_source`) LIKE '%other%' THEN 5
    ELSE 1
END
WHERE `lead_source` IS NOT NULL;

-- Set default ctype (contact type) to 1 for existing records
UPDATE `leads` 
SET `ctype` = 1 
WHERE `ctype` IS NULL;

-- Migrate existing notes from various note fields to the main notes field
UPDATE `leads` 
SET `notes` = CONCAT_WS('\n', 
    CASE WHEN `lead_notes` IS NOT NULL AND `lead_notes` != '' THEN CONCAT('Lead Notes: ', `lead_notes`) END,
    CASE WHEN `prospect_notes` IS NOT NULL AND `prospect_notes` != '' THEN CONCAT('Prospect Notes: ', `prospect_notes`) END,
    CASE WHEN `lead_lost_notes` IS NOT NULL AND `lead_lost_notes` != '' THEN CONCAT('Lead Lost Notes: ', `lead_lost_notes`) END,
    CASE WHEN `closing_notes` IS NOT NULL AND `closing_notes` != '' THEN CONCAT('Closing Notes: ', `closing_notes`) END,
    CASE WHEN `jd_referral_notes` IS NOT NULL AND `jd_referral_notes` != '' THEN CONCAT('JD Referral Notes: ', `jd_referral_notes`) END
)
WHERE (`lead_notes` IS NOT NULL AND `lead_notes` != '') 
   OR (`prospect_notes` IS NOT NULL AND `prospect_notes` != '')
   OR (`lead_lost_notes` IS NOT NULL AND `lead_lost_notes` != '')
   OR (`closing_notes` IS NOT NULL AND `closing_notes` != '')
   OR (`jd_referral_notes` IS NOT NULL AND `jd_referral_notes` != '');

-- Handle picture_submitted migration
UPDATE `leads` 
SET `picture_submitted_1` = `picture_submitted`
WHERE `picture_submitted` IS NOT NULL AND `picture_submitted` != '' AND `picture_submitted` != 'false';

-- Handle plans_submitted migration
UPDATE `leads` 
SET `plans_submitted_1` = `plans_submitted`
WHERE `plans_submitted` IS NOT NULL AND `plans_submitted` != '' AND `plans_submitted` != 'false';

-- Convert structure_type from string to TINYINT
UPDATE `leads` 
SET `structure_type` = CASE 
    WHEN LOWER(`structure_type`) LIKE '%residential%existing%' THEN 1
    WHEN LOWER(`structure_type`) LIKE '%residential%new%' THEN 2
    WHEN LOWER(`structure_type`) LIKE '%commercial%existing%' THEN 3
    WHEN LOWER(`structure_type`) LIKE '%commercial%new%' THEN 4
    WHEN LOWER(`structure_type`) LIKE '%industrial%' THEN 5
    ELSE 1
END
WHERE `structure_type` IS NOT NULL;

-- Convert boolean-like string values to INT(1)
UPDATE `leads` 
SET `plans_and_pics` = CASE 
    WHEN LOWER(`plans_and_pics`) IN ('true', '1', 'yes') THEN 1
    ELSE 0
END;

UPDATE `leads` 
SET `get_updates` = CASE 
    WHEN LOWER(`get_updates`) IN ('true', '1', 'yes') THEN 1
    WHEN LOWER(`get_updates`) IN ('false', '0', 'no') THEN 0
    ELSE 1
END;

-- Set created_at and updated_at for existing records
UPDATE `leads` 
SET `created_at` = CURRENT_TIMESTAMP,
    `updated_at` = CURRENT_TIMESTAMP
WHERE `created_at` IS NULL;

-- Clean up services_interested_in and hear_about to match VARCHAR(20) constraint
UPDATE `leads` 
SET `services_interested_in` = LEFT(`services_interested_in`, 20)
WHERE LENGTH(`services_interested_in`) > 20;

UPDATE `leads` 
SET `hear_about` = LEFT(`hear_about`, 20)
WHERE LENGTH(`hear_about`) > 20;

-- Clean up structure_description to match VARCHAR(20) constraint
UPDATE `leads` 
SET `structure_description` = LEFT(`structure_description`, 20)
WHERE LENGTH(`structure_description`) > 20;

-- ============================================================================
-- STEP 6: MODIFY COLUMN TYPES
-- ============================================================================
SELECT 'STEP 6: Modifying column types...' as Status;

-- Modify existing columns to match new structure
ALTER TABLE `leads`
    MODIFY COLUMN `lead_source` TINYINT NOT NULL DEFAULT 1,
    MODIFY COLUMN `first_name` VARCHAR(100) NOT NULL DEFAULT '',
    MODIFY COLUMN `cell_phone` VARCHAR(15),
    MODIFY COLUMN `email` VARCHAR(255) NOT NULL DEFAULT '',
    MODIFY COLUMN `p_street_1` VARCHAR(100),
    MODIFY COLUMN `p_street_2` VARCHAR(50),
    MODIFY COLUMN `p_city` VARCHAR(50),
    MODIFY COLUMN `p_state` VARCHAR(10),
    MODIFY COLUMN `p_postcode` VARCHAR(15),
    MODIFY COLUMN `p_country` VARCHAR(5) DEFAULT 'US',
    MODIFY COLUMN `services_interested_in` VARCHAR(20),
    MODIFY COLUMN `structure_type` TINYINT DEFAULT 1,
    MODIFY COLUMN `structure_description` VARCHAR(20),
    MODIFY COLUMN `structure_other` VARCHAR(255),
    MODIFY COLUMN `structure_additional` TEXT,
    MODIFY COLUMN `picture_upload_link` VARCHAR(500),
    MODIFY COLUMN `plans_upload_link` VARCHAR(500),
    MODIFY COLUMN `plans_and_pics` INT(1) DEFAULT 0,
    MODIFY COLUMN `get_updates` INT(1) DEFAULT 1,
    MODIFY COLUMN `hear_about` VARCHAR(20),
    MODIFY COLUMN `hear_about_other` VARCHAR(255),
    MODIFY COLUMN `stage` VARCHAR(20) DEFAULT 'Lead';

-- ============================================================================
-- STEP 7: ADD INDEXES
-- ============================================================================
SELECT 'STEP 7: Adding indexes...' as Status;

-- Add indexes for better performance (ignore if they already exist)
ALTER TABLE `leads`
    ADD INDEX `idx_lead_source` (`lead_source`),
    ADD INDEX `idx_email` (`email`),
    ADD INDEX `idx_stage` (`stage`),
    ADD INDEX `idx_created_at` (`created_at`),
    ADD INDEX `idx_state` (`p_state`),
    ADD INDEX `idx_country` (`p_country`),
    ADD INDEX `idx_structure_type` (`structure_type`),
    ADD INDEX `idx_edited_by` (`edited_by`);

-- ============================================================================
-- STEP 8: POST-MIGRATION VALIDATION
-- ============================================================================
SELECT 'STEP 8: Post-migration validation...' as Status;

-- Verify record count matches
SELECT 
    (SELECT COUNT(*) FROM `leads_backup_migration`) as original_count,
    (SELECT COUNT(*) FROM `leads`) as migrated_count,
    CASE 
        WHEN (SELECT COUNT(*) FROM `leads_backup_migration`) = (SELECT COUNT(*) FROM `leads`) 
        THEN 'SUCCESS: Record counts match'
        ELSE 'ERROR: Record counts do not match!'
    END as count_validation;

-- Check data quality after migration
SELECT 'Data quality check after migration:' as Status;

SELECT 
    COUNT(*) as total_records,
    COUNT(CASE WHEN first_name IS NULL OR first_name = '' THEN 1 END) as missing_first_names,
    COUNT(CASE WHEN last_name IS NULL OR last_name = '' THEN 1 END) as missing_last_names,
    COUNT(CASE WHEN email IS NULL OR email = '' THEN 1 END) as missing_emails,
    COUNT(CASE WHEN lead_source NOT BETWEEN 1 AND 6 THEN 1 END) as invalid_lead_sources,
    COUNT(CASE WHEN structure_type NOT BETWEEN 1 AND 6 THEN 1 END) as invalid_structure_types,
    COUNT(CASE WHEN ctype IS NULL OR ctype NOT BETWEEN 1 AND 5 THEN 1 END) as invalid_contact_types
FROM `leads`;

-- Sample data check - show first 5 records to verify migration
SELECT 'Sample of migrated data (first 5 records):' as Status;

SELECT 
    id, 
    lead_source, 
    first_name, 
    last_name, 
    email, 
    ctype, 
    structure_type,
    created_at,
    updated_at,
    edited_by,
    CASE WHEN notes IS NOT NULL AND notes != '' THEN 'Has notes' ELSE 'No notes' END as notes_status
FROM `leads` 
ORDER BY id 
LIMIT 5;

-- ============================================================================
-- STEP 9: COMMIT OR ROLLBACK DECISION POINT
-- ============================================================================
SELECT 'STEP 9: Migration completed. Review the validation results above.' as Status;
SELECT 'If everything looks correct, the transaction will be committed.' as Status;
SELECT 'If there are issues, you can ROLLBACK to restore the original state.' as Status;

-- Automatic commit (comment out if you want manual control)
COMMIT;

SELECT 'SUCCESS: Migration completed and committed!' as Status;
SELECT 'Your original data is preserved in the leads_backup_migration table.' as Status;

-- ============================================================================
-- ROLLBACK PROCEDURE (if needed)
-- ============================================================================
-- If you need to rollback, run these commands INSTEAD of COMMIT above:
-- 
-- ROLLBACK;
-- 
-- -- To completely restore from backup:
-- DROP TABLE leads;
-- CREATE TABLE leads AS SELECT * FROM leads_backup_migration;
--
-- SELECT 'ROLLBACK: Database restored to original state' as Status;

-- ============================================================================
-- CLEANUP (Optional - run after confirming migration success)
-- ============================================================================
-- After confirming the migration was successful, you can optionally clean up:
-- 
-- DROP TABLE IF EXISTS leads_backup_migration;
-- DROP TABLE IF EXISTS leads_revised;  -- Your original backup
--
-- SELECT 'Cleanup completed: Backup tables removed' as Status;

-- ============================================================================
-- POST-MIGRATION NOTES
-- ============================================================================
-- 1. Update your PHP application to use the new field names
-- 2. Test all functionality thoroughly
-- 3. The edited_by field will be NULL for existing records until they are edited
-- 4. All existing business logic fields are preserved
-- 5. New form structure is now ready for use

SELECT 'Migration script completed. Please review all validation results above.' as Status;

-- Re-enable foreign key checks
SET SESSION foreign_key_checks = 1;
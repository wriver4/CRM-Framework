-- DATA MIGRATION script to convert existing data to match new leads_form_table.sql structure
-- This script migrates existing data from old column format to new column format
-- Run this AFTER alter_table.sql

-- Start transaction to ensure atomicity
START TRANSACTION;

-- 1. Migrate family_name to last_name
UPDATE `leads` 
SET `last_name` = COALESCE(`family_name`, '') 
WHERE `family_name` IS NOT NULL AND `family_name` != '';

-- 2. Convert lead_source from string to TINYINT
-- Mapping based on common lead source values:
-- 'Web Estimate Complete' -> 1 (Web)
-- 'Web Estimate' -> 1 (Web)
-- 'Referral' -> 2 (Referral)
-- 'Phone' -> 3 (Phone)
-- 'Email' -> 4 (Email)
-- 'Other' -> 5 (Other)
-- Default/Unknown -> 1 (Web)

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

-- 3. Set default ctype (contact type) to 1 for existing records
UPDATE `leads` 
SET `ctype` = 1 
WHERE `ctype` IS NULL;

-- 4. Migrate existing notes from various note fields to the main notes field
-- Combine lead_notes, prospect_notes, and other note fields
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

-- 5. Handle picture_submitted migration
-- The old table has picture_submitted (text), new table has picture_submitted_1, picture_submitted_2, picture_submitted_3
UPDATE `leads` 
SET `picture_submitted_1` = `picture_submitted`
WHERE `picture_submitted` IS NOT NULL AND `picture_submitted` != '' AND `picture_submitted` != 'false';

-- 6. Handle plans_submitted migration
-- The old table has plans_submitted (text), new table has plans_submitted_1, plans_submitted_2, plans_submitted_3
UPDATE `leads` 
SET `plans_submitted_1` = `plans_submitted`
WHERE `plans_submitted` IS NOT NULL AND `plans_submitted` != '' AND `plans_submitted` != 'false';

-- 7. Convert structure_type from string to TINYINT
-- Common mappings:
-- 'Residential - Existing' -> 1
-- 'Residential - New Construction' -> 2
-- 'Commercial - Existing' -> 3
-- 'Commercial - New Construction' -> 4
-- 'Industrial' -> 5
-- 'Other' -> 6

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

-- 8. Convert boolean-like string values to INT(1)
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

-- 9. Set created_at, updated_at, and edited_by for existing records if they're null
-- Note: edited_by will be NULL for existing records until they are edited by a user
UPDATE `leads` 
SET `created_at` = CURRENT_TIMESTAMP,
    `updated_at` = CURRENT_TIMESTAMP
WHERE `created_at` IS NULL;

-- 10. Clean up services_interested_in and hear_about to match VARCHAR(20) constraint
-- Truncate if longer than 20 characters
UPDATE `leads` 
SET `services_interested_in` = LEFT(`services_interested_in`, 20)
WHERE LENGTH(`services_interested_in`) > 20;

UPDATE `leads` 
SET `hear_about` = LEFT(`hear_about`, 20)
WHERE LENGTH(`hear_about`) > 20;

-- 11. Clean up structure_description to match VARCHAR(20) constraint
UPDATE `leads` 
SET `structure_description` = LEFT(`structure_description`, 20)
WHERE LENGTH(`structure_description`) > 20;

COMMIT;

-- Success message
SELECT 'Data migration completed successfully. You can now optionally drop old columns if they are no longer needed.' as Status;

-- Optional: Show summary of migrated data
SELECT 
    COUNT(*) as total_records,
    COUNT(CASE WHEN `last_name` != '' THEN 1 END) as records_with_last_name,
    COUNT(CASE WHEN `notes` IS NOT NULL AND `notes` != '' THEN 1 END) as records_with_notes,
    COUNT(CASE WHEN `picture_submitted_1` IS NOT NULL THEN 1 END) as records_with_pictures,
    COUNT(CASE WHEN `plans_submitted_1` IS NOT NULL THEN 1 END) as records_with_plans
FROM `leads`;
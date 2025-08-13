-- ALTER TABLE script to convert leads table to match leads_form_table.sql structure
-- This script modifies the existing leads table structure while preserving existing business fields

-- Start transaction to ensure atomicity
START TRANSACTION;

-- 1. Add new columns that don't exist in current table
ALTER TABLE `leads` 
    ADD COLUMN `last_name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `first_name`,
    ADD COLUMN `ctype` TINYINT DEFAULT 1 AFTER `email`,
    ADD COLUMN `notes` TEXT AFTER `ctype`,
    ADD COLUMN `picture_submitted_1` VARCHAR(255) AFTER `structure_additional`,
    ADD COLUMN `picture_submitted_2` VARCHAR(255) AFTER `picture_submitted_1`,
    ADD COLUMN `picture_submitted_3` VARCHAR(255) AFTER `picture_submitted_2`,
    ADD COLUMN `plans_submitted_1` VARCHAR(255) AFTER `picture_submitted_3`,
    ADD COLUMN `plans_submitted_2` VARCHAR(255) AFTER `plans_submitted_1`,
    ADD COLUMN `plans_submitted_3` VARCHAR(255) AFTER `plans_submitted_2`;

-- 2. Modify existing columns to match new structure
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

-- 3. Add new timestamp columns and tracking fields if they don't exist
ALTER TABLE `leads`
    ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `stage`,
    ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
    ADD COLUMN `edited_by` INT(11) AFTER `updated_at`;

-- 4. Add indexes to match the new structure
ALTER TABLE `leads`
    ADD INDEX `idx_lead_source` (`lead_source`),
    ADD INDEX `idx_email` (`email`),
    ADD INDEX `idx_stage` (`stage`),
    ADD INDEX `idx_created_at` (`created_at`),
    ADD INDEX `idx_state` (`p_state`),
    ADD INDEX `idx_country` (`p_country`),
    ADD INDEX `idx_structure_type` (`structure_type`),
    ADD INDEX `idx_edited_by` (`edited_by`);

-- 5. Rename columns to match new structure (keeping old ones for data migration)
-- Note: We'll handle the data migration in alter_data.sql before dropping old columns

COMMIT;

-- Success message
SELECT 'ALTER TABLE script completed successfully. Run alter_data.sql next to migrate existing data.' as Status;
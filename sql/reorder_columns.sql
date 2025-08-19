-- ALTER TABLE query to reorganize leads table columns
-- 1. Rename edited_by to last_edited_by
-- 2. Move last_edited_by before updated_at
-- 3. Move updated_at before created_at  
-- 4. Move created_at to the end

-- Note: MySQL doesn't have a direct way to reorder columns, so we need to:
-- 1. First rename the column
-- 2. Then use MODIFY to reposition columns

START TRANSACTION;

-- Step 1: Rename edited_by to last_edited_by
ALTER TABLE `leads` 
    CHANGE COLUMN `edited_by` `last_edited_by` INT(11);

-- Step 2: Move last_edited_by after to_contracting column
ALTER TABLE `leads` 
    MODIFY COLUMN `last_edited_by` INT(11) AFTER `to_contracting`;

-- Step 3: Move updated_at after last_edited_by
ALTER TABLE `leads` 
    MODIFY COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `last_edited_by`;

-- Step 4: Move created_at after updated_at (at the very end)
ALTER TABLE `leads` 
    MODIFY COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `updated_at`;

-- Step 5: Move lead_source after plans_and_pics
ALTER TABLE `leads` 
    MODIFY COLUMN `lead_source` TINYINT NOT NULL DEFAULT 1 AFTER `plans_and_pics`;

-- Update the index since we renamed the column
ALTER TABLE `leads` 
    DROP INDEX `idx_edited_by`;

ALTER TABLE `leads` 
    ADD INDEX `idx_last_edited_by` (`last_edited_by`);

COMMIT;

-- Verify the new column order
DESCRIBE `leads`;

SELECT 'Column reordering completed successfully!' as Status;
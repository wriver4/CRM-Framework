-- ============================================================================
-- Migration: Rename role columns rid -> role_id and rname -> role
-- Date: 2025-01-13
-- Purpose: Improve column naming clarity and consistency
-- ============================================================================
-- Start transaction for safety
START TRANSACTION;

-- ============================================================================
-- Phase 1: Update `roles` table
-- ============================================================================
-- Step 1: Rename rid to role_id
ALTER TABLE `roles` CHANGE COLUMN `rid` `role_id` INT (11) NOT NULL;

-- Step 2: Rename rname to role
ALTER TABLE `roles` CHANGE COLUMN `rname` `role` VARCHAR(50) NOT NULL;

-- Step 3: Recreate unique constraints with new names
ALTER TABLE `roles`
DROP INDEX `rid`,
DROP INDEX `rname`,
ADD UNIQUE KEY `role_id` (`role_id`),
ADD UNIQUE KEY `role` (`role`);

-- ============================================================================
-- Phase 2: Update `roles_permissions` table (bridge table)
-- ============================================================================
-- Step 1: Drop existing primary key that uses rid
ALTER TABLE `roles_permissions`
DROP PRIMARY KEY;

-- Step 2: Rename rid to role_id
ALTER TABLE `roles_permissions` CHANGE COLUMN `rid` `role_id` INT (11) NOT NULL;

-- Step 3: Recreate primary key with new column name
ALTER TABLE `roles_permissions` ADD PRIMARY KEY (`role_id`, `pid`);

-- ============================================================================
-- Phase 3: Update `users` table (has foreign key to roles.rid)
-- ============================================================================
-- Step 1: Rename rid to role_id for consistency
ALTER TABLE `users` CHANGE COLUMN `rid` `role_id` INT (10) UNSIGNED NOT NULL;

-- ============================================================================
-- Verification queries (uncomment to verify after migration)
-- ============================================================================
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'roles' AND TABLE_SCHEMA = 'democrm';
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'roles_permissions' AND TABLE_SCHEMA = 'democrm';
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'users' AND TABLE_SCHEMA = 'democrm' 
-- AND COLUMN_NAME IN ('role_id', 'rid');
-- ============================================================================
-- Commit the transaction
-- ============================================================================
COMMIT;

-- ============================================================================
-- ROLLBACK SCRIPT (use if migration fails)
-- ============================================================================
-- Uncomment below to rollback if needed:
/*
START TRANSACTION;

ALTER TABLE `users` 
CHANGE COLUMN `role_id` `rid` INT(10) UNSIGNED NOT NULL;

ALTER TABLE `roles_permissions` 
DROP PRIMARY KEY;

ALTER TABLE `roles_permissions` 
CHANGE COLUMN `role_id` `rid` INT(11) NOT NULL;

ALTER TABLE `roles_permissions` 
ADD PRIMARY KEY (`rid`, `pid`);

ALTER TABLE `roles` 
DROP INDEX `role_id`,
DROP INDEX `role`,
ADD UNIQUE KEY `rid` (`role_id`),
ADD UNIQUE KEY `rname` (`role`);

ALTER TABLE `roles` 
CHANGE COLUMN `role_id` `rid` INT(11) NOT NULL;

ALTER TABLE `roles` 
CHANGE COLUMN `role` `rname` VARCHAR(50) NOT NULL;

COMMIT;
 */
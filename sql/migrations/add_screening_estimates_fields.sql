-- Migration: Add Screening Estimates fields to leads table
-- Date: 2025-01-27
-- Description: Add 6 fields for screening estimates - 3 for engineering and 3 for sales
-- Each set has: system cost low, system cost high, and protected area
-- 
-- ⚠️ DEVELOPER ACTION REQUIRED:
-- This migration must be executed manually in phpMyAdmin due to hosting restrictions
-- 
-- 📋 EXECUTION STEPS:
-- 1. Open phpMyAdmin and navigate to democrm_democrm database
-- 2. Copy and paste the SQL statements below into the SQL tab
-- 3. Execute the statements one by one or all at once
-- 4. Verify the new columns exist in the leads table
-- 
-- 🔔 CRITICAL: AFTER MIGRATION EXECUTION
-- You MUST update the structure file! Run this command after executing the migration:
-- 
--    php scripts/update_structure_reminder.php sql/migrations/add_screening_estimates_fields.sql
-- 
-- This will remind you to export the updated database structure from phpMyAdmin
-- and replace /sql/democrm_democrm_structure.sql with the new version.
-- 
-- 💡 WHY STRUCTURE UPDATE IS REQUIRED:
-- - Fresh installations use the structure file
-- - Other developers need the updated schema
-- - Deployment processes rely on this file
-- - It serves as the authoritative database reference
-- Add engineering screening estimate fields after structure_additional
ALTER TABLE leads
ADD COLUMN eng_system_cost_low INT DEFAULT NULL COMMENT 'Engineering estimate - system cost low range (whole dollars)' AFTER structure_additional;

ALTER TABLE leads
ADD COLUMN eng_system_cost_high INT DEFAULT NULL COMMENT 'Engineering estimate - system cost high range (whole dollars)' AFTER eng_system_cost_low;

ALTER TABLE leads
ADD COLUMN eng_protected_area INT DEFAULT NULL COMMENT 'Engineering estimate - protected area (square feet)' AFTER eng_system_cost_high;

-- Add sales screening estimate fields
ALTER TABLE leads
ADD COLUMN sales_system_cost_low INT DEFAULT NULL COMMENT 'Sales estimate - system cost low range (whole dollars)' AFTER eng_protected_area;

ALTER TABLE leads
ADD COLUMN sales_system_cost_high INT DEFAULT NULL COMMENT 'Sales estimate - system cost high range (whole dollars)' AFTER sales_system_cost_low;

ALTER TABLE leads
ADD COLUMN sales_protected_area INT DEFAULT NULL COMMENT 'Sales estimate - protected area (square feet)' AFTER sales_system_cost_high;

-- ==================================================================================
-- 
-- 🎯 POST-MIGRATION CHECKLIST:
-- □ Migration executed successfully in phpMyAdmin
-- □ All 6 new columns added to leads table:
--   □ eng_system_cost_low
--   □ eng_system_cost_high  
--   □ eng_protected_area
--   □ sales_system_cost_low
--   □ sales_system_cost_high
--   □ sales_protected_area
-- □ Application tested with new schema
-- □ Structure file updated (run reminder script above)
-- □ Changes committed to version control
-- 
-- ==================================================================================
-- Migration: Rename fullname column to full_name in contacts table
-- Date: 2025-01-09
-- Description: Standardize column naming convention from fullname to full_name
-- Step 1: Create backup table before making changes
CREATE TABLE
  contacts_backup_before_fullname_rename AS
SELECT
  *
FROM
  contacts;

-- Step 2: Add the new full_name column
ALTER TABLE contacts
ADD COLUMN full_name varchar(200) NOT NULL DEFAULT '';

-- Step 3: Copy data from fullname to full_name
UPDATE contacts
SET
  full_name = fullname;

-- Step 4: Drop the old fullname column
ALTER TABLE contacts
DROP COLUMN fullname;

-- Verification query (run this after migration to verify)
-- SELECT COUNT(*) as total_contacts, 
--        COUNT(full_name) as full_name_count,
--        COUNT(CASE WHEN full_name = '' THEN 1 END) as empty_full_names
-- FROM contacts;
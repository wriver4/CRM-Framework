-- Migration script to fix contacts.lead_id data type
-- Convert from varchar(10) to int(11) to match leads.id and other tables
-- Step 1: Check current data integrity
-- This will show any lead_id values that cannot be converted to integers
SELECT
  'Checking for non-numeric lead_id values in contacts table:' as status;

SELECT
  id,
  lead_id,
  first_name,
  family_name
FROM
  contacts
WHERE
  lead_id NOT REGEXP '^[0-9]+$'
  OR lead_id = ''
  OR lead_id IS NULL;

-- Step 2: Check for lead_id values that don't exist in leads table
SELECT
  'Checking for orphaned lead_id references:' as status;

SELECT
  c.id,
  c.lead_id,
  c.first_name,
  c.family_name
FROM
  contacts c
  LEFT JOIN leads l ON CAST(c.lead_id AS UNSIGNED) = l.id
WHERE
  l.id IS NULL
  AND c.lead_id != ''
  AND c.lead_id IS NOT NULL;

-- Step 3: Backup the current contacts table structure and data
CREATE TABLE
  contacts_backup_before_lead_id_fix AS
SELECT
  *
FROM
  contacts;

SELECT
  'Backup table created: contacts_backup_before_lead_id_fix' as status;

-- Step 4: Alter the lead_id column to int(11)
-- Note: This will automatically convert string values to integers
ALTER TABLE contacts MODIFY COLUMN lead_id int (11) DEFAULT NULL;

-- Step 5: Add foreign key constraint to ensure referential integrity
-- First, ensure all lead_id values reference valid leads
UPDATE contacts
SET
  lead_id = NULL
WHERE
  lead_id NOT IN (
    SELECT
      id
    FROM
      leads
  );

-- Add the foreign key constraint
ALTER TABLE contacts ADD CONSTRAINT fk_contacts_lead_id FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Step 6: Verify the changes
SELECT
  'Verification - contacts table structure:' as status;

DESCRIBE contacts;

SELECT
  'Verification - sample data after conversion:' as status;

SELECT
  id,
  lead_id,
  first_name,
  family_name
FROM
  contacts
LIMIT
  10;

SELECT
  'Migration completed successfully!' as status;
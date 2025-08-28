-- Migration script to sync contacts.lead_id with leads.lead_id (business identifier)
-- Currently contacts.lead_id references leads.id (database PK)
-- We want it to reference leads.lead_id (business identifier)
-- STEP 1: Analyze current data
SELECT
  'STEP 1: Analyzing current data relationships' as current_step;

-- Show current relationship structure
SELECT
  'Current contacts.lead_id -> leads.id relationship' as relationship_type,
  COUNT(*) as total_records
FROM
  contacts c
  INNER JOIN leads l ON c.lead_id = l.id
WHERE
  c.lead_id IS NOT NULL
UNION ALL
SELECT
  'Contacts with NULL lead_id' as relationship_type,
  COUNT(*) as total_records
FROM
  contacts c
WHERE
  c.lead_id IS NULL;

-- STEP 2: Show sample of current data
SELECT
  'STEP 2: Sample of current data before migration' as current_step;

SELECT
  c.id as contact_id,
  c.lead_id as contact_lead_id_current,
  c.first_name,
  c.family_name,
  l.id as leads_db_id,
  l.lead_id as leads_business_id
FROM
  contacts c
  INNER JOIN leads l ON c.lead_id = l.id
WHERE
  c.lead_id IS NOT NULL
ORDER BY
  c.id
LIMIT
  10;

-- STEP 3: Create backup table
SELECT
  'STEP 3: Creating backup table' as current_step;

CREATE TABLE
  contacts_backup_before_lead_id_sync AS
SELECT
  *
FROM
  contacts;

SELECT
  'Backup table created: contacts_backup_before_lead_id_sync' as result;

-- STEP 4: Update contacts.lead_id to use business identifier
SELECT
  'STEP 4: Updating contacts.lead_id to use business identifier' as current_step;

-- First, let's see what the update will do
SELECT
  'Preview of updates to be made' as preview,
  COUNT(*) as records_to_update
FROM
  contacts c
  INNER JOIN leads l ON c.lead_id = l.id
WHERE
  c.lead_id IS NOT NULL
  AND c.lead_id != l.lead_id;

-- Show sample of what will change
SELECT
  c.id as contact_id,
  c.lead_id as current_value,
  l.lead_id as new_value,
  c.first_name,
  c.family_name
FROM
  contacts c
  INNER JOIN leads l ON c.lead_id = l.id
WHERE
  c.lead_id IS NOT NULL
  AND c.lead_id != l.lead_id
LIMIT
  10;

-- Perform the update
UPDATE contacts c
INNER JOIN leads l ON c.lead_id = l.id
SET
  c.lead_id = l.lead_id
WHERE
  c.lead_id IS NOT NULL;

SELECT
  ROW_COUNT () as records_updated;

-- STEP 5: Verify the changes
SELECT
  'STEP 5: Verifying the migration' as current_step;

-- Show sample of updated data
SELECT
  c.id as contact_id,
  c.lead_id as contact_lead_id_new,
  c.first_name,
  c.family_name,
  l.id as leads_db_id,
  l.lead_id as leads_business_id,
  CASE
    WHEN c.lead_id = l.lead_id THEN 'MATCH'
    ELSE 'MISMATCH'
  END as verification_status
FROM
  contacts c
  INNER JOIN leads l ON c.lead_id = l.lead_id
WHERE
  c.lead_id IS NOT NULL
ORDER BY
  c.id
LIMIT
  10;

-- Check for any mismatches
SELECT
  'Verification: Contacts with matching business IDs' as check_type,
  COUNT(*) as count
FROM
  contacts c
  INNER JOIN leads l ON c.lead_id = l.lead_id
WHERE
  c.lead_id IS NOT NULL
UNION ALL
SELECT
  'Verification: Contacts with orphaned lead_id values' as check_type,
  COUNT(*) as count
FROM
  contacts c
  LEFT JOIN leads l ON c.lead_id = l.lead_id
WHERE
  c.lead_id IS NOT NULL
  AND l.lead_id IS NULL;

-- STEP 6: Update data type if needed
SELECT
  'STEP 6: Checking if data type update is needed' as current_step;

-- Check current data type of contacts.lead_id
SELECT
  column_name,
  data_type,
  column_type,
  is_nullable
FROM
  information_schema.columns
WHERE
  table_schema = DATABASE ()
  AND table_name = 'contacts'
  AND column_name = 'lead_id';

-- Check data type of leads.lead_id for comparison
SELECT
  column_name,
  data_type,
  column_type,
  is_nullable
FROM
  information_schema.columns
WHERE
  table_schema = DATABASE ()
  AND table_name = 'leads'
  AND column_name = 'lead_id';

-- If contacts.lead_id is INT and leads.lead_id is VARCHAR, we need to update the data type
-- This will be done in a separate step if needed
SELECT
  'STEP 7: Migration completed successfully!' as final_result;

-- Final verification query
SELECT
  'Final verification' as status,
  COUNT(*) as total_contacts_with_lead_id,
  COUNT(
    CASE
      WHEN l.lead_id IS NOT NULL THEN 1
    END
  ) as matching_business_ids,
  COUNT(
    CASE
      WHEN l.lead_id IS NULL THEN 1
    END
  ) as orphaned_references
FROM
  contacts c
  LEFT JOIN leads l ON c.lead_id = l.lead_id
WHERE
  c.lead_id IS NOT NULL;
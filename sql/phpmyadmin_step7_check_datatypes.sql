-- STEP 7: Check if data types need to be aligned
SELECT
  'STEP 7: Checking data types' as current_step;

-- Check current data type of contacts.lead_id
SELECT
  'contacts.lead_id' as table_field,
  column_name,
  data_type,
  column_type,
  is_nullable
FROM
  information_schema.columns
WHERE
  table_schema = DATABASE ()
  AND table_name = 'contacts'
  AND column_name = 'lead_id'
UNION ALL
-- Check data type of leads.lead_id for comparison
SELECT
  'leads.lead_id' as table_field,
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
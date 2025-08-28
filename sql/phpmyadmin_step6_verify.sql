-- STEP 6: Verify the changes
SELECT
  'STEP 6: Verifying the migration' as current_step;

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
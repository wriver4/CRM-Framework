-- STEP 4: Preview what the update will do
SELECT
  'STEP 4: Preview of updates to be made' as preview;

SELECT
  'Records that will be updated' as info,
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
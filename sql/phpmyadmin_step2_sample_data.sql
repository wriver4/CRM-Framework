-- STEP 2: Show sample of current data before migration
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
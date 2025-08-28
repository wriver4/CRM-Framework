-- STEP 5: Perform the actual update
-- WARNING: This will modify your data. Make sure you've run the backup step first!
UPDATE contacts c
INNER JOIN leads l ON c.lead_id = l.id
SET
  c.lead_id = l.lead_id
WHERE
  c.lead_id IS NOT NULL;
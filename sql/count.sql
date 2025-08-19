-- Query to count records with null ID numbers in leads table            
-- Count leads with NULL id                                              
SELECT
  COUNT(*) as null_id_count
FROM
  leads
WHERE
  id IS NULL;

-- Count leads with NULL estimate_number                                 
SELECT
  COUNT(*) as null_estimate_number_count
FROM
  leads
WHERE
  estimate_number IS NULL;

-- Count leads with empty string estimate_number                         
SELECT
  COUNT(*) as empty_estimate_number_count
FROM
  leads
WHERE
  estimate_number = ''
  OR estimate_number = '0';

-- Show records with NULL or empty estimate numbers (for inspection)     
SELECT
  id,
  estimate_number,
  first_name,
  family_name,
  created_at
FROM
  leads
WHERE
  estimate_number IS NULL
  OR estimate_number = ''
  OR estimate_number = '0'
ORDER BY
  created_at DESC
LIMIT
  20;

-- Overall summary                                                       
SELECT
  COUNT(*) as total_leads,
  COUNT(id) as leads_with_id,
  COUNT(*) - COUNT(id) as leads_with_null_id,
  COUNT(estimate_number) as leads_with_estimate_number,
  COUNT(*) - COUNT(estimate_number) as leads_with_null_estimate_number
FROM
  leads;
-- =====================================================
-- Complete Stage System Migration SQL Script
-- =====================================================
-- This script handles the complete migration from string-based stages
-- to numeric stages with 10-unit increments.
--
-- IMPORTANT: Run this in phpMyAdmin or your preferred MySQL client
-- 
-- Key Changes:
-- 1. Convert string stages to temporary numeric values
-- 2. Change column type from varchar(20) to int(11)
-- 3. Apply new 10-unit increment numbering system
-- 4. Move Closed Won/Lost before Contracting
-- =====================================================
-- Show current stage distribution before migration
SELECT
  'BEFORE MIGRATION - Current Stage Distribution:' as info;

SELECT
  stage,
  COUNT(*) as lead_count
FROM
  leads
GROUP BY
  stage
ORDER BY
  stage;

-- Start transaction for safety
START TRANSACTION;

-- =====================================================
-- STEP 1: Convert string stages to temporary numeric values
-- =====================================================
-- Convert common string stages to temporary numbers (1-15 range)
-- Main leads table
UPDATE leads
SET
  stage = '1'
WHERE
  stage IN ('Lead', 'lead', 'LEAD');

UPDATE leads
SET
  stage = '2'
WHERE
  stage IN (
    'Pre-Qualification',
    'pre-qualification',
    'PRE-QUALIFICATION'
  );

UPDATE leads
SET
  stage = '3'
WHERE
  stage IN ('Qualified', 'qualified', 'QUALIFIED');

UPDATE leads
SET
  stage = '4'
WHERE
  stage IN ('Referral', 'referral', 'REFERRAL');

UPDATE leads
SET
  stage = '5'
WHERE
  stage IN ('Prospect', 'prospect', 'PROSPECT');

UPDATE leads
SET
  stage = '6'
WHERE
  stage IN ('Prelim Design', 'prelim design', 'PRELIM DESIGN');

UPDATE leads
SET
  stage = '7'
WHERE
  stage IN (
    'Manufacturing Estimate',
    'manufacturing estimate',
    'MANUFACTURING ESTIMATE'
  );

UPDATE leads
SET
  stage = '8'
WHERE
  stage IN (
    'Contractor Estimate',
    'contractor estimate',
    'CONTRACTOR ESTIMATE'
  );

UPDATE leads
SET
  stage = '9'
WHERE
  stage IN (
    'Completed Estimate',
    'completed estimate',
    'COMPLETED ESTIMATE'
  );

UPDATE leads
SET
  stage = '10'
WHERE
  stage IN (
    'Prospect Response',
    'prospect response',
    'PROSPECT RESPONSE'
  );

UPDATE leads
SET
  stage = '11'
WHERE
  stage IN (
    'Closing Conference',
    'closing conference',
    'CLOSING CONFERENCE'
  );

UPDATE leads
SET
  stage = '12'
WHERE
  stage IN (
    'Potential Client Response',
    'potential client response',
    'POTENTIAL CLIENT RESPONSE'
  );

UPDATE leads
SET
  stage = '13'
WHERE
  stage IN (
    'Contracting',
    'contracting',
    'CONTRACTING',
    'To Contracting',
    'to contracting',
    'TO CONTRACTING'
  );

UPDATE leads
SET
  stage = '14'
WHERE
  stage IN (
    'Closed Won',
    'closed won',
    'CLOSED WON',
    'Won',
    'won',
    'WON'
  );

UPDATE leads
SET
  stage = '15'
WHERE
  stage IN (
    'Closed Lost',
    'closed lost',
    'CLOSED LOST',
    'Lost',
    'lost',
    'LOST'
  );

-- Handle any remaining non-numeric stages by setting them to '1' (Lead)
UPDATE leads
SET
  stage = '1'
WHERE
  stage NOT REGEXP '^[0-9]+$';

-- leads_extras table (same conversions)
UPDATE leads_extras
SET
  stage = '1'
WHERE
  stage IN ('Lead', 'lead', 'LEAD');

UPDATE leads_extras
SET
  stage = '2'
WHERE
  stage IN (
    'Pre-Qualification',
    'pre-qualification',
    'PRE-QUALIFICATION'
  );

UPDATE leads_extras
SET
  stage = '3'
WHERE
  stage IN ('Qualified', 'qualified', 'QUALIFIED');

UPDATE leads_extras
SET
  stage = '4'
WHERE
  stage IN ('Referral', 'referral', 'REFERRAL');

UPDATE leads_extras
SET
  stage = '5'
WHERE
  stage IN ('Prospect', 'prospect', 'PROSPECT');

UPDATE leads_extras
SET
  stage = '6'
WHERE
  stage IN ('Prelim Design', 'prelim design', 'PRELIM DESIGN');

UPDATE leads_extras
SET
  stage = '7'
WHERE
  stage IN (
    'Manufacturing Estimate',
    'manufacturing estimate',
    'MANUFACTURING ESTIMATE'
  );

UPDATE leads_extras
SET
  stage = '8'
WHERE
  stage IN (
    'Contractor Estimate',
    'contractor estimate',
    'CONTRACTOR ESTIMATE'
  );

UPDATE leads_extras
SET
  stage = '9'
WHERE
  stage IN (
    'Completed Estimate',
    'completed estimate',
    'COMPLETED ESTIMATE'
  );

UPDATE leads_extras
SET
  stage = '10'
WHERE
  stage IN (
    'Prospect Response',
    'prospect response',
    'PROSPECT RESPONSE'
  );

UPDATE leads_extras
SET
  stage = '11'
WHERE
  stage IN (
    'Closing Conference',
    'closing conference',
    'CLOSING CONFERENCE'
  );

UPDATE leads_extras
SET
  stage = '12'
WHERE
  stage IN (
    'Potential Client Response',
    'potential client response',
    'POTENTIAL CLIENT RESPONSE'
  );

UPDATE leads_extras
SET
  stage = '13'
WHERE
  stage IN (
    'Contracting',
    'contracting',
    'CONTRACTING',
    'To Contracting',
    'to contracting',
    'TO CONTRACTING'
  );

UPDATE leads_extras
SET
  stage = '14'
WHERE
  stage IN (
    'Closed Won',
    'closed won',
    'CLOSED WON',
    'Won',
    'won',
    'WON'
  );

UPDATE leads_extras
SET
  stage = '15'
WHERE
  stage IN (
    'Closed Lost',
    'closed lost',
    'CLOSED LOST',
    'Lost',
    'lost',
    'LOST'
  );

-- Handle any remaining non-numeric stages in leads_extras
UPDATE leads_extras
SET
  stage = '1'
WHERE
  stage NOT REGEXP '^[0-9]+$';

-- =====================================================
-- STEP 2: Change column type from varchar to int
-- =====================================================
-- Change leads table column type
ALTER TABLE leads MODIFY COLUMN stage int (11) DEFAULT 1;

-- Change leads_extras table column type  
ALTER TABLE leads_extras MODIFY COLUMN stage int (11) DEFAULT 1;

-- =====================================================
-- STEP 3: Apply new 10-unit increment numbering system
-- =====================================================
-- Note: We update in reverse order to avoid conflicts
-- since we're moving to higher numbers
-- Stage 15 → 140 (Closed Lost)
UPDATE leads
SET
  stage = 140
WHERE
  stage = 15;

UPDATE leads_extras
SET
  stage = 140
WHERE
  stage = 15;

-- Stage 14 → 130 (Closed Won) 
UPDATE leads
SET
  stage = 130
WHERE
  stage = 14;

UPDATE leads_extras
SET
  stage = 130
WHERE
  stage = 14;

-- Stage 13 → 150 (Contracting) - moved after Won/Lost
UPDATE leads
SET
  stage = 150
WHERE
  stage = 13;

UPDATE leads_extras
SET
  stage = 150
WHERE
  stage = 13;

-- Stage 12 → 120 (Potential Client Response)
UPDATE leads
SET
  stage = 120
WHERE
  stage = 12;

UPDATE leads_extras
SET
  stage = 120
WHERE
  stage = 12;

-- Stage 11 → 110 (Closing Conference)
UPDATE leads
SET
  stage = 110
WHERE
  stage = 11;

UPDATE leads_extras
SET
  stage = 110
WHERE
  stage = 11;

-- Stage 10 → 100 (Prospect Response)
UPDATE leads
SET
  stage = 100
WHERE
  stage = 10;

UPDATE leads_extras
SET
  stage = 100
WHERE
  stage = 10;

-- Stage 9 → 90 (Completed Estimate)
UPDATE leads
SET
  stage = 90
WHERE
  stage = 9;

UPDATE leads_extras
SET
  stage = 90
WHERE
  stage = 9;

-- Stage 8 → 80 (Contractor Estimate)
UPDATE leads
SET
  stage = 80
WHERE
  stage = 8;

UPDATE leads_extras
SET
  stage = 80
WHERE
  stage = 8;

-- Stage 7 → 70 (Manufacturing Estimate)
UPDATE leads
SET
  stage = 70
WHERE
  stage = 7;

UPDATE leads_extras
SET
  stage = 70
WHERE
  stage = 7;

-- Stage 6 → 60 (Prelim Design)
UPDATE leads
SET
  stage = 60
WHERE
  stage = 6;

UPDATE leads_extras
SET
  stage = 60
WHERE
  stage = 6;

-- Stage 5 → 50 (Prospect)
UPDATE leads
SET
  stage = 50
WHERE
  stage = 5;

UPDATE leads_extras
SET
  stage = 50
WHERE
  stage = 5;

-- Stage 4 → 40 (Referral)
UPDATE leads
SET
  stage = 40
WHERE
  stage = 4;

UPDATE leads_extras
SET
  stage = 40
WHERE
  stage = 4;

-- Stage 3 → 30 (Qualified)
UPDATE leads
SET
  stage = 30
WHERE
  stage = 3;

UPDATE leads_extras
SET
  stage = 30
WHERE
  stage = 3;

-- Stage 2 → 20 (Pre-Qualification)
UPDATE leads
SET
  stage = 20
WHERE
  stage = 2;

UPDATE leads_extras
SET
  stage = 20
WHERE
  stage = 2;

-- Stage 1 → 10 (Lead)
UPDATE leads
SET
  stage = 10
WHERE
  stage = 1;

UPDATE leads_extras
SET
  stage = 10
WHERE
  stage = 1;

-- =====================================================
-- VERIFICATION AND RESULTS
-- =====================================================
-- Show results after migration
SELECT
  'AFTER MIGRATION - New Stage Distribution:' as info;

SELECT
  stage,
  COUNT(*) as lead_count,
  CASE stage
    WHEN 10 THEN 'Lead'
    WHEN 20 THEN 'Pre-Qualification'
    WHEN 30 THEN 'Qualified'
    WHEN 40 THEN 'Referral'
    WHEN 50 THEN 'Prospect'
    WHEN 60 THEN 'Prelim Design'
    WHEN 70 THEN 'Manufacturing Estimate'
    WHEN 80 THEN 'Contractor Estimate'
    WHEN 90 THEN 'Completed Estimate'
    WHEN 100 THEN 'Prospect Response'
    WHEN 110 THEN 'Closing Conference'
    WHEN 120 THEN 'Potential Client Response'
    WHEN 130 THEN 'Closed Won'
    WHEN 140 THEN 'Closed Lost'
    WHEN 150 THEN 'Contracting'
    ELSE CONCAT ('Unknown: ', stage)
  END as stage_name
FROM
  leads
GROUP BY
  stage
ORDER BY
  stage;

-- Show column types after migration
SELECT
  'COLUMN TYPES AFTER MIGRATION:' as info;

DESCRIBE leads;

-- Show summary of changes
SELECT
  'MIGRATION SUMMARY:' as info;

SELECT
  'Total leads migrated:' as description,
  COUNT(*) as count
FROM
  leads;

SELECT
  'KEY IMPROVEMENTS:' as info;

SELECT
  '✅ Converted from varchar(20) to int(11) column type' as improvement
UNION ALL
SELECT
  '✅ 10-unit increments provide room for expansion'
UNION ALL
SELECT
  '✅ Closed Won (130) and Closed Lost (140) moved before Contracting (150)'
UNION ALL
SELECT
  '✅ Lead dropdown will show: 10,20,30,40,50,140'
UNION ALL
SELECT
  '✅ Trigger stages identified: 40 (Referral), 50 (Prospect), 140 (Closed Lost)'
UNION ALL
SELECT
  '✅ Module filtering updated for proper lead distribution';

-- Commit the transaction
COMMIT;

-- =====================================================
-- VERIFICATION QUERIES (Optional - uncomment to run)
-- =====================================================
-- Run these queries after migration to verify everything worked:
-- 1. Check that no old stage numbers remain (1-15 range)
-- SELECT 'Old stage numbers check (should be empty):' as info;
-- SELECT stage, COUNT(*) FROM leads WHERE stage IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15) GROUP BY stage;
-- 2. Verify new stage distribution
-- SELECT 'New stage distribution:' as info;
-- SELECT stage, COUNT(*) as count FROM leads GROUP BY stage ORDER BY stage;
-- 3. Check for any unexpected stage numbers
-- SELECT 'Unexpected stages (should be empty):' as info;
-- SELECT stage, COUNT(*) FROM leads WHERE stage NOT IN (10,20,30,40,50,60,70,80,90,100,110,120,130,140,150) GROUP BY stage;
-- 4. Verify column types
-- SELECT 'Column type verification:' as info;
-- DESCRIBE leads;
-- DESCRIBE leads_extras;
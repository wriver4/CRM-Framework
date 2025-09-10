-- Migration: Migrate Existing Lead Data to Stage-Specific Tables
-- Date: 2024-09-09
-- Description: Move existing lead data to appropriate stage-specific tables
-- =====================================================
-- MIGRATE REFERRALS DATA
-- =====================================================
INSERT INTO
  `referrals` (
    `lead_id`,
    `referral_source_type`,
    `referral_notes`,
    `referral_status`,
    `created_at`,
    `updated_at`
  )
SELECT
  id as lead_id,
  'other' as referral_source_type,
  CONCAT (
    'Migrated from leads table. Hear about: ',
    COALESCE(hear_about, 'Unknown')
  ) as referral_notes,
  'pending' as referral_status,
  created_at,
  updated_at
FROM
  leads
WHERE
  stage = '4' -- Referral stage
  AND id NOT IN (
    SELECT
      lead_id
    FROM
      referrals
  );

-- Avoid duplicates
-- =====================================================
-- MIGRATE PROSPECTS DATA  
-- =====================================================
INSERT INTO
  `prospects` (
    `lead_id`,
    `building_type`,
    `special_requirements`,
    `estimated_cost_low`,
    `estimated_cost_high`,
    `engineering_notes`,
    `proposal_status`,
    `prospect_temperature`,
    `created_at`,
    `updated_at`
  )
SELECT
  id as lead_id,
  CASE
    WHEN structure_type = 1 THEN 'Residential'
    WHEN structure_type = 2 THEN 'Commercial'
    WHEN structure_type = 3 THEN 'Industrial'
    ELSE 'Other'
  END as building_type,
  COALESCE(structure_additional, structure_other) as special_requirements,
  COALESCE(sales_system_cost_low, eng_system_cost_low) as estimated_cost_low,
  COALESCE(sales_system_cost_high, eng_system_cost_high) as estimated_cost_high,
  'Migrated from leads table' as engineering_notes,
  'draft' as proposal_status,
  'warm' as prospect_temperature,
  created_at,
  updated_at
FROM
  leads
WHERE
  stage IN ('5', '6', '7', '8', '9', '10', '11', '12') -- Prospect stages
  AND id NOT IN (
    SELECT
      lead_id
    FROM
      prospects
  );

-- Avoid duplicates
-- =====================================================
-- MIGRATE CONTRACTING DATA
-- =====================================================
INSERT INTO
  `contracting` (
    `lead_id`,
    `contract_type`,
    `contract_value`,
    `project_status`,
    `deliverables`,
    `internal_notes`,
    `created_at`,
    `updated_at`
  )
SELECT
  id as lead_id,
  CASE
    WHEN structure_type = 2 THEN 'commercial'
    WHEN structure_type = 3 THEN 'commercial'
    ELSE 'standard'
  END as contract_type,
  COALESCE(sales_system_cost_high, eng_system_cost_high) as contract_value,
  CASE
    WHEN stage = '13' THEN 'pending'
    WHEN stage = '14' THEN 'completed'
    ELSE 'pending'
  END as project_status,
  JSON_ARRAY (
    'System Installation',
    'Documentation',
    'Training'
  ) as deliverables,
  'Migrated from leads table' as internal_notes,
  created_at,
  updated_at
FROM
  leads
WHERE
  stage IN ('13', '14') -- Contracting stages
  AND id NOT IN (
    SELECT
      lead_id
    FROM
      contracting
  );

-- Avoid duplicates
-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================
-- Check migration results
SELECT
  'Referrals' as table_name,
  COUNT(*) as migrated_count
FROM
  referrals
UNION ALL
SELECT
  'Prospects' as table_name,
  COUNT(*) as migrated_count
FROM
  prospects
UNION ALL
SELECT
  'Contracting' as table_name,
  COUNT(*) as migrated_count
FROM
  contracting;

-- Check original data counts by stage
SELECT
  stage,
  COUNT(*) as lead_count
FROM
  leads
GROUP BY
  stage
ORDER BY
  stage;
-- Migration: Move existing data to bridge tables
-- Date: 2024-09-09
-- Description: Migrate existing lead data to the new bridge table structure
-- =====================================================
-- MIGRATE STRUCTURE INFO
-- =====================================================
INSERT INTO
  `lead_structure_info` (
    `lead_id`,
    `structure_type`,
    `structure_description`,
    `structure_other`,
    `structure_additional`,
    `created_at`,
    `updated_at`
  )
SELECT
  id as lead_id,
  structure_type,
  structure_description,
  structure_other,
  structure_additional,
  created_at,
  updated_at
FROM
  leads
WHERE
  (
    structure_type IS NOT NULL
    OR structure_description IS NOT NULL
    OR structure_other IS NOT NULL
    OR structure_additional IS NOT NULL
  )
  AND id NOT IN (
    SELECT
      lead_id
    FROM
      lead_structure_info
  );

-- =====================================================
-- MIGRATE DOCUMENTS (Pictures and Plans)
-- =====================================================
-- Migrate picture_upload_link
INSERT INTO
  `lead_documents` (
    `lead_id`,
    `document_type`,
    `document_category`,
    `file_name`,
    `file_path`,
    `description`,
    `upload_date`,
    `is_active`
  )
SELECT
  id as lead_id,
  'picture' as document_type,
  'initial_submission' as document_category,
  CONCAT ('picture_upload_', id) as file_name,
  picture_upload_link as file_path,
  'Migrated from picture_upload_link' as description,
  created_at as upload_date,
  1 as is_active
FROM
  leads
WHERE
  picture_upload_link IS NOT NULL
  AND picture_upload_link != ''
  AND id NOT IN (
    SELECT
      lead_id
    FROM
      lead_documents
    WHERE
      document_type = 'picture'
      AND document_category = 'initial_submission'
  );

-- Migrate plans_upload_link
INSERT INTO
  `lead_documents` (
    `lead_id`,
    `document_type`,
    `document_category`,
    `file_name`,
    `file_path`,
    `description`,
    `upload_date`,
    `is_active`
  )
SELECT
  id as lead_id,
  'plan' as document_type,
  'initial_submission' as document_category,
  CONCAT ('plans_upload_', id) as file_name,
  plans_upload_link as file_path,
  'Migrated from plans_upload_link' as description,
  created_at as upload_date,
  1 as is_active
FROM
  leads
WHERE
  plans_upload_link IS NOT NULL
  AND plans_upload_link != ''
  AND id NOT IN (
    SELECT
      lead_id
    FROM
      lead_documents
    WHERE
      document_type = 'plan'
      AND document_category = 'initial_submission'
  );

-- Migrate individual picture files (picture_submitted_1, 2, 3)
INSERT INTO
  `lead_documents` (
    `lead_id`,
    `document_type`,
    `document_category`,
    `file_name`,
    `file_path`,
    `description`,
    `upload_date`,
    `is_active`,
    `sort_order`
  )
SELECT
  id as lead_id,
  'picture' as document_type,
  'submitted_files' as document_category,
  COALESCE(picture_submitted_1, CONCAT ('picture_1_', id)) as file_name,
  picture_submitted_1 as file_path,
  'Migrated from picture_submitted_1' as description,
  created_at as upload_date,
  1 as is_active,
  1 as sort_order
FROM
  leads
WHERE
  picture_submitted_1 IS NOT NULL
  AND picture_submitted_1 != ''
UNION ALL
SELECT
  id as lead_id,
  'picture' as document_type,
  'submitted_files' as document_category,
  COALESCE(picture_submitted_2, CONCAT ('picture_2_', id)) as file_name,
  picture_submitted_2 as file_path,
  'Migrated from picture_submitted_2' as description,
  created_at as upload_date,
  1 as is_active,
  2 as sort_order
FROM
  leads
WHERE
  picture_submitted_2 IS NOT NULL
  AND picture_submitted_2 != ''
UNION ALL
SELECT
  id as lead_id,
  'picture' as document_type,
  'submitted_files' as document_category,
  COALESCE(picture_submitted_3, CONCAT ('picture_3_', id)) as file_name,
  picture_submitted_3 as file_path,
  'Migrated from picture_submitted_3' as description,
  created_at as upload_date,
  1 as is_active,
  3 as sort_order
FROM
  leads
WHERE
  picture_submitted_3 IS NOT NULL
  AND picture_submitted_3 != '';

-- Migrate individual plan files (plans_submitted_1, 2, 3)
INSERT INTO
  `lead_documents` (
    `lead_id`,
    `document_type`,
    `document_category`,
    `file_name`,
    `file_path`,
    `description`,
    `upload_date`,
    `is_active`,
    `sort_order`
  )
SELECT
  id as lead_id,
  'plan' as document_type,
  'submitted_files' as document_category,
  COALESCE(plans_submitted_1, CONCAT ('plan_1_', id)) as file_name,
  plans_submitted_1 as file_path,
  'Migrated from plans_submitted_1' as description,
  created_at as upload_date,
  1 as is_active,
  1 as sort_order
FROM
  leads
WHERE
  plans_submitted_1 IS NOT NULL
  AND plans_submitted_1 != ''
UNION ALL
SELECT
  id as lead_id,
  'plan' as document_type,
  'submitted_files' as document_category,
  COALESCE(plans_submitted_2, CONCAT ('plan_2_', id)) as file_name,
  plans_submitted_2 as file_path,
  'Migrated from plans_submitted_2' as description,
  created_at as upload_date,
  1 as is_active,
  2 as sort_order
FROM
  leads
WHERE
  plans_submitted_2 IS NOT NULL
  AND plans_submitted_2 != ''
UNION ALL
SELECT
  id as lead_id,
  'plan' as document_type,
  'submitted_files' as document_category,
  COALESCE(plans_submitted_3, CONCAT ('plan_3_', id)) as file_name,
  plans_submitted_3 as file_path,
  'Migrated from plans_submitted_3' as description,
  created_at as upload_date,
  1 as is_active,
  3 as sort_order
FROM
  leads
WHERE
  plans_submitted_3 IS NOT NULL
  AND plans_submitted_3 != '';

-- =====================================================
-- MIGRATE REFERRAL DATA
-- =====================================================
INSERT INTO
  `lead_referrals` (
    `lead_id`,
    `referral_source_type`,
    `referral_source_name`,
    `referral_notes`,
    `referral_status`,
    `created_at`,
    `updated_at`
  )
SELECT
  id as lead_id,
  CASE
    WHEN hear_about = 'referral' THEN 'customer'
    WHEN hear_about = 'partner' THEN 'partner'
    WHEN hear_about = 'online' THEN 'online'
    ELSE 'other'
  END as referral_source_type,
  COALESCE(hear_about_other, hear_about) as referral_source_name,
  CONCAT (
    'Migrated from leads table. Original hear_about: ',
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
      lead_referrals
  );

-- =====================================================
-- MIGRATE PROSPECT DATA
-- =====================================================
INSERT INTO
  `lead_prospects` (
    `lead_id`,
    `estimated_cost_low`,
    `estimated_cost_high`,
    `prospect_notes`,
    `prospect_temperature`,
    `proposal_status`,
    `created_at`,
    `updated_at`
  )
SELECT
  id as lead_id,
  COALESCE(sales_system_cost_low, eng_system_cost_low) as estimated_cost_low,
  COALESCE(sales_system_cost_high, eng_system_cost_high) as estimated_cost_high,
  'Migrated from leads table' as prospect_notes,
  CASE
    WHEN (
      sales_system_cost_low IS NOT NULL
      OR eng_system_cost_low IS NOT NULL
    ) THEN 'warm'
    ELSE 'cold'
  END as prospect_temperature,
  'draft' as proposal_status,
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
      lead_prospects
  );

-- =====================================================
-- MIGRATE CONTRACTING DATA
-- =====================================================
INSERT INTO
  `lead_contracting` (
    `lead_id`,
    `contract_type`,
    `contract_value`,
    `project_status`,
    `project_notes`,
    `deliverables`,
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
  'Migrated from leads table' as project_notes,
  JSON_ARRAY (
    'System Installation',
    'Documentation',
    'Training'
  ) as deliverables,
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
      lead_contracting
  );

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================
-- Check migration results
SELECT
  'Structure Info' as table_name,
  COUNT(*) as migrated_count
FROM
  lead_structure_info
UNION ALL
SELECT
  'Documents' as table_name,
  COUNT(*) as migrated_count
FROM
  lead_documents
UNION ALL
SELECT
  'Referrals' as table_name,
  COUNT(*) as migrated_count
FROM
  lead_referrals
UNION ALL
SELECT
  'Prospects' as table_name,
  COUNT(*) as migrated_count
FROM
  lead_prospects
UNION ALL
SELECT
  'Contracting' as table_name,
  COUNT(*) as migrated_count
FROM
  lead_contracting;

-- Check document types
SELECT
  document_type,
  document_category,
  COUNT(*) as count
FROM
  lead_documents
GROUP BY
  document_type,
  document_category;

-- Test the views
SELECT
  COUNT(*) as complete_leads_count
FROM
  v_leads_complete;

SELECT
  COUNT(*) as referrals_count
FROM
  v_referrals_complete;

SELECT
  COUNT(*) as prospects_count
FROM
  v_prospects_complete;

SELECT
  COUNT(*) as contracting_count
FROM
  v_contracting_complete;
-- Migration: Bridge Table Approach for Stage-Specific Data
-- Date: 2024-09-09
-- Description: Create bridge tables that extend lead data for each stage
-- This approach keeps the core leads table intact while adding stage-specific functionality
-- =====================================================
-- LEAD DOCUMENTS BRIDGE TABLE
-- =====================================================
CREATE TABLE
  IF NOT EXISTS `lead_documents` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `lead_id` int (11) NOT NULL,
    `document_type` enum (
      'picture',
      'plan',
      'contract',
      'proposal',
      'survey',
      'other'
    ) NOT NULL,
    `document_category` varchar(100) DEFAULT NULL COMMENT 'e.g., initial_submission, site_survey, final_plans',
    `file_name` varchar(255) NOT NULL,
    `file_path` varchar(500) NOT NULL,
    `file_size` int (11) DEFAULT NULL,
    `mime_type` varchar(100) DEFAULT NULL,
    `upload_date` timestamp DEFAULT current_timestamp(),
    `uploaded_by` int (11) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `is_active` tinyint (1) DEFAULT 1,
    `sort_order` int (3) DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_lead_id` (`lead_id`),
    KEY `idx_document_type` (`document_type`),
    KEY `idx_category` (`document_category`),
    FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- =====================================================
-- LEAD STRUCTURE INFO BRIDGE TABLE
-- =====================================================
CREATE TABLE
  IF NOT EXISTS `lead_structure_info` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `lead_id` int (11) NOT NULL,
    `structure_type` tinyint (4) DEFAULT 1,
    `structure_description` varchar(100) DEFAULT NULL,
    `structure_other` varchar(255) DEFAULT NULL,
    `structure_additional` text DEFAULT NULL,
    `building_age` int (4) DEFAULT NULL,
    `building_stories` int (2) DEFAULT NULL,
    `roof_type` varchar(100) DEFAULT NULL,
    `roof_condition` enum ('excellent', 'good', 'fair', 'poor') DEFAULT NULL,
    `roof_age` int (3) DEFAULT NULL,
    `electrical_panel_type` varchar(100) DEFAULT NULL,
    `electrical_capacity` varchar(50) DEFAULT NULL,
    `hvac_type` varchar(100) DEFAULT NULL,
    `special_requirements` text DEFAULT NULL,
    `access_restrictions` text DEFAULT NULL,
    `hoa_restrictions` text DEFAULT NULL,
    `permit_requirements` text DEFAULT NULL,
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_at` timestamp DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_lead_structure` (`lead_id`),
    FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- =====================================================
-- REFERRAL BRIDGE TABLE
-- =====================================================
CREATE TABLE
  IF NOT EXISTS `lead_referrals` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `lead_id` int (11) NOT NULL,
    `referral_source_type` enum (
      'partner',
      'customer',
      'employee',
      'online',
      'other'
    ) DEFAULT 'partner',
    `referral_source_name` varchar(255) DEFAULT NULL,
    `referral_contact_id` int (11) DEFAULT NULL COMMENT 'Links to contacts table',
    `referral_code` varchar(50) DEFAULT NULL,
    `commission_rate` decimal(5, 2) DEFAULT NULL,
    `commission_amount` decimal(10, 2) DEFAULT NULL,
    `commission_type` enum ('percentage', 'fixed', 'tiered') DEFAULT 'percentage',
    `commission_paid` tinyint (1) DEFAULT 0,
    `commission_paid_date` date DEFAULT NULL,
    `agreement_type` varchar(100) DEFAULT NULL,
    `agreement_signed_date` date DEFAULT NULL,
    `referral_notes` text DEFAULT NULL,
    `follow_up_required` tinyint (1) DEFAULT 0,
    `follow_up_date` date DEFAULT NULL,
    `referral_status` enum ('pending', 'qualified', 'converted', 'declined') DEFAULT 'pending',
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_at` timestamp DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_lead_referral` (`lead_id`),
    KEY `idx_referral_contact` (`referral_contact_id`),
    KEY `idx_referral_status` (`referral_status`),
    FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`referral_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- =====================================================
-- PROSPECT BRIDGE TABLE
-- =====================================================
CREATE TABLE
  IF NOT EXISTS `lead_prospects` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `lead_id` int (11) NOT NULL,
    -- Site survey data
    `site_survey_completed` tinyint (1) DEFAULT 0,
    `site_survey_date` date DEFAULT NULL,
    `site_survey_by` int (11) DEFAULT NULL COMMENT 'User ID who conducted survey',
    `site_survey_notes` text DEFAULT NULL,
    -- Engineering data
    `engineering_review_required` tinyint (1) DEFAULT 0,
    `engineering_review_completed` tinyint (1) DEFAULT 0,
    `engineering_review_date` date DEFAULT NULL,
    `engineering_review_by` int (11) DEFAULT NULL,
    `engineering_notes` text DEFAULT NULL,
    -- System specifications
    `estimated_system_size` decimal(8, 2) DEFAULT NULL COMMENT 'kW or sq ft',
    `system_type` varchar(100) DEFAULT NULL,
    `equipment_specifications` text DEFAULT NULL,
    -- Pricing
    `estimated_cost_low` decimal(10, 2) DEFAULT NULL,
    `estimated_cost_high` decimal(10, 2) DEFAULT NULL,
    `final_quoted_price` decimal(10, 2) DEFAULT NULL,
    `pricing_notes` text DEFAULT NULL,
    -- Proposal tracking
    `proposal_version` int (3) DEFAULT 1,
    `proposal_sent_date` date DEFAULT NULL,
    `proposal_valid_until` date DEFAULT NULL,
    `proposal_status` enum (
      'draft',
      'sent',
      'viewed',
      'accepted',
      'rejected',
      'expired'
    ) DEFAULT 'draft',
    -- Follow-up
    `last_contact_date` date DEFAULT NULL,
    `next_follow_up_date` date DEFAULT NULL,
    `follow_up_method` enum ('email', 'phone', 'meeting', 'site_visit') DEFAULT NULL,
    `prospect_temperature` enum ('hot', 'warm', 'cold') DEFAULT 'warm',
    `prospect_notes` text DEFAULT NULL,
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_at` timestamp DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_lead_prospect` (`lead_id`),
    KEY `idx_survey_date` (`site_survey_date`),
    KEY `idx_proposal_status` (`proposal_status`),
    KEY `idx_follow_up_date` (`next_follow_up_date`),
    KEY `idx_temperature` (`prospect_temperature`),
    FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- =====================================================
-- CONTRACTING BRIDGE TABLE
-- =====================================================
CREATE TABLE
  IF NOT EXISTS `lead_contracting` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `lead_id` int (11) NOT NULL,
    -- Contract details
    `contract_number` varchar(50) DEFAULT NULL,
    `contract_type` enum ('standard', 'custom', 'government', 'commercial') DEFAULT 'standard',
    `contract_value` decimal(12, 2) DEFAULT NULL,
    `contract_signed_date` date DEFAULT NULL,
    `contract_start_date` date DEFAULT NULL,
    `contract_completion_date` date DEFAULT NULL,
    -- Payment information
    `payment_terms` varchar(200) DEFAULT NULL,
    `payment_schedule` enum (
      'upfront',
      '50_50',
      'milestone',
      'net_30',
      'custom'
    ) DEFAULT 'milestone',
    `deposit_amount` decimal(10, 2) DEFAULT NULL,
    `deposit_received` tinyint (1) DEFAULT 0,
    `deposit_date` date DEFAULT NULL,
    -- Project management
    `project_manager_id` int (11) DEFAULT NULL,
    `lead_technician_id` int (11) DEFAULT NULL,
    `project_start_date` date DEFAULT NULL,
    `estimated_completion_date` date DEFAULT NULL,
    `actual_completion_date` date DEFAULT NULL,
    `project_status` enum (
      'pending',
      'in_progress',
      'on_hold',
      'completed',
      'cancelled'
    ) DEFAULT 'pending',
    `completion_percentage` int (3) DEFAULT 0,
    -- Deliverables (JSON format)
    `deliverables` text DEFAULT NULL COMMENT 'JSON array of deliverables',
    `milestones` text DEFAULT NULL COMMENT 'JSON array of milestones',
    `current_milestone` varchar(200) DEFAULT NULL,
    -- Legal and compliance
    `permits_required` text DEFAULT NULL,
    `permits_obtained` text DEFAULT NULL,
    `insurance_verified` tinyint (1) DEFAULT 0,
    `warranty_terms` text DEFAULT NULL,
    `warranty_start_date` date DEFAULT NULL,
    `warranty_end_date` date DEFAULT NULL,
    -- Quality and completion
    `quality_check_completed` tinyint (1) DEFAULT 0,
    `quality_check_date` date DEFAULT NULL,
    `client_satisfaction_score` int (2) DEFAULT NULL COMMENT '1-10 scale',
    `project_notes` text DEFAULT NULL,
    `change_orders` text DEFAULT NULL COMMENT 'JSON array of change orders',
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_at` timestamp DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_lead_contract` (`lead_id`),
    KEY `idx_contract_number` (`contract_number`),
    KEY `idx_project_status` (`project_status`),
    KEY `idx_project_manager` (`project_manager_id`),
    KEY `idx_completion_date` (`estimated_completion_date`),
    FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- =====================================================
-- ENHANCED VIEWS FOR EASY UI EXECUTION
-- =====================================================
-- Complete lead view with all related data
CREATE
OR REPLACE VIEW `v_leads_complete` AS
SELECT
  l.*,
  -- Contact info (primary contact)
  c.first_name as contact_first_name,
  c.family_name as contact_family_name,
  c.cell_phone as contact_phone,
  c.personal_email as contact_email,
  c.timezone as contact_timezone,
  -- Structure info
  s.structure_type as bridge_structure_type,
  s.structure_description as bridge_structure_description,
  s.building_age,
  s.roof_type,
  s.roof_condition,
  s.special_requirements,
  -- Stage-specific data flags
  CASE
    WHEN r.id IS NOT NULL THEN 1
    ELSE 0
  END as has_referral_data,
  CASE
    WHEN p.id IS NOT NULL THEN 1
    ELSE 0
  END as has_prospect_data,
  CASE
    WHEN ct.id IS NOT NULL THEN 1
    ELSE 0
  END as has_contract_data,
  -- Document counts
  (
    SELECT
      COUNT(*)
    FROM
      lead_documents
    WHERE
      lead_id = l.id
      AND document_type = 'picture'
      AND is_active = 1
  ) as picture_count,
  (
    SELECT
      COUNT(*)
    FROM
      lead_documents
    WHERE
      lead_id = l.id
      AND document_type = 'plan'
      AND is_active = 1
  ) as plan_count
FROM
  leads l
  LEFT JOIN contacts c ON l.id = c.lead_id
  AND c.call_order = 1
  LEFT JOIN lead_structure_info s ON l.id = s.lead_id
  LEFT JOIN lead_referrals r ON l.id = r.lead_id
  LEFT JOIN lead_prospects p ON l.id = p.lead_id
  LEFT JOIN lead_contracting ct ON l.id = ct.lead_id;

-- Referral-specific view
CREATE
OR REPLACE VIEW `v_referrals_complete` AS
SELECT
  l.*,
  -- Referral data (aliased to avoid duplicate column names)
  r.id as referral_id,
  r.referral_source_type,
  r.referral_source_name,
  r.referral_contact_id,
  r.referral_code,
  r.commission_rate,
  r.commission_amount,
  r.commission_type,
  r.commission_paid,
  r.commission_paid_date,
  r.agreement_type,
  r.agreement_signed_date,
  r.referral_notes,
  r.follow_up_required,
  r.follow_up_date,
  r.referral_status,
  r.updated_at as referral_updated_at,
  r.created_at as referral_created_at,
  -- Contact info
  c.first_name as contact_first_name,
  c.family_name as contact_family_name,
  rc.first_name as referral_contact_first_name,
  rc.family_name as referral_contact_family_name,
  rc.personal_email as referral_contact_email
FROM
  leads l
  JOIN lead_referrals r ON l.id = r.lead_id
  LEFT JOIN contacts c ON l.id = c.lead_id
  AND c.call_order = 1
  LEFT JOIN contacts rc ON r.referral_contact_id = rc.id
WHERE
  l.stage = '4';

-- Prospect-specific view
CREATE
OR REPLACE VIEW `v_prospects_complete` AS
SELECT
  l.*,
  -- Prospect data (aliased to avoid duplicate column names)
  p.id as prospect_id,
  p.site_survey_completed,
  p.site_survey_date,
  p.site_survey_by,
  p.site_survey_notes,
  p.engineering_review_required,
  p.engineering_review_completed,
  p.engineering_review_date,
  p.engineering_review_by,
  p.engineering_notes,
  p.estimated_system_size,
  p.system_type,
  p.equipment_specifications,
  p.estimated_cost_low,
  p.estimated_cost_high,
  p.final_quoted_price,
  p.pricing_notes,
  p.proposal_version,
  p.proposal_sent_date,
  p.proposal_valid_until,
  p.proposal_status,
  p.last_contact_date,
  p.next_follow_up_date,
  p.follow_up_method,
  p.prospect_temperature,
  p.prospect_notes,
  p.updated_at as prospect_updated_at,
  p.created_at as prospect_created_at,
  -- Contact info
  c.first_name as contact_first_name,
  c.family_name as contact_family_name,
  -- Structure info (aliased to avoid duplicates)
  s.structure_type as bridge_structure_type,
  s.building_age,
  s.roof_type
FROM
  leads l
  JOIN lead_prospects p ON l.id = p.lead_id
  LEFT JOIN contacts c ON l.id = c.lead_id
  AND c.call_order = 1
  LEFT JOIN lead_structure_info s ON l.id = s.lead_id
WHERE
  l.stage IN ('5', '6', '7', '8', '9', '10', '11', '12');

-- Contracting-specific view
CREATE
OR REPLACE VIEW `v_contracting_complete` AS
SELECT
  l.*,
  -- Contracting data (aliased to avoid duplicate column names)
  ct.id as contract_id,
  ct.contract_number,
  ct.contract_type,
  ct.contract_value,
  ct.contract_signed_date,
  ct.contract_start_date,
  ct.contract_completion_date,
  ct.payment_terms,
  ct.payment_schedule,
  ct.deposit_amount,
  ct.deposit_received,
  ct.deposit_date,
  ct.project_manager_id,
  ct.lead_technician_id,
  ct.project_start_date,
  ct.estimated_completion_date,
  ct.actual_completion_date,
  ct.project_status,
  ct.completion_percentage,
  ct.deliverables,
  ct.milestones,
  ct.current_milestone,
  ct.permits_required,
  ct.permits_obtained,
  ct.insurance_verified,
  ct.warranty_terms,
  ct.warranty_start_date,
  ct.warranty_end_date,
  ct.quality_check_completed,
  ct.quality_check_date,
  ct.client_satisfaction_score,
  ct.project_notes,
  ct.change_orders,
  ct.updated_at as contract_updated_at,
  ct.created_at as contract_created_at,
  -- Contact info
  c.first_name as contact_first_name,
  c.family_name as contact_family_name,
  pm.first_name as pm_first_name,
  pm.family_name as pm_family_name
FROM
  leads l
  JOIN lead_contracting ct ON l.id = ct.lead_id
  LEFT JOIN contacts c ON l.id = c.lead_id
  AND c.call_order = 1
  LEFT JOIN contacts pm ON ct.project_manager_id = pm.id
WHERE
  l.stage IN ('13', '14');
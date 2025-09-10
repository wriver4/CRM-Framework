-- Migration: Create Stage-Specific Tables
-- Date: 2024-09-09
-- Description: Split leads into stage-specific tables for better data organization
-- =====================================================
-- REFERRALS TABLE
-- =====================================================
CREATE TABLE
  `referrals` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `lead_id` int (11) NOT NULL,
    -- Referral-specific fields
    `referral_source_type` enum ('partner', 'customer', 'employee', 'other') DEFAULT 'partner',
    `referral_source_name` varchar(255) DEFAULT NULL,
    `referral_source_contact` varchar(255) DEFAULT NULL,
    `referral_source_email` varchar(255) DEFAULT NULL,
    `referral_source_phone` varchar(20) DEFAULT NULL,
    -- Commission and agreements
    `commission_rate` decimal(5, 2) DEFAULT NULL COMMENT 'Commission percentage (e.g., 5.00 for 5%)',
    `commission_amount` decimal(10, 2) DEFAULT NULL COMMENT 'Fixed commission amount',
    `commission_type` enum ('percentage', 'fixed', 'tiered') DEFAULT 'percentage',
    `agreement_type` varchar(100) DEFAULT NULL,
    `agreement_signed_date` date DEFAULT NULL,
    `agreement_document_path` varchar(500) DEFAULT NULL,
    -- Referral tracking
    `referral_code` varchar(50) DEFAULT NULL,
    `referral_notes` text DEFAULT NULL,
    `follow_up_required` tinyint (1) DEFAULT 0,
    `follow_up_date` date DEFAULT NULL,
    `referral_status` enum ('pending', 'qualified', 'converted', 'declined') DEFAULT 'pending',
    -- Timestamps
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_lead_id` (`lead_id`),
    KEY `idx_referral_source` (`referral_source_type`, `referral_source_name`),
    KEY `idx_referral_status` (`referral_status`),
    FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- =====================================================
-- PROSPECTS TABLE  
-- =====================================================
CREATE TABLE
  `prospects` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `lead_id` int (11) NOT NULL,
    -- Site survey and technical data
    `site_survey_completed` tinyint (1) DEFAULT 0,
    `site_survey_date` date DEFAULT NULL,
    `site_survey_notes` text DEFAULT NULL,
    `site_survey_document_path` varchar(500) DEFAULT NULL,
    -- Technical specifications
    `building_type` varchar(100) DEFAULT NULL,
    `building_age` int (4) DEFAULT NULL,
    `roof_type` varchar(100) DEFAULT NULL,
    `roof_condition` enum ('excellent', 'good', 'fair', 'poor') DEFAULT NULL,
    `electrical_capacity` varchar(100) DEFAULT NULL,
    `special_requirements` text DEFAULT NULL,
    -- Engineering data
    `engineering_review_required` tinyint (1) DEFAULT 0,
    `engineering_review_completed` tinyint (1) DEFAULT 0,
    `engineering_review_date` date DEFAULT NULL,
    `engineering_notes` text DEFAULT NULL,
    `engineering_document_path` varchar(500) DEFAULT NULL,
    -- Proposal information
    `proposal_version` int (3) DEFAULT 1,
    `proposal_sent_date` date DEFAULT NULL,
    `proposal_document_path` varchar(500) DEFAULT NULL,
    `proposal_valid_until` date DEFAULT NULL,
    `proposal_status` enum (
      'draft',
      'sent',
      'viewed',
      'accepted',
      'rejected',
      'expired'
    ) DEFAULT 'draft',
    -- Pricing details
    `estimated_system_size` decimal(8, 2) DEFAULT NULL COMMENT 'System size in kW or sq ft',
    `estimated_cost_low` decimal(10, 2) DEFAULT NULL,
    `estimated_cost_high` decimal(10, 2) DEFAULT NULL,
    `final_quoted_price` decimal(10, 2) DEFAULT NULL,
    `pricing_notes` text DEFAULT NULL,
    -- Follow-up tracking
    `last_contact_date` date DEFAULT NULL,
    `next_follow_up_date` date DEFAULT NULL,
    `follow_up_method` enum ('email', 'phone', 'meeting', 'site_visit') DEFAULT NULL,
    `prospect_temperature` enum ('hot', 'warm', 'cold') DEFAULT 'warm',
    -- Timestamps
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_lead_id` (`lead_id`),
    KEY `idx_proposal_status` (`proposal_status`),
    KEY `idx_prospect_temperature` (`prospect_temperature`),
    KEY `idx_follow_up_date` (`next_follow_up_date`),
    FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- =====================================================
-- CONTRACTING TABLE
-- =====================================================
CREATE TABLE
  `contracting` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `lead_id` int (11) NOT NULL,
    -- Contract information
    `contract_number` varchar(50) DEFAULT NULL,
    `contract_type` enum ('standard', 'custom', 'government', 'commercial') DEFAULT 'standard',
    `contract_value` decimal(12, 2) DEFAULT NULL,
    `contract_signed_date` date DEFAULT NULL,
    `contract_start_date` date DEFAULT NULL,
    `contract_completion_date` date DEFAULT NULL,
    `contract_document_path` varchar(500) DEFAULT NULL,
    -- Payment terms
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
    -- Project timeline
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
    -- Deliverables and milestones
    `deliverables` text DEFAULT NULL COMMENT 'JSON array of deliverables',
    `milestones` text DEFAULT NULL COMMENT 'JSON array of milestones',
    `current_milestone` varchar(200) DEFAULT NULL,
    `milestone_completion_percentage` int (3) DEFAULT 0,
    -- Legal and compliance
    `permits_required` text DEFAULT NULL,
    `permits_obtained` text DEFAULT NULL,
    `insurance_certificate_path` varchar(500) DEFAULT NULL,
    `warranty_terms` text DEFAULT NULL,
    `warranty_start_date` date DEFAULT NULL,
    `warranty_end_date` date DEFAULT NULL,
    -- Project team
    `project_manager_id` int (11) DEFAULT NULL,
    `lead_technician_id` int (11) DEFAULT NULL,
    `assigned_team` text DEFAULT NULL COMMENT 'JSON array of team member IDs',
    -- Communication and notes
    `client_communication_log` text DEFAULT NULL,
    `internal_notes` text DEFAULT NULL,
    `change_orders` text DEFAULT NULL COMMENT 'JSON array of change orders',
    -- Quality and completion
    `quality_check_completed` tinyint (1) DEFAULT 0,
    `quality_check_date` date DEFAULT NULL,
    `client_satisfaction_score` int (2) DEFAULT NULL COMMENT '1-10 scale',
    `project_photos_path` varchar(500) DEFAULT NULL,
    -- Timestamps
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_lead_id` (`lead_id`),
    KEY `idx_contract_number` (`contract_number`),
    KEY `idx_project_status` (`project_status`),
    KEY `idx_completion_date` (`estimated_completion_date`),
    KEY `idx_project_manager` (`project_manager_id`),
    FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- =====================================================
-- UPDATE LEADS TABLE (Remove stage-specific columns)
-- =====================================================
-- Note: This should be done carefully in production
-- First migrate existing data to new tables, then drop columns
-- Add indexes for better performance
ALTER TABLE `leads` ADD INDEX `idx_stage` (`stage`);

ALTER TABLE `leads` ADD INDEX `idx_updated_at` (`updated_at`);

-- =====================================================
-- CREATE VIEWS FOR EASY QUERYING
-- =====================================================
-- View for referrals with lead data
CREATE VIEW
  `v_referrals` AS
SELECT
  l.id as lead_id,
  l.lead_id as lead_number,
  l.first_name,
  l.family_name,
  l.email,
  l.cell_phone,
  l.stage,
  r.*
FROM
  leads l
  JOIN referrals r ON l.id = r.lead_id
WHERE
  l.stage IN ('4');

-- Referral stage
-- View for prospects with lead data  
CREATE VIEW
  `v_prospects` AS
SELECT
  l.id as lead_id,
  l.lead_id as lead_number,
  l.first_name,
  l.family_name,
  l.email,
  l.cell_phone,
  l.stage,
  p.*
FROM
  leads l
  JOIN prospects p ON l.id = p.lead_id
WHERE
  l.stage IN ('5', '6', '7', '8', '9', '10', '11', '12');

-- Prospect stages
-- View for contracting with lead data
CREATE VIEW
  `v_contracting` AS
SELECT
  l.id as lead_id,
  l.lead_id as lead_number,
  l.first_name,
  l.family_name,
  l.email,
  l.cell_phone,
  l.stage,
  c.*
FROM
  leads l
  JOIN contracting c ON l.id = c.lead_id
WHERE
  l.stage IN ('13', '14');

-- Contracting stages
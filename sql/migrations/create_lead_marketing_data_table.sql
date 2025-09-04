-- CREATE LEAD MARKETING DATA TABLE
-- Stores marketing attribution data for leads
-- Supports multiple marketing channels per lead
-- Safe for phpMyAdmin execution
-- Step 1: Show current leads table structure for reference
SELECT
  'Current leads table marketing fields:' as info;

SHOW COLUMNS
FROM
  leads LIKE '%hear%';

-- Step 2: Create the new marketing data table
SELECT
  'Creating lead_marketing_data table...' as status;

CREATE TABLE
  IF NOT EXISTS `lead_marketing_data` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `lead_id` int (11) NOT NULL COMMENT 'Foreign key to leads.id',
    `marketing_channel` varchar(50) NOT NULL COMMENT 'Marketing channel (mass_mailing, tv_radio, internet, etc.)',
    `marketing_channel_other` varchar(255) DEFAULT NULL COMMENT 'Custom marketing channel description',
    `attribution_weight` decimal(3, 2) DEFAULT 1.00 COMMENT 'Attribution weight for multi-channel leads',
    `campaign_source` varchar(100) DEFAULT NULL COMMENT 'Specific campaign or source identifier',
    `referral_details` text DEFAULT NULL COMMENT 'Additional referral or campaign details',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_lead_id` (`lead_id`),
    KEY `idx_marketing_channel` (`marketing_channel`),
    KEY `idx_created_at` (`created_at`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'Marketing attribution data for leads';

-- Step 3: Show the new table structure
SELECT
  'New table structure:' as info;

DESCRIBE lead_marketing_data;

-- Step 4: Create foreign key constraint (after table exists)
SELECT
  'Adding foreign key constraint...' as status;

-- Try to drop existing constraint first (may not exist)
ALTER TABLE lead_marketing_data
DROP FOREIGN KEY fk_lead_marketing_lead_id;

ALTER TABLE lead_marketing_data
DROP FOREIGN KEY lead_marketing_data_ibfk_1;

-- Add the foreign key constraint
ALTER TABLE lead_marketing_data ADD CONSTRAINT fk_lead_marketing_lead_id FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE CASCADE;

-- Step 5: Show final table structure with constraints
SELECT
  'Final table structure with constraints:' as info;

SHOW
CREATE TABLE
  lead_marketing_data;

-- Step 6: Create sample data migration query (commented out for safety)
SELECT
  'Sample migration query (run separately if needed):' as info;

/*
-- MIGRATION QUERY - Migrate existing hear_about data
-- Run this separately after testing

INSERT INTO lead_marketing_data (lead_id, marketing_channel, marketing_channel_other, created_at)
SELECT 
id as lead_id,
CASE 
WHEN hear_about = 'mass_mailing' THEN 'mass_mailing'
WHEN hear_about = 'tv_radio' THEN 'tv_radio'
WHEN hear_about = 'internet' THEN 'internet'
WHEN hear_about = 'neighbor' THEN 'neighbor'
WHEN hear_about = 'trade_show' THEN 'trade_show'
WHEN hear_about = 'other' THEN 'other'
ELSE 'unknown'
END as marketing_channel,
hear_about_other as marketing_channel_other,
created_at
FROM leads 
WHERE hear_about IS NOT NULL 
AND hear_about != '';
 */
SELECT
  'Table creation completed successfully!' as result;

SELECT
  'Next steps: Update form processing to use new table' as note;
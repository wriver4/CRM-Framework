-- Add timezone fields to leads and contacts tables
-- Run these SQL commands to add timezone support
-- Add timezone field to leads table
ALTER TABLE `leads`
ADD COLUMN `timezone` VARCHAR(50) DEFAULT NULL COMMENT 'Client timezone (e.g., America/New_York)' AFTER `form_country`;

-- Add timezone field to contacts table  
ALTER TABLE `contacts`
ADD COLUMN `timezone` VARCHAR(50) DEFAULT NULL COMMENT 'Contact timezone (e.g., America/New_York)' AFTER `m_country`;

-- Add index for better performance on timezone queries
CREATE INDEX `idx_leads_timezone` ON `leads` (`timezone`);

CREATE INDEX `idx_contacts_timezone` ON `contacts` (`timezone`);
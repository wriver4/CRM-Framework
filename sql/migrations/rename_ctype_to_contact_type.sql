-- Migration: Rename ctype column to contact_type in leads and contacts tables
-- Date: 2025-01-27
-- Description: Rename ctype column to contact_type for better code readability
-- Backup tables before making changes (recommended)
-- CREATE TABLE leads_backup AS SELECT * FROM leads;
-- CREATE TABLE contacts_backup AS SELECT * FROM contacts;
-- Rename ctype to contact_type in leads table
ALTER TABLE leads CHANGE COLUMN ctype contact_type INT (11) NOT NULL DEFAULT 1;

-- Rename ctype to contact_type in contacts table  
ALTER TABLE contacts CHANGE COLUMN ctype contact_type INT (11) NOT NULL DEFAULT 1;

-- Verify the changes
-- DESCRIBE leads;
-- DESCRIBE contacts;
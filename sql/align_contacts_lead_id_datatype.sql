-- Script to align contacts.lead_id data type with leads.lead_id
-- This should be run AFTER sync_contacts_lead_id_with_business_id.sql

-- STEP 1: Check current data types
SELECT 'STEP 1: Checking current data types' as current_step;

SELECT 
    'leads.lead_id' as field,
    data_type,
    column_type,
    is_nullable,
    character_maximum_length
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'leads' 
AND column_name = 'lead_id'

UNION ALL

SELECT 
    'contacts.lead_id' as field,
    data_type,
    column_type,
    is_nullable,
    character_maximum_length
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'contacts' 
AND column_name = 'lead_id';

-- STEP 2: Check if data types match
SELECT 'STEP 2: Data type compatibility check' as current_step;

SELECT 
    CASE 
        WHEN l_col.column_type = c_col.column_type THEN 'COMPATIBLE - No changes needed'
        ELSE CONCAT('INCOMPATIBLE - Need to change contacts.lead_id from ', c_col.column_type, ' to ', l_col.column_type)
    END as compatibility_status
FROM 
    (SELECT column_type FROM information_schema.columns 
     WHERE table_schema = DATABASE() AND table_name = 'leads' AND column_name = 'lead_id') l_col,
    (SELECT column_type FROM information_schema.columns 
     WHERE table_schema = DATABASE() AND table_name = 'contacts' AND column_name = 'lead_id') c_col;

-- STEP 3: Create backup before data type change
SELECT 'STEP 3: Creating backup before data type change' as current_step;

CREATE TABLE contacts_backup_before_datatype_change AS 
SELECT * FROM contacts;

SELECT 'Backup created: contacts_backup_before_datatype_change' as result;

-- STEP 4: Check for data that might be lost in conversion
SELECT 'STEP 4: Checking for potential data loss' as current_step;

-- Check max length of current lead_id values in contacts
SELECT 
    'Max length of contacts.lead_id values' as check_type,
    MAX(CHAR_LENGTH(CAST(lead_id AS CHAR))) as max_length,
    COUNT(*) as total_non_null_values
FROM contacts 
WHERE lead_id IS NOT NULL;

-- Check if all values are compatible with leads.lead_id format
SELECT 
    'Contacts with lead_id values not found in leads table' as check_type,
    COUNT(*) as count
FROM contacts c
LEFT JOIN leads l ON c.lead_id = l.lead_id
WHERE c.lead_id IS NOT NULL 
AND l.lead_id IS NULL;

-- STEP 5: Remove foreign key constraint if it exists
SELECT 'STEP 5: Removing foreign key constraint if it exists' as current_step;

-- Check if foreign key exists
SELECT 
    constraint_name,
    table_name,
    column_name,
    referenced_table_name,
    referenced_column_name
FROM information_schema.key_column_usage
WHERE table_schema = DATABASE()
AND table_name = 'contacts'
AND column_name = 'lead_id'
AND referenced_table_name IS NOT NULL;

-- Drop foreign key constraint if it exists (this will fail silently if it doesn't exist)
SET @sql = (
    SELECT CONCAT('ALTER TABLE contacts DROP FOREIGN KEY ', constraint_name)
    FROM information_schema.key_column_usage
    WHERE table_schema = DATABASE()
    AND table_name = 'contacts'
    AND column_name = 'lead_id'
    AND referenced_table_name IS NOT NULL
    LIMIT 1
);

SET @sql = IFNULL(@sql, 'SELECT "No foreign key constraint to drop" as result');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- STEP 6: Alter the data type to match leads.lead_id
SELECT 'STEP 6: Altering contacts.lead_id data type' as current_step;

-- Get the exact column definition from leads table
SET @target_type = (
    SELECT column_type 
    FROM information_schema.columns 
    WHERE table_schema = DATABASE() 
    AND table_name = 'leads' 
    AND column_name = 'lead_id'
);

SET @alter_sql = CONCAT('ALTER TABLE contacts MODIFY COLUMN lead_id ', @target_type, ' DEFAULT NULL');

SELECT @alter_sql as sql_to_execute;

PREPARE stmt FROM @alter_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- STEP 7: Verify the change
SELECT 'STEP 7: Verifying data type change' as current_step;

SELECT 
    'contacts.lead_id after change' as field,
    data_type,
    column_type,
    is_nullable
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'contacts' 
AND column_name = 'lead_id';

-- STEP 8: Re-create foreign key constraint if appropriate
SELECT 'STEP 8: Considering foreign key constraint' as current_step;

-- Note: We probably DON'T want a foreign key constraint here because
-- contacts.lead_id now references leads.lead_id (business identifier)
-- not leads.id (database primary key)
-- Foreign keys typically reference primary keys, not business identifiers

SELECT 'Foreign key constraint NOT added - contacts.lead_id references business identifier, not primary key' as fk_decision;

-- STEP 9: Final verification
SELECT 'STEP 9: Final verification' as current_step;

-- Check data integrity
SELECT 
    'Data integrity check' as check_type,
    COUNT(*) as total_contacts_with_lead_id,
    COUNT(CASE WHEN l.lead_id IS NOT NULL THEN 1 END) as valid_references,
    COUNT(CASE WHEN l.lead_id IS NULL THEN 1 END) as invalid_references
FROM contacts c
LEFT JOIN leads l ON c.lead_id = l.lead_id
WHERE c.lead_id IS NOT NULL;

-- Show sample of final data
SELECT 
    c.id as contact_id,
    c.lead_id as contact_lead_id,
    c.first_name,
    c.family_name,
    l.id as leads_db_id,
    l.lead_id as leads_business_id,
    'SUCCESS' as status
FROM contacts c
INNER JOIN leads l ON c.lead_id = l.lead_id
WHERE c.lead_id IS NOT NULL
ORDER BY c.id
LIMIT 5;

SELECT 'Data type alignment completed successfully!' as final_result;
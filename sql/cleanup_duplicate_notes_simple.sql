-- Simple Duplicate Notes Cleanup (No Stored Procedures)
-- File: sql/cleanup_duplicate_notes_simple.sql
-- Workaround for MariaDB upgrade issues

-- ============================================================================
-- STEP 1: CREATE BACKUP TABLES
-- ============================================================================

CREATE TABLE IF NOT EXISTS notes_backup_simple_cleanup AS 
SELECT * FROM notes;

CREATE TABLE IF NOT EXISTS leads_notes_backup_simple_cleanup AS 
SELECT * FROM leads_notes;

-- ============================================================================
-- STEP 2: IDENTIFY SAFE CLEANUP CANDIDATES  
-- ============================================================================

-- Create table of safe duplicates to remove (keep oldest, remove newer)
DROP TABLE IF EXISTS duplicates_to_remove;

CREATE TABLE duplicates_to_remove AS
SELECT 
    dna.lead_id,
    dna.note_text,
    -- Parse note IDs to identify which to keep vs remove
    SUBSTRING_INDEX(dna.note_ids, ',', 1) as keep_note_id,
    REPLACE(dna.note_ids, CONCAT(SUBSTRING_INDEX(dna.note_ids, ',', 1), ','), '') as remove_note_ids_raw,
    dna.duplicate_notes_count,
    dna.minutes_between_first_last,
    'SAFE_AUTO_CLEANUP' as cleanup_type
FROM duplicate_notes_analysis dna
WHERE dna.minutes_between_first_last <= 10  -- Within 10 minutes
    AND dna.duplicate_notes_count <= 5       -- Not excessive
    AND dna.duplicate_notes_count >= 2       -- Actually has duplicates
    AND CHAR_LENGTH(dna.note_text) >= 5;     -- Has substantial content

-- Show what will be cleaned up
SELECT 
    'SAFE CLEANUP CANDIDATES' as status,
    COUNT(*) as duplicate_groups,
    SUM(duplicate_notes_count - 1) as notes_to_remove
FROM duplicates_to_remove;

-- ============================================================================
-- STEP 3: MANUAL CLEANUP EXECUTION (RUN EACH SECTION SEPARATELY)
-- ============================================================================

-- Section A: Update junction table to point all duplicates to the kept note
-- NOTE: Run this query for each row in duplicates_to_remove, replacing the values

/*
EXAMPLE - Replace with actual values from your duplicates_to_remove table:

UPDATE leads_notes 
SET note_id = 123  -- keep_note_id from duplicates_to_remove
WHERE lead_id = 456  -- lead_id from duplicates_to_remove  
    AND note_id IN (124, 125, 126);  -- comma-separated IDs from remove_note_ids_raw

Repeat this pattern for each row in duplicates_to_remove table.
*/

-- Section B: Remove duplicate junction records (same lead_id + note_id)
DELETE ln1 FROM leads_notes ln1
INNER JOIN leads_notes ln2 ON ln1.lead_id = ln2.lead_id 
    AND ln1.note_id = ln2.note_id 
    AND ln1.id > ln2.id;

-- Section C: Delete the duplicate notes (run after updating junction table)
/*
EXAMPLE - Replace with actual note IDs to remove:

DELETE FROM notes WHERE id IN (124, 125, 126);

Repeat for each set of duplicate note IDs.
*/

-- ============================================================================
-- STEP 4: AUTOMATED APPROACH USING PREPARED STATEMENTS
-- ============================================================================

-- Create a table with the exact SQL statements to execute
DROP TABLE IF EXISTS cleanup_statements;

CREATE TABLE cleanup_statements AS
SELECT 
    dtr.lead_id,
    dtr.keep_note_id,
    dtr.remove_note_ids_raw,
    dtr.duplicate_notes_count,
    -- Generate UPDATE statement
    CONCAT(
        'UPDATE leads_notes SET note_id = ', dtr.keep_note_id, 
        ' WHERE lead_id = ', dtr.lead_id, 
        ' AND note_id IN (', dtr.remove_note_ids_raw, ');'
    ) as update_statement,
    -- Generate DELETE statement  
    CONCAT(
        'DELETE FROM notes WHERE id IN (', dtr.remove_note_ids_raw, ');'
    ) as delete_statement
FROM duplicates_to_remove dtr
ORDER BY dtr.lead_id;

-- Show the generated statements for review
SELECT 
    lead_id,
    duplicate_notes_count,
    update_statement,
    delete_statement
FROM cleanup_statements
ORDER BY lead_id;

-- ============================================================================
-- STEP 5: VERIFICATION QUERIES
-- ============================================================================

-- Check current state before cleanup
SELECT 
    'BEFORE CLEANUP' as status,
    'Total Notes' as metric,
    COUNT(*) as count
FROM notes

UNION ALL

SELECT 
    'BEFORE CLEANUP',
    'Total Junction Records',
    COUNT(*) as count
FROM leads_notes

UNION ALL

SELECT 
    'BEFORE CLEANUP',
    'Leads with Duplicates', 
    COUNT(DISTINCT lead_id) as count
FROM duplicate_notes_per_lead;

-- Query to run AFTER cleanup to verify results
/*
SELECT 
    'AFTER CLEANUP' as status,
    'Total Notes' as metric,
    COUNT(*) as count
FROM notes

UNION ALL

SELECT 
    'AFTER CLEANUP',
    'Total Junction Records',
    COUNT(*) as count
FROM leads_notes

UNION ALL

SELECT 
    'AFTER CLEANUP',
    'Remaining Duplicates',
    COUNT(*) as count
FROM (
    SELECT ln.lead_id, n.note_text, COUNT(DISTINCT n.id) as cnt
    FROM leads_notes ln
    INNER JOIN notes n ON ln.note_id = n.id  
    WHERE n.note_text != '' AND n.note_text IS NOT NULL
    GROUP BY ln.lead_id, n.note_text
    HAVING COUNT(DISTINCT n.id) > 1
) as remaining_dups;
*/

-- ============================================================================
-- STEP 6: EXECUTION INSTRUCTIONS
-- ============================================================================

/*
MANUAL EXECUTION STEPS (due to MariaDB upgrade issue):

1. First run this script to create the analysis tables

2. Review the safe cleanup candidates:
   SELECT * FROM duplicates_to_remove;

3. Review the generated SQL statements:
   SELECT * FROM cleanup_statements;

4. Execute each UPDATE statement from cleanup_statements manually:
   Copy and paste each update_statement into phpMyAdmin and run it

5. Run the junction cleanup:
   DELETE ln1 FROM leads_notes ln1
   INNER JOIN leads_notes ln2 ON ln1.lead_id = ln2.lead_id 
       AND ln1.note_id = ln2.note_id AND ln1.id > ln2.id;

6. Execute each DELETE statement from cleanup_statements:
   Copy and paste each delete_statement into phpMyAdmin and run it

7. Verify results with the AFTER CLEANUP queries above

8. For cases not in safe cleanup, handle manually:
   SELECT * FROM duplicate_notes_analysis 
   WHERE lead_id NOT IN (SELECT lead_id FROM duplicates_to_remove)
   ORDER BY duplicate_notes_count DESC;

ALTERNATIVE: If you have server access, run this command to fix the MariaDB issue:
sudo mariadb-upgrade -u root -p

Then you can use the stored procedure approach from the previous script.
*/

-- Show completion message
SELECT 
    'SCRIPT READY' as status,
    'Review duplicates_to_remove and cleanup_statements tables' as next_step,
    'Execute UPDATE and DELETE statements manually due to MariaDB upgrade issue' as note;
-- Comprehensive Solution for Lead-Specific Duplicate Notes Issue
-- File: sql/lead_duplicate_notes_solution.sql

/*
ROOT CAUSE ANALYSIS:
Based on code examination, duplicate notes per lead_id can occur from:

1. MIGRATION ISSUES: The migrate_notes.php script can create duplicate entries if run multiple times
2. JUNCTION TABLE DUPLICATES: Same note_id + lead_id combination inserted multiple times
3. FORM DOUBLE SUBMISSIONS: Users clicking submit multiple times quickly
4. CONCURRENT REQUESTS: Multiple simultaneous AJAX requests creating same note
5. MANUAL DUPLICATIONS: Notes manually created with same content
6. ORPHANED RELATIONSHIPS: Junction records pointing to deleted notes/leads

SOLUTION APPROACH:
- Immediate cleanup of existing duplicates
- Prevention mechanisms to stop future duplicates
- Monitoring and maintenance procedures
*/

-- ============================================================================
-- STEP 1: CREATE SAFETY BACKUPS
-- ============================================================================

-- Backup junction table
CREATE TABLE IF NOT EXISTS leads_notes_backup_dedup AS 
SELECT * FROM leads_notes;

-- Backup notes table (if not already backed up)
CREATE TABLE IF NOT EXISTS notes_backup_dedup AS 
SELECT * FROM notes;

-- ============================================================================
-- STEP 2: REMOVE JUNCTION TABLE DUPLICATES (SAME LEAD_ID + NOTE_ID)
-- ============================================================================

-- Create temporary table to identify junction duplicates to remove
CREATE TEMPORARY TABLE junction_duplicates_to_remove AS
SELECT 
    ln2.id as duplicate_id,
    ln1.id as keep_id,
    ln1.lead_id,
    ln1.note_id,
    ln1.date_linked as keep_date,
    ln2.date_linked as duplicate_date
FROM leads_notes ln1
INNER JOIN leads_notes ln2 ON ln1.lead_id = ln2.lead_id 
    AND ln1.note_id = ln2.note_id 
    AND ln1.id < ln2.id;  -- Keep the older record

-- Show what will be removed
SELECT 
    'Junction Duplicates to Remove' as action,
    COUNT(*) as count
FROM junction_duplicates_to_remove;

-- Remove duplicate junction records
DELETE ln FROM leads_notes ln
INNER JOIN junction_duplicates_to_remove jdr ON ln.id = jdr.duplicate_id;

-- ============================================================================
-- STEP 3: HANDLE CONTENT-BASED DUPLICATES PER LEAD
-- ============================================================================

-- Create temporary table for notes with identical content within same lead
CREATE TEMPORARY TABLE content_duplicates_per_lead AS
SELECT 
    ln.lead_id,
    MIN(n.id) as keep_note_id,
    GROUP_CONCAT(n.id ORDER BY n.date_created DESC SEPARATOR ',') as all_note_ids,
    n.note_text,
    n.user_id,
    n.form_source,
    COUNT(*) as duplicate_count
FROM leads_notes ln
INNER JOIN notes n ON ln.note_id = n.id
WHERE n.note_text != '' AND n.note_text IS NOT NULL
GROUP BY ln.lead_id, n.note_text, n.user_id, n.form_source
HAVING COUNT(*) > 1;

-- Show content duplicates found
SELECT 
    'Content Duplicates Per Lead Found' as status,
    COUNT(*) as groups,
    SUM(duplicate_count - 1) as notes_to_remove
FROM content_duplicates_per_lead;

-- Update junction table to point all duplicate relationships to kept note
-- and remove the redundant notes
UPDATE leads_notes ln
INNER JOIN content_duplicates_per_lead cdpl ON ln.lead_id = cdpl.lead_id
INNER JOIN notes n ON ln.note_id = n.id AND n.note_text = cdpl.note_text
SET ln.note_id = cdpl.keep_note_id
WHERE n.id != cdpl.keep_note_id;

-- Remove junction records that now have same lead_id + note_id (created by above update)
DELETE ln1 FROM leads_notes ln1
INNER JOIN leads_notes ln2 ON ln1.lead_id = ln2.lead_id 
    AND ln1.note_id = ln2.note_id 
    AND ln1.id > ln2.id;

-- Delete the duplicate notes
DELETE n FROM notes n
INNER JOIN content_duplicates_per_lead cdpl ON FIND_IN_SET(n.id, cdpl.all_note_ids) > 0
WHERE n.id != cdpl.keep_note_id;

-- ============================================================================
-- STEP 4: CLEAN UP ORPHANED RELATIONSHIPS
-- ============================================================================

-- Remove junction records pointing to non-existent notes
DELETE ln FROM leads_notes ln
LEFT JOIN notes n ON ln.note_id = n.id
WHERE n.id IS NULL;

-- Remove junction records pointing to non-existent leads
DELETE ln FROM leads_notes ln
LEFT JOIN leads l ON ln.lead_id = l.id
WHERE l.id IS NULL;

-- ============================================================================
-- STEP 5: ADD PREVENTIVE CONSTRAINTS AND INDEXES
-- ============================================================================

-- Add unique constraint to prevent junction table duplicates
ALTER TABLE leads_notes 
ADD CONSTRAINT uk_lead_note_unique UNIQUE (lead_id, note_id);

-- Add index for better performance on note content searches
ALTER TABLE notes 
ADD INDEX idx_content_hash (note_text(100), user_id, date_created);

-- Add index for junction table optimization
ALTER TABLE leads_notes 
ADD INDEX idx_lead_date (lead_id, date_linked);

-- ============================================================================
-- STEP 6: CREATE PREVENTIVE PROCEDURES AND FUNCTIONS
-- ============================================================================

DELIMITER //

-- Procedure to safely create note for lead (prevents duplicates)
CREATE PROCEDURE CreateNoteForLeadSafely(
    IN p_lead_id INT,
    IN p_source INT,
    IN p_note_text TEXT,
    IN p_user_id INT,
    IN p_form_source VARCHAR(50)
)
BEGIN
    DECLARE v_existing_note_id INT DEFAULT NULL;
    DECLARE v_new_note_id INT DEFAULT NULL;
    DECLARE v_content_hash VARCHAR(100);
    
    -- Create a simple hash of content for quick comparison
    SET v_content_hash = LEFT(p_note_text, 100);
    
    -- Check if identical note already exists for this lead within last hour
    SELECT n.id INTO v_existing_note_id
    FROM notes n
    INNER JOIN leads_notes ln ON n.id = ln.note_id
    WHERE ln.lead_id = p_lead_id
        AND n.note_text = p_note_text
        AND n.user_id = p_user_id
        AND n.source = p_source
        AND n.form_source = p_form_source
        AND n.date_created >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ORDER BY n.date_created DESC
    LIMIT 1;
    
    -- If duplicate found, just return existing note ID
    IF v_existing_note_id IS NOT NULL THEN
        SELECT v_existing_note_id as note_id, 'duplicate_prevented' as status;
    ELSE
        -- Create new note
        INSERT INTO notes (source, note_text, user_id, form_source, date_created)
        VALUES (p_source, p_note_text, p_user_id, p_form_source, NOW());
        
        SET v_new_note_id = LAST_INSERT_ID();
        
        -- Link to lead (ON DUPLICATE KEY prevents junction duplicates)
        INSERT INTO leads_notes (lead_id, note_id, date_linked) 
        VALUES (p_lead_id, v_new_note_id, NOW())
        ON DUPLICATE KEY UPDATE date_linked = VALUES(date_linked);
        
        SELECT v_new_note_id as note_id, 'created' as status;
    END IF;
END //

-- Function to check for recent duplicate notes before insertion
CREATE FUNCTION HasRecentDuplicateNote(
    p_lead_id INT,
    p_note_text TEXT,
    p_user_id INT,
    p_minutes_back INT
) RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO v_count
    FROM notes n
    INNER JOIN leads_notes ln ON n.id = ln.note_id
    WHERE ln.lead_id = p_lead_id
        AND n.note_text = p_note_text
        AND n.user_id = p_user_id
        AND n.date_created >= DATE_SUB(NOW(), INTERVAL p_minutes_back MINUTE);
    
    RETURN v_count > 0;
END //

-- Procedure for ongoing maintenance cleanup
CREATE PROCEDURE CleanupLeadNoteDuplicates()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_lead_id INT;
    DECLARE v_note_text TEXT;
    DECLARE v_keep_id INT;
    DECLARE v_duplicate_ids TEXT;
    
    -- Cursor for content duplicates
    DECLARE duplicate_cursor CURSOR FOR
        SELECT 
            ln.lead_id,
            n.note_text,
            MIN(n.id) as keep_id,
            GROUP_CONCAT(n.id ORDER BY n.date_created DESC) as duplicate_ids
        FROM leads_notes ln
        INNER JOIN notes n ON ln.note_id = n.id
        WHERE n.note_text != '' AND n.note_text IS NOT NULL
        GROUP BY ln.lead_id, n.note_text, n.user_id
        HAVING COUNT(*) > 1;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    START TRANSACTION;
    
    OPEN duplicate_cursor;
    
    read_loop: LOOP
        FETCH duplicate_cursor INTO v_lead_id, v_note_text, v_keep_id, v_duplicate_ids;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Update all junction records to point to kept note
        UPDATE leads_notes ln 
        SET ln.note_id = v_keep_id
        WHERE ln.lead_id = v_lead_id 
            AND FIND_IN_SET(ln.note_id, v_duplicate_ids) > 0
            AND ln.note_id != v_keep_id;
        
        -- Remove duplicate junction records
        DELETE ln1 FROM leads_notes ln1
        INNER JOIN leads_notes ln2 ON ln1.lead_id = ln2.lead_id 
            AND ln1.note_id = ln2.note_id 
            AND ln1.id > ln2.id;
        
        -- Delete duplicate notes
        DELETE FROM notes 
        WHERE FIND_IN_SET(id, v_duplicate_ids) > 0 
            AND id != v_keep_id;
        
    END LOOP;
    
    CLOSE duplicate_cursor;
    COMMIT;
END //

DELIMITER ;

-- ============================================================================
-- STEP 7: UPDATE APPLICATION CODE RECOMMENDATIONS
-- ============================================================================

/*
RECOMMENDED APPLICATION CHANGES:

1. UPDATE Notes.php create_note_for_lead() method:
   - Use CALL CreateNoteForLeadSafely() instead of direct INSERT
   - Add duplicate check before insertion
   
2. UPDATE leads/post.php:
   - Add debouncing mechanism for form submissions
   - Check for existing identical notes before creation
   
3. ADD CLIENT-SIDE PREVENTION:
   - Disable submit button after first click
   - Add JavaScript to prevent double submissions
   - Show loading state during note creation
   
4. UPDATE migrate_notes.php:
   - Add check to prevent re-running migration
   - Add duplicate detection during migration
   - Use INSERT IGNORE or ON DUPLICATE KEY UPDATE
   
5. ADD MONITORING:
   - Log duplicate prevention events
   - Track submission patterns
   - Monitor for unusual duplicate rates
*/

-- ============================================================================
-- STEP 8: VERIFICATION QUERIES
-- ============================================================================

-- Final cleanup summary
SELECT 
    'CLEANUP SUMMARY' as section,
    '' as details

UNION ALL

SELECT 
    'Original Junction Records',
    COUNT(*) as details
FROM leads_notes_backup_dedup

UNION ALL

SELECT 
    'Current Junction Records',
    COUNT(*) as details  
FROM leads_notes

UNION ALL

SELECT 
    'Junction Records Removed',
    (SELECT COUNT(*) FROM leads_notes_backup_dedup) - COUNT(*) as details
FROM leads_notes

UNION ALL

SELECT 
    'Leads with Remaining Junction Duplicates',
    COUNT(DISTINCT CONCAT(lead_id, '-', note_id)) as details
FROM (
    SELECT lead_id, note_id, COUNT(*) as cnt
    FROM leads_notes
    GROUP BY lead_id, note_id
    HAVING COUNT(*) > 1
) as remaining_dupes

UNION ALL

SELECT 
    'Leads with Remaining Content Duplicates',
    COUNT(DISTINCT subq.lead_id) as details
FROM (
    SELECT ln.lead_id
    FROM leads_notes ln
    INNER JOIN notes n ON ln.note_id = n.id
    WHERE n.note_text != '' AND n.note_text IS NOT NULL
    GROUP BY ln.lead_id, n.note_text
    HAVING COUNT(DISTINCT n.id) > 1
) as subq;

-- Test the new prevention function
SELECT 
    'PREVENTION TEST' as section,
    HasRecentDuplicateNote(1, 'Test note content', 1, 60) as has_duplicate;

/*
ROLLBACK PLAN (if needed):
1. DROP TABLE leads_notes;
2. CREATE TABLE leads_notes AS SELECT * FROM leads_notes_backup_dedup;
3. DROP TABLE notes;  
4. CREATE TABLE notes AS SELECT * FROM notes_backup_dedup;
5. DROP PROCEDURE CreateNoteForLeadSafely;
6. DROP FUNCTION HasRecentDuplicateNote;
7. DROP PROCEDURE CleanupLeadNoteDuplicates;

MONITORING QUERIES (run monthly):
1. SELECT * FROM sql/lead_duplicate_notes_analysis.sql (summary section)
2. CALL CleanupLeadNoteDuplicates(); -- for maintenance
3. Check application logs for duplicate prevention events
*/
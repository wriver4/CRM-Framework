-- Analysis and Solution for duplicate_notes_per_lead Results
-- File: sql/analyze_and_solve_duplicate_notes.sql

-- ============================================================================
-- ANALYSIS OF DUPLICATE NOTES PER LEAD RESULTS
-- ============================================================================

/*
ANALYSIS OF duplicate_notes_per_lead TABLE:

The query identifies leads that have multiple notes with identical content.
This reveals several patterns that need different cleanup approaches:

1. EXACT CONTENT DUPLICATES: Same note text appearing multiple times
2. TIMING PATTERNS: How quickly duplicates were created (indicates cause)
3. SCALE OF PROBLEM: Which leads are most affected
4. CLEANUP PRIORITY: Which duplicates are safe to auto-remove vs manual review

SOLUTION STRATEGY:
- Categorize duplicates by risk level
- Create safe cleanup procedures for each category  
- Preserve data integrity while removing redundancy
- Prevent future duplicates
*/

-- ============================================================================
-- STEP 1: ENHANCED ANALYSIS WITH CATEGORIZATION
-- ============================================================================

-- First, let's create a more detailed analysis table
DROP TABLE IF EXISTS duplicate_notes_analysis;

CREATE TABLE duplicate_notes_analysis AS
SELECT 
    dnpl.*,
    -- Add lead details for context
    l.lead_number,
    l.full_name as lead_name,
    l.email as lead_email,
    l.stage as lead_stage,
    -- Calculate time patterns
    TIMESTAMPDIFF(SECOND, dnpl.first_created, dnpl.last_created) as seconds_between_first_last,
    TIMESTAMPDIFF(MINUTE, dnpl.first_created, dnpl.last_created) as minutes_between_first_last,
    CHAR_LENGTH(dnpl.note_text) as note_length,
    -- Add user information for duplicates
    (SELECT GROUP_CONCAT(DISTINCT COALESCE(u.full_name, u.username, 'System') ORDER BY n.date_created)
     FROM notes n 
     LEFT JOIN users u ON n.user_id = u.id 
     WHERE FIND_IN_SET(n.id, dnpl.note_ids) > 0) as created_by_users,
    -- Add form source information
    (SELECT GROUP_CONCAT(DISTINCT n.form_source ORDER BY n.date_created)
     FROM notes n 
     WHERE FIND_IN_SET(n.id, dnpl.note_ids) > 0) as form_sources,
    -- Risk categorization
    CASE 
        WHEN TIMESTAMPDIFF(MINUTE, dnpl.first_created, dnpl.last_created) <= 2 THEN 'IMMEDIATE_DUPLICATE'
        WHEN TIMESTAMPDIFF(MINUTE, dnpl.first_created, dnpl.last_created) <= 10 THEN 'RAPID_DUPLICATE' 
        WHEN TIMESTAMPDIFF(HOUR, dnpl.first_created, dnpl.last_created) <= 1 THEN 'HOURLY_DUPLICATE'
        WHEN TIMESTAMPDIFF(DAY, dnpl.first_created, dnpl.last_created) <= 1 THEN 'DAILY_DUPLICATE'
        ELSE 'SPACED_DUPLICATE'
    END as timing_category,
    -- Cleanup safety assessment
    CASE 
        WHEN dnpl.duplicate_notes_count > 5 THEN 'MANUAL_REVIEW_REQUIRED'
        WHEN TIMESTAMPDIFF(MINUTE, dnpl.first_created, dnpl.last_created) <= 5 THEN 'SAFE_AUTO_CLEANUP'
        WHEN CHAR_LENGTH(dnpl.note_text) < 20 THEN 'SAFE_AUTO_CLEANUP'
        ELSE 'MODERATE_RISK_CLEANUP'
    END as cleanup_risk,
    -- Priority scoring
    CASE 
        WHEN dnpl.duplicate_notes_count > 5 THEN 'CRITICAL'
        WHEN dnpl.duplicate_notes_count > 3 THEN 'HIGH'
        WHEN dnpl.duplicate_notes_count > 2 THEN 'MEDIUM'
        ELSE 'LOW'
    END as cleanup_priority
FROM duplicate_notes_per_lead dnpl
LEFT JOIN leads l ON dnpl.lead_id = l.id
ORDER BY dnpl.duplicate_notes_count DESC, dnpl.lead_id;

-- ============================================================================
-- STEP 2: CLEANUP SOLUTION BY CATEGORY
-- ============================================================================

-- Create backup before any cleanup
CREATE TABLE IF NOT EXISTS notes_backup_before_duplicate_cleanup AS 
SELECT * FROM notes;

CREATE TABLE IF NOT EXISTS leads_notes_backup_before_duplicate_cleanup AS 
SELECT * FROM leads_notes;

-- Phase 1: IMMEDIATE_DUPLICATE and RAPID_DUPLICATE (safest to auto-clean)
-- These are likely double-clicks or system glitches
DROP TABLE IF EXISTS safe_auto_cleanup_candidates;

CREATE TABLE safe_auto_cleanup_candidates AS
SELECT 
    dna.lead_id,
    dna.note_text,
    dna.note_ids,
    SUBSTRING_INDEX(dna.note_ids, ',', 1) as keep_note_id,  -- Keep first (oldest) note
    SUBSTRING(dna.note_ids, LENGTH(SUBSTRING_INDEX(dna.note_ids, ',', 1)) + 2) as remove_note_ids,
    dna.duplicate_notes_count,
    dna.timing_category,
    dna.cleanup_risk
FROM duplicate_notes_analysis dna
WHERE dna.cleanup_risk = 'SAFE_AUTO_CLEANUP'
    AND dna.timing_category IN ('IMMEDIATE_DUPLICATE', 'RAPID_DUPLICATE')
    AND dna.duplicate_notes_count <= 5;  -- Additional safety limit

-- Phase 2: Create cleanup procedure for safe candidates
DELIMITER //

CREATE PROCEDURE CleanupSafeDuplicateNotes()
BEGIN
    -- All variable declarations must come first
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_lead_id INT;
    DECLARE v_keep_note_id INT;
    DECLARE v_remove_ids TEXT;
    DECLARE v_duplicate_count INT;
    DECLARE v_notes_removed INT DEFAULT 0;
    DECLARE v_junction_updated INT DEFAULT 0;
    
    -- Then cursor declarations
    DECLARE cleanup_cursor CURSOR FOR
        SELECT lead_id, keep_note_id, remove_note_ids, duplicate_notes_count
        FROM safe_auto_cleanup_candidates;
    
    -- Finally handler declarations
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    START TRANSACTION;
    
    OPEN cleanup_cursor;
    
    cleanup_loop: LOOP
        FETCH cleanup_cursor INTO v_lead_id, v_keep_note_id, v_remove_ids, v_duplicate_count;
        IF done THEN
            LEAVE cleanup_loop;
        END IF;
        
        -- Update all junction table references to point to the kept note
        UPDATE leads_notes ln 
        SET ln.note_id = v_keep_note_id
        WHERE ln.lead_id = v_lead_id 
            AND FIND_IN_SET(ln.note_id, v_remove_ids) > 0;
        
        SET v_junction_updated = v_junction_updated + ROW_COUNT();
        
        -- Remove duplicate junction entries (now pointing to same note)
        DELETE ln1 FROM leads_notes ln1
        INNER JOIN leads_notes ln2 ON ln1.lead_id = ln2.lead_id 
            AND ln1.note_id = ln2.note_id 
            AND ln1.id > ln2.id
        WHERE ln1.lead_id = v_lead_id AND ln1.note_id = v_keep_note_id;
        
        -- Delete the duplicate notes
        DELETE FROM notes 
        WHERE FIND_IN_SET(id, v_remove_ids) > 0;
        
        SET v_notes_removed = v_notes_removed + (v_duplicate_count - 1);
        
    END LOOP;
    
    CLOSE cleanup_cursor;
    
    -- Show results
    SELECT 
        'SAFE CLEANUP COMPLETED' as status,
        v_notes_removed as notes_removed,
        v_junction_updated as junction_records_updated;
    
    COMMIT;
END //

DELIMITER ;

-- ============================================================================
-- STEP 3: MANUAL REVIEW CANDIDATES
-- ============================================================================

-- Create table for cases requiring manual review
DROP TABLE IF EXISTS manual_review_candidates;

CREATE TABLE manual_review_candidates AS
SELECT 
    dna.lead_id,
    dna.lead_number,
    dna.lead_name,
    dna.note_text,
    dna.duplicate_notes_count,
    dna.timing_category,
    dna.cleanup_risk,
    dna.cleanup_priority,
    dna.created_by_users,
    dna.form_sources,
    dna.note_ids,
    dna.creation_dates,
    -- Preview of note content
    LEFT(dna.note_text, 200) as note_preview,
    -- Recommendation
    CASE 
        WHEN dna.created_by_users LIKE '%System%' AND dna.created_by_users NOT LIKE '%,%' THEN 'LIKELY_MIGRATION_DUPLICATE'
        WHEN dna.form_sources LIKE '%,%' THEN 'CROSS_SOURCE_DUPLICATE'
        WHEN dna.duplicate_notes_count > 5 THEN 'EXCESSIVE_DUPLICATES'
        ELSE 'STANDARD_MANUAL_REVIEW'
    END as review_category
FROM duplicate_notes_analysis dna
WHERE dna.cleanup_risk IN ('MANUAL_REVIEW_REQUIRED', 'MODERATE_RISK_CLEANUP')
    OR dna.duplicate_notes_count > 5
    OR dna.timing_category = 'SPACED_DUPLICATE'
ORDER BY dna.cleanup_priority DESC, dna.duplicate_notes_count DESC;

-- ============================================================================
-- STEP 4: EXECUTION PLAN AND VERIFICATION
-- ============================================================================

-- Summary of cleanup plan
SELECT 
    'CLEANUP EXECUTION PLAN' as section,
    '' as details
    
UNION ALL

SELECT 
    'Safe Auto-Cleanup Candidates',
    COUNT(*) as details
FROM safe_auto_cleanup_candidates

UNION ALL

SELECT 
    'Manual Review Required',
    COUNT(*) as details  
FROM manual_review_candidates

UNION ALL

SELECT 
    'Total Notes to be Removed (Safe)',
    SUM(duplicate_notes_count - 1) as details
FROM safe_auto_cleanup_candidates

UNION ALL

SELECT 
    'Estimated Time Savings',
    CONCAT(SUM(duplicate_notes_count - 1) * 2, ' minutes') as details
FROM safe_auto_cleanup_candidates;

-- Verification queries to run BEFORE cleanup
SELECT 
    'PRE-CLEANUP VERIFICATION' as check_type,
    'Total Notes' as metric,
    COUNT(*) as current_value
FROM notes

UNION ALL

SELECT 
    'PRE-CLEANUP VERIFICATION',
    'Total Junction Records',
    COUNT(*) as current_value
FROM leads_notes

UNION ALL

SELECT 
    'PRE-CLEANUP VERIFICATION', 
    'Leads with Duplicates',
    COUNT(DISTINCT lead_id) as current_value
FROM duplicate_notes_per_lead;

-- ============================================================================
-- STEP 5: EXECUTION INSTRUCTIONS
-- ============================================================================

/*
RECOMMENDED EXECUTION ORDER:

1. REVIEW FIRST:
   SELECT * FROM duplicate_notes_analysis ORDER BY cleanup_priority DESC, duplicate_notes_count DESC;
   
2. EXAMINE SAFE CLEANUP CANDIDATES:
   SELECT * FROM safe_auto_cleanup_candidates;
   
3. REVIEW MANUAL CASES:
   SELECT * FROM manual_review_candidates ORDER BY review_category, duplicate_notes_count DESC;

4. IF SATISFIED WITH ANALYSIS, EXECUTE SAFE CLEANUP:
   CALL CleanupSafeDuplicateNotes();
   
5. VERIFY RESULTS:
   SELECT 
       'POST-CLEANUP VERIFICATION' as check_type,
       'Remaining Duplicates' as metric,
       COUNT(*) as value
   FROM duplicate_notes_per_lead;

6. HANDLE MANUAL REVIEW CASES ONE BY ONE:
   - Review each case in manual_review_candidates
   - Manually decide which notes to keep/remove
   - Use individual DELETE statements with specific note IDs

7. CLEANUP TEMP TABLES:
   DROP TABLE duplicate_notes_per_lead;
   DROP TABLE duplicate_notes_analysis; 
   DROP TABLE safe_auto_cleanup_candidates;
   DROP TABLE manual_review_candidates;

ROLLBACK PLAN (if needed):
- Restore from notes_backup_before_duplicate_cleanup
- Restore from leads_notes_backup_before_duplicate_cleanup
*/

-- Show final summary
SELECT 
    'ANALYSIS COMPLETE' as status,
    'Review the generated tables and execute cleanup when ready' as next_steps;
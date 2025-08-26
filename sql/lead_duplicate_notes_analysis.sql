-- Analysis of Duplicate Notes Per Lead_ID Issue
-- File: sql/lead_duplicate_notes_analysis.sql

-- 1. Find leads with duplicate note relationships (same note linked multiple times)
SELECT 
    ln.lead_id,
    ln.note_id,
    n.note_text,
    COUNT(*) as duplicate_count,
    GROUP_CONCAT(ln.id ORDER BY ln.id) as junction_ids,
    GROUP_CONCAT(ln.date_linked ORDER BY ln.date_linked) as link_dates,
    MIN(ln.date_linked) as first_linked,
    MAX(ln.date_linked) as last_linked
FROM leads_notes ln
INNER JOIN notes n ON ln.note_id = n.id
GROUP BY ln.lead_id, ln.note_id
HAVING COUNT(*) > 1
ORDER BY duplicate_count DESC, ln.lead_id;

-- 2. Find leads with multiple notes that have identical content
SELECT 
    ln.lead_id,
    n.note_text,
    COUNT(DISTINCT n.id) as duplicate_notes_count,
    GROUP_CONCAT(DISTINCT n.id ORDER BY n.id) as note_ids,
    GROUP_CONCAT(DISTINCT n.date_created ORDER BY n.date_created) as creation_dates,
    MIN(n.date_created) as first_created,
    MAX(n.date_created) as last_created
FROM leads_notes ln
INNER JOIN notes n ON ln.note_id = n.id
WHERE n.note_text != '' AND n.note_text IS NOT NULL
GROUP BY ln.lead_id, n.note_text
HAVING COUNT(DISTINCT n.id) > 1
ORDER BY duplicate_notes_count DESC, ln.lead_id;

-- 3. Find leads with notes created within short time spans (potential system duplicates)
SELECT 
    ln1.lead_id,
    n1.id as note1_id,
    n2.id as note2_id,
    n1.note_text,
    n1.date_created as first_created,
    n2.date_created as second_created,
    TIMESTAMPDIFF(SECOND, n1.date_created, n2.date_created) as seconds_apart,
    n1.user_id,
    n1.form_source
FROM leads_notes ln1
INNER JOIN notes n1 ON ln1.note_id = n1.id
INNER JOIN leads_notes ln2 ON ln1.lead_id = ln2.lead_id AND ln1.note_id < ln2.note_id
INNER JOIN notes n2 ON ln2.note_id = n2.id
WHERE n1.note_text = n2.note_text  -- Same content
    AND n1.user_id = n2.user_id    -- Same user
    AND n1.form_source = n2.form_source  -- Same source
    AND TIMESTAMPDIFF(MINUTE, n1.date_created, n2.date_created) <= 5  -- Within 5 minutes
ORDER BY ln1.lead_id, seconds_apart;

-- 4. Migration-related duplicates: find notes that might have been created both by migration and manual entry
SELECT 
    ln.lead_id,
    n.note_text,
    COUNT(*) as duplicate_count,
    GROUP_CONCAT(DISTINCT n.form_source) as sources,
    GROUP_CONCAT(DISTINCT COALESCE(n.user_id, 'NULL')) as user_ids,
    GROUP_CONCAT(n.id ORDER BY n.date_created) as note_ids,
    MIN(n.date_created) as first_created,
    MAX(n.date_created) as last_created
FROM leads_notes ln
INNER JOIN notes n ON ln.note_id = n.id
WHERE n.note_text != '' AND n.note_text IS NOT NULL
GROUP BY ln.lead_id, n.note_text
HAVING COUNT(*) > 1 
    AND GROUP_CONCAT(DISTINCT n.form_source) LIKE '%leads%'
ORDER BY duplicate_count DESC, ln.lead_id;

-- 5. Check for orphaned junction records (leads_notes pointing to non-existent notes or leads)
SELECT 
    'Orphaned junction records (note_id does not exist)' as issue_type,
    COUNT(*) as count
FROM leads_notes ln
LEFT JOIN notes n ON ln.note_id = n.id
WHERE n.id IS NULL

UNION ALL

SELECT 
    'Orphaned junction records (lead_id does not exist)' as issue_type,
    COUNT(*) as count
FROM leads_notes ln
LEFT JOIN leads l ON ln.lead_id = l.id
WHERE l.id IS NULL;

-- 6. Summary statistics for leads with duplicate note issues
SELECT 
    'Total Leads' as metric,
    COUNT(DISTINCT l.id) as count
FROM leads l

UNION ALL

SELECT 
    'Leads with Notes' as metric,
    COUNT(DISTINCT ln.lead_id) as count
FROM leads_notes ln

UNION ALL

SELECT 
    'Leads with Duplicate Junction Records' as metric,
    COUNT(DISTINCT ln.lead_id) as count
FROM leads_notes ln
GROUP BY ln.lead_id, ln.note_id
HAVING COUNT(*) > 1

UNION ALL

SELECT 
    'Leads with Duplicate Note Content' as metric,
    COUNT(DISTINCT subq.lead_id) as count
FROM (
    SELECT ln.lead_id
    FROM leads_notes ln
    INNER JOIN notes n ON ln.note_id = n.id
    WHERE n.note_text != '' AND n.note_text IS NOT NULL
    GROUP BY ln.lead_id, n.note_text
    HAVING COUNT(DISTINCT n.id) > 1
) as subq

UNION ALL

SELECT 
    'Total Duplicate Junction Records' as metric,
    SUM(duplicate_count - 1) as count
FROM (
    SELECT COUNT(*) as duplicate_count
    FROM leads_notes ln
    GROUP BY ln.lead_id, ln.note_id
    HAVING COUNT(*) > 1
) as duplicates;

-- 7. Find specific patterns that indicate root causes
-- Pattern A: Same note content, different creation times, same user (double submission)
SELECT 
    'Double Submission Pattern' as pattern_type,
    COUNT(DISTINCT ln1.lead_id) as affected_leads,
    COUNT(*) as instances
FROM leads_notes ln1
INNER JOIN notes n1 ON ln1.note_id = n1.id
INNER JOIN leads_notes ln2 ON ln1.lead_id = ln2.lead_id AND ln1.note_id != ln2.note_id
INNER JOIN notes n2 ON ln2.note_id = n2.id
WHERE n1.note_text = n2.note_text
    AND n1.user_id = n2.user_id
    AND n1.user_id IS NOT NULL
    AND TIMESTAMPDIFF(MINUTE, n1.date_created, n2.date_created) BETWEEN 1 AND 30

UNION ALL

-- Pattern B: Migration artifacts (null user_id with form_source 'leads')
SELECT 
    'Migration Artifact Pattern' as pattern_type,
    COUNT(DISTINCT ln.lead_id) as affected_leads,
    COUNT(*) as instances
FROM leads_notes ln
INNER JOIN notes n ON ln.note_id = n.id
WHERE n.user_id IS NULL 
    AND n.form_source = 'leads'
    AND EXISTS (
        SELECT 1 FROM leads_notes ln2 
        INNER JOIN notes n2 ON ln2.note_id = n2.id
        WHERE ln2.lead_id = ln.lead_id 
        AND n2.note_text = n.note_text
        AND n2.user_id IS NOT NULL
    )

UNION ALL

-- Pattern C: Junction table duplicates (same lead_id + note_id combination multiple times)
SELECT 
    'Junction Table Duplicate Pattern' as pattern_type,
    COUNT(DISTINCT CONCAT(ln.lead_id, '-', ln.note_id)) as affected_combinations,
    SUM(duplicate_count - 1) as excess_records
FROM (
    SELECT lead_id, note_id, COUNT(*) as duplicate_count
    FROM leads_notes
    GROUP BY lead_id, note_id
    HAVING COUNT(*) > 1
) as ln;
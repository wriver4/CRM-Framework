-- Create Table with Lead Content Duplicates Results
-- File: sql/create_duplicate_notes_table.sql
-- Drop table if exists (for re-running)
DROP TABLE IF EXISTS duplicate_notes_per_lead;

-- Create table with the query results
CREATE TABLE
    duplicate_notes_per_lead AS
SELECT
    ln.lead_id,
    n.note_text,
    COUNT(DISTINCT n.id) as duplicate_notes_count,
    GROUP_CONCAT(DISTINCT n.id ORDER BY n.id) as note_ids,
    GROUP_CONCAT(DISTINCT n.date_created ORDER BY n.date_created) as creation_dates,
    MIN(n.date_created) as first_created,
    MAX(n.date_created) as last_created
FROM
    leads_notes ln
    INNER JOIN notes n ON ln.note_id = n.id
WHERE
    n.note_text != ''
    AND n.note_text IS NOT NULL
GROUP BY
    ln.lead_id,
    n.note_text
HAVING
    COUNT(DISTINCT n.id) > 1
ORDER BY
    duplicate_notes_count DESC,
    ln.lead_id;

-- Add indexes for better performance
ALTER TABLE duplicate_notes_per_lead ADD INDEX idx_lead_id (lead_id),
ADD INDEX idx_duplicate_count (duplicate_notes_count);

-- Show completion status
SELECT
    'Table created successfully' as status,
    COUNT(*) as total_rows,
    MAX(duplicate_notes_count) as max_duplicates,
    MIN(duplicate_notes_count) as min_duplicates
FROM
    duplicate_notes_per_lead;

-- Preview the results
SELECT
    *
FROM
    duplicate_notes_per_lead;
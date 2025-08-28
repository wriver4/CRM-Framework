-- phpMyAdmin Script: Sync contacts.lead_id with leads.lead_id (business identifier)
-- Run this in phpMyAdmin by copying and pasting each section separately
-- STEP 1: Analyze current data relationships
SELECT
  'STEP 1: Analyzing current data relationships' as current_step;

SELECT
  'Current contacts.lead_id -> leads.id relationship' as relationship_type,
  COUNT(*) as total_records
FROM
  contacts c
  INNER JOIN leads l ON c.lead_id = l.id
WHERE
  c.lead_id IS NOT NULL
UNION ALL
SELECT
  'Contacts with NULL lead_id' as relationship_type,
  COUNT(*) as total_records
FROM
  contacts c
WHERE
  c.lead_id IS NULL;
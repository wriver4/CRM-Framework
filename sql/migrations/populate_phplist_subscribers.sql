-- POPULATE PHPLIST_SUBSCRIBERS FROM LEADS
-- Simple SQL to populate phplist_subscribers table with existing lead data
-- Safe for phpMyAdmin execution
-- Step 1: Show current data counts
SELECT
  'Current data counts:' as info;

SELECT
  COUNT(*) as total_leads
FROM
  leads;

SELECT
  COUNT(*) as total_contacts
FROM
  contacts;

SELECT
  COUNT(*) as current_phplist_subscribers
FROM
  phplist_subscribers;

-- Step 2: Show sample data to verify structure
SELECT
  'Sample leads data:' as info;

SELECT
  id,
  contact_id,
  created_at
FROM
  leads
LIMIT
  5;

SELECT
  'Sample contacts data:' as info;

SELECT
  id,
  full_name,
  email
FROM
  contacts
LIMIT
  5;

-- Step 3: Populate phplist_subscribers with lead data
SELECT
  'Populating phplist_subscribers from leads...' as status;

INSERT INTO
  phplist_subscribers (
    lead_id,
    contact_id,
    email,
    sync_status,
    created_at
  )
SELECT
  l.id as lead_id,
  l.contact_id,
  COALESCE(
    c.email,
    CONCAT ('noemail_', l.id, '@placeholder.com')
  ) as email,
  'pending' as sync_status,
  l.created_at
FROM
  leads l
  LEFT JOIN contacts c ON l.contact_id = c.id
WHERE
  l.id NOT IN (
    SELECT
      lead_id
    FROM
      phplist_subscribers
    WHERE
      lead_id IS NOT NULL
  )
  AND l.contact_id IS NOT NULL;

-- Step 4: Handle leads without contact_id (if any exist)
SELECT
  'Handling leads without contact_id...' as status;

INSERT INTO
  phplist_subscribers (
    lead_id,
    contact_id,
    email,
    sync_status,
    created_at
  )
SELECT
  l.id as lead_id,
  NULL as contact_id,
  CONCAT ('lead_', l.id, '@placeholder.com') as email,
  'pending' as sync_status,
  l.created_at
FROM
  leads l
WHERE
  l.contact_id IS NULL
  AND l.id NOT IN (
    SELECT
      lead_id
    FROM
      phplist_subscribers
    WHERE
      lead_id IS NOT NULL
  );

-- Step 5: Show results
SELECT
  'Population completed! Results:' as status;

SELECT
  COUNT(*) as total_phplist_subscribers
FROM
  phplist_subscribers;

SELECT
  'Sample populated data:' as info;

SELECT
  ps.id,
  ps.lead_id,
  ps.contact_id,
  ps.email,
  ps.sync_status,
  c.full_name as contact_name
FROM
  phplist_subscribers ps
  LEFT JOIN contacts c ON ps.contact_id = c.id
ORDER BY
  ps.id
LIMIT
  10;

-- Step 6: Show summary by sync status
SELECT
  'Summary by sync status:' as info;

SELECT
  sync_status,
  COUNT(*) as count
FROM
  phplist_subscribers
GROUP BY
  sync_status;

-- Step 7: Show any potential issues
SELECT
  'Checking for potential issues:' as info;

SELECT
  'Subscribers without valid email addresses:' as check_info;

SELECT
  COUNT(*) as count
FROM
  phplist_subscribers
WHERE
  email LIKE '%@placeholder.com';

SELECT
  'Subscribers with missing contact data:' as check_info;

SELECT
  COUNT(*) as count
FROM
  phplist_subscribers ps
  LEFT JOIN contacts c ON ps.contact_id = c.id
WHERE
  ps.contact_id IS NOT NULL
  AND c.id IS NULL;

SELECT
  'Population process completed successfully!' as result;
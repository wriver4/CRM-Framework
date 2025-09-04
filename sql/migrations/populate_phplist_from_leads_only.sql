-- POPULATE PHPLIST_SUBSCRIBERS FROM LEADS ONLY
-- Emulates the phpList integration from /leads/post.php (lines 182-227)
-- Only processes leads where get_updates = 1 (opted in for updates)
-- Safe for phpMyAdmin execution
-- Step 1: Show current data counts
SELECT
  'Current data counts:' as info;

SELECT
  COUNT(*) as total_leads
FROM
  leads;

SELECT
  COUNT(*) as leads_with_get_updates
FROM
  leads
WHERE
  get_updates = 1;

SELECT
  COUNT(*) as current_phplist_subscribers
FROM
  phplist_subscribers;

-- Step 2: Show sample lead data to verify structure
SELECT
  'Sample leads data (get_updates = 1):' as info;

SELECT
  id,
  email,
  get_updates,
  created_at
FROM
  leads
WHERE
  get_updates = 1
LIMIT
  5;

-- Step 3: Populate phplist_subscribers from leads (emulating post.php logic)
SELECT
  'Populating phplist_subscribers from leads with get_updates = 1...' as status;

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
  CASE
    WHEN l.email IS NOT NULL
    AND l.email != '' THEN l.email
    ELSE CONCAT ('lead_', l.id, '@placeholder.com')
  END as email,
  'pending' as sync_status,
  l.created_at
FROM
  leads l
WHERE
  l.get_updates = 1 -- Only leads that opted in for updates
  AND l.id NOT IN (
    SELECT
      lead_id
    FROM
      phplist_subscribers
    WHERE
      lead_id IS NOT NULL
  );

-- Step 4: Show results
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
  ps.sync_status
FROM
  phplist_subscribers ps
ORDER BY
  ps.id
LIMIT
  10;

-- Step 5: Show summary by sync status
SELECT
  'Summary by sync status:' as info;

SELECT
  sync_status,
  COUNT(*) as count
FROM
  phplist_subscribers
GROUP BY
  sync_status;

-- Step 6: Show leads that were NOT added (get_updates = 0)
SELECT
  'Leads NOT added to phpList (get_updates = 0):' as info;

SELECT
  COUNT(*) as leads_not_added
FROM
  leads
WHERE
  get_updates = 0
  OR get_updates IS NULL;

SELECT
  'Population process completed successfully!' as result;

SELECT
  'Only leads with get_updates = 1 were added to phplist_subscribers' as note;
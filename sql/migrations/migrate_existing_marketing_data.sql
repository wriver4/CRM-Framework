-- MIGRATE EXISTING MARKETING DATA TO NEW TABLE
-- Migrates hear_about and hear_about_other data from leads table to lead_marketing_data table
-- Safe for phpMyAdmin execution
-- Run AFTER creating the lead_marketing_data table
-- Step 1: Show current marketing data in leads table
SELECT
  'Current marketing data in leads table:' as info;

SELECT
  COUNT(*) as total_leads,
  COUNT(
    CASE
      WHEN hear_about IS NOT NULL
      AND hear_about != '' THEN 1
    END
  ) as leads_with_marketing_data,
  COUNT(
    CASE
      WHEN hear_about_other IS NOT NULL
      AND hear_about_other != '' THEN 1
    END
  ) as leads_with_other_details
FROM
  leads;

-- Step 2: Show sample of existing marketing data
SELECT
  'Sample existing marketing data:' as info;

SELECT
  id,
  lead_id,
  full_name,
  hear_about,
  hear_about_other,
  created_at
FROM
  leads
WHERE
  hear_about IS NOT NULL
  AND hear_about != ''
LIMIT
  10;

-- Step 3: Show marketing channel distribution
SELECT
  'Current marketing channel distribution:' as info;

SELECT
  hear_about,
  COUNT(*) as count,
  COUNT(
    CASE
      WHEN hear_about_other IS NOT NULL
      AND hear_about_other != '' THEN 1
    END
  ) as with_other_details
FROM
  leads
WHERE
  hear_about IS NOT NULL
  AND hear_about != ''
GROUP BY
  hear_about
ORDER BY
  count DESC;

-- Step 4: Check if lead_marketing_data table exists and is empty
SELECT
  'Checking lead_marketing_data table status:' as info;

SELECT
  COUNT(*) as current_marketing_data_records
FROM
  lead_marketing_data;

-- Step 5: Migrate existing marketing data
SELECT
  'Migrating existing marketing data...' as status;

INSERT INTO
  lead_marketing_data (
    lead_id,
    marketing_channel,
    marketing_channel_other,
    created_at
  )
SELECT
  l.id as lead_id,
  CASE
  -- Map existing hear_about values to standardized marketing channels
    WHEN l.hear_about = 'Mass mailing' THEN 'mass_mailing'
    WHEN l.hear_about = 'mass_mailing' THEN 'mass_mailing'
    WHEN l.hear_about = 'TV/radio ad' THEN 'tv_radio'
    WHEN l.hear_about = 'tv_radio' THEN 'tv_radio'
    WHEN l.hear_about = 'Internet search' THEN 'internet'
    WHEN l.hear_about = 'internet' THEN 'internet'
    WHEN l.hear_about = 'Neighbor/friend' THEN 'neighbor'
    WHEN l.hear_about = 'neighbor' THEN 'neighbor'
    WHEN l.hear_about = 'Trade show' THEN 'trade_show'
    WHEN l.hear_about = 'trade_show' THEN 'trade_show'
    WHEN l.hear_about = 'Other' THEN 'other'
    WHEN l.hear_about = 'other' THEN 'other'
    -- Handle insurance company variations
    WHEN LOWER(l.hear_about) LIKE '%insurance%' THEN 'insurance'
    WHEN LOWER(l.hear_about) LIKE '%insurer%' THEN 'insurance'
    -- Handle referral variations
    WHEN LOWER(l.hear_about) LIKE '%referral%' THEN 'referral'
    WHEN LOWER(l.hear_about) LIKE '%refer%' THEN 'referral'
    WHEN LOWER(l.hear_about) LIKE '%professional%' THEN 'referral'
    WHEN LOWER(l.hear_about) LIKE '%contractor%' THEN 'referral'
    WHEN LOWER(l.hear_about) LIKE '%agent%' THEN 'referral'
    -- Handle any unmapped values
    ELSE COALESCE(
      LOWER(REPLACE (l.hear_about, ' ', '_')),
      'unknown'
    )
  END as marketing_channel,
  l.hear_about_other as marketing_channel_other,
  l.created_at
FROM
  leads l
WHERE
  l.hear_about IS NOT NULL
  AND l.hear_about != ''
  AND l.id NOT IN (
    SELECT DISTINCT
      lead_id
    FROM
      lead_marketing_data
    WHERE
      lead_id IS NOT NULL
  );

-- Step 6: Show migration results
SELECT
  'Migration completed! Results:' as status;

SELECT
  COUNT(*) as total_marketing_data_records
FROM
  lead_marketing_data;

-- Step 7: Show migrated data summary
SELECT
  'Migrated marketing data summary:' as info;

SELECT
  lmd.marketing_channel,
  COUNT(*) as count,
  COUNT(
    CASE
      WHEN lmd.marketing_channel_other IS NOT NULL
      AND lmd.marketing_channel_other != '' THEN 1
    END
  ) as with_other_details
FROM
  lead_marketing_data lmd
GROUP BY
  lmd.marketing_channel
ORDER BY
  count DESC;

-- Step 8: Show sample migrated records
SELECT
  'Sample migrated records:' as info;

SELECT
  lmd.id,
  lmd.lead_id,
  l.lead_id as lead_number,
  l.full_name,
  lmd.marketing_channel,
  lmd.marketing_channel_other,
  lmd.created_at
FROM
  lead_marketing_data lmd
  INNER JOIN leads l ON lmd.lead_id = l.id
ORDER BY
  lmd.created_at DESC
LIMIT
  10;

-- Step 9: Validation - Check for any unmapped marketing channels
SELECT
  'Validation - Check for unmapped channels:' as info;

SELECT DISTINCT
  l.hear_about as original_value,
  lmd.marketing_channel as mapped_value,
  COUNT(*) as count
FROM
  leads l
  LEFT JOIN lead_marketing_data lmd ON l.id = lmd.lead_id
WHERE
  l.hear_about IS NOT NULL
  AND l.hear_about != ''
GROUP BY
  l.hear_about,
  lmd.marketing_channel
ORDER BY
  count DESC;

-- Step 10: Show leads with multiple marketing touchpoints (if any)
SELECT
  'Leads with multiple marketing touchpoints:' as info;

SELECT
  l.id,
  l.lead_id as lead_number,
  l.full_name,
  COUNT(lmd.id) as marketing_touchpoints,
  GROUP_CONCAT (lmd.marketing_channel) as channels
FROM
  leads l
  INNER JOIN lead_marketing_data lmd ON l.id = lmd.lead_id
GROUP BY
  l.id,
  l.lead_id,
  l.full_name
HAVING
  COUNT(lmd.id) > 1
ORDER BY
  marketing_touchpoints DESC;

SELECT
  'Migration process completed successfully!' as result;

SELECT
  'Next steps: Update application code to use lead_marketing_data table' as note;

SELECT
  'Consider keeping original hear_about fields for backward compatibility initially' as recommendation;
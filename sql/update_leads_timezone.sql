-- Update timezone field for leads table based on existing address data
-- Run this SQL in phpMyAdmin to populate timezone field for existing leads
-- Update US leads with timezone based on state
UPDATE `leads`
SET
  `timezone` = CASE
  -- Pacific Time Zone
    WHEN `form_state` IN ('CA', 'WA', 'OR', 'NV') THEN 'America/Los_Angeles'
    -- Mountain Time Zone (including Arizona which doesn't observe DST)
    WHEN `form_state` = 'AZ' THEN 'America/Phoenix'
    WHEN `form_state` IN ('UT', 'CO', 'WY', 'MT', 'NM', 'ND', 'SD', 'ID') THEN 'America/Denver'
    -- Central Time Zone
    WHEN `form_state` IN (
      'TX',
      'OK',
      'KS',
      'NE',
      'MN',
      'IA',
      'MO',
      'AR',
      'LA',
      'MS',
      'AL',
      'TN',
      'KY',
      'IN',
      'IL',
      'WI'
    ) THEN 'America/Chicago'
    -- Eastern Time Zone
    WHEN `form_state` = 'MI' THEN 'America/Detroit'
    WHEN `form_state` IN (
      'OH',
      'WV',
      'VA',
      'PA',
      'NY',
      'VT',
      'NH',
      'ME',
      'MA',
      'RI',
      'CT',
      'NJ',
      'DE',
      'MD',
      'DC',
      'NC',
      'SC',
      'GA',
      'FL'
    ) THEN 'America/New_York'
    -- Alaska and Hawaii
    WHEN `form_state` = 'AK' THEN 'America/Anchorage'
    WHEN `form_state` = 'HI' THEN 'Pacific/Honolulu'
    -- Default for US if state is unknown or empty
    WHEN (
      `form_country` = 'US'
      OR `form_country` = 'USA'
      OR `form_country` = 'United States'
    ) THEN 'America/New_York'
    ELSE `timezone` -- Keep existing value if no match
  END
WHERE
  `timezone` IS NULL
  OR `timezone` = '';

-- Update non-US leads with timezone based on country
UPDATE `leads`
SET
  `timezone` = CASE
    WHEN `form_country` IN ('CA', 'Canada') THEN 'America/Toronto'
    WHEN `form_country` IN ('MX', 'Mexico') THEN 'America/Mexico_City'
    WHEN `form_country` IN ('UK', 'GB', 'United Kingdom', 'Great Britain') THEN 'Europe/London'
    WHEN `form_country` IN ('AU', 'Australia') THEN 'Australia/Sydney'
    WHEN `form_country` IN ('NZ', 'New Zealand') THEN 'Pacific/Auckland'
    WHEN `form_country` IN ('BR', 'Brazil') THEN 'America/Sao_Paulo'
    WHEN `form_country` IN ('DE', 'Germany') THEN 'Europe/Berlin'
    WHEN `form_country` IN ('FR', 'France') THEN 'Europe/Paris'
    WHEN `form_country` IN ('IT', 'Italy') THEN 'Europe/Rome'
    WHEN `form_country` IN ('ES', 'Spain') THEN 'Europe/Madrid'
    WHEN `form_country` IN ('JP', 'Japan') THEN 'Asia/Tokyo'
    WHEN `form_country` IN ('CN', 'China') THEN 'Asia/Shanghai'
    WHEN `form_country` IN ('IN', 'India') THEN 'Asia/Kolkata'
    ELSE 'UTC' -- Default to UTC for unknown countries
  END
WHERE
  (
    `timezone` IS NULL
    OR `timezone` = ''
  )
  AND `form_country` NOT IN ('US', 'USA', 'United States', '');

-- Show results summary
SELECT
  `timezone`,
  COUNT(*) as lead_count,
  CONCAT (
    ROUND(
      (
        COUNT(*) * 100.0 / (
          SELECT
            COUNT(*)
          FROM
            `leads`
        )
      ),
      2
    ),
    '%'
  ) as percentage
FROM
  `leads`
WHERE
  `timezone` IS NOT NULL
  AND `timezone` != ''
GROUP BY
  `timezone`
ORDER BY
  lead_count DESC;
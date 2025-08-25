-- Update timezone field for contacts table based on existing address data
-- Run this SQL in phpMyAdmin to populate timezone field for existing contacts
-- Update US contacts with timezone based on state
UPDATE `contacts`
SET
  `timezone` = CASE
  -- Pacific Time Zone
    WHEN `m_state` IN ('CA', 'WA', 'OR', 'NV') THEN 'America/Los_Angeles'
    -- Mountain Time Zone (including Arizona which doesn't observe DST)
    WHEN `m_state` = 'AZ' THEN 'America/Phoenix'
    WHEN `m_state` IN ('UT', 'CO', 'WY', 'MT', 'NM', 'ND', 'SD', 'ID') THEN 'America/Denver'
    -- Central Time Zone
    WHEN `m_state` IN (
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
    WHEN `m_state` = 'MI' THEN 'America/Detroit'
    WHEN `m_state` IN (
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
    WHEN `m_state` = 'AK' THEN 'America/Anchorage'
    WHEN `m_state` = 'HI' THEN 'Pacific/Honolulu'
    -- Default for US if state is unknown or empty
    WHEN (
      `m_country` = 'US'
      OR `m_country` = 'USA'
      OR `m_country` = 'United States'
    ) THEN 'America/New_York'
    ELSE `timezone` -- Keep existing value if no match
  END
WHERE
  `timezone` IS NULL
  OR `timezone` = '';

-- Update non-US contacts with timezone based on country
UPDATE `contacts`
SET
  `timezone` = CASE
    WHEN `m_country` IN ('CA', 'Canada') THEN 'America/Toronto'
    WHEN `m_country` IN ('MX', 'Mexico') THEN 'America/Mexico_City'
    WHEN `m_country` IN ('UK', 'GB', 'United Kingdom', 'Great Britain') THEN 'Europe/London'
    WHEN `m_country` IN ('AU', 'Australia') THEN 'Australia/Sydney'
    WHEN `m_country` IN ('NZ', 'New Zealand') THEN 'Pacific/Auckland'
    WHEN `m_country` IN ('BR', 'Brazil') THEN 'America/Sao_Paulo'
    WHEN `m_country` IN ('DE', 'Germany') THEN 'Europe/Berlin'
    WHEN `m_country` IN ('FR', 'France') THEN 'Europe/Paris'
    WHEN `m_country` IN ('IT', 'Italy') THEN 'Europe/Rome'
    WHEN `m_country` IN ('ES', 'Spain') THEN 'Europe/Madrid'
    WHEN `m_country` IN ('JP', 'Japan') THEN 'Asia/Tokyo'
    WHEN `m_country` IN ('CN', 'China') THEN 'Asia/Shanghai'
    WHEN `m_country` IN ('IN', 'India') THEN 'Asia/Kolkata'
    ELSE 'UTC' -- Default to UTC for unknown countries
  END
WHERE
  (
    `timezone` IS NULL
    OR `timezone` = ''
  )
  AND `m_country` NOT IN ('US', 'USA', 'United States', '');

-- Show results summary
SELECT
  `timezone`,
  COUNT(*) as contact_count,
  CONCAT (
    ROUND(
      (
        COUNT(*) * 100.0 / (
          SELECT
            COUNT(*)
          FROM
            `contacts`
        )
      ),
      2
    ),
    '%'
  ) as percentage
FROM
  `contacts`
WHERE
  `timezone` IS NOT NULL
  AND `timezone` != ''
GROUP BY
  `timezone`
ORDER BY
  contact_count DESC;
-- Parse full_address field into individual form fields for contacts table
-- Run this SQL in phpMyAdmin to populate address fields from full_address
-- First, let's check what address fields exist in contacts table
-- Note: Contacts table uses 'm_' prefix for mailing address fields
-- Update contacts where individual address fields are empty but full_address exists
UPDATE `contacts`
SET
  -- Extract street address (everything before the last comma-separated parts)
  `m_street_1` = CASE
    WHEN `full_address` IS NOT NULL
    AND `full_address` != ''
    AND (
      `m_street_1` IS NULL
      OR `m_street_1` = ''
    ) THEN TRIM(
      SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -4), ',', 1)
    )
    ELSE `m_street_1`
  END,
  -- Extract city (second to last part when split by comma)
  `m_city` = CASE
    WHEN `full_address` IS NOT NULL
    AND `full_address` != ''
    AND (
      `m_city` IS NULL
      OR `m_city` = ''
    ) THEN TRIM(
      SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -3), ',', 1)
    )
    ELSE `m_city`
  END,
  -- Extract state (look for 2-letter state codes)
  `m_state` = CASE
    WHEN `full_address` IS NOT NULL
    AND `full_address` != ''
    AND (
      `m_state` IS NULL
      OR `m_state` = ''
    ) THEN CASE
    -- Look for state in second to last part
      WHEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ',', 1)
      ) REGEXP '^[A-Z]{2}$' THEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ',', 1)
      )
      -- Look for state with zip code (e.g., "CA 90210")
      WHEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ' ', 1)
      ) REGEXP '^[A-Z]{2}$' THEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ' ', 1)
      )
      -- Look in last part before country
      WHEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -1), ' ', 1)
      ) REGEXP '^[A-Z]{2}$' THEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -1), ' ', 1)
      )
      ELSE NULL
    END
    ELSE `m_state`
  END,
  -- Extract postal code (simplified approach - extract from last parts)
  `m_postcode` = CASE
    WHEN `full_address` IS NOT NULL
    AND `full_address` != ''
    AND (
      `m_postcode` IS NULL
      OR `m_postcode` = ''
    ) THEN CASE
    -- Look for numbers in the last part (likely ZIP code)
      WHEN TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)) REGEXP '[0-9]{5}' THEN LEFT (
        TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)),
        15
      )
      -- Look for numbers in second to last part
      WHEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ',', 1)
      ) REGEXP '[0-9]{5}' THEN LEFT (
        TRIM(
          SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ',', 1)
        ),
        15
      )
      ELSE NULL
    END
    ELSE `m_postcode`
  END,
  -- Extract country (usually the last part after final comma)
  `m_country` = CASE
    WHEN `full_address` IS NOT NULL
    AND `full_address` != ''
    AND (
      `m_country` IS NULL
      OR `m_country` = ''
    ) THEN CASE
    -- If last part looks like a country (more than 2 characters, not just state+zip)
      WHEN LENGTH (TRIM(SUBSTRING_INDEX (`full_address`, ',', -1))) > 2
      AND NOT (
        TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)) REGEXP '^[A-Z]{2} [0-9]'
      ) THEN LEFT (
        TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)),
        25
      )
      -- Default to US if we found a US state
      WHEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ',', 1)
      ) REGEXP '^[A-Z]{2}$'
      OR TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ' ', 1)
      ) REGEXP '^[A-Z]{2}$' THEN 'US'
      ELSE 'US' -- Default assumption
    END
    ELSE `m_country`
  END
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != ''
  AND (
    `m_street_1` IS NULL
    OR `m_street_1` = ''
    OR `m_city` IS NULL
    OR `m_city` = ''
    OR `m_state` IS NULL
    OR `m_state` = ''
    OR `m_postcode` IS NULL
    OR `m_postcode` = ''
    OR `m_country` IS NULL
    OR `m_country` = ''
  );

-- Clean up extracted data
UPDATE `contacts`
SET
  -- Clean up street address (remove city, state, zip if they got included)
  `m_street_1` = CASE
    WHEN `m_street_1` IS NOT NULL
    AND LENGTH (`m_street_1`) > 100 THEN TRIM(SUBSTRING(`m_street_1`, 1, 100))
    ELSE `m_street_1`
  END,
  -- Standardize state codes to uppercase
  `m_state` = CASE
    WHEN `m_state` IS NOT NULL THEN UPPER(TRIM(`m_state`))
    ELSE `m_state`
  END,
  -- Clean up country names
  `m_country` = CASE
    WHEN `m_country` = 'United States'
    OR `m_country` = 'USA' THEN 'US'
    WHEN `m_country` = 'United Kingdom'
    OR `m_country` = 'UK' THEN 'GB'
    WHEN `m_country` = 'Canada' THEN 'CA'
    WHEN `m_country` IS NOT NULL THEN LEFT (UPPER(TRIM(`m_country`)), 25)
    ELSE `m_country`
  END
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != '';

-- Show results summary
SELECT
  'Parsed Address Fields' as summary,
  COUNT(*) as total_contacts,
  SUM(
    CASE
      WHEN `m_street_1` IS NOT NULL
      AND `m_street_1` != '' THEN 1
      ELSE 0
    END
  ) as has_street,
  SUM(
    CASE
      WHEN `m_city` IS NOT NULL
      AND `m_city` != '' THEN 1
      ELSE 0
    END
  ) as has_city,
  SUM(
    CASE
      WHEN `m_state` IS NOT NULL
      AND `m_state` != '' THEN 1
      ELSE 0
    END
  ) as has_state,
  SUM(
    CASE
      WHEN `m_postcode` IS NOT NULL
      AND `m_postcode` != '' THEN 1
      ELSE 0
    END
  ) as has_postcode,
  SUM(
    CASE
      WHEN `m_country` IS NOT NULL
      AND `m_country` != '' THEN 1
      ELSE 0
    END
  ) as has_country
FROM
  `contacts`
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != '';

-- Show sample of parsed addresses
SELECT
  `id`,
  `full_address`,
  `m_street_1`,
  `m_city`,
  `m_state`,
  `m_postcode`,
  `m_country`
FROM
  `contacts`
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != ''
ORDER BY
  `id` DESC
LIMIT
  10;
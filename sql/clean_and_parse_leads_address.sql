-- Clean and Parse Leads Address Data
-- This script first cleans up typos and formatting issues in full_address,
-- then parses the cleaned data into individual form fields
-- Step 1: Clean up full_address field - remove formatting issues and typos
UPDATE `leads`
SET
  `full_address` = CASE
  -- Remove carriage returns and extra whitespace
    WHEN `full_address` IS NOT NULL THEN TRIM(
      REPLACE (
        REPLACE (
          REPLACE (REPLACE (`full_address`, '\r\n', ''), '\n', ''),
          '\r',
          ''
        ),
        '  ',
        ' '
      )
    )
    ELSE `full_address`
  END
WHERE
  `full_address` IS NOT NULL;

-- Step 2: Fix common typos and standardize formatting in full_address
UPDATE `leads`
SET
  `full_address` = CASE
  -- Fix truncated country codes
    WHEN `full_address` LIKE '%, U' THEN CONCAT (
      SUBSTRING(`full_address`, 1, LENGTH (`full_address`) - 2),
      'US'
    )
    WHEN `full_address` LIKE '%, Unite' THEN CONCAT (
      SUBSTRING(`full_address`, 1, LENGTH (`full_address`) - 6),
      'US'
    )
    WHEN `full_address` LIKE '%, Canad' THEN CONCAT (
      SUBSTRING(`full_address`, 1, LENGTH (`full_address`) - 7),
      'Canada'
    )
    -- Fix spacing issues around commas
    WHEN `full_address` LIKE '%,%' THEN REPLACE (`full_address`, ',', ', ')
    -- Fix double spaces
    WHEN `full_address` LIKE '%  %' THEN REPLACE (`full_address`, '  ', ' ')
    ELSE `full_address`
  END
WHERE
  `full_address` IS NOT NULL;

-- Step 3: Additional cleanup for specific formatting issues
UPDATE `leads`
SET
  `full_address` = CASE
  -- Remove leading/trailing commas and spaces
    WHEN `full_address` LIKE ', %' THEN SUBSTRING(`full_address`, 3)
    WHEN `full_address` LIKE '% ,' THEN SUBSTRING(`full_address`, 1, LENGTH (`full_address`) - 2)
    WHEN `full_address` LIKE ' %' THEN SUBSTRING(`full_address`, 2)
    WHEN `full_address` LIKE '% ' THEN SUBSTRING(`full_address`, 1, LENGTH (`full_address`) - 1)
    -- Fix multiple consecutive commas
    WHEN `full_address` LIKE '%,,%' THEN REPLACE (`full_address`, ',,', ',')
    WHEN `full_address` LIKE '%, ,%' THEN REPLACE (`full_address`, ', ,', ',')
    ELSE `full_address`
  END
WHERE
  `full_address` IS NOT NULL;

-- Step 4: Parse cleaned full_address into individual form fields
-- Only update fields that are currently empty or NULL
UPDATE `leads`
SET
  -- Extract street address (first part before first comma)
  `form_street_1` = CASE
    WHEN `full_address` IS NOT NULL
    AND `full_address` != ''
    AND (
      `form_street_1` IS NULL
      OR `form_street_1` = ''
    ) THEN LEFT (
      TRIM(SUBSTRING_INDEX (`full_address`, ',', 1)),
      100
    )
    ELSE `form_street_1`
  END,
  -- Extract city (second part between first and second comma)
  `form_city` = CASE
    WHEN `full_address` IS NOT NULL
    AND `full_address` != ''
    AND (
      `form_city` IS NULL
      OR `form_city` = ''
    ) THEN LEFT (
      TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', 2), ',', -1)
      ),
      50
    )
    ELSE `form_city`
  END,
  -- Extract state (look for 2-letter state codes in various parts)
  `form_state` = CASE
    WHEN `full_address` IS NOT NULL
    AND `full_address` != ''
    AND (
      `form_state` IS NULL
      OR `form_state` = ''
    ) THEN CASE
    -- Look for state in second to last part (e.g., "CA 90210")
      WHEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ' ', 1)
      ) REGEXP '^[A-Z]{2}$' THEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ' ', 1)
      )
      -- Look for state as standalone second to last part
      WHEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ',', 1)
      ) REGEXP '^[A-Z]{2}$' THEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ',', 1)
      )
      -- Look in last part before country (e.g., "CO 80517 US")
      WHEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -1), ' ', 1)
      ) REGEXP '^[A-Z]{2}$' THEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -1), ' ', 1)
      )
      ELSE NULL
    END
    ELSE `form_state`
  END,
  -- Extract postal code (look for 5+ digit patterns, extract just the numbers)
  `form_postcode` = CASE
    WHEN `full_address` IS NOT NULL
    AND `full_address` != ''
    AND (
      `form_postcode` IS NULL
      OR `form_postcode` = ''
    ) THEN CASE
    -- Look for pattern like "CA 90210" in second to last part
      WHEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ',', 1)
      ) REGEXP '^[A-Z]{2} [0-9]{5}' THEN LEFT (
        TRIM(
          SUBSTRING(
            SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ',', 1),
            4
          )
        ),
        15
      )
      -- Look for pattern like "CO 80517 US" in last part
      WHEN TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)) REGEXP '^[A-Z]{2} [0-9]{5}' THEN LEFT (
        TRIM(
          SUBSTRING(SUBSTRING_INDEX (`full_address`, ',', -1), 4, 10)
        ),
        15
      )
      -- Look for standalone numbers in last part
      WHEN TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)) REGEXP '[0-9]{5}' THEN LEFT (
        TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)),
        15
      )
      -- Look for standalone numbers in second to last part
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
    ELSE `form_postcode`
  END,
  -- Extract country (usually the last part after final comma)
  `form_country` = CASE
    WHEN `full_address` IS NOT NULL
    AND `full_address` != ''
    AND (
      `form_country` IS NULL
      OR `form_country` = ''
    ) THEN CASE
    -- Look for "US" at the end of the last part
      WHEN TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)) LIKE '%US'
      AND TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)) REGEXP '[A-Z]{2} [0-9]{5} US$' THEN 'US'
      -- Look for standalone "US" as last part
      WHEN TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)) = 'US' THEN 'US'
      -- Look for "Canada" or "CA" 
      WHEN TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)) IN ('Canada', 'CA') THEN 'CA'
      -- Look for "United Kingdom", "UK", "GB"
      WHEN TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)) IN ('United Kingdom', 'UK', 'GB') THEN 'GB'
      -- If last part is just a state+zip pattern, default to US
      WHEN TRIM(SUBSTRING_INDEX (`full_address`, ',', -1)) REGEXP '^[A-Z]{2} [0-9]{5}' THEN 'US'
      -- If we found a US state anywhere, default to US
      WHEN TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -2), ' ', 1)
      ) REGEXP '^[A-Z]{2}$'
      OR TRIM(
        SUBSTRING_INDEX (SUBSTRING_INDEX (`full_address`, ',', -1), ' ', 1)
      ) REGEXP '^[A-Z]{2}$' THEN 'US'
      ELSE 'US' -- Default assumption
    END
    ELSE `form_country`
  END
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != ''
  AND (
    `form_street_1` IS NULL
    OR `form_street_1` = ''
    OR `form_city` IS NULL
    OR `form_city` = ''
    OR `form_state` IS NULL
    OR `form_state` = ''
    OR `form_postcode` IS NULL
    OR `form_postcode` = ''
    OR `form_country` IS NULL
    OR `form_country` = ''
  );

-- Step 5: Clean up extracted data and fix common issues
UPDATE `leads`
SET
  -- Clean up street address (remove city, state, zip if they got included)
  `form_street_1` = CASE
    WHEN `form_street_1` IS NOT NULL
    AND LENGTH (`form_street_1`) > 100 THEN LEFT (TRIM(`form_street_1`), 100)
    ELSE TRIM(`form_street_1`)
  END,
  -- Clean up city names
  `form_city` = CASE
    WHEN `form_city` IS NOT NULL THEN LEFT (TRIM(`form_city`), 50)
    ELSE `form_city`
  END,
  -- Standardize state codes to uppercase
  `form_state` = CASE
    WHEN `form_state` IS NOT NULL THEN LEFT (UPPER(TRIM(`form_state`)), 10)
    ELSE `form_state`
  END,
  -- Clean up postal codes
  `form_postcode` = CASE
    WHEN `form_postcode` IS NOT NULL THEN LEFT (TRIM(`form_postcode`), 15)
    ELSE `form_postcode`
  END,
  -- Clean up and standardize country names
  `form_country` = CASE
    WHEN `form_country` = 'United States'
    OR `form_country` = 'USA'
    OR `form_country` = 'Unite' THEN 'US'
    WHEN `form_country` = 'United Kingdom'
    OR `form_country` = 'UK'
    OR `form_country` = 'Unite' THEN 'GB'
    WHEN `form_country` = 'Canada'
    OR `form_country` = 'Canad' THEN 'CA'
    WHEN `form_country` IS NOT NULL THEN LEFT (UPPER(TRIM(`form_country`)), 5)
    ELSE `form_country`
  END
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != '';

-- Step 6: Fix specific data issues found in the export
UPDATE `leads`
SET
  -- Fix postcode format issues (remove country codes from postcode)
  `form_postcode` = CASE
    WHEN `form_postcode` LIKE '%US' THEN TRIM(
      SUBSTRING(`form_postcode`, 1, LENGTH (`form_postcode`) - 2)
    )
    WHEN `form_postcode` LIKE '%CA' THEN TRIM(
      SUBSTRING(`form_postcode`, 1, LENGTH (`form_postcode`) - 2)
    )
    WHEN `form_postcode` LIKE '%GB' THEN TRIM(
      SUBSTRING(`form_postcode`, 1, LENGTH (`form_postcode`) - 2)
    )
    ELSE `form_postcode`
  END,
  -- Fix state issues (some have wrong states for locations)
  `form_state` = CASE
  -- Fix Boulder, OK should be Boulder, CO
    WHEN `form_city` = 'Boulder'
    AND `form_state` = 'OK' THEN 'CO'
    -- Fix other obvious state errors based on city
    WHEN `form_city` = 'Granby'
    AND `form_state` != 'CO' THEN 'CO'
    ELSE `form_state`
  END,
  -- Fix country field for entries that should be US
  `form_country` = CASE
    WHEN `form_state` IN (
      'CA',
      'CO',
      'OK',
      'TX',
      'NY',
      'FL',
      'WA',
      'OR',
      'NV',
      'AZ',
      'UT',
      'ID',
      'MT',
      'WY',
      'ND',
      'SD',
      'NE',
      'KS',
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
      'WI',
      'MI',
      'OH',
      'WV',
      'VA',
      'NC',
      'SC',
      'GA',
      'FL',
      'MD',
      'DE',
      'NJ',
      'PA',
      'CT',
      'RI',
      'MA',
      'VT',
      'NH',
      'ME',
      'AK',
      'HI'
    ) THEN 'US'
    ELSE `form_country`
  END
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != '';

-- Step 7: Show results summary
SELECT
  'Address Parsing Results' as summary,
  COUNT(*) as total_leads,
  SUM(
    CASE
      WHEN `form_street_1` IS NOT NULL
      AND `form_street_1` != '' THEN 1
      ELSE 0
    END
  ) as has_street,
  SUM(
    CASE
      WHEN `form_city` IS NOT NULL
      AND `form_city` != '' THEN 1
      ELSE 0
    END
  ) as has_city,
  SUM(
    CASE
      WHEN `form_state` IS NOT NULL
      AND `form_state` != '' THEN 1
      ELSE 0
    END
  ) as has_state,
  SUM(
    CASE
      WHEN `form_postcode` IS NOT NULL
      AND `form_postcode` != '' THEN 1
      ELSE 0
    END
  ) as has_postcode,
  SUM(
    CASE
      WHEN `form_country` IS NOT NULL
      AND `form_country` != '' THEN 1
      ELSE 0
    END
  ) as has_country
FROM
  `leads`
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != '';

-- Step 8: Show sample of cleaned and parsed addresses
SELECT
  `id`,
  `full_address` as cleaned_full_address,
  `form_street_1`,
  `form_city`,
  `form_state`,
  `form_postcode`,
  `form_country`
FROM
  `leads`
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != ''
ORDER BY
  `id`
LIMIT
  20;

-- Step 9: Show any problematic entries that might need manual review
SELECT
  `id`,
  `full_address`,
  `form_street_1`,
  `form_city`,
  `form_state`,
  `form_postcode`,
  `form_country`,
  'Missing State' as issue
FROM
  `leads`
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != ''
  AND (
    `form_state` IS NULL
    OR `form_state` = ''
  )
UNION ALL
SELECT
  `id`,
  `full_address`,
  `form_street_1`,
  `form_city`,
  `form_state`,
  `form_postcode`,
  `form_country`,
  'Missing City' as issue
FROM
  `leads`
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != ''
  AND (
    `form_city` IS NULL
    OR `form_city` = ''
  )
UNION ALL
SELECT
  `id`,
  `full_address`,
  `form_street_1`,
  `form_city`,
  `form_state`,
  `form_postcode`,
  `form_country`,
  'Long Postcode' as issue
FROM
  `leads`
WHERE
  `full_address` IS NOT NULL
  AND `full_address` != ''
  AND LENGTH (`form_postcode`) > 10
ORDER BY
  `id`
LIMIT
  10;
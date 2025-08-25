-- Parse form_postcode field that contains "STATE ZIP" format
-- Extract state to form_state and clean postcode to contain only ZIP
-- Step 1: Update form_state from form_postcode where form_state is empty
UPDATE `leads`
SET
  `form_state` = CASE
    WHEN `form_postcode` IS NOT NULL
    AND `form_postcode` REGEXP '^[A-Z]{2} [0-9]'
    AND (
      `form_state` IS NULL
      OR `form_state` = ''
    ) THEN LEFT (TRIM(`form_postcode`), 2)
    ELSE `form_state`
  END
WHERE
  `form_postcode` IS NOT NULL
  AND `form_postcode` REGEXP '^[A-Z]{2} [0-9]';

-- Step 2: Clean form_postcode to remove state prefix and keep only ZIP code
UPDATE `leads`
SET
  `form_postcode` = CASE
    WHEN `form_postcode` IS NOT NULL
    AND `form_postcode` REGEXP '^[A-Z]{2} [0-9]' THEN TRIM(SUBSTRING(`form_postcode`, 4))
    ELSE `form_postcode`
  END
WHERE
  `form_postcode` IS NOT NULL
  AND `form_postcode` REGEXP '^[A-Z]{2} [0-9]';

-- Step 3: Additional cleanup - remove any trailing country codes from postcode
UPDATE `leads`
SET
  `form_postcode` = CASE
    WHEN `form_postcode` LIKE '% US' THEN TRIM(
      SUBSTRING(`form_postcode`, 1, LENGTH (`form_postcode`) - 3)
    )
    WHEN `form_postcode` LIKE '% CA' THEN TRIM(
      SUBSTRING(`form_postcode`, 1, LENGTH (`form_postcode`) - 3)
    )
    WHEN `form_postcode` LIKE '% GB' THEN TRIM(
      SUBSTRING(`form_postcode`, 1, LENGTH (`form_postcode`) - 3)
    )
    ELSE `form_postcode`
  END
WHERE
  `form_postcode` IS NOT NULL
  AND (
    `form_postcode` LIKE '% US'
    OR `form_postcode` LIKE '% CA'
    OR `form_postcode` LIKE '% GB'
  );

-- Step 4: Show results summary
SELECT
  'Postcode Parsing Results' as summary,
  COUNT(*) as total_leads,
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
      WHEN `form_postcode` REGEXP '^[0-9]{5}' THEN 1
      ELSE 0
    END
  ) as clean_postcodes
FROM
  `leads`;

-- Step 5: Show sample of parsed records
SELECT
  `id`,
  `full_address`,
  `form_state`,
  `form_postcode`,
  `form_country`
FROM
  `leads`
WHERE
  `form_state` IS NOT NULL
  AND `form_postcode` IS NOT NULL
ORDER BY
  `id`
LIMIT
  15;

-- Step 6: Show any problematic postcodes that might need manual review
SELECT
  `id`,
  `full_address`,
  `form_state`,
  `form_postcode`,
  'Still contains letters' as issue
FROM
  `leads`
WHERE
  `form_postcode` IS NOT NULL
  AND `form_postcode` REGEXP '[A-Z]'
  AND `form_postcode` NOT REGEXP '^[A-Z]{2} [0-9]'
ORDER BY
  `id`
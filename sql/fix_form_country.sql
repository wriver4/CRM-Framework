-- Fix form_country field - replace truncated "U" and " U" with "US"
UPDATE `leads`
SET
  `form_country` = 'US'
WHERE
  `form_country` IN ('U', ' U', '\r\n  U', '\n  U', '  U')
  OR `form_country` LIKE '%U'
  OR TRIM(`form_country`) = 'U';

-- Show results
SELECT
  'Form Country Fix Results' as summary,
  COUNT(*) as total_updated
FROM
  `leads`
WHERE
  `form_country` = 'US';

-- Show sample of updated records
SELECT
  `id`,
  `full_address`,
  `form_country`
FROM
  `leads`
WHERE
  `form_country` = 'US'
ORDER BY
  `id`
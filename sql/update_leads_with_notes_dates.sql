-- Update leads.updated_at with the newest date found in notes fields  
-- This query finds the first date (MM/DD/YY format) in notes, lead_notes, closing_notes, and lead_lost_notes
-- and sets updated_at to that newest date while preserving the existing time component
UPDATE leads
SET
    updated_at = CONCAT (
        GREATEST (
            COALESCE(
                CASE
                    WHEN notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                        REGEXP_SUBSTR (notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                        '%m/%d/%y'
                    )
                    ELSE '1900-01-01'
                END,
                '1900-01-01'
            ),
            COALESCE(
                CASE
                    WHEN lead_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                        REGEXP_SUBSTR (lead_notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                        '%m/%d/%y'
                    )
                    ELSE '1900-01-01'
                END,
                '1900-01-01'
            ),
            COALESCE(
                CASE
                    WHEN closing_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                        REGEXP_SUBSTR (closing_notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                        '%m/%d/%y'
                    )
                    ELSE '1900-01-01'
                END,
                '1900-01-01'
            ),
            COALESCE(
                CASE
                    WHEN lead_lost_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                        REGEXP_SUBSTR (lead_lost_notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                        '%m/%d/%y'
                    )
                    ELSE '1900-01-01'
                END,
                '1900-01-01'
            )
        ),
        ' ',
        TIME(updated_at)
    )
WHERE
    (
        (
            notes IS NOT NULL
            AND notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)'
        )
        OR (
            lead_notes IS NOT NULL
            AND lead_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)'
        )
        OR (
            closing_notes IS NOT NULL
            AND closing_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)'
        )
        OR (
            lead_lost_notes IS NOT NULL
            AND lead_lost_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)'
        )
    )
    AND updated_at IS NOT NULL;

-- Test query to see what dates would be found (run this first to verify)
SELECT
    id,
    estimate_number,
    updated_at as current_updated_at,
    GREATEST (
        COALESCE(
            CASE
                WHEN notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                    REGEXP_SUBSTR (notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                    '%m/%d/%y'
                )
                ELSE '1900-01-01'
            END,
            '1900-01-01'
        ),
        COALESCE(
            CASE
                WHEN lead_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                    REGEXP_SUBSTR (lead_notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                    '%m/%d/%y'
                )
                ELSE '1900-01-01'
            END,
            '1900-01-01'
        ),
        COALESCE(
            CASE
                WHEN closing_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                    REGEXP_SUBSTR (closing_notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                    '%m/%d/%y'
                )
                ELSE '1900-01-01'
            END,
            '1900-01-01'
        ),
        COALESCE(
            CASE
                WHEN lead_lost_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                    REGEXP_SUBSTR (lead_lost_notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                    '%m/%d/%y'
                )
                ELSE '1900-01-01'
            END,
            '1900-01-01'
        )
    ) as newest_date_found,
    CONCAT (
        GREATEST (
            COALESCE(
                CASE
                    WHEN notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                        REGEXP_SUBSTR (notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                        '%m/%d/%y'
                    )
                    ELSE '1900-01-01'
                END,
                '1900-01-01'
            ),
            COALESCE(
                CASE
                    WHEN lead_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                        REGEXP_SUBSTR (lead_notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                        '%m/%d/%y'
                    )
                    ELSE '1900-01-01'
                END,
                '1900-01-01'
            ),
            COALESCE(
                CASE
                    WHEN closing_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                        REGEXP_SUBSTR (closing_notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                        '%m/%d/%y'
                    )
                    ELSE '1900-01-01'
                END,
                '1900-01-01'
            ),
            COALESCE(
                CASE
                    WHEN lead_lost_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)' THEN STR_TO_DATE (
                        REGEXP_SUBSTR (lead_lost_notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}'),
                        '%m/%d/%y'
                    )
                    ELSE '1900-01-01'
                END,
                '1900-01-01'
            )
        ),
        ' ',
        TIME(updated_at)
    ) as new_updated_at,
    REGEXP_SUBSTR (notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}') as notes_date_found,
    REGEXP_SUBSTR (lead_notes, '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}') as lead_notes_date_found
FROM
    leads
WHERE
    (
        (
            notes IS NOT NULL
            AND notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)'
        )
        OR (
            lead_notes IS NOT NULL
            AND lead_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)'
        )
        OR (
            closing_notes IS NOT NULL
            AND closing_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)'
        )
        OR (
            lead_lost_notes IS NOT NULL
            AND lead_lost_notes REGEXP '[0-9]{1,2}/[0-9]{1,2}/[0-9]{2}([^0-9]|$)'
        )
    )
    AND updated_at IS NOT NULL
ORDER BY
    estimate_number
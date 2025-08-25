SELECT
    id,
    CASE
        WHEN REPLACE (
            REPLACE (REPLACE (TRIM(notes), ' ', ''), CHAR(10), ''),
            CHAR(13),
            ''
        ) = REPLACE (
            REPLACE (REPLACE (TRIM(lead_notes), ' ', ''), CHAR(10), ''),
            CHAR(13),
            ''
        ) THEN 'IDENTICAL'
        ELSE 'DIFFERENT'
    END AS comparison_result,
    notes AS original_notes,
    lead_notes AS lead_notes_field,
    REPLACE (
        REPLACE (REPLACE (TRIM(notes), ' ', ''), CHAR(10), ''),
        CHAR(13),
        ''
    ) AS normalized_notes,
    REPLACE (
        REPLACE (REPLACE (TRIM(lead_notes), ' ', ''), CHAR(10), ''),
        CHAR(13),
        ''
    ) AS normalized_lead_notes
FROM
    leads
WHERE
    notes IS NOT NULL
    OR lead_notes IS NOT NULL
ORDER BY
    id;

-- Delete identical content from lead_notes field
UPDATE leads
SET lead_notes = NULL
WHERE REPLACE (
    REPLACE (REPLACE (TRIM(notes), ' ', ''), CHAR(10), ''),
    CHAR(13),
    ''
) = REPLACE (
    REPLACE (REPLACE (TRIM(lead_notes), ' ', ''), CHAR(10), ''),
    CHAR(13),
    ''
);

 -- Compare notes and lead_notes for non-null values             │ │
  SELECT                                                          │ │
     id,                                                         │ │
 CASE                                                        │ │
 WHEN notes = lead_notes THEN 'IDENTICAL'                │ │
ELSE 'DIFFERENT'                                        │ │
│ │   331 +      END AS comparison_result,                                   │ │
│ │   332 +      notes AS original_notes,                                    │ │
│ │   333 +      lead_notes AS lead_notes_field                              │ │
│ │   334 +  FROM leads                                                      │ │
│ │   335 +  WHERE notes IS NOT NULL AND lead_notes IS NOT NULL              │ │
│ │   336 +  ORDER BY id;

SELECT
      id,
      CASE
          WHEN notes = lead_notes THEN 'IDENTICAL'
          ELSE 'DIFFERENT'
      END AS comparison_result,
      notes AS original_notes,
      lead_notes AS lead_notes_field
  FROM leads
  WHERE notes IS NOT NULL AND lead_notes IS NOT NULL
  ORDER BY id;

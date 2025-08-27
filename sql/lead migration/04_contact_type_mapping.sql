-- ===============================================
-- STEP 4: CONTACT TYPE MAPPING VALIDATION
-- File: 04_contact_type_mapping.sql
-- Purpose: Validate contact type mapping for migration
-- ===============================================

-- Create temporary mapping table for validation
CREATE TEMPORARY TABLE lead_contact_type_mapping (
    lead_contact_type INT(11),
    lead_type_description VARCHAR(100),
    contact_ctype INT(11),
    contact_type_description VARCHAR(100)
);

-- Insert mapping rules
INSERT INTO lead_contact_type_mapping VALUES
(1, 'Owner', 1, 'Primary Owner'),
(2, 'Owner with Existing System', 1, 'Primary Owner'),
(3, 'Representative', 6, 'Owner\'s Rep.'),
(4, 'Architect or Engineer', 11, 'Developer'),
(5, 'Product Dealer or Installer', 10, 'Installer');

-- Display mapping table for validation
SELECT 
    lead_contact_type,
    lead_type_description,
    contact_ctype,
    contact_type_description
FROM lead_contact_type_mapping
ORDER BY lead_contact_type;

-- Check leads that will be mapped
SELECT 
    l.contact_type as lead_contact_type,
    m.lead_type_description,
    m.contact_ctype,
    m.contact_type_description,
    COUNT(*) as lead_count
FROM leads l
LEFT JOIN lead_contact_type_mapping m ON l.contact_type = m.lead_contact_type
GROUP BY l.contact_type, m.lead_type_description, m.contact_ctype, m.contact_type_description
ORDER BY l.contact_type;

-- Check for unmapped lead contact types
SELECT 
    contact_type,
    COUNT(*) as lead_count,
    'UNMAPPED - NEEDS ATTENTION' as status
FROM leads 
WHERE contact_type NOT IN (1,2,3,4,5) 
   OR contact_type IS NULL
GROUP BY contact_type;

-- Validate business name handling for contact types
SELECT 
    contact_type,
    CASE 
        WHEN business_name IS NOT NULL AND business_name != '' THEN 'Has Business'
        ELSE 'Individual'
    END as business_status,
    COUNT(*) as count
FROM leads
GROUP BY contact_type, 
         CASE 
             WHEN business_name IS NOT NULL AND business_name != '' THEN 'Has Business'
             ELSE 'Individual'
         END
ORDER BY contact_type;
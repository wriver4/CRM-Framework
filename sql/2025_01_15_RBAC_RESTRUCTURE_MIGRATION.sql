-- =========================================================================
-- RBAC SYSTEM RESTRUCTURE MIGRATION - 2025-01-15
-- =========================================================================
-- Purpose: Implement new role structure with:
-- 1. Internal Sales (20-29)
-- 2. External Sales Partners (141-143)
-- 3. Clients moved to 150
-- 4. Support roles expanded (80-89, 90-99)
-- =========================================================================
SET
  FOREIGN_KEY_CHECKS = 0;

-- ============== STEP 1: Update existing roles ===============
-- Ensure Sales Manager exists
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    20,
    'Sales Manager',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- Ensure Partner Manager exists
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    21,
    'Partner Manager',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- Add Sales Lead (if not exists)
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    22,
    'Sales Lead',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- Add Sales User
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    25,
    'Sales User',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- Add Partner Sales
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    26,
    'Partner Sales',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 2: Add External Sales Partners (141-149) ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    141,
    'Distributor',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    142,
    'Installer',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    143,
    'Applicator',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 3: Update/Add Support Roles (80-89) ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    80,
    'Translator',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    81,
    'Technical Writer',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    82,
    'Training Specialist',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    83,
    'Support Manager',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    84,
    'Support Agent',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    85,
    'QA Specialist',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 4: Update/Add External Roles (90-99) ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    90,
    'Vendor',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    91,
    'Strategic Partner',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    92,
    'Contractor',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (93, 'Guest', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  (
    99,
    'Viewer',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 5: Add Clients at role 150 ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    150,
    'Client',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 6: Add Executive Roles (10-19) ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    10,
    'President',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (11, 'CTO', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  (12, 'CFO', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  (13, 'COO', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  (
    14,
    'VP Operations',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    15,
    'VP Sales',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    16,
    'VP Engineering',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    17,
    'VP Administration',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    18,
    'VP Manufacturing',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    19,
    'VP Field Operations',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 7: Add Engineering Roles (30-39) ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    30,
    'Engineering Manager',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    31,
    'Tech Lead',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    32,
    'Technician 1',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    33,
    'Technician 2',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    34,
    'Translator',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 8: Add Manufacturing Roles (40-49) ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    40,
    'Manufacturing Manager',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    41,
    'Production Lead',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    42,
    'Quality Lead',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    43,
    'Production Tech',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    44,
    'Quality Tech',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    47,
    'Installer',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 9: Add Field Operations Roles (50-59) ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    50,
    'Field Manager',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    51,
    'Service Lead',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    52,
    'Field Technician',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    53,
    'Installer Lead',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    54,
    'Field Installer',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 10: Add Administration Roles (60-69) ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    60,
    'HR Manager',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    61,
    'Compliance Manager',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    62,
    'Office Manager',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    63,
    'HR Specialist',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    64,
    'Compliance Officer',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 11: Add Finance Roles (70-79) ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    70,
    'Accounting Manager',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    71,
    'Bookkeeper',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    72,
    'AP/AR Clerk',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    73,
    'Accountant',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    74,
    'Finance Analyst',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    75,
    'Auditor',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 12: Keep System Maintenance Roles (1-9) ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    1,
    'Super Admin',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (2, 'Admin', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

SET
  FOREIGN_KEY_CHECKS = 1;

-- ============== VERIFICATION QUERIES ===============
-- Verify all roles were inserted
SELECT
  COUNT(*) as total_roles
FROM
  roles;

-- List all roles in order by role_id
SELECT
  role_id,
  role,
  created_at
FROM
  roles
ORDER BY
  role_id ASC;

-- Show Sales structure (20-29, 141-143)
SELECT
  role_id,
  role
FROM
  roles
WHERE
  role_id IN (15, 20, 21, 22, 23, 25, 26, 141, 142, 143)
ORDER BY
  role_id;

-- Show Client role
SELECT
  role_id,
  role
FROM
  roles
WHERE
  role_id = 150;

-- ============== END MIGRATION ===============
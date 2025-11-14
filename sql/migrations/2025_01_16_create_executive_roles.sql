-- =========================================================================
-- EXECUTIVE ROLES CREATION - 2025-01-16
-- =========================================================================
-- Purpose: Create executive leadership roles in range 10-19
-- This establishes C-level and VP positions for organizational hierarchy
-- =========================================================================
SET
  FOREIGN_KEY_CHECKS = 0;

-- ============== STEP 1: Add base Executive role ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    10,
    'Executive',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 2: Add C-level executive roles ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    11,
    'Chief Executive Officer',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    12,
    'Chief Financial Officer',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    13,
    'Chief Operating Officer',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    14,
    'Chief Technology Officer',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 3: Add VP-level executive roles ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    15,
    'VP Sales',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    16,
    'VP Operations',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    17,
    'VP Engineering',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    18,
    'VP Support',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    19,
    'VP Human Resources',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

SET
  FOREIGN_KEY_CHECKS = 1;
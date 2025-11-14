-- =========================================================================
-- CLIENT ROLES RESTORATION - 2025-01-16
-- =========================================================================
-- Purpose: Restore deleted client roles in new range (150-154)
-- Previously these were at role_ids 18-21, now moved to 150-154
-- =========================================================================
SET
  FOREIGN_KEY_CHECKS = 0;

-- ============== STEP 1: Add base Client role ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    150,
    'Client',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

-- ============== STEP 2: Add Client specialized roles ===============
INSERT IGNORE INTO `roles` (`role_id`, `role`, `created_at`, `updated_at`)
VALUES
  (
    151,
    'Client Advanced',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    152,
    'Client Standard',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    153,
    'Client Restricted',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  ),
  (
    154,
    'Client Status',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
  );

SET
  FOREIGN_KEY_CHECKS = 1;
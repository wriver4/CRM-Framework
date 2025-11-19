-- =============================================================================
-- PHASE 4: SCHEMA ENHANCEMENT - Advanced Analytics and Bulk Operations
-- Date: 2025-11-18
-- Purpose: Add tables for analytics, templates, and bulk operations
--
-- This migration adds:
-- 1. Delegation templates for common permission patterns
-- 2. Analytics and reporting infrastructure
-- 3. Conflict detection and resolution tracking
-- 4. Bulk operation audit logging
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================================
-- STEP 1: Create delegation templates table
-- =============================================================================

CREATE TABLE IF NOT EXISTS delegation_templates (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    role_id INT COMMENT 'Role this template is associated with',
    permissions_json JSON NOT NULL COMMENT 'Array of permission IDs',
    duration_days INT COMMENT 'Default duration for delegations using this template',
    usage_count INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    KEY idx_role_id (role_id),
    KEY idx_is_active (is_active),
    KEY idx_created_at (created_at),
    UNIQUE KEY uk_name (name),
    
    CONSTRAINT fk_template_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Templates for common permission delegation patterns';

-- =============================================================================
-- STEP 2: Add template reference to permission delegations
-- =============================================================================

ALTER TABLE permission_delegations ADD COLUMN IF NOT EXISTS template_id INT DEFAULT NULL AFTER granted_role_id;
ALTER TABLE permission_delegations ADD KEY IF NOT EXISTS idx_template_id (template_id);
ALTER TABLE permission_delegations ADD CONSTRAINT IF NOT EXISTS fk_delegation_template 
  FOREIGN KEY (template_id) REFERENCES delegation_templates(id) ON DELETE SET NULL;

-- =============================================================================
-- STEP 3: Create permission conflict log
-- =============================================================================

CREATE TABLE IF NOT EXISTS permission_conflicts (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    conflict_type ENUM('mutual_exclusion', 'role_conflict', 'user_conflict', 'delegation_conflict', 'circular_hierarchy', 'permission_gap') NOT NULL,
    severity ENUM('high', 'medium', 'low') DEFAULT 'medium',
    affected_user_id INT,
    affected_role_id INT,
    affected_permission_id INT,
    description TEXT,
    resolution_status ENUM('open', 'in_progress', 'resolved', 'ignored') DEFAULT 'open',
    resolution_notes TEXT,
    resolved_by_user_id INT,
    resolved_at DATETIME,
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    KEY idx_conflict_type (conflict_type),
    KEY idx_severity (severity),
    KEY idx_affected_user_id (affected_user_id),
    KEY idx_affected_role_id (affected_role_id),
    KEY idx_resolution_status (resolution_status),
    KEY idx_detected_at (detected_at),
    
    CONSTRAINT fk_conflict_user FOREIGN KEY (affected_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_conflict_role FOREIGN KEY (affected_role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_conflict_permission FOREIGN KEY (affected_permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_conflict_resolver FOREIGN KEY (resolved_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tracks permission conflicts and their resolution status';

-- =============================================================================
-- STEP 4: Create compliance report schedules table
-- =============================================================================

CREATE TABLE IF NOT EXISTS compliance_report_schedules (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    report_type ENUM('permission', 'user', 'role', 'delegation', 'comprehensive') DEFAULT 'comprehensive',
    frequency ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly') DEFAULT 'monthly',
    start_day INT DEFAULT 1 COMMENT 'Day of month/week to run',
    recipient_emails JSON,
    include_recommendations TINYINT(1) DEFAULT 1,
    last_run_at DATETIME,
    next_run_at DATETIME,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    KEY idx_frequency (frequency),
    KEY idx_is_active (is_active),
    KEY idx_next_run_at (next_run_at),
    
    UNIQUE KEY uk_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Scheduled compliance report generation';

-- =============================================================================
-- STEP 5: Create analytics summary table for performance
-- =============================================================================

CREATE TABLE IF NOT EXISTS delegation_analytics_cache (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    metric_type VARCHAR(100) NOT NULL,
    metric_value JSON NOT NULL,
    date_calculated DATE NOT NULL,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_metric_date (metric_type, date_calculated),
    KEY idx_metric_type (metric_type),
    KEY idx_date_calculated (date_calculated)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cache for analytics calculations for performance';

-- =============================================================================
-- STEP 6: Create bulk operation audit table
-- =============================================================================

CREATE TABLE IF NOT EXISTS bulk_operations (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    operation_type ENUM('assign', 'revoke', 'import', 'export', 'delegate') NOT NULL,
    initiated_by_user_id INT NOT NULL,
    target_count INT NOT NULL COMMENT 'Number of items affected',
    successful_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    status ENUM('pending', 'in_progress', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    parameters JSON COMMENT 'Parameters used for operation',
    result_summary JSON COMMENT 'Summary of results',
    error_log TEXT COMMENT 'Errors encountered',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    KEY idx_operation_type (operation_type),
    KEY idx_initiated_by_user_id (initiated_by_user_id),
    KEY idx_status (status),
    KEY idx_started_at (started_at),
    
    CONSTRAINT fk_bulk_user FOREIGN KEY (initiated_by_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Audit trail for bulk permission operations';

-- =============================================================================
-- STEP 7: Add indexes to existing tables for analytics queries
-- =============================================================================

ALTER TABLE permission_delegations ADD INDEX IF NOT EXISTS idx_created_updated (created_at, updated_at);
ALTER TABLE permission_delegations ADD INDEX IF NOT EXISTS idx_approver_status (approved_by_user_id, approval_status);
ALTER TABLE permission_delegations ADD INDEX IF NOT EXISTS idx_delegator_receiver (delegating_user_id, receiving_user_id);

ALTER TABLE permission_audit_log ADD INDEX IF NOT EXISTS idx_user_action_date (user_id, action_type, created_at);
ALTER TABLE permission_audit_log ADD INDEX IF NOT EXISTS idx_target_user_action (target_user_id, action_type, created_at);

ALTER TABLE roles_permissions ADD INDEX IF NOT EXISTS idx_role_is_active (role_id, is_active);
ALTER TABLE roles_permissions ADD INDEX IF NOT EXISTS idx_permission_is_active (pid, is_active);

-- =============================================================================
-- STEP 8: Create stored procedure for analytics
-- =============================================================================

DELIMITER //

DROP PROCEDURE IF EXISTS sp_get_delegation_summary//
CREATE PROCEDURE sp_get_delegation_summary()
BEGIN
    SELECT 
        COUNT(DISTINCT id) as total_delegations,
        COUNT(DISTINCT receiving_user_id) as unique_receivers,
        COUNT(DISTINCT delegating_user_id) as unique_delegators,
        SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN end_date > NOW() OR end_date IS NULL THEN 1 ELSE 0 END) as active_delegations
    FROM permission_delegations;
END//

DROP PROCEDURE IF EXISTS sp_get_compliance_metrics//
CREATE PROCEDURE sp_get_compliance_metrics(IN p_start_date DATETIME, IN p_end_date DATETIME)
BEGIN
    SELECT 
        COUNT(DISTINCT pal.user_id) as active_users,
        COUNT(DISTINCT pal.permission_id) as permissions_affected,
        COUNT(*) as total_actions,
        SUM(CASE WHEN pal.action_type IN ('grant', 'delegate') THEN 1 ELSE 0 END) as grant_actions,
        SUM(CASE WHEN pal.action_type = 'revoke' THEN 1 ELSE 0 END) as revoke_actions
    FROM permission_audit_log pal
    WHERE pal.created_at BETWEEN p_start_date AND p_end_date;
END//

DELIMITER ;

-- =============================================================================
-- STEP 9: Verification Queries
-- =============================================================================

SELECT 'Phase 4 Schema Enhancement Complete' as status;
SELECT COUNT(*) as delegation_template_tables FROM information_schema.tables WHERE table_name='delegation_templates' AND table_schema=database();
SELECT COUNT(*) as permission_conflicts_tables FROM information_schema.tables WHERE table_name='permission_conflicts' AND table_schema=database();
SELECT COUNT(*) as compliance_schedules_tables FROM information_schema.tables WHERE table_name='compliance_report_schedules' AND table_schema=database();

SET FOREIGN_KEY_CHECKS = 1;

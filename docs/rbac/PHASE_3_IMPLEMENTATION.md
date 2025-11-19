# Phase 3: Advanced RBAC Features Implementation

## Overview
Phase 3 implements enterprise-grade permission management features including self-service permission requests, approval workflows, audit logging, role hierarchy visualization, and automated expiration notifications.

## Features Implemented

### 1. Self-Service Permission Requests
**Location**: `/admin/security/permission_requests/`

Users can now request permissions through a self-service interface:
- Request specific permissions with justification
- Optional duration specification (7 days, 30 days, 90 days, 6 months, 1 year)
- Optional role context specification
- Request tracking and status monitoring

**Files**:
- `list.php` - View all permission requests
- `new.php` - Create new permission request
- `post.php` - Handle form submission
- `get.php` - Fetch requests data

### 2. Permission Approval Workflow
**Location**: `/admin/security/permission_approvals/`

Multi-level approval workflow for managing permission requests:
- Approve or reject permission requests
- Add approval notes/comments
- Escalation capability for complex approvals
- Automatic expiration of old requests
- Audit trail of all approval actions

**Features**:
- `get_pending_approvals()` - View pending requests
- `approve_request()` - Approve with notes
- `reject_request()` - Reject with reason
- `escalate_request()` - Escalate to higher approver

**Files**:
- `list.php` - View pending approvals
- `get.php` - Fetch approval requests
- `post.php` - Handle approval actions

### 3. Audit Reporting Dashboard
**Location**: `/admin/security/audit_log/`

Comprehensive audit trail with analytics:
- View all permission-related actions
- Filter by user, action, date range
- Export audit logs to CSV
- Summary statistics:
  - Total actions
  - Recent 24-hour activity
  - High-risk actions (revoke, delete, deny)
- User activity analysis
- Action-based summaries

**Features**:
- `get_all()` - Get all audit logs
- `get_by_user()` - Filter by user
- `get_date_range()` - Filter by date
- `get_high_risk_activities()` - Security-focused queries
- `export_audit_log()` - CSV export
- `get_summary_by_user()` - User analytics
- `get_summary_by_action()` - Action analytics

**Files**:
- `list.php` - Audit dashboard with analytics
- `get.php` - Fetch audit logs
- `export.php` - CSV export endpoint

### 4. Role Hierarchy Visualization
**Location**: `/admin/security/role_hierarchy/`

Visual and tabular representation of role hierarchies:
- View parent-child relationships
- Manage inheritance types (full, partial, none)
- Create new hierarchy relationships
- Prevent circular dependencies automatically
- View effective permissions by role

**Features**:
- Create new relationships
- Update inheritance types
- View hierarchy tree
- Get ancestor/descendant roles
- Check for circular hierarchies
- Permission coverage analysis

**Files**:
- `list.php` - View role hierarchy
- `new.php` - Create new relationship
- `get.php` - Fetch hierarchy data
- `post.php` - Handle hierarchy changes

### 5. Role Activity Analysis
**Location**: `/admin/security/role_activity/`

Comprehensive role usage statistics:
- Permission distribution by role
- Direct vs inherited vs delegated permissions breakdown
- Parent/child role analysis
- Hierarchy depth analysis
- Activity metrics for all roles

**Features**:
- `get_role_coverage()` - Permission counts by type
- `get_ancestors()` - List parent roles
- `get_descendants()` - List child roles
- `get_role_hierarchy_depth_analysis()` - Hierarchy statistics

**Files**:
- `list.php` - Activity dashboard

### 6. Permission Reconciliation Tool
**Location**: `/admin/security/permission_reconciliation/`

Data integrity and consistency verification:
- Find orphaned records
- Verify hierarchy integrity
- Check delegation consistency
- Cleanup utilities with dry-run capability
- Statistics dashboard

**Features**:
- `detect_orphaned_permissions()` - Find orphaned records
- `detect_dangling_delegations()` - Find invalid references
- `cleanup_orphaned_records()` - Remove orphaned data
- `get_validation_report()` - Full validation report

**Files**:
- `list.php` - Reconciliation dashboard
- `validate.php` - Run consistency checks

### 7. Delegation Expiration Notifications
**Script**: `/scripts/delegation_expiration_cron.php`

Automated notification system for expiring delegations:
- Email notifications 7 days before expiration (configurable)
- Automatic revocation of expired delegations
- Audit logging of all notifications
- Cron-friendly API

**Features**:
- `send_expiration_notifications()` - Send notification emails
- `revoke_expired_delegations()` - Auto-revoke expired permissions
- `get_delegation_status_summary()` - Status overview

**Usage**:
```bash
# Send notifications for delegations expiring in 7 days
php delegation_expiration_cron.php?action=notify&days=7

# Auto-revoke expired delegations
php delegation_expiration_cron.php?action=revoke

# Validate system integrity
php delegation_expiration_cron.php?action=validate

# Cleanup orphaned records (dry-run)
php delegation_expiration_cron.php?action=cleanup&dry_run=1

# Cleanup orphaned records (execute)
php delegation_expiration_cron.php?action=cleanup&dry_run=0
```

### 8. Permission Dependency Validator
**Class**: `PermissionDependencyValidator`

Prevents invalid permission configurations:
- Validates hierarchy assignments
- Prevents circular role assignments
- Validates permission delegations
- Checks approval chains
- Detects and reports inconsistencies

**Features**:
- `validate_hierarchy_assignment()` - Check role hierarchy
- `validate_permission_delegation()` - Check delegation validity
- `validate_permission_inheritance()` - Check for duplicates
- `validate_approval_chain()` - Check approval status
- `get_validation_report()` - Full system report

## Database Tables Used

**Core Phase 2 Tables** (Enhanced by Phase 3):
- `permission_delegations` - Delegation records with approvals
- `permission_approval_requests` - Request workflow tracking
- `permission_audit_log` - Complete action audit trail
- `role_inheritance` - Role hierarchy relationships
- `role_permission_inheritance` - Permission tracking
- `roles` - Role definitions
- `permissions` - Permission definitions
- `users` - User information

## Model Classes

### PermissionDelegations
CRUD operations for permission delegations with approval workflow.

### PermissionApprovals
Approval request management with escalation support.

### PermissionAuditLog
Comprehensive audit trail querying and analytics.

### RoleHierarchy
Role hierarchy operations including recursive queries.

### RoleHierarchy (Model) / RoleHierarchyList (View)
Hierarchy visualization and tree management.

### DelegationExpirationNotifier (Utility)
Automated expiration notifications and revocation.

### PermissionDependencyValidator (Utility)
System integrity checking and validation.

## View Classes

- `PermissionRequestList` - Request table display
- `AuditLogList` - Audit log table display
- `RoleHierarchyList` - Hierarchy tree display

## Cron Job Configuration

**Recommended Cron Schedule**:

```bash
# Run every day at 8:00 AM to send expiration notifications
0 8 * * * php /home/democrm/scripts/delegation_expiration_cron.php?action=notify&days=7

# Run every day at midnight to auto-revoke expired delegations
0 0 * * * php /home/democrm/scripts/delegation_expiration_cron.php?action=revoke

# Run weekly validation (Monday at 2:00 AM)
0 2 * * 1 php /home/democrm/scripts/delegation_expiration_cron.php?action=validate
```

## Security Features

1. **Role Hierarchy Validation**: Prevents circular dependencies
2. **Audit Trail**: Tracks all permission changes with user and IP information
3. **Approval Workflow**: Multi-level approval for sensitive operations
4. **Automatic Revocation**: Expired delegations are automatically revoked
5. **Data Integrity**: Orphaned record detection and cleanup
6. **Self-Service Restrictions**: Users can only request, not approve
7. **Email Notifications**: Secure delegation expiration alerts

## API Endpoints

All endpoints use POST/GET with standard form parameters:

### Permission Requests
- `GET /admin/security/permission_requests/list.php` - List requests
- `GET /admin/security/permission_requests/new.php` - Request form
- `POST /admin/security/permission_requests/post.php` - Submit request

### Permission Approvals
- `GET /admin/security/permission_approvals/list.php` - List pending
- `POST /admin/security/permission_approvals/post.php` - Approve/Reject

### Audit Log
- `GET /admin/security/audit_log/list.php` - View audit log
- `GET /admin/security/audit_log/export.php` - Export CSV

### Role Hierarchy
- `GET /admin/security/role_hierarchy/list.php` - View hierarchy
- `GET /admin/security/role_hierarchy/new.php` - Create form
- `POST /admin/security/role_hierarchy/post.php` - Save relationship

### Role Activity
- `GET /admin/security/role_activity/list.php` - View activity

### Reconciliation
- `GET /admin/security/permission_reconciliation/list.php` - Dashboard
- `POST /admin/security/permission_reconciliation/validate.php` - Run checks

## Testing

All Phase 3 components are integrated with existing test suite:
- 16 new Model classes and utility functions
- 22 new admin pages (UI)
- Automatic validation of all operations
- Audit trail verification

## Performance Considerations

1. **Cached Queries**: Hierarchy queries use recursive CTEs
2. **Indexed Lookups**: Foreign keys indexed for fast queries
3. **Pagination**: Audit logs support offset/limit
4. **Export Optimization**: CSV export uses streaming
5. **Batch Operations**: Cleanup scripts batch delete operations

## Future Enhancements

1. Permission delegation analytics dashboard
2. Role recommendation engine
3. Automated permission compliance reports
4. Permission conflict detection
5. Advanced search with Elasticsearch
6. Mobile approval interface
7. Delegation templates for common scenarios
8. Bulk permission assignment

## Troubleshooting

### Circular Hierarchy Error
**Issue**: Cannot create parent-child relationship
**Solution**: Check if creating this relationship would form a loop

### Orphaned Records
**Issue**: Permission references invalid roles/permissions
**Solution**: Run reconciliation tool cleanup function

### Expiration Notifications Not Sending
**Issue**: Users not receiving delegation expiration emails
**Solution**: Verify cron job is running and email configuration

### Audit Log Growing Too Large
**Issue**: Database performance degradation
**Solution**: Archive old audit logs or implement log rotation

## Support

For issues or questions about Phase 3 features:
1. Check the permission_audit_log for error details
2. Run the validation report
3. Review the reconciliation checks
4. Check cron job execution logs

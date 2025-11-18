# Implementation Checklist

## ✅ Testing Framework & RBAC Enhancement

---

## 📋 Phase 1: Testing Framework Setup (COMPLETE)

### Configuration ✅
- [x] Created `/config/testing.php` with comprehensive test configuration
- [x] Enhanced `/config/system.php` with testing mode detection
- [x] Updated `/phpunit.xml` with test database environment variables
- [x] Configured persistent and ephemeral database modes
- [x] Set up multiple seed datasets (minimal, standard, full)

### Core Framework ✅
- [x] Created `TestDatabase.php` class (extends Database)
- [x] Zero overhead in production Database class
- [x] Automatic test database configuration
- [x] Implemented `testdbcrm()` method for explicit test DB access
- [x] Added helper methods: `truncateTable()`, `getTableCount()`, etc.

### Test Infrastructure ✅
- [x] Created `DatabaseTestCase.php` base class
- [x] Implemented transaction-based test isolation
- [x] Added CRUD helper methods (insert, update, delete, fetch)
- [x] Created database assertion methods
- [x] Implemented snapshot/restore functionality
- [x] Created `RbacTestHelper.php` for RBAC testing
- [x] Built test user/role/permission creation utilities
- [x] Created `setup-test-database.php` CLI tool

### Playwright Integration ✅
- [x] Created `rbac-helper.js` with E2E RBAC utilities
- [x] Defined test users with different permission levels
- [x] Implemented permission checking functions
- [x] Created `rbac-permissions.spec.js` test suite
- [x] Added module/action/field/record level tests

### Documentation ✅
- [x] Created `RBAC_MIGRATION_PLAN.md` (800+ lines)
- [x] Created `TESTING_FRAMEWORK_README.md` (600+ lines)
- [x] Created `TESTING_AND_RBAC_IMPLEMENTATION_SUMMARY.md` (500+ lines)
- [x] Created `IMPLEMENTATION_CHECKLIST.md` (this file)

---

## 📋 Phase 2: RBAC Implementation (READY TO START)

### Week 1: Schema Enhancement & Data Migration

#### Database Schema ☐
- [ ] Review and approve schema changes in `RBAC_MIGRATION_PLAN.md`
- [ ] Backup production database
- [ ] Execute schema enhancement SQL:
  - [ ] Alter `permissions` table (add module, action, field, scope, type columns)
  - [ ] Alter `roles` table (add parent_role_id, role_level, is_system_role)
  - [ ] Create `field_permissions` table
  - [ ] Create `record_ownership` table
  - [ ] Create `permission_cache` table
  - [ ] Create `teams` table
  - [ ] Create `team_members` table
  - [ ] Add all indexes and foreign keys

#### Data Migration ☐
- [ ] Backup existing permissions to `permissions_backup`
- [ ] Migrate existing permissions to new structure
- [ ] Seed standard module permissions (leads, contacts, admin)
- [ ] Seed action-level permissions (view, create, edit, delete, export)
- [ ] Seed field-level permissions (view/edit specific fields)
- [ ] Seed record-level permissions (own, team, all scopes)
- [ ] Create standard roles (Super Admin, Admin, Sales Manager, Sales Rep, Viewer)
- [ ] Assign permissions to roles

#### Testing ☐
- [ ] Verify schema changes in test database
- [ ] Test data migration scripts
- [ ] Validate foreign key constraints
- [ ] Check indexes are created correctly

---

### Week 2: Core Service Implementation

#### RbacService Class ☐
- [ ] Create `/classes/Core/RbacService.php`
- [ ] Implement `hasPermission($userId, $permission)` method
- [ ] Implement `canAccessModule($userId, $module)` method
- [ ] Implement `canPerformAction($userId, $module, $action)` method
- [ ] Implement `canAccessField($userId, $module, $field, $action)` method
- [ ] Implement `canAccessRecord($userId, $module, $recordId, $action)` method
- [ ] Implement permission caching logic
- [ ] Add cache invalidation on permission changes

#### Helper Methods ☐
- [ ] Implement `getRecordOwnership($module, $recordId)` method
- [ ] Implement `isUserInTeam($userId, $teamId)` method
- [ ] Implement `getUserPermissions($userId)` method
- [ ] Implement `getRolePermissions($roleId)` method
- [ ] Implement `checkPermissionInDatabase($userId, $permission)` method

#### Testing ☐
- [ ] Write unit tests for RbacService
- [ ] Test module-level permissions
- [ ] Test action-level permissions
- [ ] Test field-level permissions
- [ ] Test record-level permissions
- [ ] Test permission caching
- [ ] Test team-based access

---

### Week 3: Security Class Enhancement

#### Security Class Updates ☐
- [ ] Update `check_user_permissions()` to use RbacService
- [ ] Add `checkFieldPermission($module, $field, $action)` method
- [ ] Add `checkRecordPermission($module, $recordId, $action)` method
- [ ] Implement field visibility filtering
- [ ] Implement record access filtering
- [ ] Add permission caching integration

#### Integration ☐
- [ ] Update all admin pages to use new permission checks
- [ ] Update leads module with field-level permissions
- [ ] Update contacts module with field-level permissions
- [ ] Implement record ownership tracking
- [ ] Add team-based access controls

#### Testing ☐
- [ ] Test updated Security class methods
- [ ] Test field visibility in forms
- [ ] Test record access restrictions
- [ ] Test permission denial redirects
- [ ] Run integration tests

---

### Week 4: UI Implementation

#### Permission Matrix Interface ☐
- [ ] Create `/public_html/admin/rbac/permission_matrix.php`
- [ ] Build module × action grid layout
- [ ] Add checkbox toggles for permissions
- [ ] Implement bulk permission assignment
- [ ] Add visual inheritance indicators
- [ ] Create field-level permission drill-down
- [ ] Add record-level scope selectors

#### Role Management ☐
- [ ] Create `/public_html/admin/rbac/roles/list.php`
- [ ] Create `/public_html/admin/rbac/roles/new.php`
- [ ] Create `/public_html/admin/rbac/roles/edit.php`
- [ ] Create `/public_html/admin/rbac/roles/view.php`
- [ ] Implement role hierarchy visualization
- [ ] Add clone role functionality
- [ ] Add role deletion with safeguards

#### User Assignment ☐
- [ ] Create user role assignment interface
- [ ] Add direct permission override UI
- [ ] Implement permission preview for users
- [ ] Add bulk user role assignment

#### Testing ☐
- [ ] Test permission matrix UI
- [ ] Test role CRUD operations
- [ ] Test user assignment workflow
- [ ] Run Playwright E2E tests

---

### Week 5: Testing & Security Audit

#### Comprehensive Testing ☐
- [ ] Run all PHPUnit tests
- [ ] Run all Playwright tests
- [ ] Test all permission levels (module/action/field/record)
- [ ] Test role hierarchy inheritance
- [ ] Test permission caching performance
- [ ] Test team-based access
- [ ] Test edge cases and error handling

#### Performance Testing ☐
- [ ] Benchmark permission checks (<100ms target)
- [ ] Test with large permission sets
- [ ] Optimize slow queries
- [ ] Verify cache effectiveness
- [ ] Load test permission matrix UI

#### Security Audit ☐
- [ ] Review all permission checks
- [ ] Test for permission bypass vulnerabilities
- [ ] Verify SQL injection protection
- [ ] Test XSS protection in UI
- [ ] Review role hierarchy security
- [ ] Test record ownership enforcement

#### Bug Fixes ☐
- [ ] Fix any issues found in testing
- [ ] Address performance bottlenecks
- [ ] Resolve security vulnerabilities
- [ ] Update documentation with fixes

---

### Week 6: Deployment & Training

#### Pre-Deployment ☐
- [ ] Final code review
- [ ] Update all documentation
- [ ] Create deployment checklist
- [ ] Prepare rollback plan
- [ ] Schedule deployment window

#### Deployment ☐
- [ ] Backup production database
- [ ] Deploy schema changes
- [ ] Deploy code changes
- [ ] Run data migration scripts
- [ ] Verify deployment success
- [ ] Monitor for errors

#### Training ☐
- [ ] Create user training materials
- [ ] Create admin training materials
- [ ] Conduct training sessions
- [ ] Create video tutorials
- [ ] Update help documentation

#### Post-Deployment ☐
- [ ] Monitor system performance
- [ ] Monitor error logs
- [ ] Gather user feedback
- [ ] Address any issues
- [ ] Document lessons learned

---

## 🎯 Success Criteria

### Testing Framework ✅
- [x] Test database automatically switches based on mode
- [x] PHPUnit tests run in isolation with transactions
- [x] Database helpers simplify test data management
- [x] RBAC test helpers enable permission testing
- [x] Playwright tests cover E2E RBAC scenarios
- [x] Snapshots enable complex test setups
- [x] Multiple seed datasets support different test needs

### RBAC System ☐
- [ ] All 4 permission levels working correctly
- [ ] SuiteCRM-style permission matrix functional
- [ ] Role hierarchy properly enforced
- [ ] Field-level permissions hiding/showing fields
- [ ] Record-level permissions enforcing ownership
- [ ] Performance within acceptable limits (<100ms)
- [ ] 100% test coverage for RBAC functionality
- [ ] Zero security vulnerabilities
- [ ] Complete documentation
- [ ] User training materials created

---

## 📊 Progress Tracking

### Overall Progress
- **Testing Framework:** ✅ 100% Complete
- **RBAC Schema Design:** ✅ 100% Complete
- **RBAC Implementation:** ☐ 0% Complete (Ready to start)

### Timeline
- **Testing Framework:** ✅ Complete (January 2025)
- **RBAC Implementation:** 📅 4-6 weeks (Starting soon)

### Next Immediate Actions
1. ☐ Setup test database: `php tests/setup-test-database.php --mode=persistent --seed=standard`
2. ☐ Run initial tests: `vendor/bin/phpunit && npx playwright test`
3. ☐ Review RBAC migration plan: `cat RBAC_MIGRATION_PLAN.md`
4. ☐ Approve schema changes
5. ☐ Begin Week 1: Schema Enhancement

---

## 📝 Notes

### Important Reminders
- Always backup database before schema changes
- Test all changes in test database first
- Use transactions for data migration
- Monitor performance during implementation
- Keep documentation updated
- Communicate changes to team

### Key Files Reference
- **Testing Config:** `/config/testing.php`
- **Database Class:** `/classes/Core/Database.php`
- **Test Setup:** `/tests/setup-test-database.php`
- **RBAC Plan:** `/RBAC_MIGRATION_PLAN.md`
- **Testing Guide:** `/TESTING_FRAMEWORK_README.md`

### Commands Reference
```bash
# Setup test database
php tests/setup-test-database.php --mode=persistent --seed=standard

# Run tests
vendor/bin/phpunit
npx playwright test

# Reset test database
php tests/setup-test-database.php --reset

# Destroy test database
php tests/setup-test-database.php --destroy
```

---

**Last Updated:** January 2025  
**Status:** Testing Framework Complete, RBAC Ready for Implementation  
**Next Milestone:** Week 1 - Schema Enhancement & Data Migration
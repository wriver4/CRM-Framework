# Testing Framework Expansion Plan

## 🎯 Goal
Build comprehensive test coverage for all DemoCRM modules with focus on:
- Core business logic (Models)
- User interface components (Views)
- Utility functions (Helpers, Services)
- Security & RBAC
- API endpoints
- Database operations

---

## 📋 Current Coverage Analysis

### ✅ Already Tested (Good Coverage)
- **Leads Module**: LeadsModelTest, LeadsListTest, LeadsIntegrationTest
- **Email Processing**: EmailFormProcessorTest, EmailAccountManagerTest, CrmSyncManagerTest
- **Calendar**: 35+ Playwright tests, CalendarIntegrationUnitTest
- **Helpers**: HelpersTest (partial)
- **RBAC**: rbac-permissions.spec.js (Playwright)
- **Language System**: language_test.php, monitor_language_errors.php

### 🔨 Needs Test Coverage

#### High Priority - Core Models (No Tests Yet)
1. **Users.php** - User management, authentication
2. **Contacts.php** - Contact CRUD operations
3. **Communications.php** - Communication tracking
4. **Notes.php** - Note management (partial coverage via NoteDeleteTest)
5. **Roles.php** - Role management
6. **Permissions.php** - Permission management
7. **RolesPermissions.php** - RBAC mapping
8. **Sales.php** - Sales tracking
9. **Prospects.php** - Prospect management
10. **CalendarEvent.php** - Calendar event operations

#### Medium Priority - Specialized Models
11. **LeadBridgeManager.php** - Lead integration
12. **LeadContracting.php** - Contract management
13. **LeadDocuments.php** - Document handling
14. **LeadMarketingData.php** - Marketing data
15. **LeadReferrals.php** - Referral tracking
16. **LeadStructureInfo.php** - Structure information
17. **Referrals.php** - General referrals
18. **EmailTemplate.php** - Email template management
19. **Languages.php** - Language management

#### Medium Priority - Utilities
20. **EmailService.php** - Email sending
21. **EmailQueueManager.php** - Email queue
22. **EmailRenderer.php** - Email rendering
23. **EmailTriggerHandler.php** - Email triggers
24. **FormComponents.php** - Form generation
25. **EditorHelper.php** - Editor utilities
26. **SummernoteManager.php** - Rich text editor
27. **PhpListApi.php** - External API integration

#### Medium Priority - Views
28. **UsersList.php** - Users list view
29. **ContactsList.php** - Contacts list view
30. **RolesList.php** - Roles list view
31. **PermissionsList.php** - Permissions list view
32. **RolesPermissionsList.php** - RBAC list view

#### High Priority - Core Classes
33. **Database.php** - Database singleton (critical!)
34. **Security.php** - Security functions
35. **Sessions.php** - Session management
36. **Nonce.php** - CSRF protection
37. **Table.php** - Base table class
38. **ActionTable.php** - Action table rendering
39. **EditDeleteTable.php** - Edit/delete table rendering
40. **ViewTable.php** - View table rendering

#### Low Priority - Logging (Already Functional)
41. **Audit.php** - Audit logging (used in tests)
42. **Logit.php** - General logging
43. **InternalErrors.php** - Error tracking
44. **PhpErrorLog.php** - PHP error logging
45. **SqlErrorLogger.php** - SQL error logging

---

## 🏗️ Implementation Strategy

### Phase 1: Core Foundation (Week 1-2)
**Goal**: Test critical infrastructure that everything depends on

#### 1.1 Core Classes Tests
```
tests/phpunit/Unit/Core/
├── DatabaseTest.php          ⭐ CRITICAL - Singleton pattern, connection management
├── SecurityTest.php          ⭐ CRITICAL - Input validation, XSS prevention
├── SessionsTest.php          ⭐ CRITICAL - Session handling
├── NonceTest.php             ⭐ CRITICAL - CSRF protection
├── TableTest.php             - Base table rendering
├── ActionTableTest.php       - Action table rendering
├── EditDeleteTableTest.php   - Edit/delete table rendering
└── ViewTableTest.php         - View table rendering
```

**Priority**: 🔴 CRITICAL - These are used by everything else

#### 1.2 Core Models Tests
```
tests/phpunit/Unit/Models/
├── UsersTest.php             ⭐ CRITICAL - Authentication, user management
├── RolesTest.php             ⭐ CRITICAL - RBAC foundation
├── PermissionsTest.php       ⭐ CRITICAL - RBAC foundation
└── RolesPermissionsTest.php  ⭐ CRITICAL - RBAC mapping
```

**Priority**: 🔴 CRITICAL - Security and access control

### Phase 2: Business Logic (Week 3-4)
**Goal**: Test main business functionality

#### 2.1 Contact Management
```
tests/phpunit/Unit/Models/
├── ContactsTest.php          - Contact CRUD
├── CommunicationsTest.php    - Communication tracking
└── NotesTest.php             - Note management (expand existing)
```

#### 2.2 Sales & Prospects
```
tests/phpunit/Unit/Models/
├── SalesTest.php             - Sales tracking
├── ProspectsTest.php         - Prospect management
└── ReferralsTest.php         - Referral tracking
```

#### 2.3 Calendar & Events
```
tests/phpunit/Unit/Models/
└── CalendarEventTest.php     - Event CRUD (expand existing)
```

### Phase 3: Specialized Features (Week 5-6)
**Goal**: Test specialized lead management features

#### 3.1 Lead Extensions
```
tests/phpunit/Unit/Models/
├── LeadBridgeManagerTest.php
├── LeadContractingTest.php
├── LeadDocumentsTest.php
├── LeadMarketingDataTest.php
├── LeadReferralsTest.php
└── LeadStructureInfoTest.php
```

### Phase 4: Utilities & Services (Week 7-8)
**Goal**: Test utility classes and services

#### 4.1 Email System
```
tests/phpunit/Unit/Utilities/
├── EmailServiceTest.php
├── EmailQueueManagerTest.php
├── EmailRendererTest.php
├── EmailTriggerHandlerTest.php
└── EmailTemplateTest.php
```

#### 4.2 UI Components
```
tests/phpunit/Unit/Utilities/
├── FormComponentsTest.php
├── EditorHelperTest.php
└── SummernoteManagerTest.php
```

#### 4.3 External Integrations
```
tests/phpunit/Unit/Utilities/
└── PhpListApiTest.php
```

### Phase 5: View Layer (Week 9-10)
**Goal**: Test view rendering and list generation

#### 5.1 List Views
```
tests/phpunit/Unit/Views/
├── UsersListTest.php
├── ContactsListTest.php
├── RolesListTest.php
├── PermissionsListTest.php
└── RolesPermissionsListTest.php
```

### Phase 6: Integration & E2E (Week 11-12)
**Goal**: Test complete workflows

#### 6.1 Integration Tests
```
tests/phpunit/Integration/
├── UserManagementIntegrationTest.php
├── ContactManagementIntegrationTest.php
├── RBACIntegrationTest.php
├── SalesWorkflowIntegrationTest.php
└── CalendarWorkflowIntegrationTest.php
```

#### 6.2 Playwright E2E Tests
```
tests/playwright/
├── users-management.spec.js
├── contacts-management.spec.js
├── rbac-workflows.spec.js
├── sales-workflow.spec.js
└── full-user-journey.spec.js
```

---

## 🎯 Test Template Standards

### Unit Test Template
```php
<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use Models\ClassName;

class ClassNameTest extends TestCase
{
    private $instance;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->instance = new ClassName();
    }
    
    protected function tearDown(): void
    {
        $this->instance = null;
        parent::tearDown();
    }
    
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(ClassName::class, $this->instance);
    }
    
    /** @test */
    public function it_has_required_methods()
    {
        $this->assertTrue(method_exists($this->instance, 'methodName'));
    }
    
    // Add specific test methods...
}
```

### Integration Test Template
```php
<?php

namespace Tests\Integration;

use Tests\DatabaseTestCase;

class FeatureIntegrationTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Setup test data
    }
    
    /** @test */
    public function it_performs_complete_workflow()
    {
        // Arrange
        // Act
        // Assert
    }
}
```

---

## 📊 Success Metrics

### Coverage Goals
- **Unit Tests**: 80%+ code coverage
- **Integration Tests**: All critical workflows covered
- **E2E Tests**: All user journeys covered
- **Performance**: All tests run in < 5 minutes

### Quality Gates
- ✅ All tests pass before deployment
- ✅ No decrease in code coverage
- ✅ All critical paths tested
- ✅ Security tests pass
- ✅ RBAC tests pass

---

## 🚀 Quick Start Commands

### Run All Tests
```bash
# PHPUnit - All tests
vendor/bin/phpunit

# PHPUnit - Specific suite
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Integration
vendor/bin/phpunit --testsuite=Feature

# Playwright - All E2E tests
npm run test:playwright

# Enhanced Integration Test
php tests/enhanced_integration_test.php --comprehensive

# Language Tests
php tests/language_test.php --comprehensive

# Performance Monitor
php tests/performance_monitor.php --full-analysis
```

### Run Specific Module Tests
```bash
# Leads module
vendor/bin/phpunit --testsuite=LeadsModule

# Email processing
vendor/bin/phpunit --testsuite=EmailProcessing

# Calendar tests
npm run test:calendar
```

### Generate Coverage Report
```bash
vendor/bin/phpunit --coverage-html coverage/
```

---

## 🔧 Tools & Infrastructure

### Required Tools
- ✅ PHPUnit 12.0+ (installed)
- ✅ Playwright (installed)
- ✅ Composer (installed)
- ✅ Node.js & npm (installed)

### Test Database
- **Database**: `democrm_test`
- **User**: `democrm_test`
- **Password**: `TestDB_2025_Secure!`
- **Setup**: `php tests/setup-test-database.php --mode=persistent --seed=standard`

### CI/CD Integration
```yaml
# .github/workflows/tests.yml
name: Tests
on: [push, pull_request]
jobs:
  phpunit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Install dependencies
        run: composer install
      - name: Run PHPUnit
        run: vendor/bin/phpunit
  
  playwright:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Install dependencies
        run: npm install
      - name: Run Playwright
        run: npm run test:playwright
```

---

## 📝 Next Steps

### Immediate Actions (This Week)
1. ✅ Review this plan
2. 🔨 Create Phase 1 test files (Core Foundation)
3. 🔨 Implement DatabaseTest.php
4. 🔨 Implement SecurityTest.php
5. 🔨 Implement SessionsTest.php
6. 🔨 Implement NonceTest.php

### Short Term (Next 2 Weeks)
1. Complete Phase 1 (Core Foundation)
2. Start Phase 2 (Business Logic)
3. Set up CI/CD pipeline
4. Generate initial coverage report

### Medium Term (Next 2 Months)
1. Complete Phases 2-4
2. Achieve 80%+ code coverage
3. Document all test patterns
4. Train team on testing practices

### Long Term (Ongoing)
1. Maintain test coverage
2. Add tests for new features
3. Refactor tests as needed
4. Monitor test performance

---

## 📚 Documentation

### Test Documentation Files
- `TESTING_FRAMEWORK_COMPLETE.md` - Complete testing guide
- `TESTING_QUICK_START.md` - Quick start guide
- `.zencoder/rules/testing-complete.md` - Testing rules
- `tests/INDEX.md` - Test database setup
- `tests/NEXT_STEPS.md` - Test setup next steps

### Writing Test Documentation
Each test file should include:
- Purpose and scope
- Setup requirements
- Test data requirements
- Expected outcomes
- Known limitations

---

## 🎓 Testing Best Practices

### General Principles
1. **Arrange-Act-Assert**: Structure all tests clearly
2. **One Assertion Per Test**: Keep tests focused
3. **Descriptive Names**: Use `it_does_something` format
4. **Independent Tests**: No test dependencies
5. **Fast Tests**: Keep unit tests under 100ms

### DemoCRM-Specific
1. **Use Translation Keys**: Test with `$lang['key']` format
2. **Test RBAC**: Always test permission checks
3. **Test Audit Logging**: Verify audit trail creation
4. **Test Input Validation**: Security first!
5. **Test Database Transactions**: Use test database

### Security Testing
1. **SQL Injection**: Test prepared statements
2. **XSS Prevention**: Test output escaping
3. **CSRF Protection**: Test nonce validation
4. **Authentication**: Test session handling
5. **Authorization**: Test RBAC enforcement

---

**Last Updated**: 2025-01-12
**Status**: 📋 Planning Phase
**Next Review**: After Phase 1 completion
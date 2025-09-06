# Email Processing System - Testing Guide

## Overview
Comprehensive testing suite for the WaveGuard Email Processing System, covering unit tests, integration tests, feature tests, and end-to-end testing.

## Test Structure

### 1. Unit Tests (`tests/phpunit/Unit/`)
Tests individual components in isolation:

#### `EmailFormProcessorTest.php`
- **Purpose**: Tests email parsing, form data validation, and lead generation
- **Key Tests**:
  - `testParseEstimateForm()` - Parses estimate form emails
  - `testParseLtrForm()` - Parses LTR form emails  
  - `testParseContactForm()` - Parses contact form emails
  - `testValidateFormData()` - Validates extracted form data
  - `testDetectDuplicateEmail()` - Duplicate detection logic
  - `testGenerateLeadData()` - Lead data generation
  - `testErrorHandling()` - Error handling scenarios

#### `EmailAccountManagerTest.php`
- **Purpose**: Tests email account configuration and management
- **Key Tests**:
  - `testValidateAccountConfig()` - Account configuration validation
  - `testEncryptDecryptPassword()` - Password encryption/decryption
  - `testValidateEmailAddress()` - Email address validation
  - `testValidateImapSettings()` - IMAP configuration validation
  - `testGenerateAccountData()` - Account data generation

#### `CrmSyncManagerTest.php`
- **Purpose**: Tests CRM synchronization logic
- **Key Tests**:
  - `testValidateSyncData()` - Sync data validation
  - `testValidateSyncAction()` - Sync action validation
  - `testRetryLogic()` - Retry mechanism testing
  - `testSyncDataSerialization()` - JSON serialization/deserialization
  - `testSyncStatusTransitions()` - Status transition validation

### 2. Integration Tests (`tests/phpunit/Integration/`)

#### `EmailProcessingIntegrationTest.php`
- **Purpose**: Tests component interactions and database operations
- **Key Tests**:
  - `testDatabaseTablesExist()` - Verifies required tables exist
  - `testEmailAccountConfigCRUD()` - Email account CRUD operations
  - `testEmailProcessingLogCRUD()` - Processing log CRUD operations
  - `testCrmSyncQueueCRUD()` - Sync queue CRUD operations
  - `testFullEmailProcessingWorkflow()` - End-to-end workflow validation
  - `testApiEndpointStructure()` - API endpoint validation
  - `testWebInterfaceFiles()` - Web interface file existence

### 3. Feature Tests (`tests/phpunit/Feature/`)

#### `EmailProcessingWorkflowTest.php`
- **Purpose**: Tests complete business workflows
- **Key Tests**:
  - `testCompleteEmailProcessingWorkflow()` - Full email-to-CRM workflow
  - `testEmailProcessingStatistics()` - Statistics generation
  - `testSyncQueueStatistics()` - Sync queue metrics
  - `testSystemHealthChecks()` - System health validation
  - `testApiEndpointFunctionality()` - API functionality

### 4. End-to-End Tests (`tests/playwright/`)

#### `email-processing.spec.js`
- **Purpose**: Tests user interface and user workflows
- **Key Tests**:
  - Navigation to all email processing pages
  - Form interactions and validation
  - Filter functionality
  - Mobile responsiveness
  - Accessibility features
  - Error handling
  - API endpoint testing

## Running Tests

### PHPUnit Tests

**Run All Tests:**
```bash
# Local development
./vendor/bin/phpunit

# Remote server
php phpunit.phar
```

**Run Specific Test Suites:**
```bash
# Unit tests only
./vendor/bin/phpunit --testsuite=Unit

# Integration tests only
./vendor/bin/phpunit --testsuite=Integration

# Feature tests only
./vendor/bin/phpunit --testsuite=Feature

# Email processing tests only
./vendor/bin/phpunit --testsuite=EmailProcessing
```

**Run Individual Test Files:**
```bash
# Email form processor tests
./vendor/bin/phpunit tests/phpunit/Unit/EmailFormProcessorTest.php

# Integration tests
./vendor/bin/phpunit tests/phpunit/Integration/EmailProcessingIntegrationTest.php

# Workflow tests
./vendor/bin/phpunit tests/phpunit/Feature/EmailProcessingWorkflowTest.php
```

### Playwright E2E Tests

**Run All E2E Tests:**
```bash
npx playwright test
```

**Run Email Processing Tests Only:**
```bash
npx playwright test email-processing.spec.js
```

**Run with UI Mode:**
```bash
npx playwright test --ui
```

**Run in Headed Mode:**
```bash
npx playwright test --headed
```

## Test Data Management

### Database Test Data
- Tests create temporary test data during execution
- All test data is cleaned up in `tearDown()` methods
- Test data uses identifiable prefixes (`test-`, `integration-`, `workflow-`)

### Test Isolation
- Each test is independent and can run in isolation
- Database transactions are used where possible
- Cleanup methods ensure no test data persists

## Test Coverage Areas

### 1. Email Processing Core
- ✅ Email parsing for all form types (estimate, ltr, contact)
- ✅ Form data validation and sanitization
- ✅ Duplicate detection mechanisms
- ✅ Lead creation and updates
- ✅ Error handling and logging

### 2. Email Account Management
- ✅ Account configuration validation
- ✅ IMAP settings validation
- ✅ Password encryption/decryption
- ✅ Account status management
- ✅ Connection string generation

### 3. CRM Synchronization
- ✅ Sync queue management
- ✅ Retry logic and exponential backoff
- ✅ Status transitions
- ✅ External system integration points
- ✅ Data serialization

### 4. Database Operations
- ✅ Table structure validation
- ✅ CRUD operations for all entities
- ✅ Foreign key relationships
- ✅ Data integrity constraints
- ✅ Query performance

### 5. Web Interface
- ✅ Page navigation and routing
- ✅ Form submissions and validation
- ✅ Filter and search functionality
- ✅ Data display and formatting
- ✅ Error message display

### 6. API Endpoints
- ✅ Authentication and authorization
- ✅ Request/response formats
- ✅ Error handling
- ✅ Rate limiting (if implemented)
- ✅ Data validation

### 7. System Integration
- ✅ Complete workflow testing
- ✅ Cross-component communication
- ✅ Performance under load
- ✅ Error propagation
- ✅ Logging and monitoring

## Test Environment Setup

### Prerequisites
1. **Database**: Test database with email processing tables
2. **PHP Extensions**: PDO, JSON, IMAP (for full functionality)
3. **Composer**: For PHPUnit dependencies
4. **Node.js**: For Playwright tests
5. **Web Server**: For E2E testing

### Configuration
1. **PHPUnit**: Configured in `phpunit.xml`
2. **Playwright**: Configured in `playwright.config.js`
3. **Database**: Uses same connection as main application
4. **Environment**: Set `APP_ENV=testing` for test runs

## Continuous Integration

### Test Automation
- Tests can be run automatically on code changes
- Both PHPUnit and Playwright tests are CI-friendly
- Test results are available in multiple formats (HTML, JSON, JUnit XML)

### Performance Monitoring
- Tests include performance assertions where appropriate
- Database query performance is monitored
- API response times are validated

## Troubleshooting

### Common Issues

**Database Connection Errors:**
- Verify database credentials in `classes/Core/Database.php`
- Ensure test database has required tables
- Check database permissions

**Missing Dependencies:**
- Run `composer install` for PHPUnit dependencies
- Run `npm install` for Playwright dependencies
- Verify PHP extensions are installed

**Test Failures:**
- Check error logs in `logs/php_errors.log`
- Verify test data cleanup is working
- Ensure proper test isolation

**Playwright Issues:**
- Verify base URL in `playwright.config.js`
- Check browser installation: `npx playwright install`
- Ensure web server is running for E2E tests

### Debug Mode
```bash
# PHPUnit with verbose output
./vendor/bin/phpunit --verbose

# Playwright with debug mode
PWDEBUG=1 npx playwright test

# Playwright with trace
npx playwright test --trace on
```

## Test Metrics

### Coverage Goals
- **Unit Tests**: 90%+ code coverage for core classes
- **Integration Tests**: 100% database operation coverage
- **Feature Tests**: 100% workflow coverage
- **E2E Tests**: 100% user interface coverage

### Performance Benchmarks
- **Unit Tests**: < 1 second per test
- **Integration Tests**: < 5 seconds per test
- **Feature Tests**: < 10 seconds per test
- **E2E Tests**: < 30 seconds per test

## Maintenance

### Regular Tasks
1. **Update test data** when schema changes
2. **Review test coverage** after new features
3. **Update E2E tests** when UI changes
4. **Validate test performance** regularly

### Test Review Process
1. **Code Review**: All test code should be reviewed
2. **Test Validation**: New tests should be validated against requirements
3. **Performance Review**: Test execution time should be monitored
4. **Documentation**: Test documentation should be kept current

## Success Criteria

### Test Suite Health
- ✅ All tests pass consistently
- ✅ Test execution time within benchmarks
- ✅ High code coverage maintained
- ✅ No flaky or intermittent failures
- ✅ Clear test failure messages
- ✅ Comprehensive error scenario coverage

The email processing system testing framework provides comprehensive coverage of all functionality, ensuring reliability and maintainability of the email processing features.
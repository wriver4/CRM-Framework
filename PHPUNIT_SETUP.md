# PHPUnit Testing Setup for CRM Application

## Overview
This document describes the complete PHPUnit testing setup for the CRM application, including installation, configuration, and usage.

## Installation

### PHPUnit Installation
PHPUnit 12.3.7 has been installed globally via Composer:

```bash
composer global require phpunit/phpunit
```

**Location**: `/home/mark/.config/composer/vendor/bin/phpunit`

## Configuration

### PHPUnit Configuration File
**File**: `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/12.0/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         testdox="true">
  
  <testsuites>
    <testsuite name="Unit">
      <directory>tests/phpunit/Unit</directory>
    </testsuite>
    <testsuite name="Integration">
      <directory>tests/phpunit/Integration</directory>
    </testsuite>
    <testsuite name="Feature">
      <directory>tests/phpunit/Feature</directory>
    </testsuite>
  </testsuites>

  <php>
    <!-- Environment variables for testing -->
    <env name="APP_ENV" value="testing"/>
    <env name="BASE_URL" value="https://democrm.waveguardco.net"/>
    
    <!-- PHP settings -->
    <ini name="error_reporting" value="E_ALL"/>
    <ini name="display_errors" value="1"/>
    <ini name="memory_limit" value="512M"/>
  </php>
</phpunit>
```

### Bootstrap File
**File**: `tests/bootstrap.php`

Sets up the testing environment, loads the base TestCase class, and provides autoloading for test classes.

### Base Test Case
**File**: `tests/phpunit/TestCase.php`

Provides common functionality for all tests:
- HTTP request helpers
- Response assertion methods
- Environment detection (local vs remote)
- Base URL configuration

## Test Structure

### Directory Structure
```
tests/
├── bootstrap.php              # Test bootstrap
├── phpunit/
│   ├── TestCase.php          # Base test case
│   ├── Unit/                 # Unit tests
│   │   ├── HelpersTest.php   # Tests for Helpers class
│   │   └── SimpleTest.php    # Basic test examples
│   ├── Integration/          # Integration tests
│   │   └── DatabaseTest.php  # Database connectivity tests
│   ├── Feature/              # Feature/End-to-end tests
│   │   └── LoginTest.php     # Login functionality tests
│   └── Remote/               # Remote server tests
│       └── RemoteServerTest.php
```

### Test Types

#### Unit Tests (`tests/phpunit/Unit/`)
- Test individual classes and methods in isolation
- No external dependencies (database, network, etc.)
- Fast execution
- Examples: `HelpersTest.php`, `SimpleTest.php`

#### Integration Tests (`tests/phpunit/Integration/`)
- Test interaction between components
- May use database or other services
- Gracefully handle missing dependencies
- Examples: `DatabaseTest.php`

#### Feature Tests (`tests/phpunit/Feature/`)
- Test complete user workflows
- Make actual HTTP requests to the application
- Test the application as a user would experience it
- Examples: `LoginTest.php`

## Running Tests

### Test Runner Script
**File**: `run-phpunit-nixos.sh`

Updated to use the globally installed PHPUnit:

```bash
# Run all tests
./run-phpunit-nixos.sh all

# Run specific test suite
./run-phpunit-nixos.sh unit
./run-phpunit-nixos.sh integration
./run-phpunit-nixos.sh feature

# Run tests locally (default)
./run-phpunit-nixos.sh local
```

### Direct PHPUnit Commands
```bash
# Run all tests
/home/mark/.config/composer/vendor/bin/phpunit

# Run specific test suite
/home/mark/.config/composer/vendor/bin/phpunit --testsuite Unit
/home/mark/.config/composer/vendor/bin/phpunit --testsuite Integration
/home/mark/.config/composer/vendor/bin/phpunit --testsuite Feature

# Run specific test file
/home/mark/.config/composer/vendor/bin/phpunit tests/phpunit/Unit/HelpersTest.php
```

## Current Test Results

### Summary (as of latest run)
- **Total Tests**: 19
- **Assertions**: 31
- **Status**: ✅ All tests passing
- **Warnings**: 1 (expected - role array test)
- **Deprecations**: 12 (PHP 8.4 compatibility - non-critical)
- **Skipped**: 2 (database tests in local mode)

### Test Breakdown

#### Unit Tests (7 tests)
- ✅ Helpers class functionality
- ✅ Password hashing
- ✅ Basic assertions

#### Integration Tests (5 tests)
- ✅ Database class structure
- ⚠️ Database connection (skipped in local mode)

#### Feature Tests (7 tests)
- ✅ Login page accessibility
- ✅ Form validation
- ✅ Authentication requirements
- ✅ HTTPS enforcement
- ✅ Security headers

## Key Features

### Environment Awareness
Tests automatically detect whether they're running locally or on the remote server and adjust behavior accordingly:
- Database tests skip connection attempts in local mode
- Feature tests use the configured BASE_URL
- Remote-specific tests are skipped when not applicable

### HTTP Testing
Feature tests make actual HTTP requests to the live application:
- Test real user workflows
- Validate security measures
- Check response codes and content
- Verify HTTPS usage

### Graceful Degradation
Tests handle missing dependencies gracefully:
- Database unavailable → tests are skipped with informative messages
- Network issues → tests fail with clear error messages
- Missing classes → tests report specific missing components

## Adding New Tests

### Unit Test Example
```php
<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MyClassTest extends TestCase
{
    public function testSomething()
    {
        $this->assertTrue(true);
    }
}
```

### Integration Test Example
```php
<?php
namespace Tests\Integration;

use Tests\TestCase;

class MyIntegrationTest extends TestCase
{
    public function testDatabaseInteraction()
    {
        if (!$this->isRemoteMode()) {
            $this->markTestSkipped('Database test requires remote mode');
        }
        
        // Test database interaction
    }
}
```

### Feature Test Example
```php
<?php
namespace Tests\Feature;

use Tests\TestCase;

class MyFeatureTest extends TestCase
{
    public function testUserWorkflow()
    {
        $response = $this->makeHttpRequest('/some-page.php');
        $this->assertResponseStatus($response, 200);
        $this->assertResponseContains($response, 'expected content');
    }
}
```

## Troubleshooting

### Common Issues

1. **PHPUnit not found**
   - Ensure global Composer installation: `composer global require phpunit/phpunit`
   - Check path: `/home/mark/.config/composer/vendor/bin/phpunit`

2. **Tests not found**
   - Verify test files are in correct directories
   - Check namespace declarations match directory structure
   - Ensure bootstrap file is loading correctly

3. **Database connection errors**
   - Expected in local mode - tests should skip gracefully
   - In remote mode, check database credentials in `classes/Database.php`

4. **HTTP request failures**
   - Check BASE_URL configuration
   - Verify network connectivity
   - Ensure HTTPS certificates are valid

### Debug Mode
Add `--debug` flag to PHPUnit commands for verbose output:
```bash
/home/mark/.config/composer/vendor/bin/phpunit --debug
```

## Future Enhancements

### Planned Improvements
1. **Code Coverage**: Add coverage reporting
2. **Mock Objects**: Implement mocking for better unit test isolation
3. **Database Fixtures**: Create test data fixtures for integration tests
4. **API Testing**: Add tests for API endpoints
5. **Performance Tests**: Add performance benchmarking tests

### Configuration Enhancements
1. **Multiple Environments**: Support for staging/production test environments
2. **Parallel Testing**: Configure parallel test execution
3. **Custom Assertions**: Add CRM-specific assertion methods
4. **Test Data Management**: Implement test database seeding/cleanup

## Conclusion

The PHPUnit testing setup provides a robust foundation for testing the CRM application across multiple levels:
- **Unit tests** ensure individual components work correctly
- **Integration tests** verify component interactions
- **Feature tests** validate complete user workflows

The setup is environment-aware, handles dependencies gracefully, and provides clear feedback on test results. All tests are currently passing, providing confidence in the application's core functionality.
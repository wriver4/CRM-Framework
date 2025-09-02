# Testing Guide for CRM Framework

This guide explains how to leverage the existing testing frameworks for specific issues in the CRM project.

## Overview

The project includes two comprehensive testing systems:
- **Playwright** - End-to-end web interface testing
- **PHPUnit** - Unit, integration, and feature testing of PHP classes

## 1. Playwright (End-to-End Web Testing)

### For UI/Browser Issues

```bash
# Test specific functionality
npx playwright test --grep "login"           # Test login functionality
npx playwright test --grep "navigation"      # Test navigation issues
npx playwright test --grep "leads"           # Test leads-related UI

# Test specific browsers
npx playwright test --project=chromium       # Chrome-specific issues
npx playwright test --project=webkit         # Safari-specific issues

# Run with debugging
npx playwright test --debug                  # Step through tests
npx playwright test --headed                 # See browser actions
```

### Example Test Creation

For UI issues like contact dropdowns not loading:

```javascript
// tests/playwright/leads-contacts.spec.js
test('leads edit page loads contacts dropdown', async ({ page }) => {
  await page.goto('/leads/edit.php?id=1');
  await expect(page.locator('#contact_selector')).toBeVisible();
  await expect(page.locator('#contact_selector option')).toHaveCount.greaterThan(0);
});
```

## 2. PHPUnit (Backend/Logic Testing)

### For Database/Class Issues

```bash
# Test specific classes
./vendor/bin/phpunit tests/phpunit/Unit/ContactsTest.php

# Test database integration
./vendor/bin/phpunit tests/phpunit/Integration/DatabaseTest.php

# Test specific functionality
./vendor/bin/phpunit --filter testGetContactsByLeadId
```

### Example Test Creation

For database/class issues like table name mismatches:

```php
// tests/phpunit/Unit/ContactsTest.php
public function testGetContactsByLeadIdUsesCorrectTable()
{
    $contacts = new Contacts();
    $result = $contacts->get_contacts_by_lead_id(1);
    
    // Should not throw "table doesn't exist" exception
    $this->assertIsArray($result);
}
```

## 3. How to Request Testing for Specific Issues

### When Reporting Issues

Use this format:
```
"I'm having an issue with [specific functionality]. Can you:
1. Create a test that reproduces the problem
2. Fix the issue
3. Verify the fix with the test"
```

### Example Requests

**For Database Issues:**
```
"The leads list page is showing an SQL error. Can you create a PHPUnit test 
that verifies the Leads::get_active_list() method works correctly?"
```

**For UI Issues:**
```
"The contact form isn't submitting properly. Can you create a Playwright test 
that fills out and submits the contact form, then verify it was saved?"
```

**For Integration Issues:**
```
"When creating a new lead, the contact isn't being linked properly. Can you 
create tests that verify the entire lead creation workflow?"
```

## 4. Existing Test Structure

### Current Test Files

- `tests/playwright/authenticated-tests.spec.js` - For logged-in functionality
- `tests/phpunit/Unit/HelpersTest.php` - For utility function testing
- `tests/phpunit/Integration/DatabaseTest.php` - For database connectivity
- `tests/phpunit/Feature/LoginTest.php` - For complete user workflows

### Test Utilities Available

- `tests/playwright/auth-helper.js` - Login automation
- `tests/phpunit/TestCase.php` - Base test class with utilities
- `tests/create_test_users.php` - Test user creation
- `tests/verify_test_login.php` - Login verification

## 5. Best Practices for Issue-Specific Testing

### When You Encounter an Issue

1. **Identify the Layer:**
   - Frontend/UI → Playwright
   - Backend Logic → PHPUnit Unit
   - Database → PHPUnit Integration
   - Full Workflow → PHPUnit Feature + Playwright

2. **Request Format:**
```
"Issue: [Brief description]
Expected: [What should happen]
Actual: [What's happening]
Test Request: Create [type] test that [specific verification]"
```

3. **Example for Table Name Issue:**
```
"Issue: Leads edit page crashes with table not found error
Expected: Page should load with contact dropdown
Actual: PDO exception about lead_contacts table
Test Request: Create PHPUnit test that verifies Contacts::get_contacts_by_lead_id() 
uses correct table name and Playwright test that verifies edit page loads"
```

## 6. Running Tests in Development Workflow

### Before Making Changes
```bash
# Run existing tests to establish baseline
./vendor/bin/phpunit
npx playwright test
```

### After Making Changes
```bash
# Run specific tests related to your changes
./vendor/bin/phpunit --filter Contact
npx playwright test --grep "contact"
```

### For Continuous Testing
```bash
# Watch for changes and re-run tests
npx playwright test --watch
```

## 7. Test Categories by Issue Type

### Database Issues
- **Test Type:** PHPUnit Integration
- **Location:** `tests/phpunit/Integration/`
- **Focus:** SQL queries, table existence, data integrity

### Class Logic Issues
- **Test Type:** PHPUnit Unit
- **Location:** `tests/phpunit/Unit/`
- **Focus:** Method behavior, return values, error handling

### User Interface Issues
- **Test Type:** Playwright
- **Location:** `tests/playwright/`
- **Focus:** Element visibility, form submission, navigation

### Complete Workflow Issues
- **Test Type:** PHPUnit Feature + Playwright
- **Location:** `tests/phpunit/Feature/` + `tests/playwright/`
- **Focus:** End-to-end user scenarios

## 8. Common Test Patterns

### Testing Database Methods
```php
public function testMethodDoesNotThrowException()
{
    $class = new ClassName();
    $result = $class->methodName($validInput);
    $this->assertIsArray($result);
}
```

### Testing UI Elements
```javascript
test('element is visible and functional', async ({ page }) => {
  await page.goto('/path/to/page');
  await expect(page.locator('#element')).toBeVisible();
  await page.click('#element');
  // Assert expected behavior
});
```

### Testing Form Submissions
```javascript
test('form submits successfully', async ({ page }) => {
  await page.goto('/form/page');
  await page.fill('#input', 'test value');
  await page.click('#submit');
  await expect(page).toHaveURL(/success/);
});
```

## 9. Debugging Failed Tests

### PHPUnit Debugging
```bash
# Run with verbose output
./vendor/bin/phpunit --verbose

# Run specific test with debug info
./vendor/bin/phpunit --filter testName --debug
```

### Playwright Debugging
```bash
# Run in headed mode to see browser
npx playwright test --headed

# Run with debug mode for step-by-step
npx playwright test --debug

# Generate trace for analysis
npx playwright test --trace on
```

This approach ensures that fixes are properly tested and won't regress in the future!
# CRM Playwright Testing Setup

## Overview
This setup allows you to run Playwright tests on your local NixOS machine to test the remote CRM application at `https://democrm.waveguardco.net`.

## Local Setup Instructions

### 1. Copy Files to Local Machine
```bash
# On your local NixOS machine
mkdir ~/crm-playwright-tests
cd ~/crm-playwright-tests

# Copy configuration files
scp -P 222 user@159.203.116.150:/home/democrm/playwright-local.config.js ./playwright.config.js
scp -P 222 user@159.203.116.150:/home/democrm/run-tests-nixos.sh ./run-tests.sh
chmod +x run-tests.sh

# Copy test files
mkdir -p tests/playwright
scp -P 222 user@159.203.116.150:/home/democrm/tests/playwright/*.js ./tests/playwright/
```

### 2. Install Dependencies
```bash
# Initialize npm project
npm init -y

# Install Playwright
npm install @playwright/test

# Install browser binaries
npx playwright install
```

### 3. Run Tests
```bash
# Run all tests
./run-tests.sh test

# Run with visible browser
./run-tests.sh headed

# Run with Playwright UI
./run-tests.sh ui

# Run in debug mode
./run-tests.sh debug

# View test report
./run-tests.sh report
```

## Test Files Created

### 1. `login.spec.js`
- Tests login page loading
- Form validation
- Invalid login attempts
- Placeholder for valid login test (needs credentials)

### 2. `navigation.spec.js`
- Homepage accessibility
- Navigation structure
- Main page accessibility (leads, contacts, users)

### 3. `responsive.spec.js`
- Mobile, tablet, desktop viewport testing
- Responsive navigation
- Layout spacing

### 4. `accessibility.spec.js`
- Form labels and ARIA attributes
- Heading structure
- Image alt text
- Keyboard navigation
- Color contrast basics

## Configuration

The tests are configured to run against:
- **Base URL**: `https://democrm.waveguardco.net`
- **Browsers**: Chromium, Firefox, WebKit
- **Mobile**: Pixel 5, iPhone 12
- **Screenshots**: On failure
- **Videos**: On failure
- **Traces**: On retry

## Adding New Tests

Create new test files in `tests/playwright/` following this pattern:

```javascript
// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Feature Name Tests', () => {
  test('test description', async ({ page }) => {
    await page.goto('/path');
    await expect(page.locator('selector')).toBeVisible();
  });
});
```

## Common Commands

```bash
# Run specific test file
npx playwright test login.spec.js

# Run tests matching pattern
npx playwright test --grep "login"

# Run only Chromium tests
npx playwright test --project=chromium

# Generate test code
npx playwright codegen https://democrm.waveguardco.net

# Update snapshots
npx playwright test --update-snapshots
```

## Debugging

1. **Use headed mode**: `./run-tests.sh headed`
2. **Use debug mode**: `./run-tests.sh debug`
3. **Use Playwright UI**: `./run-tests.sh ui`
4. **Check screenshots**: Look in `test-results/` folder
5. **View traces**: Use `npx playwright show-trace trace.zip`

## Authentication Testing

To test authenticated features:

1. Update the skipped test in `login.spec.js` with valid test credentials
2. Create authenticated test context:

```javascript
// Create authenticated context
const context = await browser.newContext();
const page = await context.newPage();

// Login
await page.goto('/login.php');
await page.fill('input[name="username"]', 'test_user');
await page.fill('input[name="password"]', 'test_password');
await page.click('input[type="submit"]');

// Now test authenticated features
await page.goto('/leads/list.php');
// ... test authenticated functionality
```

## Continuous Integration

For CI/CD integration, the tests can be run with:
```bash
CI=true npx playwright test --reporter=json
```

## Troubleshooting

### Browser Installation Issues
```bash
# Reinstall browsers
npx playwright install --force

# Check browser status
npx playwright install --dry-run
```

### Network Issues
- Check if `https://democrm.waveguardco.net` is accessible
- Verify SSL certificates
- Check firewall settings

### Test Failures
- Review screenshots in `test-results/`
- Check console logs in test output
- Use debug mode to step through tests

## Next Steps

1. **Add Authentication**: Create test user credentials and enable authenticated tests
2. **Expand Coverage**: Add tests for CRUD operations (leads, contacts, users)
3. **Performance Testing**: Add tests for page load times
4. **API Testing**: Add tests for AJAX endpoints
5. **Database Testing**: Add tests that verify data persistence
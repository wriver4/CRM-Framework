# CRM Playwright Testing Guide

## 🎯 Overview

This guide explains how to run comprehensive browser tests for the CRM system using Playwright on your local machine.

## 📋 Prerequisites

- Node.js 18+ installed locally
- Playwright installed (`npm install @playwright/test`)
- All test files copied from server

## 🔧 Setup

### 1. Copy Test Files to Local Machine

```bash
# Create local test directory
mkdir -p ./tests/playwright

# Copy all test files from server
scp wswg:/home/democrm/tests/playwright/*.js ./tests/playwright/
scp wswg:/home/democrm/playwright.config.js ./

# Make setup script executable
chmod +x setup-local-tests.sh

# Run setup verification
./setup-local-tests.sh
```

### 2. Install Playwright (if not already installed)

```bash
npm install @playwright/test
npx playwright install
```

## 🔑 Test Credentials

The following test users have been created specifically for testing:

| Username          | Password      | Role                | Role ID |
| ----------------- | ------------- | ------------------- | ------- |
| `testadmin`       | `testpass123` | Super Administrator | 1       |
| `testadmin2`      | `testpass123` | Administrator       | 2       |
| `testsalesmgr`    | `testpass123` | Sales Manager       | 13      |
| `testsalesasst`   | `testpass123` | Sales Assistant     | 14      |
| `testsalesperson` | `testpass123` | Sales Person        | 15      |

## 🧪 Available Test Suites

### 1. Login Tests (`login.spec.js`)
- ✅ Login page loads correctly
- ✅ Form validation
- ✅ Invalid login handling
- ✅ Valid login for default user
- ✅ All user roles can login

### 2. Navigation Tests (`navigation.spec.js`)
- Navigation menu functionality
- Page routing
- Protected page access

### 3. Authenticated Tests (`authenticated-tests.spec.js`)
- Dashboard functionality
- CRUD operations
- Role-based access control

### 4. Responsive Tests (`responsive.spec.js`)
- Mobile viewport testing
- Tablet viewport testing
- Desktop responsiveness

### 5. Accessibility Tests (`accessibility.spec.js`)
- ARIA compliance
- Keyboard navigation
- Screen reader compatibility

## 🚀 Running Tests

### Basic Commands

```bash
# Run all tests
npx playwright test

# Run specific test suite
npx playwright test login.spec.js
npx playwright test navigation.spec.js
npx playwright test authenticated-tests.spec.js

# Run with browser visible (helpful for debugging)
npx playwright test --headed

# Run in UI mode (interactive)
npx playwright test --ui

# Run tests in specific browser
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit
```

### Recommended Test Sequence

1. **Start with login tests** to verify credentials:
   ```bash
   npx playwright test login.spec.js --headed
   ```

2. **Run navigation tests** to verify basic functionality:
   ```bash
   npx playwright test navigation.spec.js --headed
   ```

3. **Run authenticated tests** for full functionality:
   ```bash
   npx playwright test authenticated-tests.spec.js
   ```

4. **Run all tests** once individual suites pass:
   ```bash
   npx playwright test
   ```

## 📊 Test Reports

After running tests, view the HTML report:

```bash
npx playwright show-report
```

Reports are generated in:
- `playwright-report/` - HTML report
- `test-results.json` - JSON results

## 🐛 Debugging

### Common Issues

1. **Login failures**: Verify test users exist and are active
2. **Timeout errors**: Increase timeout in `playwright.config.js`
3. **SSL errors**: Configuration already includes `ignoreHTTPSErrors: true`

### Debug Commands

```bash
# Run with debug mode
npx playwright test --debug

# Run single test with debug
npx playwright test login.spec.js --debug

# Generate trace for failed tests
npx playwright test --trace=on
```

### Viewing Traces

```bash
npx playwright show-trace trace.zip
```

## 🔧 Configuration

The `playwright.config.js` is configured for:
- **Base URL**: `https://democrm.waveguardco.net`
- **Browsers**: Chrome, Firefox, Safari, Mobile Chrome, Mobile Safari
- **Timeouts**: 30 seconds for actions and navigation
- **Screenshots**: On failure
- **Videos**: On failure
- **Traces**: On retry

## 📁 Test File Structure

```
tests/playwright/
├── login.spec.js              # Login functionality tests
├── navigation.spec.js         # Navigation and routing tests
├── authenticated-tests.spec.js # Post-login functionality tests
├── responsive.spec.js         # Responsive design tests
├── accessibility.spec.js      # Accessibility compliance tests
├── auth-helper.js            # Authentication utilities
├── test-credentials.js       # Test user credentials
├── example.spec.js           # Example/template tests
└── remote-crm.spec.js        # Remote CRM specific tests
```

## 🎯 Test Coverage

The test suite covers:
- ✅ **Authentication**: Login/logout functionality
- ✅ **Authorization**: Role-based access control
- ✅ **Navigation**: Menu and page routing
- ✅ **CRUD Operations**: Create, read, update, delete
- ✅ **Responsive Design**: Mobile and desktop layouts
- ✅ **Accessibility**: WCAG compliance
- ✅ **Error Handling**: Invalid inputs and edge cases

## 📈 Expected Results

Based on previous testing:
- **PHP Backend**: 11/11 tests passed (100%)
- **Web Interface**: 8/11 tests passed (73% - expected behavior)
- **Browser Tests**: Should achieve 80%+ pass rate

## 🔄 Continuous Integration

To integrate with CI/CD:

```yaml
# Example GitHub Actions workflow
- name: Run Playwright Tests
  run: |
    npm install @playwright/test
    npx playwright install
    npx playwright test
```

## 📞 Support

If tests fail:
1. Check test user credentials are still valid
2. Verify server is accessible at `https://democrm.waveguardco.net`
3. Review test logs and screenshots in `test-results/`
4. Check browser console for JavaScript errors

---

**Ready to test!** 🚀 Start with `npx playwright test login.spec.js --headed` to verify everything is working.
# Playwright End-to-End Testing - Local Execution Required

## ⚠️ Important: Playwright MUST Run Locally

**Playwright browser automation cannot be executed over SSH.** End-to-end tests require local execution on your machine with a graphical environment (browser display, GPU support, etc.).

## Why Playwright Requires Local Execution

1. **Browser Display**: Playwright needs to launch and control actual browser instances (Chrome, Firefox, Safari)
2. **GPU Support**: Modern browsers require GPU acceleration for proper rendering
3. **System Environment**: Playwright needs access to the local system's display server (X11/Wayland on Linux)
4. **Network Constraints**: Easier local networking for WebSocket connections to browser instances

## Remote Development Setup

### SSH Server (Remote)
```bash
# ✅ These run on the remote server via SSH:
ssh wswg "cd /home/democrm && vendor/bin/phpunit"           # PHPUnit tests
ssh wswg "cd /home/democrm && php tests/enhanced_integration_test.php"  # Integration tests
```

### Local Machine (Your Computer)
```bash
# ✅ These MUST run locally on your machine:
npm run test:e2e          # Playwright E2E tests
npx playwright test       # Direct Playwright execution
npx playwright show-trace <trace-file>  # View test traces
```

## How to Run Playwright Tests Locally

### Prerequisites
```bash
# On your local machine, ensure you have:
node --version            # Node.js 16+ required
npm --version             # npm 7+

# Navigate to project directory
cd /path/to/democrm
npm install               # Install dependencies if not done
```

### Execute Tests
```bash
# Run all Playwright tests
npm run test:e2e

# Run specific test file
npx playwright test tests/playwright/leads.spec.js

# Run in headed mode (see browser)
npx playwright test --headed

# Run with trace viewer for debugging
npx playwright test --trace on

# Run with verbose output
npx playwright test --verbose

# Run against specific test database
DEMO_TEST_DB=democrm_test npx playwright test
```

## Test Database Access

The remote test database is accessible to Playwright tests:

```javascript
// In Playwright test files
const testDbConfig = {
  host: '159.203.116.150',
  port: 3306,
  user: 'democrm_test',
  password: 'TestDB_2025_Secure!',
  database: 'democrm_test'
};
```

However, your tests will need to:
1. Query the **remote** test database for setup/teardown
2. Use the **remote** application URL in tests

## Test Configuration

Update your Playwright config if needed:

```javascript
// playwright.config.js
export default {
  webServer: {
    command: 'npm run dev',  // Start local dev server if needed
    port: 3000,
    reuseExistingServer: !process.env.CI,
  },
  use: {
    baseURL: 'http://localhost:3000',  // Or remote URL
    trace: 'on-first-retry',
  },
};
```

## Full E2E Testing Workflow

### 1. **On Remote Server** - Set up test environment
```bash
ssh wswg "cd /home/democrm && php tests/setup-test-database.php --reset --seed=minimal"
```

### 2. **On Remote Server** - Run backend tests
```bash
ssh wswg "cd /home/democrm && vendor/bin/phpunit"
ssh wswg "cd /home/democrm && php tests/enhanced_integration_test.php --comprehensive"
```

### 3. **On Local Machine** - Run browser tests
```bash
# Make sure test DB is still clean from step 1
npm run test:e2e
```

### 4. **On Local Machine** - Review results
```bash
# View test report
npx playwright show-report

# View trace from failed test
npx playwright show-trace trace/<test-name>.zip
```

## Test User Credentials (Remote Test DB)

For Playwright tests connecting to remote test database:

| Username | Password | Role |
|----------|----------|------|
| superadmin | testpass123 | Super Administrator |
| admin | testpass123 | Administrator |
| salesman | testpass123 | Sales Manager |
| salesasst | testpass123 | Sales Assistant |
| salesperson | testpass123 | Sales Person |

## Common Issues

### ❌ "Can't find display"
**Cause**: Trying to run Playwright on a headless SSH server  
**Solution**: Run tests on your local machine instead

### ❌ "Can't download browser"
**Cause**: Browser binaries not installed  
**Solution**: Run `npx playwright install` on local machine

### ❌ "Connection refused to remote database"
**Cause**: Firewall/network restrictions  
**Solution**: Ensure remote test database credentials are correct, or use local setup

## Summary

| Task | Location | Command |
|------|----------|---------|
| **PHPUnit tests** | Remote SSH | `ssh wswg "vendor/bin/phpunit"` |
| **Integration tests** | Remote SSH | `ssh wswg "php tests/enhanced_integration_test.php"` |
| **E2E tests** | Local machine | `npm run test:e2e` |
| **Setup test DB** | Remote SSH | `ssh wswg "php tests/setup-test-database.php"` |

---

**Remember**: Keep the test database (democrm_test) isolated from production (democrm_democrm) - they're on the same server but completely separate!

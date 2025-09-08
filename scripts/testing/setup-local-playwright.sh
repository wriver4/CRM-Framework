#!/usr/bin/env bash

# Setup script for local Playwright testing on NixOS
# Run this script on your LOCAL NixOS machine

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🎭 Setting up local Playwright testing environment for CRM...${NC}"

# Create local project directory
LOCAL_DIR="$HOME/crm-playwright-tests"
echo -e "${BLUE}📁 Creating project directory: $LOCAL_DIR${NC}"
mkdir -p "$LOCAL_DIR"
cd "$LOCAL_DIR"

# Initialize npm project if package.json doesn't exist
if [ ! -f "package.json" ]; then
    echo -e "${BLUE}📦 Initializing npm project...${NC}"
    npm init -y
fi

# Install Playwright test framework
echo -e "${BLUE}📦 Installing @playwright/test...${NC}"
npm install @playwright/test

# Install browser binaries
echo -e "${BLUE}🌐 Installing browser binaries...${NC}"
npx playwright install

# Create tests directory
mkdir -p tests/playwright

# Create a basic test file
cat > tests/playwright/example.spec.js << 'EOF'
// @ts-check
const { test, expect } = require('@playwright/test');

test('CRM login page loads', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveTitle(/CRM|Login|Dashboard/);
});

test('Login form exists', async ({ page }) => {
  await page.goto('/login.php');
  await expect(page.locator('input[name="username"]')).toBeVisible();
  await expect(page.locator('input[name="password"]')).toBeVisible();
});
EOF

# Create local config file (will need baseURL updated)
cat > playwright.config.js << 'EOF'
// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Local NixOS Playwright configuration for testing remote CRM
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
  testDir: './tests/playwright',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['json', { outputFile: 'test-results.json' }]
  ],

  use: {
    // TODO: Update this with your actual CRM URL
    baseURL: 'https://your-crm-domain.com',
    
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 30000,
    navigationTimeout: 30000,
    ignoreHTTPSErrors: true,
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
  ],
});
EOF

# Create run script
cat > run-tests.sh << 'EOF'
#!/usr/bin/env bash

# Local test runner
set -e

case "${1:-test}" in
    "test")
        npx playwright test "${@:2}"
        ;;
    "headed")
        npx playwright test --headed "${@:2}"
        ;;
    "ui")
        npx playwright test --ui "${@:2}"
        ;;
    "debug")
        npx playwright test --debug "${@:2}"
        ;;
    "report")
        npx playwright show-report
        ;;
    "help"|"-h"|"--help")
        echo "Local Playwright Test Runner for Remote CRM"
        echo ""
        echo "Usage: $0 [command] [options]"
        echo ""
        echo "Commands:"
        echo "  test      Run all tests (default)"
        echo "  headed    Run tests with browser UI visible"
        echo "  ui        Run tests with Playwright UI"
        echo "  debug     Run tests in debug mode"
        echo "  report    Show test report"
        echo "  help      Show this help message"
        ;;
    *)
        echo "Unknown command: $1"
        echo "Use '$0 help' for usage information"
        exit 1
        ;;
esac
EOF

chmod +x run-tests.sh

echo -e "${GREEN}✅ Local Playwright setup complete!${NC}"
echo -e "${BLUE}📁 Project created at: $LOCAL_DIR${NC}"
echo ""
echo -e "${YELLOW}⚠️  Next steps:${NC}"
echo "1. Update the baseURL in playwright.config.js with your CRM's web URL"
echo "2. Run tests with: ./run-tests.sh test"
echo "3. View results with: ./run-tests.sh report"
echo ""
echo -e "${BLUE}📋 Project structure:${NC}"
ls -la "$LOCAL_DIR"
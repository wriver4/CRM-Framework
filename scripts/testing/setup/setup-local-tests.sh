#!/bin/bash

# Setup script for running CRM Playwright tests locally
# Run this on your local machine where you have Playwright installed

echo "🚀 Setting up CRM Playwright tests for local execution..."
echo "========================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "playwright.config.js" ]; then
    echo -e "${RED}❌ Error: playwright.config.js not found${NC}"
    echo "Please run this script from the directory containing the copied test files"
    exit 1
fi

# Check if Playwright is installed
if ! command -v npx playwright &> /dev/null; then
    echo -e "${YELLOW}⚠️  Playwright not found. Installing...${NC}"
    npm install @playwright/test
    npx playwright install
else
    echo -e "${GREEN}✅ Playwright found${NC}"
fi

# Check if test files exist
echo -e "${BLUE}📁 Checking test files...${NC}"
test_files=(
    "tests/playwright/login.spec.js"
    "tests/playwright/navigation.spec.js"
    "tests/playwright/authenticated-tests.spec.js"
    "tests/playwright/responsive.spec.js"
    "tests/playwright/accessibility.spec.js"
    "tests/playwright/auth-helper.js"
    "tests/playwright/test-credentials.js"
)

missing_files=0
for file in "${test_files[@]}"; do
    if [ -f "$file" ]; then
        echo -e "  ✅ $file"
    else
        echo -e "  ❌ $file ${RED}(missing)${NC}"
        ((missing_files++))
    fi
done

if [ $missing_files -gt 0 ]; then
    echo -e "${RED}❌ $missing_files test files are missing${NC}"
    echo "Please copy all test files from the server first"
    exit 1
fi

echo -e "${GREEN}✅ All test files present${NC}"

# Verify configuration
echo -e "${BLUE}🔧 Verifying configuration...${NC}"
if grep -q "https://democrm.waveguardco.net" playwright.config.js; then
    echo -e "  ✅ Base URL configured correctly"
else
    echo -e "  ⚠️  Base URL may need verification"
fi

# Test credentials check
echo -e "${BLUE}🔑 Test credentials configured:${NC}"
echo "  - Super Admin: testadmin / testpass123"
echo "  - Administrator: testadmin2 / testpass123"
echo "  - Sales Manager: testsalesmgr / testpass123"
echo "  - Sales Assistant: testsalesasst / testpass123"
echo "  - Sales Person: testsalesperson / testpass123"

echo ""
echo -e "${GREEN}🎯 Setup complete! Ready to run tests.${NC}"
echo ""
echo -e "${BLUE}Available test commands:${NC}"
echo "  npx playwright test                    # Run all tests"
echo "  npx playwright test login.spec.js     # Run login tests only"
echo "  npx playwright test --headed           # Run with browser visible"
echo "  npx playwright test --ui               # Run with UI mode"
echo "  npx playwright show-report            # View test report"
echo ""
echo -e "${YELLOW}💡 Tip: Start with login tests to verify credentials work${NC}"
echo "  npx playwright test login.spec.js --headed"
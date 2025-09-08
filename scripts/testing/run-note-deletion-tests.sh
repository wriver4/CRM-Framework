#!/usr/bin/env zsh

# Note Deletion Test Runner
# Runs all tests related to the note deletion functionality fix

echo "🧪 Running Note Deletion Tests"
echo "================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test results tracking
UNIT_TESTS_PASSED=0
INTEGRATION_TESTS_PASSED=0
FEATURE_TESTS_PASSED=0
E2E_TESTS_PASSED=0

echo -e "${BLUE}📋 Test Plan:${NC}"
echo "1. Unit Tests - Core logic validation"
echo "2. Integration Tests - Database and endpoint testing"
echo "3. Feature Tests - Complete workflow testing"
echo "4. E2E Tests - Browser automation testing"
echo ""

# Function to run PHPUnit tests
run_phpunit_test() {
    local test_file=$1
    local test_name=$2
    
    echo -e "${YELLOW}Running ${test_name}...${NC}"
    
    if [ -f "phpunit.phar" ]; then
        php phpunit.phar --testdox "$test_file"
    elif command -v phpunit &> /dev/null; then
        phpunit --testdox "$test_file"
    else
        echo -e "${RED}❌ PHPUnit not found${NC}"
        return 1
    fi
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ ${test_name} passed${NC}"
        return 0
    else
        echo -e "${RED}❌ ${test_name} failed${NC}"
        return 1
    fi
}

# Function to run Playwright tests
run_playwright_test() {
    local test_file=$1
    local test_name=$2
    
    echo -e "${YELLOW}Running ${test_name}...${NC}"
    
    if command -v npx &> /dev/null; then
        npx playwright test "$test_file" --reporter=line
    else
        echo -e "${RED}❌ Playwright not found${NC}"
        return 1
    fi
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ ${test_name} passed${NC}"
        return 0
    else
        echo -e "${RED}❌ ${test_name} failed${NC}"
        return 1
    fi
}

echo -e "${BLUE}🔬 1. Running Unit Tests${NC}"
echo "=========================="

if run_phpunit_test "tests/phpunit/Unit/NoteDeleteTest.php" "Note Delete Unit Tests"; then
    UNIT_TESTS_PASSED=1
fi

echo ""
echo -e "${BLUE}🔗 2. Running Integration Tests${NC}"
echo "==============================="

if run_phpunit_test "tests/phpunit/Integration/NoteDeleteIntegrationTest.php" "Note Delete Integration Tests"; then
    INTEGRATION_TESTS_PASSED=1
fi

echo ""
echo -e "${BLUE}🎯 3. Running Feature Tests${NC}"
echo "============================"

if run_phpunit_test "tests/phpunit/Feature/NoteDeleteFeatureTest.php" "Note Delete Feature Tests"; then
    FEATURE_TESTS_PASSED=1
fi

echo ""
echo -e "${BLUE}🌐 4. Running E2E Tests${NC}"
echo "======================="

if run_playwright_test "tests/playwright/note-deletion.spec.js" "Note Delete E2E Tests"; then
    E2E_TESTS_PASSED=1
fi

echo ""
echo -e "${BLUE}📊 Test Results Summary${NC}"
echo "========================"

total_passed=$((UNIT_TESTS_PASSED + INTEGRATION_TESTS_PASSED + FEATURE_TESTS_PASSED + E2E_TESTS_PASSED))
total_tests=4

echo -e "Unit Tests:        $([ $UNIT_TESTS_PASSED -eq 1 ] && echo -e "${GREEN}✅ PASSED${NC}" || echo -e "${RED}❌ FAILED${NC}")"
echo -e "Integration Tests: $([ $INTEGRATION_TESTS_PASSED -eq 1 ] && echo -e "${GREEN}✅ PASSED${NC}" || echo -e "${RED}❌ FAILED${NC}")"
echo -e "Feature Tests:     $([ $FEATURE_TESTS_PASSED -eq 1 ] && echo -e "${GREEN}✅ PASSED${NC}" || echo -e "${RED}❌ FAILED${NC}")"
echo -e "E2E Tests:         $([ $E2E_TESTS_PASSED -eq 1 ] && echo -e "${GREEN}✅ PASSED${NC}" || echo -e "${RED}❌ FAILED${NC}")"

echo ""
echo -e "Overall Result: ${total_passed}/${total_tests} test suites passed"

if [ $total_passed -eq $total_tests ]; then
    echo -e "${GREEN}🎉 All tests passed! Note deletion functionality is working correctly.${NC}"
    exit 0
else
    echo -e "${RED}⚠️  Some tests failed. Please review the output above.${NC}"
    exit 1
fi
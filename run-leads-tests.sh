#!/usr/bin/env bash

# Leads Module Test Runner
# Comprehensive testing script for the leads module

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PHPUNIT_PHAR="./phpunit.phar"
TEST_ENV="testing"
COVERAGE_DIR="./coverage/leads"

echo -e "${BLUE}=== Leads Module Test Suite ===${NC}"
echo "Starting comprehensive testing for the leads module..."
echo ""

# Check if PHPUnit is available
if [ ! -f "$PHPUNIT_PHAR" ]; then
    echo -e "${RED}Error: PHPUnit not found at $PHPUNIT_PHAR${NC}"
    echo "Please ensure PHPUnit is installed."
    exit 1
fi

# Set environment variables
export APP_ENV="$TEST_ENV"
export TESTING_MODE="local"

# Function to run test suite
run_test_suite() {
    local suite_name=$1
    local description=$2
    
    echo -e "${YELLOW}Running $description...${NC}"
    
    if php "$PHPUNIT_PHAR" --testsuite="$suite_name" --testdox; then
        echo -e "${GREEN}✓ $description completed successfully${NC}"
        return 0
    else
        echo -e "${RED}✗ $description failed${NC}"
        return 1
    fi
}

# Function to run specific test file
run_test_file() {
    local test_file=$1
    local description=$2
    
    echo -e "${YELLOW}Running $description...${NC}"
    
    if php "$PHPUNIT_PHAR" "$test_file" --testdox; then
        echo -e "${GREEN}✓ $description completed successfully${NC}"
        return 0
    else
        echo -e "${RED}✗ $description failed${NC}"
        return 1
    fi
}

# Initialize test results
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Test execution plan
echo -e "${BLUE}Test Execution Plan:${NC}"
echo "1. Unit Tests - Core functionality"
echo "2. Integration Tests - Database interactions"
echo "3. Feature Tests - End-to-end workflows"
echo "4. Performance Tests - Load and stress testing"
echo ""

# 1. Unit Tests
echo -e "${BLUE}=== UNIT TESTS ===${NC}"

# Leads Model Tests
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_file "tests/phpunit/Unit/LeadsModelTest.php" "Leads Model Unit Tests"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
echo ""

# Leads List View Tests
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_file "tests/phpunit/Unit/LeadsListTest.php" "Leads List View Unit Tests"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
echo ""

# Phone Formatting Tests
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_file "tests/phpunit/Unit/PhoneFormattingTest.php" "Phone Number Formatting Unit Tests"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
echo ""

# Edit Lead Workflow Tests (existing)
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_file "tests/phpunit/Unit/EditLeadWorkflowTest.php" "Edit Lead Workflow Unit Tests"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
echo ""

# 2. Integration Tests
echo -e "${BLUE}=== INTEGRATION TESTS ===${NC}"

# Check if we can run integration tests (require database)
export TESTING_MODE="remote"

TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_file "tests/phpunit/Integration/LeadsIntegrationTest.php" "Leads Integration Tests"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
    echo -e "${YELLOW}Note: Integration tests may fail if database is not accessible${NC}"
fi
echo ""

# 3. Feature Tests
echo -e "${BLUE}=== FEATURE TESTS ===${NC}"

TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_file "tests/phpunit/Feature/LeadsFeatureTest.php" "Leads Feature Tests (End-to-End)"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
    echo -e "${YELLOW}Note: Feature tests may fail if web server is not accessible${NC}"
fi
echo ""

# 4. Complete Leads Module Test Suite
echo -e "${BLUE}=== COMPLETE LEADS MODULE SUITE ===${NC}"

TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_suite "LeadsModule" "Complete Leads Module Test Suite"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
echo ""

# 5. Performance Tests (if requested)
if [ "$1" = "--performance" ] || [ "$1" = "-p" ]; then
    echo -e "${BLUE}=== PERFORMANCE TESTS ===${NC}"
    echo "Running performance tests..."
    
    # Run a subset of tests multiple times to check performance
    echo "Testing phone formatting performance..."
    time php "$PHPUNIT_PHAR" tests/phpunit/Unit/PhoneFormattingTest.php::testPerformanceWithLargeDataset --testdox
    
    echo "Testing leads creation performance..."
    time php "$PHPUNIT_PHAR" tests/phpunit/Integration/LeadsIntegrationTest.php::testPerformanceWithRealisticVolumes --testdox
    
    echo ""
fi

# 6. Coverage Report (if requested)
if [ "$1" = "--coverage" ] || [ "$1" = "-c" ]; then
    echo -e "${BLUE}=== COVERAGE REPORT ===${NC}"
    echo "Generating code coverage report..."
    
    mkdir -p "$COVERAGE_DIR"
    
    php "$PHPUNIT_PHAR" --testsuite=LeadsModule \
        --coverage-html="$COVERAGE_DIR" \
        --coverage-text
    
    echo -e "${GREEN}Coverage report generated in: $COVERAGE_DIR${NC}"
    echo ""
fi

# Test Summary
echo -e "${BLUE}=== TEST SUMMARY ===${NC}"
echo "Total Test Suites: $TOTAL_TESTS"
echo -e "Passed: ${GREEN}$PASSED_TESTS${NC}"
echo -e "Failed: ${RED}$FAILED_TESTS${NC}"

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "${GREEN}🎉 All tests passed! The leads module is working correctly.${NC}"
    exit 0
else
    echo -e "${RED}❌ Some tests failed. Please review the output above.${NC}"
    exit 1
fi

# Usage information
if [ "$1" = "--help" ] || [ "$1" = "-h" ]; then
    echo ""
    echo -e "${BLUE}Usage:${NC}"
    echo "$0                    # Run all tests"
    echo "$0 --performance      # Include performance tests"
    echo "$0 --coverage         # Generate coverage report"
    echo "$0 --help             # Show this help"
    echo ""
    echo -e "${BLUE}Test Suites Available:${NC}"
    echo "- LeadsModule         # All leads-related tests"
    echo "- LeadsOnly           # Only unit tests for leads"
    echo "- Unit                # All unit tests"
    echo "- Integration         # All integration tests"
    echo "- Feature             # All feature tests"
    echo ""
    echo -e "${BLUE}Individual Test Files:${NC}"
    echo "- tests/phpunit/Unit/LeadsModelTest.php"
    echo "- tests/phpunit/Unit/LeadsListTest.php"
    echo "- tests/phpunit/Unit/PhoneFormattingTest.php"
    echo "- tests/phpunit/Integration/LeadsIntegrationTest.php"
    echo "- tests/phpunit/Feature/LeadsFeatureTest.php"
    echo ""
    exit 0
fi
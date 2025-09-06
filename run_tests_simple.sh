#!/usr/bin/env bash

# Simple Email Processing System Test Runner
# Bypasses system configuration issues for command line execution

set -e  # Exit on any error

echo "🚀 WaveGuard Email Processing System - Simple Test Runner"
echo "========================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're on local or remote
if [ -f "./vendor/bin/phpunit" ]; then
    PHPUNIT_CMD="./vendor/bin/phpunit"
    print_status "Using local PHPUnit installation"
else
    PHPUNIT_CMD="php phpunit.phar"
    print_status "Using remote PHPUnit installation"
fi

# Check if Playwright is available
if command -v npx &> /dev/null && [ -f "package.json" ]; then
    PLAYWRIGHT_AVAILABLE=true
    print_status "Playwright is available for E2E testing"
else
    PLAYWRIGHT_AVAILABLE=false
    print_warning "Playwright not available - skipping E2E tests"
fi

# Function to run PHPUnit tests
run_phpunit_tests() {
    local test_suite=$1
    local description=$2
    
    print_status "Running $description..."
    
    if [ -n "$test_suite" ]; then
        $PHPUNIT_CMD --testsuite="$test_suite" --testdox
    else
        $PHPUNIT_CMD --testdox
    fi
    
    if [ $? -eq 0 ]; then
        print_success "$description completed successfully"
    else
        print_error "$description failed"
        return 1
    fi
}

# Function to run Playwright tests
run_playwright_tests() {
    print_status "Running Playwright E2E tests..."
    
    npx playwright test email-processing.spec.js --reporter=line
    
    if [ $? -eq 0 ]; then
        print_success "Playwright E2E tests completed successfully"
    else
        print_error "Playwright E2E tests failed"
        return 1
    fi
}

# Main test execution
main() {
    local test_type=${1:-"help"}
    local exit_code=0
    
    case $test_type in
        "unit")
            run_phpunit_tests "Unit" "Unit Tests"
            exit_code=$?
            ;;
        "integration")
            run_phpunit_tests "Integration" "Integration Tests"
            exit_code=$?
            ;;
        "feature")
            run_phpunit_tests "Feature" "Feature Tests"
            exit_code=$?
            ;;
        "email")
            run_phpunit_tests "EmailProcessing" "Email Processing Tests"
            exit_code=$?
            ;;
        "e2e")
            if [ "$PLAYWRIGHT_AVAILABLE" = true ]; then
                run_playwright_tests
                exit_code=$?
            else
                print_error "Playwright not available for E2E tests"
                exit_code=1
            fi
            ;;
        "phpunit")
            print_status "Running all PHPUnit tests..."
            run_phpunit_tests "" "All PHPUnit Tests"
            exit_code=$?
            ;;
        "all")
            # Run all test types
            print_status "Running comprehensive test suite..."
            
            # Unit Tests
            run_phpunit_tests "Unit" "Unit Tests"
            if [ $? -ne 0 ]; then exit_code=1; fi
            
            echo ""
            
            # Integration Tests
            run_phpunit_tests "Integration" "Integration Tests"
            if [ $? -ne 0 ]; then exit_code=1; fi
            
            echo ""
            
            # Feature Tests
            run_phpunit_tests "Feature" "Feature Tests"
            if [ $? -ne 0 ]; then exit_code=1; fi
            
            echo ""
            
            # Email Processing Tests
            run_phpunit_tests "EmailProcessing" "Email Processing Tests"
            if [ $? -ne 0 ]; then exit_code=1; fi
            
            echo ""
            
            # E2E Tests (if available)
            if [ "$PLAYWRIGHT_AVAILABLE" = true ]; then
                run_playwright_tests
                if [ $? -ne 0 ]; then exit_code=1; fi
            else
                print_warning "Skipping E2E tests - Playwright not available"
            fi
            ;;
        "help"|"-h"|"--help"|*)
            echo ""
            echo "Usage: $0 [test_type]"
            echo ""
            echo "Available test types:"
            echo "  unit        - Run unit tests only"
            echo "  integration - Run integration tests only"
            echo "  feature     - Run feature tests only"
            echo "  email       - Run email processing tests only"
            echo "  e2e         - Run end-to-end tests only"
            echo "  phpunit     - Run all PHPUnit tests"
            echo "  all         - Run all tests"
            echo "  help        - Show this help message"
            echo ""
            echo "Examples:"
            echo "  $0 all               # Run all tests"
            echo "  $0 unit              # Run unit tests only"
            echo "  $0 email             # Run email processing tests only"
            echo "  $0 e2e               # Run E2E tests only"
            echo ""
            echo "Note: This simplified version skips database connectivity checks"
            echo "      to avoid system configuration issues in CLI mode."
            exit 0
            ;;
    esac
    
    echo ""
    echo "========================================================="
    
    if [ $exit_code -eq 0 ]; then
        print_success "All tests completed successfully! ✅"
    else
        print_error "Some tests failed! ❌"
        print_status "Check the output above for details"
    fi
    
    echo "========================================================="
    
    exit $exit_code
}

# Execute main function with all arguments
main "$@"
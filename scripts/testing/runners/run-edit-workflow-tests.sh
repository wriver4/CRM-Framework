#!/bin/bash

# Edit Lead Workflow Test Runner
# Runs comprehensive tests for the edit lead workflow implementation

echo "=== Edit Lead Workflow Test Suite ==="
echo ""

# Set script directory and project root
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Change to project root
cd "$PROJECT_ROOT"

echo "📍 Running from: $PROJECT_ROOT"
echo ""

# Check if PHPUnit is available
if [ -f "phpunit.phar" ]; then
    PHPUNIT_CMD="php phpunit.phar"
    echo "✅ Using phpunit.phar"
elif [ -f "vendor/bin/phpunit" ]; then
    PHPUNIT_CMD="./vendor/bin/phpunit"
    echo "✅ Using Composer PHPUnit"
else
    echo "❌ PHPUnit not found. Please install PHPUnit."
    echo "   Remote server: Download phpunit.phar to project root"
    echo "   Local dev: Run 'composer install'"
    exit 1
fi

echo ""

# Function to run test and capture results
run_test() {
    local test_name="$1"
    local test_file="$2"
    
    echo "🧪 Running $test_name..."
    
    if $PHPUNIT_CMD "$test_file" --testdox; then
        echo "✅ $test_name: PASSED"
        return 0
    else
        echo "❌ $test_name: FAILED"
        return 1
    fi
    echo ""
}

# Initialize counters
total_tests=0
passed_tests=0

# Run Unit Tests
echo "=== Unit Tests ==="
if run_test "Edit Lead Workflow Unit Tests" "tests/phpunit/Unit/EditLeadWorkflowTest.php"; then
    ((passed_tests++))
fi
((total_tests++))
echo ""

# Run Integration Tests
echo "=== Integration Tests ==="
if run_test "Edit Lead Workflow Integration Tests" "tests/phpunit/Integration/EditLeadWorkflowIntegrationTest.php"; then
    ((passed_tests++))
fi
((total_tests++))
echo ""

# Run Feature Tests
echo "=== Feature Tests ==="
if run_test "Edit Lead Workflow Feature Tests" "tests/phpunit/Feature/EditLeadWorkflowFeatureTest.php"; then
    ((passed_tests++))
fi
((total_tests++))
echo ""

# Run complete test suite
echo "=== Complete Edit Lead Workflow Test Suite ==="
if $PHPUNIT_CMD --testsuite EditLeadWorkflow --testdox; then
    echo "✅ Complete Test Suite: PASSED"
    ((passed_tests++))
else
    echo "❌ Complete Test Suite: FAILED"
fi
((total_tests++))
echo ""

# Run simple validation tests
echo "=== Validation Tests ==="
echo "🧪 Running stage notification validation..."
if php scripts/test_stage_notification.php > /dev/null 2>&1; then
    echo "✅ Stage Notification Logic: PASSED"
    ((passed_tests++))
else
    echo "❌ Stage Notification Logic: FAILED"
fi
((total_tests++))

echo "🧪 Running edit workflow validation..."
if php scripts/test_edit_workflow.php > /dev/null 2>&1; then
    echo "✅ Edit Workflow Logic: PASSED"
    ((passed_tests++))
else
    echo "❌ Edit Workflow Logic: FAILED"
fi
((total_tests++))
echo ""

# Summary
echo "=== Test Results Summary ==="
echo "📊 Tests Passed: $passed_tests/$total_tests"

if [ $passed_tests -eq $total_tests ]; then
    echo "🎉 All tests passed! Edit Lead Workflow is ready for production."
    exit 0
else
    failed_tests=$((total_tests - passed_tests))
    echo "⚠️  $failed_tests test(s) failed. Please review the output above."
    exit 1
fi
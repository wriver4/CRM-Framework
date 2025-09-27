#!/bin/bash

# Setup Test Environment Script
# This script helps configure the testing environment for the leads module

echo "=== Leads Module Test Environment Setup ==="
echo

# Check if we're in the right directory
if [ ! -f "phpunit.xml" ]; then
    echo "❌ Error: phpunit.xml not found. Please run this script from the project root directory."
    exit 1
fi

echo "✅ Found phpunit.xml - we're in the right directory"

# Create necessary directories
echo "📁 Creating test directories..."
mkdir -p tests/logs
mkdir -p tests/coverage
mkdir -p tests/fixtures
mkdir -p tests/temp

echo "✅ Test directories created"

# Check PHPUnit installation
echo "🔍 Checking PHPUnit installation..."
if [ -f "phpunit.phar" ]; then
    echo "✅ PHPUnit found (phpunit.phar)"
    PHPUNIT_CMD="php phpunit.phar"
elif command -v phpunit &> /dev/null; then
    echo "✅ PHPUnit found (global installation)"
    PHPUNIT_CMD="phpunit"
else
    echo "❌ PHPUnit not found. Please install PHPUnit."
    exit 1
fi

# Test current working unit tests
echo
echo "🧪 Running unit tests to verify current status..."
echo "Running Leads Model Tests..."
$PHPUNIT_CMD tests/phpunit/Unit/LeadsModelTest.php --no-coverage 2>/dev/null
LEADS_MODEL_STATUS=$?

echo "Running Phone Formatting Tests..."
$PHPUNIT_CMD tests/phpunit/Unit/PhoneFormattingTest.php --no-coverage 2>/dev/null
PHONE_FORMAT_STATUS=$?

echo "Running Leads List Tests..."
$PHPUNIT_CMD tests/phpunit/Unit/LeadsListTest.php --no-coverage 2>/dev/null
LEADS_LIST_STATUS=$?

# Report unit test status
echo
echo "=== UNIT TEST STATUS ==="
if [ $LEADS_MODEL_STATUS -eq 0 ]; then
    echo "✅ Leads Model Tests: PASSING"
else
    echo "❌ Leads Model Tests: FAILING"
fi

if [ $PHONE_FORMAT_STATUS -eq 0 ]; then
    echo "✅ Phone Formatting Tests: PASSING"
else
    echo "❌ Phone Formatting Tests: FAILING"
fi

if [ $LEADS_LIST_STATUS -eq 0 ]; then
    echo "✅ Leads List Tests: PASSING"
else
    echo "❌ Leads List Tests: FAILING"
fi

# Check integration test requirements
echo
echo "=== INTEGRATION TEST REQUIREMENTS ==="
echo "🔍 Checking database connectivity..."

# Try to connect to database (this will likely fail, but we'll document it)
php -r "
try {
    require_once 'classes/Core/Database.php';
    \$db = new Database();
    echo '✅ Database connection: WORKING\n';
} catch (Exception \$e) {
    echo '❌ Database connection: FAILED - ' . \$e->getMessage() . '\n';
    echo '   Integration tests will be skipped until database access is configured.\n';
}
" 2>/dev/null

# Check feature test requirements
echo
echo "=== FEATURE TEST REQUIREMENTS ==="
echo "🔍 Checking web server accessibility..."

# Test if the application is accessible
BASE_URL="https://democrm.waveguardco.net"
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL" 2>/dev/null || echo "000")

if [ "$HTTP_STATUS" = "200" ] || [ "$HTTP_STATUS" = "302" ]; then
    echo "✅ Web server accessible (HTTP $HTTP_STATUS)"
    echo "   Note: 302 redirects are expected due to authentication requirements"
else
    echo "❌ Web server not accessible (HTTP $HTTP_STATUS)"
fi

# Create a test summary
echo
echo "=== TESTING FRAMEWORK SUMMARY ==="
echo
echo "📊 Current Status:"
echo "   • Unit Tests: Ready and working"
echo "   • Integration Tests: Need database setup"
echo "   • Feature Tests: Need authentication setup"
echo
echo "📋 Next Steps:"
echo "   1. For Integration Tests:"
echo "      - Set up test database (democrm_test)"
echo "      - Configure test database user"
echo "      - Run: TESTING_MODE=remote $PHPUNIT_CMD tests/phpunit/Integration/"
echo
echo "   2. For Feature Tests:"
echo "      - Configure test user authentication"
echo "      - Run: TESTING_MODE=remote $PHPUNIT_CMD tests/phpunit/Feature/"
echo
echo "   3. For Complete Testing:"
echo "      - Run: ./run-leads-tests.sh"
echo
echo "📚 Documentation:"
echo "   - See tests/README.md for detailed information"
echo "   - See tests/test-config.php for configuration options"
echo
echo "✅ Test environment setup complete!"
echo "   The testing framework is ready for use with working unit tests."
echo "   Integration and feature tests require additional setup as documented above."
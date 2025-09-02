#!/bin/bash

# Simple web interface tests using curl
echo "🌐 Testing CRM Web Interface..."

BASE_URL="https://democrm.waveguardco.net"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test function
test_endpoint() {
    local endpoint=$1
    local expected_status=$2
    local description=$3
    
    echo -e "${BLUE}Testing: $description${NC}"
    echo "URL: $BASE_URL$endpoint"
    
    response=$(curl -s -o /dev/null -w "%{http_code}" -k "$BASE_URL$endpoint")
    
    if [ "$response" = "$expected_status" ]; then
        echo -e "${GREEN}✅ PASS: HTTP $response${NC}"
    else
        echo -e "${RED}❌ FAIL: Expected $expected_status, got $response${NC}"
    fi
    echo ""
}

# Test basic endpoints
test_endpoint "/" "200" "Home page"
test_endpoint "/login.php" "200" "Login page"
test_endpoint "/dashboard.php" "302" "Dashboard (should redirect to login)"
test_endpoint "/leads/list.php" "302" "Leads list (should redirect to login)"
test_endpoint "/contacts/list.php" "302" "Contacts list (should redirect to login)"
test_endpoint "/users/list.php" "302" "Users list (should redirect to login)"

# Test static assets
test_endpoint "/assets/css/bootstrap.min.css" "200" "Bootstrap CSS"
test_endpoint "/assets/css/style.css" "200" "Main stylesheet"
test_endpoint "/assets/js/general.js" "200" "General JavaScript"

# Test API endpoints
test_endpoint "/tests/leads/simple_test.php" "200" "Simple test endpoint"
test_endpoint "/tests/leads/test_endpoint.php" "200" "Test endpoint"

echo -e "${GREEN}🎯 Web interface tests completed!${NC}"
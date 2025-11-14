#!/bin/bash

# DemoCRM Core Tests Runner
# Runs Phase 1 Core Foundation tests

echo "🧪 DemoCRM Core Tests Runner"
echo "=============================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Change to project directory
cd /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm

echo "📍 Current directory: $(pwd)"
echo ""

# Check if vendor/bin/phpunit exists
if [ ! -f "vendor/bin/phpunit" ]; then
    echo -e "${RED}❌ PHPUnit not found!${NC}"
    echo "Run: composer install"
    exit 1
fi

echo -e "${GREEN}✅ PHPUnit found${NC}"
echo ""

# Run tests
echo "🚀 Running Core Tests..."
echo "========================"
echo ""

# Run with timeout to prevent hanging
timeout 60 vendor/bin/phpunit --testsuite=Core --testdox --colors=always

EXIT_CODE=$?

echo ""
echo "=============================="

if [ $EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✅ All tests passed!${NC}"
elif [ $EXIT_CODE -eq 124 ]; then
    echo -e "${RED}❌ Tests timed out (60 seconds)${NC}"
    echo "This might indicate an infinite loop or database connection issue."
else
    echo -e "${RED}❌ Tests failed with exit code: $EXIT_CODE${NC}"
fi

exit $EXIT_CODE
#!/run/current-system/sw/bin/bash

# PHPUnit Test Runner for CRM Application (NixOS Compatible)
# This script runs tests both locally and remotely

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
REMOTE_HOST="159.203.116.150"
REMOTE_PORT="222"
REMOTE_USER="root"
REMOTE_PATH="/home/democrm"
BASE_URL="https://democrm.waveguardco.net"

echo -e "${BLUE}🧪 CRM PHPUnit Test Runner${NC}"
echo -e "${BLUE}===========================${NC}"
echo ""

# Function to run tests locally
run_local_tests() {
    echo -e "${YELLOW}📍 Running tests locally...${NC}"
    
    # Check if we have PHPUnit available
    if [ -f "/home/mark/.config/composer/vendor/bin/phpunit" ]; then
        echo -e "${GREEN}✅ PHPUnit found in global composer${NC}"
        /home/mark/.config/composer/vendor/bin/phpunit --configuration phpunit.xml "$@"
    elif command -v phpunit &> /dev/null; then
        echo -e "${GREEN}✅ PHPUnit found in PATH${NC}"
        phpunit --configuration phpunit.xml "$@"
    elif [ -f "vendor/bin/phpunit" ]; then
        echo -e "${GREEN}✅ PHPUnit found in vendor/bin${NC}"
        ./vendor/bin/phpunit --configuration phpunit.xml "$@"
    else
        echo -e "${YELLOW}⚠️  PHPUnit not found, running simple tests${NC}"
        php simple-test.php
    fi
}

# Function to run tests on remote server
run_remote_tests() {
    echo -e "${YELLOW}🌐 Running tests on remote server...${NC}"
    
    # Test SSH connection
    if ! ssh -o ConnectTimeout=10 -p $REMOTE_PORT $REMOTE_USER@$REMOTE_HOST "echo 'SSH connection successful'" 2>/dev/null; then
        echo -e "${RED}❌ Cannot connect to remote server${NC}"
        return 1
    fi
    
    echo -e "${GREEN}✅ SSH connection successful${NC}"
    
    # Run tests on remote server
    ssh -p $REMOTE_PORT $REMOTE_USER@$REMOTE_HOST "cd $REMOTE_PATH && bash run-phpunit-nixos.sh local"
}

# Function to run specific test suite
run_test_suite() {
    local suite=$1
    echo -e "${BLUE}🎯 Running $suite test suite...${NC}"
    
    case $suite in
        "unit")
            if [ -f "/home/mark/.config/composer/vendor/bin/phpunit" ]; then
                /home/mark/.config/composer/vendor/bin/phpunit --configuration phpunit.xml --testsuite Unit
            elif [ -f "vendor/bin/phpunit" ]; then
                ./vendor/bin/phpunit --configuration phpunit.xml --testsuite Unit
            else
                php simple-test.php
            fi
            ;;
        "integration")
            if [ -f "/home/mark/.config/composer/vendor/bin/phpunit" ]; then
                /home/mark/.config/composer/vendor/bin/phpunit --configuration phpunit.xml --testsuite Integration
            elif [ -f "vendor/bin/phpunit" ]; then
                ./vendor/bin/phpunit --configuration phpunit.xml --testsuite Integration
            else
                echo -e "${YELLOW}⚠️  Integration tests require PHPUnit${NC}"
            fi
            ;;
        "feature")
            if [ -f "/home/mark/.config/composer/vendor/bin/phpunit" ]; then
                /home/mark/.config/composer/vendor/bin/phpunit --configuration phpunit.xml --testsuite Feature
            elif [ -f "vendor/bin/phpunit" ]; then
                ./vendor/bin/phpunit --configuration phpunit.xml --testsuite Feature
            else
                echo -e "${YELLOW}⚠️  Feature tests require PHPUnit${NC}"
            fi
            ;;
        *)
            echo -e "${RED}❌ Unknown test suite: $suite${NC}"
            echo "Available suites: unit, integration, feature"
            return 1
            ;;
    esac
}

# Main execution logic
case "${1:-all}" in
    "local")
        run_local_tests "${@:2}"
        ;;
    "remote")
        run_remote_tests
        ;;
    "unit"|"integration"|"feature")
        run_test_suite "$1"
        ;;
    "all")
        echo -e "${BLUE}🚀 Running all tests...${NC}"
        run_local_tests "${@:2}"
        ;;
    "help"|"-h"|"--help")
        echo "Usage: $0 [COMMAND] [OPTIONS]"
        echo ""
        echo "Commands:"
        echo "  local      Run tests locally"
        echo "  remote     Run tests on remote server"
        echo "  unit       Run unit tests only"
        echo "  integration Run integration tests only"
        echo "  feature    Run feature tests only"
        echo "  all        Run all tests (default)"
        echo "  help       Show this help message"
        echo ""
        echo "Examples:"
        echo "  $0                    # Run all tests locally"
        echo "  $0 local             # Run tests locally"
        echo "  $0 remote            # Run tests on remote server"
        echo "  $0 unit              # Run unit tests only"
        echo "  $0 local --verbose   # Run local tests with verbose output"
        ;;
    *)
        echo -e "${RED}❌ Unknown command: $1${NC}"
        echo "Use '$0 help' for usage information"
        exit 1
        ;;
esac

echo ""
echo -e "${GREEN}✅ Test execution completed${NC}"
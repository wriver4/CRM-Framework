#!/usr/bin/env bash

# NixOS Playwright test runner for remote CRM testing
# Run this script on your local NixOS machine

set -e

echo "🎭 Running Playwright tests on NixOS for remote CRM..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if we're on NixOS
if [ -f /etc/NIXOS ]; then
    echo -e "${GREEN}✅ Running on NixOS${NC}"
else
    echo -e "${YELLOW}⚠️  Not detected as NixOS, but continuing...${NC}"
fi

# Function to check if playwright is available
check_playwright() {
    if command -v playwright &> /dev/null; then
        echo -e "${GREEN}✅ Playwright found in PATH${NC}"
        playwright --version
        return 0
    else
        echo -e "${RED}❌ Playwright not found in PATH${NC}"
        return 1
    fi
}

# Function to run with nix-shell if playwright not in PATH
run_with_nix_shell() {
    echo -e "${BLUE}🔧 Running with nix-shell...${NC}"
    nix-shell -p playwright-driver playwright-test --run "playwright test --config=playwright-local.config.js $*"
}

# Function to run tests
run_tests() {
    local config_file="playwright-local.config.js"
    local test_args="$*"
    
    echo -e "${BLUE}📋 Test configuration: $config_file${NC}"
    echo -e "${BLUE}📋 Test arguments: $test_args${NC}"
    
    if check_playwright; then
        echo -e "${GREEN}🚀 Running tests with system Playwright...${NC}"
        playwright test --config="$config_file" $test_args
    else
        echo -e "${YELLOW}🚀 Running tests with nix-shell...${NC}"
        run_with_nix_shell $test_args
    fi
}

# Create screenshots directory
mkdir -p screenshots

# Parse command line arguments
case "${1:-test}" in
    "test")
        run_tests "${@:2}"
        ;;
    "headed")
        run_tests --headed "${@:2}"
        ;;
    "ui")
        run_tests --ui "${@:2}"
        ;;
    "debug")
        run_tests --debug "${@:2}"
        ;;
    "report")
        if check_playwright; then
            playwright show-report
        else
            nix-shell -p playwright-driver playwright-test --run "playwright show-report"
        fi
        ;;
    "install")
        if check_playwright; then
            playwright install
        else
            nix-shell -p playwright-driver playwright-test --run "playwright install"
        fi
        ;;
    "help"|"-h"|"--help")
        echo "NixOS Playwright Test Runner for Remote CRM"
        echo ""
        echo "Usage: $0 [command] [options]"
        echo ""
        echo "Commands:"
        echo "  test      Run all tests (default)"
        echo "  headed    Run tests with browser UI visible"
        echo "  ui        Run tests with Playwright UI"
        echo "  debug     Run tests in debug mode"
        echo "  report    Show test report"
        echo "  install   Install browser binaries"
        echo "  help      Show this help message"
        echo ""
        echo "Examples:"
        echo "  $0 test                    # Run all tests"
        echo "  $0 headed                  # Run with visible browser"
        echo "  $0 test --grep=\"login\"     # Run only login tests"
        echo "  $0 test --project=chromium # Run only Chromium tests"
        ;;
    *)
        echo -e "${RED}❌ Unknown command: $1${NC}"
        echo "Use '$0 help' for usage information"
        exit 1
        ;;
esac

echo -e "${GREEN}✅ Test execution completed${NC}"
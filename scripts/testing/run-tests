#!/usr/bin/env bash

# Test Runner Convenience Script
# Provides easy access to testing scripts from project root

echo "🧪 CRM Framework Test Runner"
echo "============================"
echo ""
echo "Available test runners:"
echo "  1. Simple Tests       - ./scripts/testing/run_tests_simple.sh"
echo "  2. Email Tests        - ./scripts/testing/run_email_tests.sh"
echo "  3. Note Deletion      - ./scripts/testing/run-note-deletion-tests.sh"
echo "  4. Edit Workflow      - ./scripts/testing/run-edit-workflow-tests.sh"
echo "  5. PHPUnit (NixOS)    - ./scripts/testing/run-phpunit-nixos.sh"
echo "  6. All Tests (NixOS)  - ./scripts/testing/run-tests-nixos.sh"
echo "  7. PHP Test Runner    - php ./scripts/testing/run-tests.php"
echo ""
echo "Setup scripts:"
echo "  - Playwright Setup    - ./scripts/testing/setup-local-playwright.sh"
echo "  - Local Test Setup    - ./scripts/testing/setup-local-tests.sh"
echo "  - Install Playwright  - ./scripts/testing/install-playwright-nixos.sh"
echo ""

if [ $# -eq 0 ]; then
    echo "Usage: $0 [simple|email|notes|edit-workflow|phpunit|all|setup-playwright|setup-tests]"
    echo ""
    exit 1
fi

case "$1" in
    "simple")
        exec ./scripts/testing/run_tests_simple.sh "${@:2}"
        ;;
    "email")
        exec ./scripts/testing/run_email_tests.sh "${@:2}"
        ;;
    "notes")
        exec ./scripts/testing/run-note-deletion-tests.sh "${@:2}"
        ;;
    "edit-workflow")
        exec ./scripts/testing/run-edit-workflow-tests.sh "${@:2}"
        ;;
    "phpunit")
        exec ./scripts/testing/run-phpunit-nixos.sh "${@:2}"
        ;;
    "all")
        exec ./scripts/testing/run-tests-nixos.sh "${@:2}"
        ;;
    "setup-playwright")
        exec ./scripts/testing/setup-local-playwright.sh "${@:2}"
        ;;
    "setup-tests")
        exec ./scripts/testing/setup-local-tests.sh "${@:2}"
        ;;
    *)
        echo "❌ Unknown test type: $1"
        echo "Available options: simple, email, notes, edit-workflow, phpunit, all, setup-playwright, setup-tests"
        exit 1
        ;;
esac
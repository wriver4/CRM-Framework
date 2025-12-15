#!/usr/bin/env bash

# NixOS Playwright Test Runner for Calendar Tests
# This script runs the calendar tests using nix-shell environment

echo "🚀 Starting Calendar Tests on NixOS..."
echo "======================================="

# Change to project directory
cd "$(dirname "$0")"

# Create screenshots directory if it doesn't exist
mkdir -p screenshots

# Run tests in nix-shell environment with Node.js and required packages
echo "📦 Setting up test environment..."

nix-shell -p nodejs_20 --run "
  echo '📥 Installing Playwright...'
  npm install @playwright/test
  
  echo '🎭 Installing browser binaries...'
  npx playwright install chromium
  
  echo '🧪 Running Calendar Tests...'
  echo '=============================='
  
  # Run calendar tests with detailed reporting
  npx playwright test tests/playwright/calendar*.spec.js \
    --reporter=list \
    --output=test-results \
    --project=chromium
  
  echo ''
  echo '📊 Test Results Summary:'
  echo '======================='
  
  # Show test results if available
  if [ -f test-results.json ]; then
    echo '✅ Test results saved to test-results.json'
  fi
  
  # Show HTML report location
  if [ -d playwright-report ]; then
    echo '📋 HTML report available at: playwright-report/index.html'
    echo '   Open with: firefox playwright-report/index.html'
  fi
  
  echo ''
  echo '🖼️  Screenshots saved to: screenshots/'
  ls -la screenshots/ 2>/dev/null || echo '   No screenshots generated'
"

echo ""
echo "✅ Calendar test execution completed!"
echo "Check the output above for test results."
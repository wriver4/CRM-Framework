#!/bin/bash

# Calendar Playwright Test Runner
# This script runs the calendar-specific Playwright tests

echo "🎭 Calendar Playwright Test Runner"
echo "=================================="

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "📦 Installing dependencies..."
    npm install
fi

# Check if Playwright browsers are installed
if [ ! -d "node_modules/playwright" ]; then
    echo "🌐 Installing Playwright browsers..."
    npx playwright install
fi

echo ""
echo "🚀 Running Calendar Tests..."
echo ""

# Run calendar tests with different options
echo "1️⃣  Running Basic Calendar Tests..."
npx playwright test tests/playwright/calendar.spec.js --reporter=line

echo ""
echo "2️⃣  Running Advanced Calendar Tests..."
npx playwright test tests/playwright/calendar-advanced.spec.js --reporter=line

echo ""
echo "3️⃣  Running Calendar API Tests..."
npx playwright test tests/playwright/calendar-api.spec.js --reporter=line

echo ""
echo "📊 Generating HTML Report..."
npx playwright test tests/playwright/calendar*.spec.js --reporter=html

echo ""
echo "✅ Calendar tests completed!"
echo "📄 View the HTML report: npx playwright show-report"
echo "📸 Screenshots saved in: test-results/"
echo "🎥 Videos saved in: test-results/"
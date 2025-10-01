#!/usr/bin/env bash

# Local NixOS Playwright Test Runner
# This script creates a local test environment and runs the calendar tests

echo "🚀 Setting up Local Calendar Test Environment"
echo "=============================================="

# Create local test directory
LOCAL_TEST_DIR="/tmp/democrm-calendar-tests"
echo "📁 Creating test directory: $LOCAL_TEST_DIR"

rm -rf "$LOCAL_TEST_DIR"
mkdir -p "$LOCAL_TEST_DIR/tests/playwright"

# Copy test files to local directory
echo "📋 Copying test files..."
cp tests/playwright/calendar*.spec.js "$LOCAL_TEST_DIR/tests/playwright/"
cp tests/playwright/auth-helper.js "$LOCAL_TEST_DIR/tests/playwright/" 2>/dev/null || echo "⚠️  auth-helper.js not found, tests may need adjustment"
cp tests/playwright/calendar-helper.js "$LOCAL_TEST_DIR/tests/playwright/" 2>/dev/null || echo "⚠️  calendar-helper.js not found"
cp playwright-minimal.config.js "$LOCAL_TEST_DIR/"

# Create package.json for local testing
cat > "$LOCAL_TEST_DIR/package.json" << 'EOF'
{
  "name": "democrm-calendar-tests",
  "version": "1.0.0",
  "type": "commonjs",
  "devDependencies": {
    "@playwright/test": "^1.55.0"
  },
  "scripts": {
    "test": "playwright test",
    "test:calendar": "playwright test tests/playwright/calendar*.spec.js"
  }
}
EOF

echo "📦 Local test package created at: $LOCAL_TEST_DIR"
echo ""
echo "🎯 To run the calendar tests:"
echo "   cd $LOCAL_TEST_DIR"
echo "   npm install"
echo "   npx playwright install chromium"
echo "   npm run test:calendar"
echo ""
echo "Or with nix-shell (NixOS):"
echo "   cd $LOCAL_TEST_DIR"
echo "   nix-shell -p nodejs_20 --run 'npm install && npx playwright install chromium && npm run test:calendar'"
echo ""
echo "For Ubuntu 20.04 (traveling machine):"
echo "   sudo apt install nodejs npm libnss3-dev libatk-bridge2.0-dev libdrm2 libxkbcommon0 libxcomposite1 libxdamage1 libxrandr2 libgbm1 libxss1 libasound2"
echo "   cd $LOCAL_TEST_DIR"
echo "   npm install && npx playwright install chromium && npm run test:calendar"
echo ""
echo "✅ Test environment ready for both NixOS and Ubuntu 20.04!"

# Show test summary
echo ""
echo "📊 Test Summary:"
node -e "
const fs = require('fs');
const files = ['tests/playwright/calendar.spec.js', 'tests/playwright/calendar-advanced.spec.js', 'tests/playwright/calendar-api.spec.js'];
let total = 0;
files.forEach(f => {
  try {
    const content = fs.readFileSync('$LOCAL_TEST_DIR/' + f, 'utf8');
    const count = (content.match(/test\(/g) || []).length;
    console.log('   📄 ' + f + ': ' + count + ' tests');
    total += count;
  } catch(e) { console.log('   ❌ ' + f + ': not found'); }
});
console.log('   📊 Total: ' + total + ' calendar tests');
"
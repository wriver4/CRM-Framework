#!/usr/bin/env bash

# Playwright installation script for NixOS
# This script handles the NixOS-specific requirements for Playwright

echo "🎭 Installing Playwright for NixOS..."

# Check if we're on NixOS
if [ ! -f /etc/NIXOS ]; then
    echo "⚠️  This script is designed for NixOS. Proceeding anyway..."
fi

# Install Playwright browsers
echo "📦 Installing Playwright browsers..."
npm run install:browsers

# For NixOS, we might need to set up browser dependencies
echo "🔧 Setting up NixOS-specific configurations..."

# Create a wrapper script for running tests with proper environment
cat > run-playwright.sh << 'EOF'
#!/usr/bin/env bash

# Set up environment for Playwright on NixOS
export PLAYWRIGHT_BROWSERS_PATH="$PWD/node_modules/.cache/ms-playwright"
export PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=0

# Run Playwright with the provided arguments
npx playwright "$@"
EOF

chmod +x run-playwright.sh

echo "✅ Playwright setup complete!"
echo ""
echo "🚀 To run tests:"
echo "   npm test                 # Run all tests"
echo "   npm run test:headed      # Run with browser UI"
echo "   npm run test:ui          # Run with Playwright UI"
echo "   npm run test:debug       # Debug mode"
echo ""
echo "🔧 Or use the wrapper script:"
echo "   ./run-playwright.sh test"
echo "   ./run-playwright.sh test --headed"
echo ""
echo "📊 To view test reports:"
echo "   npm run test:report"
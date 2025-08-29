# Playwright Testing Setup for NixOS

This document explains how to set up and use Playwright on your local NixOS machine to test the remote CRM application.

## Prerequisites

- NixOS system
- VSCode with Playwright extension
- Access to the remote CRM application

## Installation Options

### Option 1: System-wide Installation (Recommended)

Add to your `/etc/nixos/configuration.nix`:

```nix
environment.systemPackages = with pkgs; [
  playwright-driver
  playwright-test
  nodejs_20  # or nodejs_18
];
```

Then rebuild your system:
```bash
sudo nixos-rebuild switch
```

### Option 2: Home Manager Installation

Add to your `~/.config/home-manager/home.nix`:

```nix
home.packages = with pkgs; [
  playwright-driver
  playwright-test
  nodejs_20
];
```

Then apply the configuration:
```bash
home-manager switch
```

### Option 3: Temporary nix-shell (for testing)

```bash
nix-shell -p playwright-driver playwright-test nodejs_20
```

## Configuration

1. **Update the base URL** in `playwright-local.config.js`:
   ```javascript
   baseURL: 'https://your-actual-crm-domain.com',
   ```

2. **Install browser binaries** (one-time setup):
   ```bash
   ./run-tests-nixos.sh install
   ```

## Running Tests

### Using the NixOS wrapper script:

```bash
# Run all tests
./run-tests-nixos.sh test

# Run with visible browser
./run-tests-nixos.sh headed

# Run with Playwright UI
./run-tests-nixos.sh ui

# Run in debug mode
./run-tests-nixos.sh debug

# Show test report
./run-tests-nixos.sh report

# Run specific tests
./run-tests-nixos.sh test --grep="login"

# Run only Chromium tests
./run-tests-nixos.sh test --project=chromium
```

### Using Playwright directly (if installed system-wide):

```bash
# Run all tests
playwright test --config=playwright-local.config.js

# Run with UI
playwright test --config=playwright-local.config.js --ui

# Run specific test file
playwright test tests/playwright/remote-crm.spec.js --config=playwright-local.config.js
```

## VSCode Integration

1. **Install the Playwright extension** in VSCode
2. **Open the project** in VSCode
3. **Configure the extension** to use `playwright-local.config.js`
4. **Use the Test Explorer** to run individual tests

### VSCode Commands:
- `Ctrl+Shift+P` → "Test: Run All Tests"
- `Ctrl+Shift+P` → "Playwright: Run Tests"
- `Ctrl+Shift+P` → "Playwright: Show Report"

## Test Structure

```
tests/playwright/
├── remote-crm.spec.js      # Main CRM functionality tests
└── example.spec.js         # Basic example tests
```

## Screenshots and Reports

- **Screenshots**: Saved to `screenshots/` directory
- **Test Reports**: Generated in `playwright-report/` directory
- **Test Results**: JSON results in `test-results.json`

## Troubleshooting

### Browser Installation Issues
```bash
# If browsers fail to install
nix-shell -p playwright-driver --run "playwright install"
```

### Permission Issues
```bash
# Make sure the script is executable
chmod +x run-tests-nixos.sh
```

### Network Issues
- Check if the remote CRM URL is accessible
- Verify SSL certificates (config has `ignoreHTTPSErrors: true`)
- Check firewall settings

### NixOS-specific Issues
- Ensure you have the latest NixOS packages
- Try using nix-shell if system packages don't work
- Check that Node.js version is compatible

## Customizing Tests

### Adding New Tests
1. Create new `.spec.js` files in `tests/playwright/`
2. Follow the existing pattern in `remote-crm.spec.js`
3. Use the CRM-specific selectors and URLs

### Test Configuration
- Modify `playwright-local.config.js` for global settings
- Adjust timeouts, browsers, and other options as needed

### Environment Variables
```bash
# Set custom base URL
export PLAYWRIGHT_BASE_URL=https://your-crm.com
./run-tests-nixos.sh test
```

## Best Practices

1. **Always test against the actual remote URL**
2. **Use descriptive test names**
3. **Take screenshots for debugging**
4. **Test both authenticated and unauthenticated scenarios**
5. **Check for proper error handling**
6. **Verify security headers and redirects**

## Example Test Run Output

```bash
$ ./run-tests-nixos.sh test

🎭 Running Playwright tests on NixOS for remote CRM...
✅ Running on NixOS
✅ Playwright found in PATH
Version 1.54.1
📋 Test configuration: playwright-local.config.js
🚀 Running tests with system Playwright...

Running 7 tests using 1 worker
✓ should load the CRM login page (2.3s)
✓ should handle authentication redirect (1.8s)
✓ should test leads page accessibility (1.5s)
✓ should test contacts page accessibility (1.4s)
✓ should test AJAX endpoints security (0.9s)
✓ should check for common security headers (1.2s)
✓ should test multilingual support (1.6s)

7 passed (10.7s)
✅ Test execution completed
```
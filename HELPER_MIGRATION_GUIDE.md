# Helper Functions Migration Guide

## Summary of Changes

The global helper functions from `config/helpers.php` have been moved to the `Helpers` class for better organization, testing, and maintainability.

## Migration Required

### Before (Global Functions):
```php
// Old way - global functions
$ip = get_client_ip();
$country = country_by_ip();
$isValid = isValidSessionId($sessionId);
```

### After (Class Methods):
```php
// New way - class methods
$ip = $helper->get_client_ip();
$country = $helper->country_by_ip();
$isValid = $helper->isValidSessionId($sessionId);
```

## Available Helper Instance

Since `system.php` creates a global `$helper` instance, you can use it throughout your application:

```php
// The $helper instance is available globally after system.php is loaded
$clientIp = $helper->get_client_ip();
$countryCode = $helper->country_by_ip();
$sessionValid = $helper->isValidSessionId($_SESSION['id']);
```

## Functions Moved

1. **`get_client_ip()`** → **`$helper->get_client_ip()`**
   - Detects client IP with proxy support
   
2. **`country_by_ip()`** → **`$helper->country_by_ip()`**
   - Gets country code from IP using geolocation services
   
3. **`isValidSessionId($id)`** → **`$helper->isValidSessionId($id)`**
   - Validates session ID format

## Benefits of This Change

✅ **Better Organization**: All utility functions in one class  
✅ **Improved Testing**: Class methods are easier to unit test  
✅ **Dependency Injection**: Better for mocking and testing  
✅ **Autoloading**: Functions only loaded when class is used  
✅ **Namespacing**: No global function conflicts  
✅ **IDE Support**: Better autocomplete and documentation  

## Files to Update

Search your codebase for these function calls and update them:

```bash
# Find files using the old global functions
grep -r "get_client_ip()" --include="*.php" .
grep -r "country_by_ip()" --include="*.php" .
grep -r "isValidSessionId(" --include="*.php" .
```

## Backward Compatibility

The old `config/helpers.php` file can be safely removed as all functions are now in the `Helpers` class.

## Questions?

If you encounter any issues during migration, check that:
1. `$helper` instance is available (loaded via `system.php`)
2. Function calls use `->` instead of direct function calls
3. Method names are exactly the same (just called differently)
<?php
/**
 * Quick validation script for Leads Edit Asset Refactoring
 * 
 * This script validates that the asset organization refactoring
 * was completed successfully and follows framework standards.
 * 
 * Usage: php tests/validate_leads_edit_refactoring.php
 */

echo "🔍 VALIDATING LEADS EDIT ASSET REFACTORING\n";
echo "==========================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// Define file paths
$rootPath = dirname(__DIR__);
$leadsEditPath = $rootPath . '/public_html/leads/edit.php';
$footerPath = $rootPath . '/public_html/templates/footer.php';
$editLeadsJsPath = $rootPath . '/public_html/assets/js/edit-leads.js';
$contactSelectorJsPath = $rootPath . '/public_html/assets/js/contact-selector.js';

// 1. Check that leads/edit.php exists and has been cleaned
if (!file_exists($leadsEditPath)) {
    $errors[] = "leads/edit.php not found at: $leadsEditPath";
} else {
    $editContent = file_get_contents($leadsEditPath);
    
    // Check for no inline JavaScript
    $inlineScriptCount = substr_count($editContent, '<script>');
    if ($inlineScriptCount > 0) {
        $errors[] = "leads/edit.php contains $inlineScriptCount inline script tags - should be 0";
    } else {
        $success[] = "✅ leads/edit.php contains no inline script tags";
    }
    
    // Check for no data injection in page content
    if (strpos($editContent, 'window.leadsEditData') !== false) {
        $errors[] = "leads/edit.php still contains data injection - should be moved to footer";
    } else {
        $success[] = "✅ leads/edit.php contains no data injection";
    }
    
    // Check for proper template structure
    if (strpos($editContent, 'require FOOTER;') !== false) {
        $success[] = "✅ leads/edit.php properly requires FOOTER template";
    } else {
        $warnings[] = "⚠️  leads/edit.php may not be using proper template structure";
    }
    
    // Check for absence of JavaScript functions
    $functionCount = substr_count(strtolower($editContent), 'function');
    if ($functionCount > 5) { // Allow some minimal PHP functions
        $warnings[] = "⚠️  leads/edit.php contains $functionCount 'function' instances - may have residual JavaScript";
    } else {
        $success[] = "✅ leads/edit.php contains minimal function references ($functionCount)";
    }
}

// 2. Check footer template conditional loading
if (!file_exists($footerPath)) {
    $errors[] = "footer.php not found at: $footerPath";
} else {
    $footerContent = file_get_contents($footerPath);
    
    // Check for leads edit conditional loading
    if (strpos($footerContent, "dir == 'leads' && \$page == 'edit'") !== false) {
        $success[] = "✅ Footer template has leads edit conditional loading";
    } else {
        $errors[] = "Footer template missing leads edit conditional loading";
    }
    
    // Check for required scripts
    $requiredScripts = [
        'contact-selector.js',
        'edit-leads.js',
        'hide-empty-structure.js'
    ];
    
    foreach ($requiredScripts as $script) {
        if (strpos($footerContent, $script) !== false) {
            $success[] = "✅ Footer loads $script";
        } else {
            $errors[] = "Footer missing $script loading";
        }
    }
    
    // Check for data injection in footer
    if (strpos($footerContent, 'window.leadsEditData') !== false) {
        $success[] = "✅ Footer contains data injection";
    } else {
        $errors[] = "Footer missing data injection";
    }
    
    // Check loading order
    $contactPos = strpos($footerContent, 'contact-selector.js');
    $dataPos = strpos($footerContent, 'window.leadsEditData');
    $editPos = strpos($footerContent, 'edit-leads.js');
    
    if ($contactPos < $dataPos && $dataPos < $editPos) {
        $success[] = "✅ Footer has correct asset loading order";
    } else {
        $warnings[] = "⚠️  Footer asset loading order may be suboptimal";
    }
}

// 3. Check JavaScript files exist and have content
if (!file_exists($editLeadsJsPath)) {
    $errors[] = "edit-leads.js not found at: $editLeadsJsPath";
} else {
    $jsContent = file_get_contents($editLeadsJsPath);
    $jsSize = strlen($jsContent);
    
    if ($jsSize > 10000) { // Should be substantial file now
        $success[] = "✅ edit-leads.js is substantial ($jsSize bytes)";
    } else {
        $warnings[] = "⚠️  edit-leads.js may be too small ($jsSize bytes)";
    }
    
    // Check for key functions
    $keyFunctions = [
        'getTimezoneFromLocation',
        'updateTimeConversion',
        'loadNotes',
        'handleStageChange'
    ];
    
    foreach ($keyFunctions as $func) {
        if (strpos($jsContent, $func) !== false) {
            $success[] = "✅ edit-leads.js contains $func function";
        } else {
            $warnings[] = "⚠️  edit-leads.js missing $func function";
        }
    }
    
    // Check for data access
    if (strpos($jsContent, 'window.leadsEditData') !== false) {
        $success[] = "✅ edit-leads.js accesses injected data";
    } else {
        $warnings[] = "⚠️  edit-leads.js may not be accessing injected data";
    }
}

if (!file_exists($contactSelectorJsPath)) {
    $warnings[] = "⚠️  contact-selector.js not found - may need to be created";
} else {
    $success[] = "✅ contact-selector.js exists";
}

// 4. Check test files exist
$testFiles = [
    $rootPath . '/tests/playwright/leads-edit-assets.spec.js',
    $rootPath . '/tests/phpunit/Unit/LeadsEditAssetOrganizationTest.php'
];

foreach ($testFiles as $testFile) {
    if (file_exists($testFile)) {
        $success[] = "✅ Test file exists: " . basename($testFile);
    } else {
        $warnings[] = "⚠️  Test file not found: " . basename($testFile);
    }
}

// Output results
echo "RESULTS:\n";
echo "========\n\n";

if (!empty($success)) {
    echo "✅ SUCCESS (" . count($success) . " items):\n";
    foreach ($success as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  WARNINGS (" . count($warnings) . " items):\n";
    foreach ($warnings as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ ERRORS (" . count($errors) . " items):\n";
    foreach ($errors as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

// Summary
$totalIssues = count($errors) + count($warnings);
if ($totalIssues === 0) {
    echo "🎉 VALIDATION PASSED! Asset refactoring appears to be complete and correct.\n";
    exit(0);
} elseif (count($errors) === 0) {
    echo "✅ VALIDATION MOSTLY PASSED with " . count($warnings) . " warnings to address.\n";
    exit(0);
} else {
    echo "❌ VALIDATION FAILED with " . count($errors) . " errors that must be fixed.\n";
    exit(1);
}
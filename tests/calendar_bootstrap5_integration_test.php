<?php
/**
 * Calendar Bootstrap 5 Integration Test Suite
 * 
 * Comprehensive testing for the recent calendar fixes:
 * - ID conflict resolution (nav-calendar vs calendar)
 * - Bootstrap 5 theme integration
 * - Asset dependencies validation
 * - Proper element positioning
 * - JavaScript functionality verification
 * 
 * @author CRM Framework  
 * @version 1.0
 */

require_once dirname(__DIR__) . '/config/system.php';

class CalendarBootstrap5IntegrationTest
{
    private $errors = [];
    private $warnings = [];
    private $test_results = [];
    private $start_time;
    
    public function __construct()
    {
        $this->start_time = microtime(true);
        echo "=== Calendar Bootstrap 5 Integration Test Suite ===\n";
        echo "Testing calendar fixes and Bootstrap 5 integration\n";
        echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";
    }
    
    public function runAllTests()
    {
        try {
            // Critical Fix Tests
            $this->testIDConflictResolution();
            $this->testBootstrap5AssetDependencies();
            $this->testJavaScriptConfiguration();
            $this->testCSSArchitecture();
            
            // Integration Tests  
            $this->testCalendarPageStructure();
            $this->testBootstrap5ThemeElements();
            $this->testResponsiveDesign();
            
            // Performance Tests
            $this->testAssetLoadTimes();
            
            $this->displayResults();
            
        } catch (Exception $e) {
            echo "FATAL ERROR: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }
    
    private function testIDConflictResolution()
    {
        echo "Testing ID conflict resolution...\n";
        
        // Test 1: Navigation template uses nav-calendar ID
        $navTemplate = DOCTEMPLATES . '/nav_item_calendar.php';
        if (file_exists($navTemplate)) {
            $content = file_get_contents($navTemplate);
            
            if (strpos($content, 'id="nav-calendar"') !== false) {
                $this->recordTest("Navigation ID fix", true, "Navigation uses id='nav-calendar'");
            } else {
                $this->recordTest("Navigation ID fix", false, "Navigation should use id='nav-calendar'");
            }
            
            if (strpos($content, 'id="calendar"') !== false) {
                $this->recordTest("ID conflict check", false, "CRITICAL: Navigation still uses conflicting id='calendar'");
            } else {
                $this->recordTest("ID conflict check", true, "No ID conflict in navigation");
            }
        } else {
            $this->recordTest("Navigation template", false, "Navigation template not found");
        }
        
        // Test 2: Calendar page has proper calendar container ID
        $calendarPage = DOCPUBLIC . '/calendar/index.php';
        if (file_exists($calendarPage)) {
            $content = file_get_contents($calendarPage);
            
            if (strpos($content, '<div id="calendar">') !== false) {
                $this->recordTest("Calendar container ID", true, "Calendar page has proper id='calendar' container");
            } else {
                $this->recordTest("Calendar container ID", false, "Calendar page missing id='calendar' container");
            }
        } else {
            $this->recordTest("Calendar page", false, "Calendar index.php not found");
        }
        
        echo "ID conflict resolution tests completed.\n\n";
    }
    
    private function testBootstrap5AssetDependencies()
    {
        echo "Testing Bootstrap 5 asset dependencies...\n";
        
        // Test 1: Header includes Bootstrap Icons
        $headerTemplate = DOCTEMPLATES . '/header.php';
        if (file_exists($headerTemplate)) {
            $content = file_get_contents($headerTemplate);
            
            if (strpos($content, 'bootstrap-icons') !== false && strpos($content, 'calendar') !== false) {
                $this->recordTest("Bootstrap Icons CSS", true, "Bootstrap Icons included for calendar");
            } else {
                $this->recordTest("Bootstrap Icons CSS", false, "Bootstrap Icons CSS missing for calendar");
            }
        } else {
            $this->recordTest("Header template", false, "Header template not found");
        }
        
        // Test 2: Footer includes Bootstrap 5 plugin
        $footerTemplate = DOCTEMPLATES . '/footer.php';
        if (file_exists($footerTemplate)) {
            $content = file_get_contents($footerTemplate);
            
            if (strpos($content, 'fullcalendar/bootstrap5') !== false && strpos($content, 'calendar') !== false) {
                $this->recordTest("Bootstrap 5 plugin", true, "FullCalendar Bootstrap 5 plugin included");
            } else {
                $this->recordTest("Bootstrap 5 plugin", false, "FullCalendar Bootstrap 5 plugin missing");
            }
        } else {
            $this->recordTest("Footer template", false, "Footer template not found");
        }
        
        // Test 3: Calendar CSS file structure
        $calendarCSS = DOCPUBLIC . '/assets/css/calendar.css';
        if (file_exists($calendarCSS)) {
            $content = file_get_contents($calendarCSS);
            
            // Should NOT have aggressive overrides anymore
            $aggressiveRules = ['!important', 'position: fixed', 'z-index: 9999'];
            $hasAggressive = false;
            $aggressiveCount = 0;
            
            foreach ($aggressiveRules as $rule) {
                $matches = substr_count($content, $rule);
                if ($matches > 2) { // Allow minimal usage
                    $hasAggressive = true;
                    $aggressiveCount += $matches;
                }
            }
            
            if (!$hasAggressive) {
                $this->recordTest("CSS architecture", true, "Calendar CSS uses clean Bootstrap integration");
            } else {
                $this->recordTest("CSS architecture", false, "Calendar CSS still has $aggressiveCount aggressive overrides");
            }
            
            // Should have Bootstrap integration styles
            if (strpos($content, 'Bootstrap 5') !== false || strpos($content, 'bootstrap') !== false) {
                $this->recordTest("Bootstrap CSS integration", true, "Calendar CSS includes Bootstrap integration");
            } else {
                $this->recordTest("Bootstrap CSS integration", false, "Calendar CSS missing Bootstrap integration");
            }
        } else {
            $this->recordTest("Calendar CSS", false, "Calendar CSS file not found");
        }
        
        echo "Bootstrap 5 asset dependency tests completed.\n\n";
    }
    
    private function testJavaScriptConfiguration()
    {
        echo "Testing JavaScript configuration...\n";
        
        $calendarJS = DOCPUBLIC . '/assets/js/calendar.js';
        if (file_exists($calendarJS)) {
            $content = file_get_contents($calendarJS);
            
            // Test 1: Bootstrap 5 theme system configured
            if (strpos($content, "themeSystem: 'bootstrap5'") !== false) {
                $this->recordTest("Bootstrap 5 theme config", true, "JavaScript uses themeSystem: 'bootstrap5'");
            } else {
                $this->recordTest("Bootstrap 5 theme config", false, "Missing themeSystem: 'bootstrap5' configuration");
            }
            
            // Test 2: No aggressive positioning JavaScript
            $aggressiveJS = ['calendarEl.style.position', 'enforceContainment', 'forcePosition'];
            $hasAggressive = false;
            
            foreach ($aggressiveJS as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    $hasAggressive = true;
                    break;
                }
            }
            
            if (!$hasAggressive) {
                $this->recordTest("JavaScript positioning", true, "No aggressive positioning JavaScript");
            } else {
                $this->recordTest("JavaScript positioning", false, "Still contains aggressive positioning code");
            }
            
            // Test 3: Proper element selector
            if (strpos($content, "document.getElementById('calendar')") !== false) {
                $this->recordTest("Element selector", true, "Uses proper calendar element selector");
            } else {
                $this->recordTest("Element selector", false, "Missing proper element selector");
            }
            
            // Test 4: Console logging for debugging
            if (strpos($content, "console.log") !== false) {
                $this->recordTest("Debug logging", true, "Contains debug console logging");
            } else {
                $this->recordTest("Debug logging", false, "Missing debug console logging");
            }
            
        } else {
            $this->recordTest("Calendar JavaScript", false, "Calendar JavaScript file not found");
        }
        
        echo "JavaScript configuration tests completed.\n\n";
    }
    
    private function testCSSArchitecture()
    {
        echo "Testing CSS architecture...\n";
        
        $calendarCSS = DOCPUBLIC . '/assets/css/calendar.css';
        if (file_exists($calendarCSS)) {
            $content = file_get_contents($calendarCSS);
            $lines = explode("\n", $content);
            $lineCount = count($lines);
            
            // Test 1: File size should be reasonable (not bloated with overrides)
            if ($lineCount < 100) {
                $this->recordTest("CSS file size", true, "CSS file is concise ($lineCount lines)");
            } else {
                $this->recordTest("CSS file size", false, "CSS file may be bloated ($lineCount lines)");
            }
            
            // Test 2: Should not have excessive !important rules
            $importantCount = substr_count($content, '!important');
            if ($importantCount < 5) {
                $this->recordTest("!important usage", true, "Minimal !important usage ($importantCount)");
            } else {
                $this->recordTest("!important usage", false, "Excessive !important usage ($importantCount)");
            }
            
            // Test 3: Should have proper Bootstrap integration
            $bootstrapPatterns = ['.card', '.btn', '.fc-'];
            $hasBootstrapIntegration = false;
            
            foreach ($bootstrapPatterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    $hasBootstrapIntegration = true;
                    break;
                }
            }
            
            if ($hasBootstrapIntegration) {
                $this->recordTest("Bootstrap CSS integration", true, "Contains Bootstrap integration styles");
            } else {
                $this->recordTest("Bootstrap CSS integration", false, "Missing Bootstrap integration styles");
            }
            
        } else {
            $this->recordTest("CSS architecture", false, "Calendar CSS file not found");
        }
        
        echo "CSS architecture tests completed.\n\n";
    }
    
    private function testCalendarPageStructure()
    {
        echo "Testing calendar page structure...\n";
        
        $calendarPage = DOCPUBLIC . '/calendar/index.php';
        if (file_exists($calendarPage)) {
            $content = file_get_contents($calendarPage);
            
            // Test Bootstrap card structure
            if (strpos($content, '<div class="card">') !== false && 
                strpos($content, '<div class="card-body">') !== false &&
                strpos($content, '<div id="calendar">') !== false) {
                $this->recordTest("Bootstrap card structure", true, "Calendar uses proper Bootstrap card structure");
            } else {
                $this->recordTest("Bootstrap card structure", false, "Missing proper Bootstrap card structure");
            }
            
            // Test stats cards structure
            if (strpos($content, 'Calls Today') !== false && 
                strpos($content, 'Emails Today') !== false &&
                strpos($content, 'Meetings Today') !== false) {
                $this->recordTest("Stats cards", true, "Stats cards properly implemented");
            } else {
                $this->recordTest("Stats cards", false, "Missing stats cards");
            }
            
            // Test CSRF token
            if (strpos($content, 'csrf_token') !== false) {
                $this->recordTest("CSRF protection", true, "CSRF token implemented");
            } else {
                $this->recordTest("CSRF protection", false, "Missing CSRF protection");
            }
            
        } else {
            $this->recordTest("Calendar page structure", false, "Calendar page not found");
        }
        
        echo "Calendar page structure tests completed.\n\n";
    }
    
    private function testBootstrap5ThemeElements()
    {
        echo "Testing Bootstrap 5 theme elements...\n";
        
        // This would ideally be done with browser testing, but we can check configuration
        $calendarJS = DOCPUBLIC . '/assets/js/calendar.js';
        if (file_exists($calendarJS)) {
            $content = file_get_contents($calendarJS);
            
            // Check for Bootstrap 5 compatible configuration
            $bootstrap5Config = [
                "themeSystem: 'bootstrap5'",
                "headerToolbar:",
                "height: 'auto'"
            ];
            
            $configCount = 0;
            foreach ($bootstrap5Config as $config) {
                if (strpos($content, $config) !== false) {
                    $configCount++;
                }
            }
            
            if ($configCount == count($bootstrap5Config)) {
                $this->recordTest("Bootstrap 5 configuration", true, "All Bootstrap 5 configurations present");
            } else {
                $this->recordTest("Bootstrap 5 configuration", false, "Missing Bootstrap 5 configurations ($configCount/" . count($bootstrap5Config) . ")");
            }
        }
        
        echo "Bootstrap 5 theme elements tests completed.\n\n";
    }
    
    private function testResponsiveDesign()
    {
        echo "Testing responsive design...\n";
        
        $calendarPage = DOCPUBLIC . '/calendar/index.php';
        if (file_exists($calendarPage)) {
            $content = file_get_contents($calendarPage);
            
            // Test Bootstrap grid classes
            $responsiveClasses = ['col-12', 'col-md-', 'row'];
            $responsiveCount = 0;
            
            foreach ($responsiveClasses as $class) {
                if (strpos($content, $class) !== false) {
                    $responsiveCount++;
                }
            }
            
            if ($responsiveCount >= 2) {
                $this->recordTest("Responsive grid", true, "Uses Bootstrap responsive grid classes");
            } else {
                $this->recordTest("Responsive grid", false, "Missing Bootstrap responsive grid classes");
            }
        }
        
        echo "Responsive design tests completed.\n\n";
    }
    
    private function testAssetLoadTimes()
    {
        echo "Testing asset load performance...\n";
        
        $assets = [
            'Calendar CSS' => DOCPUBLIC . '/assets/css/calendar.css',
            'Calendar JS' => DOCPUBLIC . '/assets/js/calendar.js'
        ];
        
        foreach ($assets as $name => $path) {
            if (file_exists($path)) {
                $size = filesize($path);
                $sizeKB = round($size / 1024, 2);
                
                if ($sizeKB < 50) { // Under 50KB is reasonable
                    $this->recordTest("$name file size", true, "File size: {$sizeKB}KB");
                } else {
                    $this->recordTest("$name file size", false, "File size too large: {$sizeKB}KB");
                }
            } else {
                $this->recordTest("$name file", false, "Asset file not found");
            }
        }
        
        echo "Asset performance tests completed.\n\n";
    }
    
    private function recordTest($testName, $passed, $details = '')
    {
        $this->test_results[] = [
            'name' => $testName,
            'passed' => $passed,
            'details' => $details
        ];
        
        $status = $passed ? '✓ PASS' : '✗ FAIL';
        $message = $details ? " - $details" : '';
        echo "  $status: $testName$message\n";
        
        if (!$passed) {
            $this->errors[] = "$testName: $details";
        }
    }
    
    private function displayResults()
    {
        $duration = round(microtime(true) - $this->start_time, 2);
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "CALENDAR BOOTSTRAP 5 INTEGRATION TEST RESULTS\n";
        echo str_repeat("=", 60) . "\n";
        
        $totalTests = count($this->test_results);
        $passedTests = count(array_filter($this->test_results, function($test) {
            return $test['passed'];
        }));
        $failedTests = $totalTests - $passedTests;
        
        echo "Total Tests: $totalTests\n";
        echo "Passed: $passedTests\n";
        echo "Failed: $failedTests\n";
        echo "Duration: {$duration}s\n\n";
        
        if ($failedTests == 0) {
            echo "🎉 ALL TESTS PASSED!\n";
            echo "✅ ID conflict resolved\n";
            echo "✅ Bootstrap 5 theme integration complete\n";
            echo "✅ Asset dependencies properly configured\n";
            echo "✅ Calendar positioned correctly in Bootstrap cards\n";
            echo "✅ Clean CSS architecture implemented\n\n";
            echo "The calendar system is fully integrated with Bootstrap 5!\n";
        } else {
            echo "❌ $failedTests TEST(S) FAILED:\n\n";
            foreach ($this->errors as $error) {
                echo "- $error\n";
            }
            echo "\nPlease fix the failing tests before deployment.\n";
        }
        
        if (!empty($this->warnings)) {
            echo "\n⚠️  WARNINGS:\n";
            foreach ($this->warnings as $warning) {
                echo "- $warning\n";
            }
        }
        
        echo "\n" . str_repeat("=", 60) . "\n";
    }
}

// Run the tests if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $test = new CalendarBootstrap5IntegrationTest();
    $test->runAllTests();
}
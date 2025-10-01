<?php
/**
 * Calendar Navigation Test
 * 
 * Tests that the calendar navigation tab is properly integrated
 * into the DemoCRM navigation system.
 */

require_once dirname(__DIR__) . '/config/system.php';

class CalendarNavigationTest
{
    private $errors = [];
    
    public function runTests()
    {
        echo "Running Calendar Navigation Tests...\n\n";
        
        $this->testCalendarConstantExists();
        $this->testEnglishTranslationExists();
        $this->testSpanishTranslationExists();
        $this->testNavigationTemplateExists();
        $this->testNavigationIncluded();
        
        $this->displayResults();
    }
    
    private function testCalendarConstantExists()
    {
        echo "Testing CALENDAR constant... ";
        if (defined('CALENDAR')) {
            $expected = URL . "/calendar";
            if (CALENDAR === $expected) {
                echo "✓ PASS\n";
            } else {
                $this->errors[] = "CALENDAR constant has wrong value. Expected: $expected, Got: " . CALENDAR;
                echo "✗ FAIL\n";
            }
        } else {
            $this->errors[] = "CALENDAR constant is not defined";
            echo "✗ FAIL\n";
        }
    }
    
    private function testEnglishTranslationExists()
    {
        echo "Testing English translation... ";
        $langFile = LANG . '/en.php';
        if (file_exists($langFile)) {
            $lang = include $langFile;
            if (isset($lang['navbar_calendar']) && $lang['navbar_calendar'] === 'Calendar') {
                echo "✓ PASS\n";
            } else {
                $this->errors[] = "English translation for 'navbar_calendar' is missing or incorrect";
                echo "✗ FAIL\n";
            }
        } else {
            $this->errors[] = "English language file not found";
            echo "✗ FAIL\n";
        }
    }
    
    private function testSpanishTranslationExists()
    {
        echo "Testing Spanish translation... ";
        $langFile = LANG . '/es.php';
        if (file_exists($langFile)) {
            $lang = include $langFile;
            if (isset($lang['navbar_calendar']) && $lang['navbar_calendar'] === 'Calendario') {
                echo "✓ PASS\n";
            } else {
                $this->errors[] = "Spanish translation for 'navbar_calendar' is missing or incorrect";
                echo "✗ FAIL\n";
            }
        } else {
            $this->errors[] = "Spanish language file not found";
            echo "✗ FAIL\n";
        }
    }
    
    private function testNavigationTemplateExists()
    {
        echo "Testing navigation template exists... ";
        $templateFile = DOCTEMPLATES . '/nav_item_calendar.php';
        if (file_exists($templateFile)) {
            $content = file_get_contents($templateFile);
            if (strpos($content, 'navbar_calendar') !== false && strpos($content, 'CALENDAR') !== false) {
                // CRITICAL: Check for ID conflict fix
                if (strpos($content, 'id="nav-calendar"') !== false) {
                    echo "✓ PASS (with ID conflict fix)\n";
                } else if (strpos($content, 'id="calendar"') !== false) {
                    $this->errors[] = "Navigation template still uses conflicting id='calendar' (should be 'nav-calendar')";
                    echo "✗ FAIL - ID CONFLICT\n";
                } else {
                    echo "✓ PASS\n";
                }
            } else {
                $this->errors[] = "Navigation template exists but doesn't contain required elements";
                echo "✗ FAIL\n";
            }
        } else {
            $this->errors[] = "Navigation template file nav_item_calendar.php not found";
            echo "✗ FAIL\n";
        }
    }
    
    private function testNavigationIncluded()
    {
        echo "Testing navigation template is included... ";
        $navFile = DOCTEMPLATES . '/nav.php';
        if (file_exists($navFile)) {
            $content = file_get_contents($navFile);
            if (strpos($content, "require_once 'nav_item_calendar.php';") !== false) {
                echo "✓ PASS\n";
            } else {
                $this->errors[] = "Calendar navigation template is not included in nav.php";
                echo "✗ FAIL\n";
            }
        } else {
            $this->errors[] = "Main navigation file nav.php not found";
            echo "✗ FAIL\n";
        }
    }
    
    private function displayResults()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "TEST RESULTS\n";
        echo str_repeat("=", 50) . "\n";
        
        if (empty($this->errors)) {
            echo "✓ ALL TESTS PASSED!\n";
            echo "Calendar navigation has been successfully integrated.\n";
        } else {
            echo "✗ " . count($this->errors) . " TEST(S) FAILED:\n\n";
            foreach ($this->errors as $error) {
                echo "- $error\n";
            }
        }
        echo "\n";
    }
}

// Run the tests
$test = new CalendarNavigationTest();
$test->runTests();
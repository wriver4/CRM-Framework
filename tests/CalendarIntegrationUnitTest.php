<?php
/**
 * Calendar Integration Unit Test
 * 
 * PHPUnit test for calendar system integration
 */

use PHPUnit\Framework\TestCase;

// Set up environment for CLI testing
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['DOCUMENT_ROOT'] = '/run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/public_html';

class CalendarIntegrationUnitTest extends TestCase
{
    private static $rootPath;
    
    public static function setUpBeforeClass(): void
    {
        self::$rootPath = dirname(__DIR__);
    }
    
    public function testCalendarFilesExist()
    {
        $this->assertFileExists(self::$rootPath . '/classes/Models/CalendarEvent.php', 'CalendarEvent model should exist');
        $this->assertFileExists(self::$rootPath . '/tests/enhanced_integration_test.php', 'Enhanced integration test should exist');
        $this->assertFileExists(self::$rootPath . '/public_html/admin/languages/en.php', 'Language file should exist');
    }
    
    public function testEnhancedIntegrationTestContainsCalendar()
    {
        $testFile = self::$rootPath . '/tests/enhanced_integration_test.php';
        $content = file_get_contents($testFile);
        
        $this->assertStringContainsString("'calendar' => [", $content, 'Calendar module should be configured');
        $this->assertStringContainsString('CalendarEvent.php', $content, 'CalendarEvent class should be included');
        $this->assertStringContainsString('private $calendar;', $content, 'Calendar property should be declared');
        $this->assertStringContainsString('$this->calendar = new CalendarEvent();', $content, 'Calendar should be initialized');
    }
    
    public function testCalendarLanguageKeys()
    {
        $testFile = self::$rootPath . '/tests/enhanced_integration_test.php';
        $content = file_get_contents($testFile);
        
        $expectedKeys = [
            'event_type_phone_call',
            'event_type_email', 
            'event_type_text_message',
            'event_type_internal_note',
            'event_type_virtual_meeting',
            'event_type_in_person_meeting',
            'priority_1',
            'priority_5',
            'priority_10'
        ];
        
        foreach ($expectedKeys as $key) {
            $this->assertStringContainsString("'$key'", $content, "Language key '$key' should be configured");
        }
    }
    
    public function testCalendarPermissions()
    {
        $testFile = self::$rootPath . '/tests/enhanced_integration_test.php';
        $content = file_get_contents($testFile);
        
        $expectedPermissions = [
            'view_calendar',
            'create_events',
            'edit_events', 
            'delete_events'
        ];
        
        foreach ($expectedPermissions as $permission) {
            $this->assertStringContainsString("'$permission'", $content, "Permission '$permission' should be configured");
        }
    }
    
    public function testLanguageFileContainsCalendarKeys()
    {
        $langFile = self::$rootPath . '/public_html/admin/languages/en.php';
        $content = file_get_contents($langFile);
        
        $calendarKeys = [
            'event_type_phone_call',
            'event_type_email',
            'event_type_text_message',
            'event_type_internal_note',
            'event_type_virtual_meeting',
            'event_type_in_person_meeting',
            'priority_1',
            'priority_5',
            'priority_10'
        ];
        
        foreach ($calendarKeys as $key) {
            $this->assertStringContainsString("'$key'", $content, "Language key '$key' should exist in language file");
        }
    }
    
    public function testCalendarEventClassStructure()
    {
        $calendarFile = self::$rootPath . '/classes/Models/CalendarEvent.php';
        $content = file_get_contents($calendarFile);
        
        $this->assertStringContainsString('class CalendarEvent', $content, 'CalendarEvent class should be declared');
        $this->assertStringContainsString('function __construct', $content, 'Constructor should exist');
        $this->assertStringContainsString('function create', $content, 'Create method should exist');
        $this->assertStringContainsString('function update', $content, 'Update method should exist');
        $this->assertStringContainsString('function delete', $content, 'Delete method should exist');
    }
    
    public function testDatabaseSchemaFiles()
    {
        $structureFile = self::$rootPath . '/sql/democrm_democrm_structure.sql';
        $this->assertFileExists($structureFile, 'Database structure file should exist');
        
        $content = file_get_contents($structureFile);
        $this->assertStringContainsString('calendar_events', $content, 'Calendar events table should be in structure');
        $this->assertStringContainsString('calendar_settings', $content, 'Calendar settings table should be in structure');
    }
}
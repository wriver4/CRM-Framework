# CalDAV Implementation Plan for CRM-Nextcloud Integration

## 🎯 Project Overview

**Goal:** Enable full two-way synchronization between your FullCalendar CRM and Nextcloud Calendar via CalDAV protocol.

**Benefits:**
- ✅ Real-time bi-directional sync
- ✅ Mobile access via Nextcloud apps
- ✅ Standards-based integration (RFC 4791/5545)
- ✅ Multi-client support (Thunderbird, Outlook, etc.)
- ✅ Offline sync capabilities

---

## 📋 Phase 1: Foundation Setup (Week 1-2)

### 1.1 Dependencies & Libraries
```bash
composer require sabre/dav
composer require sabre/vobject
composer require ramsey/uuid
```

### 1.2 Database Schema Extensions
```sql
-- Add CalDAV-specific fields to existing tasks table
ALTER TABLE tasks ADD COLUMN caldav_uid VARCHAR(255) UNIQUE;
ALTER TABLE tasks ADD COLUMN caldav_etag VARCHAR(255);
ALTER TABLE tasks ADD COLUMN caldav_lastmod TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE tasks ADD COLUMN caldav_sequence INT DEFAULT 0;

-- Create CalDAV calendars table
CREATE TABLE caldav_calendars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    uri VARCHAR(255) NOT NULL,
    displayname VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#3366CC',
    timezone VARCHAR(255) DEFAULT 'UTC',
    components VARCHAR(255) DEFAULT 'VEVENT,VTODO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_calendar (user_id, uri)
);

-- Create CalDAV sync log table
CREATE TABLE caldav_sync_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT,
    action ENUM('create', 'update', 'delete'),
    caldav_uid VARCHAR(255),
    sync_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sync_status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    error_message TEXT,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- Insert default CRM calendar
INSERT INTO caldav_calendars (user_id, uri, displayname, description, color) 
VALUES ('admin', 'crm-tasks', 'CRM Tasks', 'Tasks from CRM system', '#007bff');
```

### 1.3 Directory Structure
```
crm-calendar/
├── caldav/
│   ├── server.php              # CalDAV server endpoint
│   ├── config/
│   │   └── caldav_config.php   # CalDAV configuration
│   ├── backends/
│   │   ├── CalendarBackend.php # Calendar data backend
│   │   ├── PrincipalBackend.php# User authentication backend
│   │   └── PropertyBackend.php # Property storage backend
│   ├── plugins/
│   │   └── CRMPlugin.php       # Custom CRM-specific logic
│   └── utils/
│       ├── VEventConverter.php # Convert tasks ↔ iCal events
│       └── SyncManager.php     # Sync coordination
├── api/
│   └── caldav_tasks.php        # Enhanced API with CalDAV support
└── config/
    └── caldav_routes.php       # URL routing for CalDAV
```

---

## 🔧 Phase 2: Core CalDAV Server (Week 3-4)

### 2.1 CalDAV Server Setup
```php
// caldav/server.php
<?php
require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once 'backends/CalendarBackend.php';
require_once 'backends/PrincipalBackend.php';

use Sabre\DAV;
use Sabre\CalDAV;
use Sabre\DAVACL;

// Initialize backends
$pdo = getDB();
$principalBackend = new CRM\CalDAV\PrincipalBackend($pdo);
$calendarBackend = new CRM\CalDAV\CalendarBackend($pdo);

// Create directory tree
$tree = [
    new DAVACL\PrincipalCollection($principalBackend),
    new CalDAV\CalendarRoot($principalBackend, $calendarBackend)
];

// Initialize server
$server = new DAV\Server($tree);
$server->setBaseUri('/caldav/');

// Add plugins
$server->addPlugin(new DAVACL\Plugin());
$server->addPlugin(new CalDAV\Plugin());
$server->addPlugin(new DAV\Sync\Plugin());
$server->addPlugin(new CalDAV\Schedule\Plugin());

// Authentication
$authBackend = new DAV\Auth\Backend\PDO($pdo);
$authPlugin = new DAV\Auth\Plugin($authBackend);
$server->addPlugin($authPlugin);

// Execute request
$server->exec();
```

### 2.2 Calendar Backend Implementation
```php
// caldav/backends/CalendarBackend.php
<?php
namespace CRM\CalDAV;

use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\VObject;

class CalendarBackend extends AbstractBackend {
    private $pdo;
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function getCalendarsForUser($principalUri) {
        $userId = $this->extractUserId($principalUri);
        
        $stmt = $this->pdo->prepare('
            SELECT id, uri, displayname, description, color, components
            FROM caldav_calendars 
            WHERE user_id = ?
        ');
        $stmt->execute([$userId]);
        
        $calendars = [];
        while ($row = $stmt->fetch()) {
            $calendars[] = [
                'id' => $row['id'],
                'uri' => $row['uri'],
                'principaluri' => $principalUri,
                '{DAV:}displayname' => $row['displayname'],
                '{urn:ietf:params:xml:ns:caldav}calendar-description' => $row['description'],
                '{http://apple.com/ns/ical/}calendar-color' => $row['color'],
                '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => 
                    new \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT', 'VTODO'])
            ];
        }
        
        return $calendars;
    }
    
    public function getCalendarObjects($calendarId) {
        $stmt = $this->pdo->prepare('
            SELECT caldav_uid, caldav_etag, caldav_lastmod
            FROM tasks 
            WHERE calendar_id = ? AND caldav_uid IS NOT NULL
        ');
        $stmt->execute([$calendarId]);
        
        $objects = [];
        while ($row = $stmt->fetch()) {
            $objects[] = [
                'id' => $row['caldav_uid'],
                'uri' => $row['caldav_uid'] . '.ics',
                'lastmodified' => strtotime($row['caldav_lastmod']),
                'etag' => '"' . $row['caldav_etag'] . '"',
                'size' => strlen($this->getCalendarObject($calendarId, $row['caldav_uid'] . '.ics')['calendardata'])
            ];
        }
        
        return $objects;
    }
    
    public function getCalendarObject($calendarId, $objectUri) {
        $uid = str_replace('.ics', '', $objectUri);
        
        $stmt = $this->pdo->prepare('
            SELECT * FROM tasks 
            WHERE calendar_id = ? AND caldav_uid = ?
        ');
        $stmt->execute([$calendarId, $uid]);
        $task = $stmt->fetch();
        
        if (!$task) {
            return null;
        }
        
        // Convert task to iCal format
        $converter = new \CRM\CalDAV\VEventConverter();
        $icalData = $converter->taskToVEvent($task);
        
        return [
            'id' => $task['caldav_uid'],
            'uri' => $objectUri,
            'lastmodified' => strtotime($task['caldav_lastmod']),
            'etag' => '"' . $task['caldav_etag'] . '"',
            'calendardata' => $icalData
        ];
    }
    
    public function createCalendarObject($calendarId, $objectUri, $calendarData) {
        $converter = new \CRM\CalDAV\VEventConverter();
        $taskData = $converter->vEventToTask($calendarData);
        
        // Insert into tasks table
        $stmt = $this->pdo->prepare('
            INSERT INTO tasks (
                title, description, task_type, start_datetime, end_datetime,
                contact_name, contact_phone, contact_email, priority, status,
                caldav_uid, caldav_etag, calendar_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $uid = str_replace('.ics', '', $objectUri);
        $etag = md5($calendarData . time());
        
        return $stmt->execute([
            $taskData['title'],
            $taskData['description'],
            $taskData['task_type'],
            $taskData['start_datetime'],
            $taskData['end_datetime'],
            $taskData['contact_name'],
            $taskData['contact_phone'],
            $taskData['contact_email'],
            $taskData['priority'],
            $taskData['status'],
            $uid,
            $etag,
            $calendarId
        ]);
    }
    
    public function updateCalendarObject($calendarId, $objectUri, $calendarData) {
        $converter = new \CRM\CalDAV\VEventConverter();
        $taskData = $converter->vEventToTask($calendarData);
        
        $uid = str_replace('.ics', '', $objectUri);
        $etag = md5($calendarData . time());
        
        $stmt = $this->pdo->prepare('
            UPDATE tasks SET 
                title = ?, description = ?, task_type = ?, 
                start_datetime = ?, end_datetime = ?,
                contact_name = ?, contact_phone = ?, contact_email = ?,
                priority = ?, status = ?, caldav_etag = ?,
                caldav_sequence = caldav_sequence + 1
            WHERE calendar_id = ? AND caldav_uid = ?
        ');
        
        return $stmt->execute([
            $taskData['title'], $taskData['description'], $taskData['task_type'],
            $taskData['start_datetime'], $taskData['end_datetime'],
            $taskData['contact_name'], $taskData['contact_phone'], $taskData['contact_email'],
            $taskData['priority'], $taskData['status'], $etag,
            $calendarId, $uid
        ]);
    }
    
    public function deleteCalendarObject($calendarId, $objectUri) {
        $uid = str_replace('.ics', '', $objectUri);
        
        $stmt = $this->pdo->prepare('
            DELETE FROM tasks 
            WHERE calendar_id = ? AND caldav_uid = ?
        ');
        
        return $stmt->execute([$calendarId, $uid]);
    }
}
```

---

## 🔄 Phase 3: Data Conversion Layer (Week 5)

### 3.1 Task ↔ iCal Event Converter
```php
// caldav/utils/VEventConverter.php
<?php
namespace CRM\CalDAV;

use Sabre\VObject;

class VEventConverter {
    
    public function taskToVEvent($task) {
        $calendar = new VObject\Component\VCalendar();
        
        $event = $calendar->createComponent('VEVENT');
        
        // Basic properties
        $event->UID = $task['caldav_uid'] ?: $this->generateUID();
        $event->SUMMARY = $task['title'];
        $event->DESCRIPTION = $task['description'];
        
        // Dates
        $startDate = new \DateTime($task['start_datetime']);
        $event->DTSTART = $startDate;
        
        if ($task['end_datetime']) {
            $endDate = new \DateTime($task['end_datetime']);
            $event->DTEND = $endDate;
        }
        
        // Status mapping
        $statusMap = [
            'pending' => 'TENTATIVE',
            'completed' => 'CONFIRMED',
            'cancelled' => 'CANCELLED'
        ];
        $event->STATUS = $statusMap[$task['status']] ?? 'TENTATIVE';
        
        // Priority mapping (1-9 scale)
        $priorityMap = [
            'low' => 9,
            'medium' => 5,
            'high' => 1
        ];
        $event->PRIORITY = $priorityMap[$task['priority']] ?? 5;
        
        // Categories for task type
        $event->CATEGORIES = strtoupper($task['task_type']);
        
        // Contact information as attendee
        if ($task['contact_email']) {
            $attendee = $event->createProperty('ATTENDEE', 'mailto:' . $task['contact_email']);
            $attendee->add('CN', $task['contact_name'] ?: $task['contact_email']);
            $attendee->add('ROLE', 'REQ-PARTICIPANT');
            $event->add($attendee);
        }
        
        // Custom properties for CRM data
        if ($task['contact_phone']) {
            $event->add('X-CRM-CONTACT-PHONE', $task['contact_phone']);
        }
        
        if ($task['notes']) {
            $event->add('X-CRM-NOTES', $task['notes']);
        }
        
        $event->add('X-CRM-TASK-TYPE', $task['task_type']);
        $event->add('X-CRM-TASK-ID', $task['id']);
        
        // Timestamps
        $event->DTSTAMP = new \DateTime();
        $event->CREATED = new \DateTime($task['created_at']);
        $event->LAST_MODIFIED = new \DateTime($task['updated_at']);
        
        // Sequence for versioning
        $event->SEQUENCE = $task['caldav_sequence'] ?? 0;
        
        $calendar->add($event);
        
        return $calendar->serialize();
    }
    
    public function vEventToTask($icalData) {
        $calendar = VObject\Reader::read($icalData);
        $event = $calendar->VEVENT;
        
        $task = [
            'title' => (string) $event->SUMMARY,
            'description' => (string) ($event->DESCRIPTION ?? ''),
            'start_datetime' => $event->DTSTART->getDateTime()->format('Y-m-d H:i:s'),
            'end_datetime' => $event->DTEND ? $event->DTEND->getDateTime()->format('Y-m-d H:i:s') : null,
        ];
        
        // Status mapping
        $statusMap = [
            'TENTATIVE' => 'pending',
            'CONFIRMED' => 'completed',
            'CANCELLED' => 'cancelled'
        ];
        $task['status'] = $statusMap[(string) ($event->STATUS ?? 'TENTATIVE')] ?? 'pending';
        
        // Priority mapping
        $priority = (int) ($event->PRIORITY ?? 5);
        if ($priority <= 3) {
            $task['priority'] = 'high';
        } elseif ($priority <= 6) {
            $task['priority'] = 'medium';
        } else {
            $task['priority'] = 'low';
        }
        
        // Task type from categories or custom property
        $taskType = (string) ($event->{'X-CRM-TASK-TYPE'} ?? '');
        if (!$taskType && isset($event->CATEGORIES)) {
            $category = strtolower((string) $event->CATEGORIES);
            $taskType = in_array($category, ['call', 'email', 'meeting', 'follow_up']) ? $category : 'call';
        }
        $task['task_type'] = $taskType ?: 'call';
        
        // Contact information from attendee
        if (isset($event->ATTENDEE)) {
            $attendeeEmail = str_replace('mailto:', '', (string) $event->ATTENDEE);
            $task['contact_email'] = $attendeeEmail;
            
            $attendeeName = (string) ($event->ATTENDEE['CN'] ?? '');
            $task['contact_name'] = $attendeeName;
        }
        
        // Custom CRM properties
        $task['contact_phone'] = (string) ($event->{'X-CRM-CONTACT-PHONE'} ?? '');
        $task['notes'] = (string) ($event->{'X-CRM-NOTES'} ?? '');
        
        return $task;
    }
    
    private function generateUID() {
        return \Ramsey\Uuid\Uuid::uuid4()->toString() . '@yourcrm.com';
    }
}
```

---

## 🔐 Phase 4: Authentication & Security (Week 6)

### 4.1 User Authentication Backend
```php
// caldav/backends/PrincipalBackend.php
<?php
namespace CRM\CalDAV;

use Sabre\DAVACL\PrincipalBackend\AbstractBackend;

class PrincipalBackend extends AbstractBackend {
    private $pdo;
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function getPrincipalsByPrefix($prefixPath) {
        $principals = [];
        
        if ($prefixPath === 'principals/users') {
            // Get all CRM users
            $stmt = $this->pdo->prepare('SELECT username, email, display_name FROM users');
            $stmt->execute();
            
            while ($row = $stmt->fetch()) {
                $principals[] = [
                    'uri' => 'principals/users/' . $row['username'],
                    '{DAV:}displayname' => $row['display_name'] ?: $row['username'],
                    '{http://sabredav.org/ns}email-address' => $row['email']
                ];
            }
        }
        
        return $principals;
    }
    
    public function getPrincipalByPath($path) {
        if (strpos($path, 'principals/users/') !== 0) {
            return null;
        }
        
        $username = substr($path, strlen('principals/users/'));
        
        $stmt = $this->pdo->prepare('
            SELECT username, email, display_name 
            FROM users 
            WHERE username = ?
        ');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return null;
        }
        
        return [
            'uri' => $path,
            '{DAV:}displayname' => $user['display_name'] ?: $user['username'],
            '{http://sabredav.org/ns}email-address' => $user['email']
        ];
    }
}
```

### 4.2 Security Configuration
```php
// caldav/config/caldav_config.php
<?php
return [
    'auth' => [
        'method' => 'digest', // or 'basic'
        'realm' => 'CRM CalDAV Server',
        'users_table' => 'users',
        'username_field' => 'username',
        'password_field' => 'password' // Should be hashed
    ],
    
    'cors' => [
        'allowed_origins' => ['https://nextcloud.yourdomain.com'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PROPFIND', 'PROPPATCH', 'REPORT'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'Depth', 'User-Agent', 'X-File-Size', 'X-Requested-With', 'If-Modified-Since', 'X-File-Name', 'Cache-Control']
    ],
    
    'rate_limiting' => [
        'requests_per_minute' => 60,
        'burst_limit' => 10
    ],
    
    'calendar_defaults' => [
        'timezone' => 'America/New_York',
        'color' => '#007bff',
        'components' => 'VEVENT,VTODO'
    ]
];
```

---

## 🔗 Phase 5: Nextcloud Integration (Week 7)

### 5.1 Nextcloud Configuration
```bash
# In Nextcloud admin settings, add remote calendar:
# URL: https://yourcrm.com/caldav/calendars/username/crm-tasks/
# Username: your_crm_username
# Password: your_crm_password
```

### 5.2 Discovery Service
```php
// .well-known/caldav (redirect to main CalDAV endpoint)
<?php
header('Location: /caldav/', true, 301);
exit;
```

### 5.3 Service Discovery XML
```xml
<!-- caldav/service-discovery.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<propfind xmlns="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
  <prop>
    <C:calendar-home-set />
    <C:calendar-user-address-set />
    <C:schedule-inbox-URL />
    <C:schedule-outbox-URL />
  </prop>
</propfind>
```

---

## 🧪 Phase 6: Testing & Validation (Week 8)

### 6.1 Unit Tests
```php
// tests/CalDAVTest.php
<?php
use PHPUnit\Framework\TestCase;

class CalDAVTest extends TestCase {
    
    public function testTaskToVEventConversion() {
        $task = [
            'id' => 1,
            'title' => 'Test Call',
            'description' => 'Call John about project',
            'task_type' => 'call',
            'start_datetime' => '2024-12-15 10:00:00',
            'end_datetime' => '2024-12-15 10:30:00',
            'priority' => 'high',
            'status' => 'pending',
            'contact_name' => 'John Doe',
            'contact_email' => 'john@example.com',
            'contact_phone' => '+1-555-0123'
        ];
        
        $converter = new \CRM\CalDAV\VEventConverter();
        $ical = $converter->taskToVEvent($task);
        
        $this->assertStringContains('BEGIN:VEVENT', $ical);
        $this->assertStringContains('SUMMARY:Test Call', $ical);
        $this->assertStringContains('CATEGORIES:CALL', $ical);
        $this->assertStringContains('PRIORITY:1', $ical);
    }
    
    public function testVEventToTaskConversion() {
        $ical = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:Test
BEGIN:VEVENT
UID:test@example.com
SUMMARY:Test Meeting
DTSTART:20241215T100000Z
DTEND:20241215T110000Z
CATEGORIES:MEETING
PRIORITY:1
STATUS:CONFIRMED
ATTENDEE;CN=Jane Doe:mailto:jane@example.com
END:VEVENT
END:VCALENDAR';
        
        $converter = new \CRM\CalDAV\VEventConverter();
        $task = $converter->vEventToTask($ical);
        
        $this->assertEquals('Test Meeting', $task['title']);
        $this->assertEquals('meeting', $task['task_type']);
        $this->assertEquals('high', $task['priority']);
        $this->assertEquals('completed', $task['status']);
        $this->assertEquals('Jane Doe', $task['contact_name']);
        $this->assertEquals('jane@example.com', $task['contact_email']);
    }
}
```

### 6.2 Integration Tests
```bash
# Test CalDAV endpoints with curl
curl -X PROPFIND https://yourcrm.com/caldav/calendars/username/ \
  -H "Content-Type: application/xml" \
  -H "Depth: 1" \
  --user username:password

# Test calendar discovery
curl -X PROPFIND https://yourcrm.com/caldav/ \
  -H "Content-Type: application/xml" \
  --user username:password
```

---

## 🚀 Phase 7: Deployment & Monitoring (Week 9)

### 7.1 Apache/Nginx Configuration
```apache
# Apache .htaccess for CalDAV
RewriteEngine On

# CalDAV discovery
RewriteRule ^\.well-known/caldav$ /caldav/ [R=301,L]

# CalDAV endpoints
RewriteRule ^caldav/(.*)$ caldav/server.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

### 7.2 Monitoring & Logging
```php
// caldav/utils/Logger.php
<?php
class CalDAVLogger {
    public static function logRequest($method, $uri, $user, $status) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $method,
            'uri' => $uri,
            'user' => $user,
            'status' => $status,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        file_put_contents(
            '../logs/caldav.log', 
            json_encode($logEntry) . "\n", 
            FILE_APPEND | LOCK_EX
        );
    }
    
    public static function logSync($action, $taskId, $uid, $status, $error = null) {
        $stmt = $GLOBALS['pdo']->prepare('
            INSERT INTO caldav_sync_log (task_id, action, caldav_uid, sync_status, error_message)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([$taskId, $action, $uid, $status, $error]);
    }
}
```

---

## 📊 Success Metrics & Validation

### Functionality Checklist:
- [ ] ✅ CalDAV server responds to PROPFIND requests
- [ ] ✅ Nextcloud can discover and connect to CRM calendar
- [ ] ✅ Events created in CRM appear in Nextcloud
- [ ] ✅ Events modified in Nextcloud sync back to CRM
- [ ] ✅ Event deletion works bi-directionally
- [ ] ✅ Contact information is preserved across sync
- [ ] ✅ Task types/priorities map correctly
- [ ] ✅ Mobile sync works via Nextcloud apps

### Performance Targets:
- **Response Time:** < 200ms for calendar object requests
- **Sync Latency:** < 30 seconds for bi-directional updates
- **Concurrent Users:** Support 50+ simultaneous CalDAV connections
- **Data Integrity:** 99.9% accuracy in task ↔ event conversion

---

## 🔮 Future Enhancements

### Phase 8: Advanced Features
- **Real-time sync** via WebSockets/Server-Sent Events
- **Conflict resolution** for simultaneous edits
- **Calendar sharing** between CRM users
- **Recurring events** support
- **Email notifications** for calendar changes
- **Mobile push notifications** integration
- **Advanced search** and filtering via CalDAV-SEARCH
- **Calendar delegation** and shared access controls

### Integration Opportunities:
- **Multiple Nextcloud instances** support
- **Google Calendar** bi-directional sync
- **Outlook/Exchange** compatibility
- **Slack/Teams** meeting integration
- **VoIP systems** for call logging
- **Email platforms** for email task tracking

This comprehensive plan provides a solid foundation for full CalDAV integration between your CRM and Nextcloud, enabling professional-grade calendar synchronization capabilities.
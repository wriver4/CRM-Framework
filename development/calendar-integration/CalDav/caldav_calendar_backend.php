<?php
// caldav/backends/CalendarBackend.php
namespace CRM\CalDAV;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../utils/VEventConverter.php';
require_once __DIR__ . '/../../config/database.php';

use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV\Backend\SyncSupport;
use Sabre\VObject;
use PDO;
use Exception;

class CalendarBackend extends AbstractBackend implements SyncSupport {
    
    private $pdo;
    private $converter;
    private $tableNameCalendars = 'caldav_calendars';
    private $tableNameTasks = 'tasks';
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->converter = new VEventConverter();
    }
    
    /**
     * Get calendars for a specific user principal
     */
    public function getCalendarsForUser($principalUri) {
        $userId = $this->extractUserId($principalUri);
        
        $stmt = $this->pdo->prepare("
            SELECT id, uri, displayname, description, color, components, ctag
            FROM {$this->tableNameCalendars} 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        
        $calendars = [];
        while ($row = $stmt->fetch()) {
            $calendars[] = [
                'id' => $row['id'],
                'uri' => $row['uri'],
                'principaluri' => $principalUri,
                '{DAV:}displayname' => $row['displayname'],
                '{urn:ietf:params:xml:ns:caldav}calendar-description' => $row['description'] ?? '',
                '{http://apple.com/ns/ical/}calendar-color' => $row['color'],
                '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => 
                    new \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT']),
                '{http://calendarserver.org/ns/}getctag' => $row['ctag'],
                '{DAV:}sync-token' => $row['ctag'],
                '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => $this->getTimezoneComponent()
            ];
        }
        
        return $calendars;
    }
    
    /**
     * Create a new calendar
     */
    public function createCalendar($principalUri, $calendarUri, array $properties) {
        $userId = $this->extractUserId($principalUri);
        
        $displayName = isset($properties['{DAV:}displayname']) ? 
            $properties['{DAV:}displayname'] : $calendarUri;
        $description = isset($properties['{urn:ietf:params:xml:ns:caldav}calendar-description']) ? 
            $properties['{urn:ietf:params:xml:ns:caldav}calendar-description'] : '';
        $color = isset($properties['{http://apple.com/ns/ical/}calendar-color']) ? 
            $properties['{http://apple.com/ns/ical/}calendar-color'] : '#3366CC';
        
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->tableNameCalendars} 
            (user_id, uri, displayname, description, color) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([$userId, $calendarUri, $displayName, $description, $color]);
    }
    
    /**
     * Update calendar properties
     */
    public function updateCalendar($calendarId, \Sabre\DAV\PropPatch $propPatch) {
        $supportedProperties = [
            '{DAV:}displayname',
            '{urn:ietf:params:xml:ns:caldav}calendar-description',
            '{http://apple.com/ns/ical/}calendar-color'
        ];
        
        $propPatch->handle($supportedProperties, function($mutations) use ($calendarId) {
            $updates = [];
            $values = [];
            
            foreach ($mutations as $property => $value) {
                switch ($property) {
                    case '{DAV:}displayname':
                        $updates[] = 'displayname = ?';
                        $values[] = $value;
                        break;
                    case '{urn:ietf:params:xml:ns:caldav}calendar-description':
                        $updates[] = 'description = ?';
                        $values[] = $value;
                        break;
                    case '{http://apple.com/ns/ical/}calendar-color':
                        $updates[] = 'color = ?';
                        $values[] = $value;
                        break;
                }
            }
            
            if ($updates) {
                $values[] = $calendarId;
                $stmt = $this->pdo->prepare("
                    UPDATE {$this->tableNameCalendars} 
                    SET " . implode(', ', $updates) . ", ctag = ctag + 1 
                    WHERE id = ?
                ");
                $stmt->execute($values);
                
                return true;
            }
            
            return false;
        });
    }
    
    /**
     * Delete a calendar
     */
    public function deleteCalendar($calendarId) {
        // First delete all tasks in this calendar
        $stmt = $this->pdo->prepare("DELETE FROM {$this->tableNameTasks} WHERE calendar_id = ?");
        $stmt->execute([$calendarId]);
        
        // Then delete the calendar itself
        $stmt = $this->pdo->prepare("DELETE FROM {$this->tableNameCalendars} WHERE id = ?");
        return $stmt->execute([$calendarId]);
    }
    
    /**
     * Get all calendar objects (events) in a calendar
     */
    public function getCalendarObjects($calendarId) {
        $stmt = $this->pdo->prepare("
            SELECT caldav_uid, caldav_etag, caldav_lastmod, start_datetime
            FROM {$this->tableNameTasks} 
            WHERE calendar_id = ? AND caldav_uid IS NOT NULL
            ORDER BY start_datetime ASC
        ");
        $stmt->execute([$calendarId]);
        
        $objects = [];
        while ($row = $stmt->fetch()) {
            $objects[] = [
                'id' => $row['caldav_uid'],
                'uri' => $row['caldav_uid'] . '.ics',
                'lastmodified' => strtotime($row['caldav_lastmod']),
                'etag' => $row['caldav_etag'],
                'size' => $this->estimateEventSize($row['caldav_uid'])
            ];
        }
        
        return $objects;
    }
    
    /**
     * Get a specific calendar object
     */
    public function getCalendarObject($calendarId, $objectUri) {
        $uid = str_replace('.ics', '', $objectUri);
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->tableNameTasks} 
            WHERE calendar_id = ? AND caldav_uid = ?
        ");
        $stmt->execute([$calendarId, $uid]);
        $task = $stmt->fetch();
        
        if (!$task) {
            return null;
        }
        
        try {
            // Convert task to iCal format
            $icalData = $this->converter->taskToVEvent($task);
            
            return [
                'id' => $task['caldav_uid'],
                'uri' => $objectUri,
                'lastmodified' => strtotime($task['caldav_lastmod']),
                'etag' => $task['caldav_etag'],
                'calendardata' => $icalData,
                'size' => strlen($icalData)
            ];
        } catch (Exception $e) {
            error_log("Error converting task to VEvent: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get multiple calendar objects by URIs
     */
    public function getMultipleCalendarObjects($calendarId, array $uris) {
        $objects = [];
        foreach ($uris as $uri) {
            $object = $this->getCalendarObject($calendarId, $uri);
            if ($object) {
                $objects[] = $object;
            }
        }
        return $objects;
    }
    
    /**
     * Create a new calendar object
     */
    public function createCalendarObject($calendarId, $objectUri, $calendarData) {
        try {
            // Validate iCal data
            $this->converter->validateICalData($calendarData);
            
            // Convert iCal to task data
            $taskData = $this->converter->vEventToTask($calendarData);
            
            // Generate UID and ETag
            $uid = str_replace('.ics', '', $objectUri);
            $etag = $this->generateETag($calendarData);
            
            // Check if task already exists
            $stmt = $this->pdo->prepare("
                SELECT id FROM {$this->tableNameTasks} 
                WHERE calendar_id = ? AND caldav_uid = ?
            ");
            $stmt->execute([$calendarId, $uid]);
            
            if ($stmt->fetch()) {
                throw new \Sabre\DAV\Exception\Conflict('Calendar object already exists');
            }
            
            // Insert new task
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->tableNameTasks} (
                    title, description, task_type, start_datetime, end_datetime,
                    contact_name, contact_phone, contact_email, priority, status, notes,
                    caldav_uid, caldav_etag, calendar_id, caldav_sequence
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $taskData['title'],
                $taskData['description'] ?? '',
                $taskData['task_type'],
                $taskData['start_datetime'],
                $taskData['end_datetime'] ?? null,
                $taskData['contact_name'] ?? '',
                $taskData['contact_phone'] ?? '',
                $taskData['contact_email'] ?? '',
                $taskData['priority'] ?? 'medium',
                $taskData['status'] ?? 'pending',
                $taskData['notes'] ?? '',
                $uid,
                $etag,
                $calendarId,
                $taskData['caldav_sequence'] ?? 0
            ]);
            
            if ($result) {
                $this->updateCalendarCTag($calendarId);
                $this->logSync('create', $this->pdo->lastInsertId(), $uid, 'success');
            }
            
            return $etag;
            
        } catch (Exception $e) {
            $this->logSync('create', null, $uid ?? $objectUri, 'failed', $e->getMessage());
            throw new \Sabre\DAV\Exception\BadRequest('Invalid calendar data: ' . $e->getMessage());
        }
    }
    
    /**
     * Update an existing calendar object
     */
    public function updateCalendarObject($calendarId, $objectUri, $calendarData) {
        try {
            // Validate iCal data
            $this->converter->validateICalData($calendarData);
            
            // Convert iCal to task data
            $taskData = $this->converter->vEventToTask($calendarData);
            
            $uid = str_replace('.ics', '', $objectUri);
            $etag = $this->generateETag($calendarData);
            
            // Get existing task
            $stmt = $this->pdo->prepare("
                SELECT id, caldav_sequence FROM {$this->tableNameTasks} 
                WHERE calendar_id = ? AND caldav_uid = ?
            ");
            $stmt->execute([$calendarId, $uid]);
            $existingTask = $stmt->fetch();
            
            if (!$existingTask) {
                throw new \Sabre\DAV\Exception\NotFound('Calendar object not found');
            }
            
            // Update task
            $stmt = $this->pdo->prepare("
                UPDATE {$this->tableNameTasks} SET 
                    title = ?, description = ?, task_type = ?, 
                    start_datetime = ?, end_datetime = ?,
                    contact_name = ?, contact_phone = ?, contact_email = ?,
                    priority = ?, status = ?, notes = ?,
                    caldav_etag = ?, caldav_sequence = caldav_sequence + 1,
                    updated_at = CURRENT_TIMESTAMP
                WHERE calendar_id = ? AND caldav_uid = ?
            ");
            
            $result = $stmt->execute([
                $taskData['title'],
                $taskData['description'] ?? '',
                $taskData['task_type'],
                $taskData['start_datetime'],
                $taskData['end_datetime'] ?? null,
                $taskData['contact_name'] ?? '',
                $taskData['contact_phone'] ?? '',
                $taskData['contact_email'] ?? '',
                $taskData['priority'] ?? 'medium',
                $taskData['status'] ?? 'pending',
                $taskData['notes'] ?? '',
                $etag,
                $calendarId,
                $uid
            ]);
            
            if ($result) {
                $this->updateCalendarCTag($calendarId);
                $this->logSync('update', $existingTask['id'], $uid, 'success');
            }
            
            return $etag;
            
        } catch (Exception $e) {
            $this->logSync('update', null, $uid ?? $objectUri, 'failed', $e->getMessage());
            throw new \Sabre\DAV\Exception\BadRequest('Invalid calendar data: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a calendar object
     */
    public function deleteCalendarObject($calendarId, $objectUri) {
        $uid = str_replace('.ics', '', $objectUri);
        
        // Get task ID for logging
        $stmt = $this->pdo->prepare("
            SELECT id FROM {$this->tableNameTasks} 
            WHERE calendar_id = ? AND caldav_uid = ?
        ");
        $stmt->execute([$calendarId, $uid]);
        $task = $stmt->fetch();
        
        if (!$task) {
            throw new \Sabre\DAV\Exception\NotFound('Calendar object not found');
        }
        
        // Delete the task
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->tableNameTasks} 
            WHERE calendar_id = ? AND caldav_uid = ?
        ");
        
        $result = $stmt->execute([$calendarId, $uid]);
        
        if ($result) {
            $this->updateCalendarCTag($calendarId);
            $this->logSync('delete', $task['id'], $uid, 'success');
        }
        
        return $result;
    }
    
    /**
     * Get changes since a sync token (for efficient sync)
     */
    public function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null) {
        // For initial sync, return all objects
        if (!$syncToken) {
            $objects = $this->getCalendarObjects($calendarId);
            return [
                'syncToken' => $this->getCurrentSyncToken($calendarId),
                'added' => array_map(function($obj) { return $obj['uri']; }, $objects),
                'modified' => [],
                'deleted' => []
            ];
        }
        
        // Get calendar's current sync token
        $currentToken = $this->getCurrentSyncToken($calendarId);
        
        if ($syncToken >= $currentToken) {
            // No changes
            return [
                'syncToken' => $currentToken,
                'added' => [],
                'modified' => [],
                'deleted' => []
            ];
        }
        
        // Get changes from sync log
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT caldav_uid, action, sync_timestamp
            FROM caldav_sync_log 
            WHERE sync_timestamp > FROM_UNIXTIME(?) 
            AND sync_status = 'success'
            ORDER BY sync_timestamp ASC
            " . ($limit ? "LIMIT " . (int)$limit : "")
        );
        $stmt->execute([$syncToken]);
        
        $added = [];
        $modified = [];
        $deleted = [];
        
        while ($row = $stmt->fetch()) {
            $uri = $row['caldav_uid'] . '.ics';
            
            switch ($row['action']) {
                case 'create':
                    $added[] = $uri;
                    break;
                case 'update':
                    $modified[] = $uri;
                    break;
                case 'delete':
                    $deleted[] = $uri;
                    break;
            }
        }
        
        return [
            'syncToken' => $currentToken,
            'added' => $added,
            'modified' => $modified,
            'deleted' => $deleted
        ];
    }
    
    /**
     * Calendar query support for filtering events
     */
    public function calendarQuery($calendarId, array $filters) {
        $whereConditions = ["calendar_id = ?"];
        $params = [$calendarId];
        
        // Handle time range filters
        if (isset($filters['comp-filters'])) {
            foreach ($filters['comp-filters'] as $filter) {
                if (isset($filter['time-range'])) {
                    $timeRange = $filter['time-range'];
                    
                    if (isset($timeRange['start'])) {
                        $whereConditions[] = "start_datetime >= ?";
                        $params[] = $timeRange['start']->format('Y-m-d H:i:s');
                    }
                    
                    if (isset($timeRange['end'])) {
                        $whereConditions[] = "start_datetime < ?";
                        $params[] = $timeRange['end']->format('Y-m-d H:i:s');
                    }
                }
            }
        }
        
        // Execute query
        $stmt = $this->pdo->prepare("
            SELECT caldav_uid
            FROM {$this->tableNameTasks} 
            WHERE " . implode(' AND ', $whereConditions) . "
            AND caldav_uid IS NOT NULL
            ORDER BY start_datetime ASC
        ");
        $stmt->execute($params);
        
        $uris = [];
        while ($row = $stmt->fetch()) {
            $uris[] = $row['caldav_uid'] . '.ics';
        }
        
        return $uris;
    }
    
    /**
     * Helper methods
     */
    
    private function extractUserId($principalUri) {
        if (strpos($principalUri, 'principals/users/') === 0) {
            return substr($principalUri, strlen('principals/users/'));
        }
        throw new \InvalidArgumentException('Invalid principal URI');
    }
    
    private function updateCalendarCTag($calendarId) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->tableNameCalendars} 
            SET ctag = ctag + 1, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$calendarId]);
    }
    
    private function getCurrentSyncToken($calendarId) {
        $stmt = $this->pdo->prepare("
            SELECT ctag FROM {$this->tableNameCalendars} WHERE id = ?
        ");
        $stmt->execute([$calendarId]);
        $result = $stmt->fetch();
        return $result ? $result['ctag'] : 1;
    }
    
    private function generateETag($data) {
        return '"' . md5($data . microtime(true)) . '"';
    }
    
    private function estimateEventSize($uid) {
        // Rough estimate - actual size calculated when needed
        return 1000;
    }
    
    private function getTimezoneComponent() {
        // Return a basic timezone component for UTC
        return "BEGIN:VTIMEZONE\r\nTZID:UTC\r\nBEGIN:STANDARD\r\nDTSTART:19700101T000000Z\r\nTZOFFSETFROM:+0000\r\nTZOFFSETTO:+0000\r\nTZNAME:UTC\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n";
    }
    
    private function logSync($action, $taskId, $uid, $status, $error = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO caldav_sync_log (task_id, action, caldav_uid, sync_status, error_message)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$taskId, $action, $uid, $status, $error]);
        } catch (Exception $e) {
            error_log("Failed to log sync: " . $e->getMessage());
        }
    }
}
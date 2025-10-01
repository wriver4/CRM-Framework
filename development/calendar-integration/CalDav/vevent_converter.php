<?php
// caldav/utils/VEventConverter.php
namespace CRM\CalDAV;

require_once __DIR__ . '/../../vendor/autoload.php';

use Sabre\VObject;
use DateTime;
use DateTimeZone;

class VEventConverter {
    
    private $defaultTimezone;
    
    public function __construct($timezone = 'UTC') {
        $this->defaultTimezone = $timezone;
    }
    
    /**
     * Convert CRM task to iCal VEVENT format
     */
    public function taskToVEvent($task) {
        $calendar = new VObject\Component\VCalendar();
        $event = $calendar->createComponent('VEVENT');
        
        // Generate UID if not exists
        $uid = $task['caldav_uid'] ?: $this->generateUID();
        $event->UID = $uid;
        
        // Basic event properties
        $event->SUMMARY = $this->sanitizeText($task['title']);
        $event->DESCRIPTION = $this->sanitizeText($task['description'] ?? '');
        
        // Date and time handling
        $this->setEventDates($event, $task);
        
        // Status mapping: CRM -> CalDAV
        $event->STATUS = $this->mapTaskStatusToCalDAV($task['status']);
        
        // Priority mapping: CRM -> CalDAV (1-9 scale, 1=highest)
        $event->PRIORITY = $this->mapTaskPriorityToCalDAV($task['priority']);
        
        // Categories for task type
        $event->CATEGORIES = strtoupper($task['task_type']);
        
        // Contact information
        $this->addContactAsAttendee($event, $task);
        
        // Custom CRM properties (X- prefix for custom properties)
        $this->addCRMCustomProperties($event, $task);
        
        // Standard timestamps
        $this->setEventTimestamps($event, $task);
        
        // Sequence for versioning
        $event->SEQUENCE = (int) ($task['caldav_sequence'] ?? 0);
        
        $calendar->add($event);
        
        return $calendar->serialize();
    }
    
    /**
     * Convert iCal VEVENT to CRM task format
     */
    public function vEventToTask($icalData) {
        try {
            $calendar = VObject\Reader::read($icalData);
            $event = $calendar->VEVENT;
            
            $task = [
                'title' => $this->sanitizeText((string) $event->SUMMARY),
                'description' => $this->sanitizeText((string) ($event->DESCRIPTION ?? '')),
            ];
            
            // Extract dates
            $this->extractTaskDates($task, $event);
            
            // Status mapping: CalDAV -> CRM
            $task['status'] = $this->mapCalDAVStatusToTask((string) ($event->STATUS ?? 'TENTATIVE'));
            
            // Priority mapping: CalDAV -> CRM
            $task['priority'] = $this->mapCalDAVPriorityToTask((int) ($event->PRIORITY ?? 5));
            
            // Task type from categories or custom property
            $task['task_type'] = $this->extractTaskType($event);
            
            // Contact information from attendee
            $this->extractContactFromAttendee($task, $event);
            
            // Custom CRM properties
            $this->extractCRMCustomProperties($task, $event);
            
            // CalDAV metadata
            $task['caldav_uid'] = (string) $event->UID;
            $task['caldav_sequence'] = (int) ($event->SEQUENCE ?? 0);
            
            return $task;
            
        } catch (Exception $e) {
            error_log("Error converting iCal to task: " . $e->getMessage());
            throw new \InvalidArgumentException("Invalid iCal data: " . $e->getMessage());
        }
    }
    
    /**
     * Set event dates with timezone handling
     */
    private function setEventDates($event, $task) {
        $timezone = new DateTimeZone($this->defaultTimezone);
        
        // Start date/time
        if (!empty($task['start_datetime'])) {
            $startDate = new DateTime($task['start_datetime'], $timezone);
            $event->DTSTART = $startDate;
        }
        
        // End date/time
        if (!empty($task['end_datetime'])) {
            $endDate = new DateTime($task['end_datetime'], $timezone);
            $event->DTEND = $endDate;
        } elseif (!empty($task['start_datetime'])) {
            // Default to 30 minutes if no end time
            $endDate = new DateTime($task['start_datetime'], $timezone);
            $endDate->modify('+30 minutes');
            $event->DTEND = $endDate;
        }
    }
    
    /**
     * Extract dates from iCal event
     */
    private function extractTaskDates(&$task, $event) {
        if (isset($event->DTSTART)) {
            $startDate = $event->DTSTART->getDateTime();
            $task['start_datetime'] = $startDate->format('Y-m-d H:i:s');
        }
        
        if (isset($event->DTEND)) {
            $endDate = $event->DTEND->getDateTime();
            $task['end_datetime'] = $endDate->format('Y-m-d H:i:s');
        }
    }
    
    /**
     * Map CRM task status to CalDAV status
     */
    private function mapTaskStatusToCalDAV($status) {
        $statusMap = [
            'pending' => 'TENTATIVE',
            'completed' => 'CONFIRMED',
            'cancelled' => 'CANCELLED',
            'in_progress' => 'TENTATIVE'
        ];
        
        return $statusMap[$status] ?? 'TENTATIVE';
    }
    
    /**
     * Map CalDAV status to CRM task status
     */
    private function mapCalDAVStatusToTask($status) {
        $statusMap = [
            'TENTATIVE' => 'pending',
            'CONFIRMED' => 'completed',
            'CANCELLED' => 'cancelled'
        ];
        
        return $statusMap[$status] ?? 'pending';
    }
    
    /**
     * Map CRM priority to CalDAV priority (1-9 scale)
     */
    private function mapTaskPriorityToCalDAV($priority) {
        $priorityMap = [
            'low' => 9,
            'medium' => 5,
            'high' => 1,
            'urgent' => 1
        ];
        
        return $priorityMap[$priority] ?? 5;
    }
    
    /**
     * Map CalDAV priority to CRM priority
     */
    private function mapCalDAVPriorityToTask($priority) {
        if ($priority >= 1 && $priority <= 3) {
            return 'high';
        } elseif ($priority >= 4 && $priority <= 6) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * Add contact information as attendee
     */
    private function addContactAsAttendee($event, $task) {
        if (!empty($task['contact_email'])) {
            $attendee = $event->createProperty('ATTENDEE', 'mailto:' . $task['contact_email']);
            
            // Add contact name if available
            if (!empty($task['contact_name'])) {
                $attendee->add('CN', $this->sanitizeText($task['contact_name']));
            }
            
            // Set role and participation status
            $attendee->add('ROLE', 'REQ-PARTICIPANT');
            $attendee->add('PARTSTAT', 'NEEDS-ACTION');
            $attendee->add('RSVP', 'FALSE');
            
            $event->add($attendee);
        }
    }
    
    /**
     * Extract contact information from attendee
     */
    private function extractContactFromAttendee(&$task, $event) {
        if (isset($event->ATTENDEE)) {
            // Handle multiple attendees - take the first one
            $attendees = is_array($event->ATTENDEE) ? $event->ATTENDEE : [$event->ATTENDEE];
            
            foreach ($attendees as $attendee) {
                $attendeeEmail = str_replace('mailto:', '', (string) $attendee);
                if (filter_var($attendeeEmail, FILTER_VALIDATE_EMAIL)) {
                    $task['contact_email'] = $attendeeEmail;
                    
                    // Extract name from CN parameter
                    if (isset($attendee['CN'])) {
                        $task['contact_name'] = $this->sanitizeText((string) $attendee['CN']);
                    }
                    break; // Use first valid email
                }
            }
        }
    }
    
    /**
     * Add CRM-specific custom properties
     */
    private function addCRMCustomProperties($event, $task) {
        // Store original CRM task ID
        if (!empty($task['id'])) {
            $event->add('X-CRM-TASK-ID', $task['id']);
        }
        
        // Store task type
        $event->add('X-CRM-TASK-TYPE', $task['task_type']);
        
        // Store contact phone
        if (!empty($task['contact_phone'])) {
            $event->add('X-CRM-CONTACT-PHONE', $task['contact_phone']);
        }
        
        // Store notes
        if (!empty($task['notes'])) {
            $event->add('X-CRM-NOTES', $this->sanitizeText($task['notes']));
        }
        
        // Store CRM URL for task
        if (!empty($task['id'])) {
            $event->add('X-CRM-URL', "https://yourcrm.com/tasks/{$task['id']}");
        }
        
        // Store priority as custom property for better mapping
        $event->add('X-CRM-PRIORITY', $task['priority']);
        
        // Store status as custom property
        $event->add('X-CRM-STATUS', $task['status']);
    }
    
    /**
     * Extract CRM custom properties
     */
    private function extractCRMCustomProperties(&$task, $event) {
        // Contact phone
        if (isset($event->{'X-CRM-CONTACT-PHONE'})) {
            $task['contact_phone'] = $this->sanitizeText((string) $event->{'X-CRM-CONTACT-PHONE'});
        }
        
        // Notes
        if (isset($event->{'X-CRM-NOTES'})) {
            $task['notes'] = $this->sanitizeText((string) $event->{'X-CRM-NOTES'});
        }
        
        // Original task ID (for updates)
        if (isset($event->{'X-CRM-TASK-ID'})) {
            $task['original_id'] = (int) $event->{'X-CRM-TASK-ID'};
        }
        
        // Use custom priority if available (more accurate than CalDAV priority)
        if (isset($event->{'X-CRM-PRIORITY'})) {
            $customPriority = (string) $event->{'X-CRM-PRIORITY'};
            if (in_array($customPriority, ['low', 'medium', 'high', 'urgent'])) {
                $task['priority'] = $customPriority;
            }
        }
        
        // Use custom status if available
        if (isset($event->{'X-CRM-STATUS'})) {
            $customStatus = (string) $event->{'X-CRM-STATUS'};
            if (in_array($customStatus, ['pending', 'completed', 'cancelled', 'in_progress'])) {
                $task['status'] = $customStatus;
            }
        }
    }
    
    /**
     * Extract task type from event
     */
    private function extractTaskType($event) {
        // First, check custom property
        if (isset($event->{'X-CRM-TASK-TYPE'})) {
            $taskType = strtolower((string) $event->{'X-CRM-TASK-TYPE'});
            if (in_array($taskType, ['call', 'email', 'meeting', 'follow_up'])) {
                return $taskType;
            }
        }
        
        // Fall back to categories
        if (isset($event->CATEGORIES)) {
            $category = strtolower((string) $event->CATEGORIES);
            if (in_array($category, ['call', 'email', 'meeting', 'follow_up'])) {
                return $category;
            }
        }
        
        // Default task type
        return 'call';
    }
    
    /**
     * Set event timestamps
     */
    private function setEventTimestamps($event, $task) {
        $now = new DateTime();
        
        // DTSTAMP is required and should be current time
        $event->DTSTAMP = $now;
        
        // CREATED timestamp
        if (!empty($task['created_at'])) {
            $event->CREATED = new DateTime($task['created_at']);
        } else {
            $event->CREATED = $now;
        }
        
        // LAST-MODIFIED timestamp
        if (!empty($task['updated_at'])) {
            $event->{'LAST-MODIFIED'} = new DateTime($task['updated_at']);
        } else {
            $event->{'LAST-MODIFIED'} = $now;
        }
    }
    
    /**
     * Sanitize text for iCal format
     */
    private function sanitizeText($text) {
        if (empty($text)) {
            return '';
        }
        
        // Remove or escape special characters
        $text = str_replace(["\r\n", "\n", "\r"], "\\n", $text);
        $text = str_replace([",", ";", "\\"], ["\\,", "\\;", "\\\\"], $text);
        
        // Trim and limit length
        return substr(trim($text), 0, 1000);
    }
    
    /**
     * Generate unique identifier for events
     */
    private function generateUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        ) . '@yourcrm.com';
    }
    
    /**
     * Validate iCal data before processing
     */
    public function validateICalData($icalData) {
        try {
            $calendar = VObject\Reader::read($icalData);
            
            // Check for required components
            if (!isset($calendar->VEVENT)) {
                throw new \InvalidArgumentException("No VEVENT component found");
            }
            
            $event = $calendar->VEVENT;
            
            // Check for required properties
            if (!isset($event->UID)) {
                throw new \InvalidArgumentException("Event UID is required");
            }
            
            if (!isset($event->DTSTART)) {
                throw new \InvalidArgumentException("Event start date is required");
            }
            
            return true;
            
        } catch (Exception $e) {
            throw new \InvalidArgumentException("Invalid iCal data: " . $e->getMessage());
        }
    }
    
    /**
     * Get event summary for logging/debugging
     */
    public function getEventSummary($icalData) {
        try {
            $calendar = VObject\Reader::read($icalData);
            $event = $calendar->VEVENT;
            
            return [
                'uid' => (string) $event->UID,
                'summary' => (string) ($event->SUMMARY ?? 'No title'),
                'start' => isset($event->DTSTART) ? $event->DTSTART->getDateTime()->format('Y-m-d H:i:s') : null,
                'end' => isset($event->DTEND) ? $event->DTEND->getDateTime()->format('Y-m-d H:i:s') : null,
                'status' => (string) ($event->STATUS ?? 'TENTATIVE')
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?>
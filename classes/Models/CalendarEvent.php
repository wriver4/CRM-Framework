<?php
/**
 * Calendar Event Model
 * 
 * Manages calendar events/tasks with full CRM integration
 * Follows framework Database inheritance pattern
 * Integrates with existing RBAC and user management
 * 
 * @author CRM Framework
 * @version 1.0
 */

require_once CLASSES . 'Core/Database.php';
require_once CLASSES . 'Logging/Audit.php';
require_once CLASSES . 'Core/Security.php';
require_once CLASSES . 'Utilities/Helpers.php';

class CalendarEvent extends Database
{
    private $audit;
    private $security;
    
    public function __construct()
    {
        parent::__construct();
        $this->audit = new Audit();
        $this->security = new Security();
    }
    
    /**
     * Get all events for a user with optional date range
     */
    public function getEventsForUser($user_id, $start_date = null, $end_date = null, $include_shared = true)
    {
        $query = "SELECT 
                    ce.id,
                    ce.title,
                    ce.description,
                    ce.event_type,
                    ce.start_datetime,
                    ce.end_datetime,
                    ce.all_day,
                    ce.status,
                    ce.priority,
                    ce.location,
                    ce.notes,
                    ce.timezone,
                    ce.lead_id,
                    ce.contact_id,
                    ce.user_id,
                    ce.created_at,
                    ce.updated_at,
                    l.business_name,
                    c.full_name as contact_name,
                    u.full_name as owner_name
                  FROM calendar_events ce
                  LEFT JOIN leads l ON ce.lead_id = l.id
                  LEFT JOIN contacts c ON ce.contact_id = c.id
                  LEFT JOIN users u ON ce.user_id = u.id
                  WHERE ce.user_id = :user_id";
        
        $params = [':user_id' => (int)$user_id];
        
        if ($start_date && $end_date) {
            $query .= " AND ce.start_datetime BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $start_date;
            $params[':end_date'] = $end_date;
        }
        
        $query .= " ORDER BY ce.start_datetime ASC";
        
        $stmt = $this->dbcrm()->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        
        return $events;
    }
    
    /**
     * Get events formatted for FullCalendar
     */
    public function getEventsForCalendar($user_id, $start_date = null, $end_date = null, $lang = null)
    {
        $events = $this->getEventsForUser($user_id, $start_date, $end_date);
        $calendar_events = [];
        
        // Ensure we have a valid language array
        if (is_string($lang)) {
            // If $lang is a string (language code), load the language file
            $langFile = DOCROOT . '/public_html/admin/languages/' . $lang . '.php';
            if (file_exists($langFile)) {
                $lang = include $langFile;
            } else {
                // Fallback to English if language file doesn't exist
                $lang = include DOCROOT . '/public_html/admin/languages/en.php';
            }
        } elseif (!is_array($lang) || empty($lang)) {
            // If $lang is null or not a valid array, load English as fallback
            $lang = include DOCROOT . '/public_html/admin/languages/en.php';
        }
        
        // Initialize helpers for getting names
        $helpers = new Helpers();
        
        foreach ($events as $event) {
            // Get event type and priority names using helper methods
            $event_type_name = $helpers->get_calendar_event_type($lang, $event['event_type']);
            $priority_name = $helpers->get_calendar_priority($lang, $event['priority']);
            
            $calendar_events[] = [
                'id' => $event['id'],
                'title' => $this->formatEventTitle($event),
                'start' => $event['start_datetime'],
                'end' => $event['end_datetime'],
                'allDay' => (bool)$event['all_day'],
                'backgroundColor' => $this->getEventTypeColor($event['event_type']),
                'borderColor' => $this->getPriorityColor($event['priority']),
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'description' => $event['description'],
                    'event_type' => $event['event_type'],
                    'event_type_name' => $event_type_name,
                    'status' => $event['status'],
                    'priority' => $event['priority'],
                    'priority_name' => $priority_name,
                    'location' => $event['location'],
                    'notes' => $event['notes'],
                    'lead_id' => $event['lead_id'],
                    'contact_id' => $event['contact_id'],
                    'company_name' => $event['company_name'],
                    'contact_name' => $event['contact_name'],
                    'timezone' => $event['timezone']
                ]
            ];
        }
        
        return $calendar_events;
    }
    
    /**
     * Create a new calendar event
     */
    public function createEvent($data, $user_id)
    {
        // Validate required fields
        if (empty($data['title']) || empty($data['start_datetime']) || empty($data['event_type'])) {
            throw new InvalidArgumentException('Missing required fields: title, start_datetime, event_type');
        }
        
        $query = "INSERT INTO calendar_events 
                 (user_id, lead_id, contact_id, title, description, event_type, 
                  start_datetime, end_datetime, all_day, status, priority, 
                  location, notes, reminder_minutes, timezone, created_by) 
                 VALUES 
                 (:user_id, :lead_id, :contact_id, :title, :description, :event_type,
                  :start_datetime, :end_datetime, :all_day, :status, :priority,
                  :location, :notes, :reminder_minutes, :timezone, :created_by)";
        
        $stmt = $this->dbcrm()->prepare($query);
        
        // Bind values with proper types
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', !empty($data['lead_id']) ? (int)$data['lead_id'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':contact_id', !empty($data['contact_id']) ? (int)$data['contact_id'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':title', $data['title'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':event_type', (int)$data['event_type'], PDO::PARAM_INT);
        $stmt->bindValue(':start_datetime', $data['start_datetime'], PDO::PARAM_STR);
        $stmt->bindValue(':end_datetime', $data['end_datetime'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':all_day', (int)($data['all_day'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':status', (int)($data['status'] ?? 1), PDO::PARAM_INT);
        $stmt->bindValue(':priority', (int)($data['priority'] ?? 5), PDO::PARAM_INT);
        $stmt->bindValue(':location', $data['location'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':notes', $data['notes'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':reminder_minutes', !empty($data['reminder_minutes']) ? (int)$data['reminder_minutes'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':timezone', $data['timezone'] ?? 'UTC', PDO::PARAM_STR);
        $stmt->bindValue(':created_by', (int)$user_id, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        if ($result) {
            $event_id = $this->dbcrm()->lastInsertId();
            
            // Log audit trail
            $this->audit->log($user_id, 'calendar_event_created', 'calendar_events', [
                'event_id' => $event_id,
                'title' => $data['title'],
                'event_type' => $data['event_type'],
                'start_datetime' => $data['start_datetime']
            ]);
            
            $stmt = null;
            return $event_id;
        }
        
        $stmt = null;
        return false;
    }
    
    /**
     * Update an existing calendar event
     */
    public function updateEvent($event_id, $data, $user_id)
    {
        // Check if user owns the event or has permission
        if (!$this->canUserModifyEvent($event_id, $user_id)) {
            throw new UnauthorizedAccessException('User does not have permission to modify this event');
        }
        
        $query = "UPDATE calendar_events SET 
                 title = :title, 
                 description = :description, 
                 event_type = :event_type,
                 start_datetime = :start_datetime, 
                 end_datetime = :end_datetime,
                 all_day = :all_day,
                 status = :status, 
                 priority = :priority,
                 location = :location, 
                 notes = :notes,
                 reminder_minutes = :reminder_minutes,
                 timezone = :timezone,
                 updated_by = :updated_by,
                 lead_id = :lead_id,
                 contact_id = :contact_id
                 WHERE id = :event_id";
        
        $stmt = $this->dbcrm()->prepare($query);
        
        $stmt->bindValue(':event_id', (int)$event_id, PDO::PARAM_INT);
        $stmt->bindValue(':title', $data['title'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':event_type', (int)$data['event_type'], PDO::PARAM_INT);
        $stmt->bindValue(':start_datetime', $data['start_datetime'], PDO::PARAM_STR);
        $stmt->bindValue(':end_datetime', $data['end_datetime'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':all_day', (int)($data['all_day'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':status', (int)($data['status'] ?? 1), PDO::PARAM_INT);
        $stmt->bindValue(':priority', (int)($data['priority'] ?? 5), PDO::PARAM_INT);
        $stmt->bindValue(':location', $data['location'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':notes', $data['notes'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':reminder_minutes', !empty($data['reminder_minutes']) ? (int)$data['reminder_minutes'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':timezone', $data['timezone'] ?? 'UTC', PDO::PARAM_STR);
        $stmt->bindValue(':updated_by', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', !empty($data['lead_id']) ? (int)$data['lead_id'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':contact_id', !empty($data['contact_id']) ? (int)$data['contact_id'] : null, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        if ($result) {
            // Log audit trail
            $this->audit->log($user_id, 'calendar_event_updated', 'calendar_events', [
                'event_id' => $event_id,
                'title' => $data['title'],
                'changes' => array_keys($data)
            ]);
        }
        
        $stmt = null;
        return $result;
    }
    
    /**
     * Delete a calendar event
     */
    public function deleteEvent($event_id, $user_id)
    {
        // Check if user owns the event or has permission
        if (!$this->canUserModifyEvent($event_id, $user_id)) {
            throw new UnauthorizedAccessException('User does not have permission to delete this event');
        }
        
        // Get event details for audit log
        $event = $this->getEventById($event_id);
        
        $query = "DELETE FROM calendar_events WHERE id = :event_id";
        $stmt = $this->dbcrm()->prepare($query);
        $stmt->bindValue(':event_id', (int)$event_id, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        if ($result && $event) {
            // Log audit trail
            $this->audit->log($user_id, 'calendar_event_deleted', 'calendar_events', [
                'event_id' => $event_id,
                'title' => $event['title'],
                'start_datetime' => $event['start_datetime']
            ]);
        }
        
        $stmt = null;
        return $result;
    }
    
    /**
     * Get a single event by ID
     */
    public function getEventById($event_id)
    {
        $query = "SELECT ce.*, 
                         l.business_name,
                         c.full_name as contact_name
                  FROM calendar_events ce
                  LEFT JOIN leads l ON ce.lead_id = l.id
                  LEFT JOIN contacts c ON ce.contact_id = c.id
                  WHERE ce.id = :event_id";
        
        $stmt = $this->dbcrm()->prepare($query);
        $stmt->bindValue(':event_id', (int)$event_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;
        
        return $event;
    }
    
    /**
     * Get event types using helper class
     */
    public function getEventTypes($lang)
    {
        // If $lang is a string (language code), load the language file
        if (is_string($lang)) {
            $langFile = DOCROOT . '/public_html/admin/languages/' . $lang . '.php';
            if (file_exists($langFile)) {
                $lang = include $langFile;
            } else {
                // Fallback to English if language file doesn't exist
                $lang = include DOCROOT . '/public_html/admin/languages/en.php';
            }
        }
        
        $helpers = new Helpers();
        $eventTypes = $helpers->get_calendar_event_type_array($lang);
        
        // Convert to format expected by frontend
        $types = [];
        foreach ($eventTypes as $id => $name) {
            $types[] = [
                'id' => (int)$id,
                'name' => $name,
                'color' => $this->getEventTypeColor($id),
                'icon' => $this->getEventTypeIcon($id)
            ];
        }
        
        return $types;
    }
    
    /**
     * Get priority levels using helper class
     */
    public function getPriorities($lang)
    {
        // If $lang is a string (language code), load the language file
        if (is_string($lang)) {
            $langFile = DOCROOT . '/public_html/admin/languages/' . $lang . '.php';
            if (file_exists($langFile)) {
                $lang = include $langFile;
            } else {
                // Fallback to English if language file doesn't exist
                $lang = include DOCROOT . '/public_html/admin/languages/en.php';
            }
        }
        
        $helpers = new Helpers();
        $priorities = $helpers->get_calendar_priority_array($lang);
        
        // Convert to format expected by frontend
        $priorityList = [];
        foreach ($priorities as $id => $name) {
            $priorityList[] = [
                'id' => (int)$id,
                'name' => $name,
                'color' => $this->getPriorityColor($id)
            ];
        }
        
        return $priorityList;
    }
    
    /**
     * Get event type color based on type ID
     */
    private function getEventTypeColor($typeId)
    {
        $colors = [
            1 => '#007bff', // Phone Call - Blue
            2 => '#28a745', // Email - Green
            3 => '#17a2b8', // Text Message - Teal
            4 => '#6c757d', // Internal Note - Gray
            5 => '#ffc107', // Virtual Meeting - Yellow
            6 => '#fd7e14', // In-Person Meeting - Orange
        ];
        
        return $colors[$typeId] ?? '#007bff';
    }
    
    /**
     * Get event type icon based on type ID
     */
    private function getEventTypeIcon($typeId)
    {
        $icons = [
            1 => 'fas fa-phone',
            2 => 'fas fa-envelope',
            3 => 'fas fa-sms',
            4 => 'fas fa-sticky-note',
            5 => 'fas fa-video',
            6 => 'fas fa-users',
        ];
        
        return $icons[$typeId] ?? 'fas fa-calendar';
    }
    
    /**
     * Get priority color based on priority level (1-10)
     */
    private function getPriorityColor($priority)
    {
        $colors = [
            1 => '#e3f2fd', // Lowest - Very light blue
            2 => '#bbdefb', // Very Low - Light blue
            3 => '#90caf9', // Low - Medium light blue
            4 => '#64b5f6', // Below Normal - Medium blue
            5 => '#42a5f5', // Normal - Standard blue
            6 => '#2196f3', // Above Normal - Darker blue
            7 => '#1976d2', // High - Dark blue
            8 => '#1565c0', // Very High - Very dark blue
            9 => '#0d47a1', // Critical - Navy blue
            10 => '#b71c1c', // Urgent - Red
        ];
        
        return $colors[$priority] ?? '#42a5f5';
    }
    
    /**
     * Get today's events for dashboard
     */
    public function getTodaysEvents($user_id, $limit = 10)
    {
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        
        $query = "SELECT ce.*, 
                         l.business_name,
                         c.full_name as contact_name
                  FROM calendar_events ce
                  LEFT JOIN leads l ON ce.lead_id = l.id
                  LEFT JOIN contacts c ON ce.contact_id = c.id
                  WHERE ce.user_id = :user_id 
                    AND ce.start_datetime BETWEEN :today_start AND :today_end
                    AND ce.status = 1
                  ORDER BY ce.start_datetime ASC
                  LIMIT :limit";
        
        $stmt = $this->dbcrm()->prepare($query);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':today_start', $today_start, PDO::PARAM_STR);
        $stmt->bindValue(':today_end', $today_end, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        
        return $events;
    }
    
    /**
     * Get event statistics for dashboard
     */
    public function getEventStats($user_id, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $date_start = $date . ' 00:00:00';
        $date_end = $date . ' 23:59:59';
        
        // Base WHERE clause for all queries
        $where_clause = "WHERE user_id = :user_id AND start_datetime BETWEEN :date_start AND :date_end";
        
        // Get total events
        $query = "SELECT COUNT(*) as count FROM calendar_events " . $where_clause;
        $stmt = $this->dbcrm()->prepare($query);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':date_start', $date_start, PDO::PARAM_STR);
        $stmt->bindValue(':date_end', $date_end, PDO::PARAM_STR);
        $stmt->execute();
        $total_events = $stmt->fetchColumn();
        $stmt = null;
        
        // Get phone calls (event_type = 1)
        $query = "SELECT COUNT(*) as count FROM calendar_events " . $where_clause . " AND event_type = 1";
        $stmt = $this->dbcrm()->prepare($query);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':date_start', $date_start, PDO::PARAM_STR);
        $stmt->bindValue(':date_end', $date_end, PDO::PARAM_STR);
        $stmt->execute();
        $phone_calls = $stmt->fetchColumn();
        $stmt = null;
        
        // Get emails (event_type = 2)
        $query = "SELECT COUNT(*) as count FROM calendar_events " . $where_clause . " AND event_type = 2";
        $stmt = $this->dbcrm()->prepare($query);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':date_start', $date_start, PDO::PARAM_STR);
        $stmt->bindValue(':date_end', $date_end, PDO::PARAM_STR);
        $stmt->execute();
        $emails = $stmt->fetchColumn();
        $stmt = null;
        
        // Get meetings (event_type IN (5,6))
        $query = "SELECT COUNT(*) as count FROM calendar_events " . $where_clause . " AND event_type IN (5,6)";
        $stmt = $this->dbcrm()->prepare($query);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':date_start', $date_start, PDO::PARAM_STR);
        $stmt->bindValue(':date_end', $date_end, PDO::PARAM_STR);
        $stmt->execute();
        $meetings = $stmt->fetchColumn();
        $stmt = null;
        
        // Get high priority events (priority >= 8)
        $query = "SELECT COUNT(*) as count FROM calendar_events " . $where_clause . " AND priority >= 8";
        $stmt = $this->dbcrm()->prepare($query);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':date_start', $date_start, PDO::PARAM_STR);
        $stmt->bindValue(':date_end', $date_end, PDO::PARAM_STR);
        $stmt->execute();
        $high_priority = $stmt->fetchColumn();
        $stmt = null;
        
        // Get completed events (status = 2)
        $query = "SELECT COUNT(*) as count FROM calendar_events " . $where_clause . " AND status = 2";
        $stmt = $this->dbcrm()->prepare($query);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':date_start', $date_start, PDO::PARAM_STR);
        $stmt->bindValue(':date_end', $date_end, PDO::PARAM_STR);
        $stmt->execute();
        $completed = $stmt->fetchColumn();
        $stmt = null;
        
        // Return stats array
        return [
            'total_events' => (int)$total_events,
            'phone_calls' => (int)$phone_calls,
            'emails' => (int)$emails,
            'meetings' => (int)$meetings,
            'high_priority' => (int)$high_priority,
            'completed' => (int)$completed
        ];
    }
    
    /**
     * Check if user can modify an event
     */
    private function canUserModifyEvent($event_id, $user_id)
    {
        $query = "SELECT user_id FROM calendar_events WHERE id = :event_id";
        $stmt = $this->dbcrm()->prepare($query);
        $stmt->bindValue(':event_id', (int)$event_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;
        
        if (!$event) {
            return false;
        }
        
        // User owns the event
        if ($event['user_id'] == $user_id) {
            return true;
        }
        
        // Check if user has admin privileges (implement based on your RBAC)
        // For now, only owner can modify
        return false;
    }
    
    /**
     * Format event title for display
     */
    private function formatEventTitle($event)
    {
        $title = $event['title'];
        
        if ($event['contact_name']) {
            $title .= ' - ' . $event['contact_name'];
        } elseif ($event['company_name']) {
            $title .= ' - ' . $event['company_name'];
        }
        
        return $title;
    }
    
    /**
     * Create event from Next Action data (integration with leads/edit.php)
     */
    public function createEventFromNextAction($lead_id, $next_action_data, $user_id)
    {
        if (empty($next_action_data['next_action']) || empty($next_action_data['next_action_date'])) {
            return false;
        }
        
        // Build datetime from date and time
        $start_datetime = $next_action_data['next_action_date'];
        if (!empty($next_action_data['next_action_time'])) {
            $start_datetime .= ' ' . $next_action_data['next_action_time'];
        } else {
            $start_datetime .= ' 09:00:00'; // Default time
        }
        
        // Map next action type to event type
        $event_type = (int)$next_action_data['next_action'];
        
        // Create event data
        $event_data = [
            'title' => $this->generateTitleFromNextAction($next_action_data, $lead_id),
            'description' => $next_action_data['next_action_notes'] ?? '',
            'event_type' => $event_type,
            'start_datetime' => $start_datetime,
            'end_datetime' => null, // Will be calculated based on type
            'status' => 1, // Pending
            'priority' => 5, // Normal priority
            'lead_id' => $lead_id,
            'notes' => $next_action_data['next_action_notes'] ?? '',
            'timezone' => 'UTC' // Will be updated based on user settings
        ];
        
        // Set default duration based on event type
        $end_datetime = new DateTime($start_datetime);
        switch ($event_type) {
            case 1: // Phone call
                $end_datetime->add(new DateInterval('PT30M'));
                break;
            case 2: // Email
                $end_datetime->add(new DateInterval('PT15M'));
                break;
            case 5: // Virtual meeting
            case 6: // In-person meeting
                $end_datetime->add(new DateInterval('PT60M'));
                break;
            default:
                $end_datetime->add(new DateInterval('PT30M'));
        }
        
        $event_data['end_datetime'] = $end_datetime->format('Y-m-d H:i:s');
        
        return $this->createEvent($event_data, $user_id);
    }
    
    /**
     * Generate title from next action data
     */
    private function generateTitleFromNextAction($next_action_data, $lead_id)
    {
        // Get lead info
        $query = "SELECT company_name FROM leads WHERE id = :lead_id";
        $stmt = $this->dbcrm()->prepare($query);
        $stmt->bindValue(':lead_id', (int)$lead_id, PDO::PARAM_INT);
        $stmt->execute();
        $lead = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;
        
        $company_name = $lead ? $lead['company_name'] : 'Unknown Company';
        
        // Map action types to titles
        $action_titles = [
            1 => 'Call',
            2 => 'Email',
            3 => 'Text Message',
            4 => 'Internal Note',
            5 => 'Virtual Meeting',
            6 => 'In-Person Meeting'
        ];
        
        $action_type = (int)$next_action_data['next_action'];
        $action_title = $action_titles[$action_type] ?? 'Task';
        
        return $action_title . ' - ' . $company_name;
    }
}
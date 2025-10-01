<?php
// models/Task.php
require_once 'config/database.php';

class Task {
    private $conn;
    private $table_name = "tasks";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all tasks
    public function getAllTasks() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY start_datetime ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get tasks for FullCalendar format
    public function getTasksForCalendar($start = null, $end = null) {
        $query = "SELECT 
                    id,
                    title,
                    description,
                    task_type,
                    start_datetime as start,
                    end_datetime as end,
                    status,
                    priority,
                    contact_name,
                    contact_phone,
                    contact_email,
                    notes
                  FROM " . $this->table_name;
        
        $params = [];
        if ($start && $end) {
            $query .= " WHERE start_datetime BETWEEN ? AND ?";
            $params = [$start, $end];
        }
        
        $query .= " ORDER BY start_datetime ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        $tasks = $stmt->fetchAll();
        
        // Format for FullCalendar
        $events = [];
        foreach ($tasks as $task) {
            $color = $this->getColorByType($task['task_type']);
            $borderColor = $this->getBorderColorByPriority($task['priority']);
            
            $events[] = [
                'id' => $task['id'],
                'title' => $task['title'] . ' - ' . $task['contact_name'],
                'description' => $task['description'],
                'start' => $task['start'],
                'end' => $task['end'],
                'backgroundColor' => $color,
                'borderColor' => $borderColor,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'task_type' => $task['task_type'],
                    'status' => $task['status'],
                    'priority' => $task['priority'],
                    'contact_name' => $task['contact_name'],
                    'contact_phone' => $task['contact_phone'],
                    'contact_email' => $task['contact_email'],
                    'notes' => $task['notes'],
                    'description' => $task['description']
                ]
            ];
        }
        
        return $events;
    }

    // Create new task
    public function createTask($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                 (title, description, task_type, start_datetime, end_datetime, 
                  contact_name, contact_phone, contact_email, priority, status, notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['task_type'],
            $data['start_datetime'],
            $data['end_datetime'] ?? null,
            $data['contact_name'] ?? '',
            $data['contact_phone'] ?? '',
            $data['contact_email'] ?? '',
            $data['priority'] ?? 'medium',
            $data['status'] ?? 'pending',
            $data['notes'] ?? ''
        ]);
    }

    // Update task
    public function updateTask($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET 
                 title = ?, description = ?, task_type = ?, 
                 start_datetime = ?, end_datetime = ?, 
                 contact_name = ?, contact_phone = ?, contact_email = ?, 
                 priority = ?, status = ?, notes = ?
                 WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['task_type'],
            $data['start_datetime'],
            $data['end_datetime'] ?? null,
            $data['contact_name'] ?? '',
            $data['contact_phone'] ?? '',
            $data['contact_email'] ?? '',
            $data['priority'] ?? 'medium',
            $data['status'] ?? 'pending',
            $data['notes'] ?? '',
            $id
        ]);
    }

    // Delete task
    public function deleteTask($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Get task by ID
    public function getTaskById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Helper methods for colors
    private function getColorByType($type) {
        switch ($type) {
            case 'call': return '#007bff';
            case 'email': return '#28a745';
            case 'meeting': return '#ffc107';
            case 'follow_up': return '#6f42c1';
            default: return '#6c757d';
        }
    }

    private function getBorderColorByPriority($priority) {
        switch ($priority) {
            case 'high': return '#dc3545';
            case 'medium': return '#fd7e14';
            case 'low': return '#20c997';
            default: return '#6c757d';
        }
    }
}
?>
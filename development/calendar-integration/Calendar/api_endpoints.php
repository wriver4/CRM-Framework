<?php
// api/tasks.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/Task.php';

$database = new Database();
$db = $database->getConnection();
$task = new Task($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single task
            $result = $task->getTaskById($_GET['id']);
            echo json_encode($result ?: ['error' => 'Task not found']);
        } else {
            // Get all tasks for calendar
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;
            $events = $task->getTasksForCalendar($start, $end);
            echo json_encode($events);
        }
        break;

    case 'POST':
        // Create new task
        if (!$input) {
            echo json_encode(['error' => 'Invalid input data']);
            break;
        }

        // Validate required fields
        if (empty($input['title']) || empty($input['start_datetime']) || empty($input['task_type'])) {
            echo json_encode(['error' => 'Missing required fields']);
            break;
        }

        if ($task->createTask($input)) {
            echo json_encode(['success' => true, 'message' => 'Task created successfully']);
        } else {
            echo json_encode(['error' => 'Failed to create task']);
        }
        break;

    case 'PUT':
        // Update task
        if (!$input || !isset($_GET['id'])) {
            echo json_encode(['error' => 'Invalid input data or missing ID']);
            break;
        }

        if ($task->updateTask($_GET['id'], $input)) {
            echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
        } else {
            echo json_encode(['error' => 'Failed to update task']);
        }
        break;

    case 'DELETE':
        // Delete task
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'Missing task ID']);
            break;
        }

        if ($task->deleteTask($_GET['id'])) {
            echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
        } else {
            echo json_encode(['error' => 'Failed to delete task']);
        }
        break;

    default:
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
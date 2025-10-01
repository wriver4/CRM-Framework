<?php
/**
 * Contacts GET Operations
 * 
 * Handles both traditional framework calls and API JSON responses
 * Maintains backward compatibility with existing framework
 * 
 * @author CRM Framework
 * @version 1.0
 */

require_once dirname(__DIR__, 2) . '/config/system.php';

// Always treat this as an API call for consistency with calendar module
$is_api_call = true;

// Check authentication - API version (returns JSON instead of redirecting)
/*if (!Sessions::isLoggedIn()) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}*/

// Initialize contacts class
$contacts = new Contacts();

// Handle API requests
if ($is_api_call) {
    // Set JSON headers for API responses
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    // Only allow GET requests for this endpoint
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    try {
        // Get user ID from session
        $user_id = Sessions::getUserId();
        
        // Get action from URL parameter
        $action = $_GET['action'] ?? 'list';
        $contact_id = $_GET['id'] ?? null;
        
        switch ($action) {
            case 'list':
                // Get contacts list
                $limit = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);
                $search = $_GET['search'] ?? '';
                
                $results = $contacts->get_list($limit, $offset, $search);
                
                // Return direct array for calendar.js compatibility
                echo json_encode($results);
                break;
                
            case 'view':
            case 'get':
                // Get single contact
                if (!$contact_id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Contact ID required']);
                    exit;
                }
                
                $result = $contacts->get_by_id($contact_id);
                if (!$result) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Contact not found']);
                    exit;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $result
                ]);
                break;
                
            case 'search':
                // Search contacts
                $query = $_GET['q'] ?? $_GET['query'] ?? '';
                if (empty($query)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Search query required']);
                    exit;
                }
                
                $results = $contacts->search($query);
                echo json_encode([
                    'success' => true,
                    'data' => $results,
                    'total' => count($results)
                ]);
                break;
                
            case 'by_lead':
                // Get contacts by lead ID
                $lead_id = $_GET['lead_id'] ?? null;
                if (!$lead_id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Lead ID required']);
                    exit;
                }
                
                $results = $contacts->get_contacts_by_lead_id($lead_id);
                echo json_encode([
                    'success' => true,
                    'data' => $results,
                    'total' => count($results)
                ]);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
        
    } catch (Exception $e) {
        error_log('Contacts GET API Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
    
    exit; // End API processing
}

// Traditional framework processing (backward compatibility)
if ($dir == 'contacts' && $page == 'list') {
    $results = $contacts->get_list();
    $list = new ContactsList($results, $lang);
    $list->create_table();
}

if ($dir == 'contacts' && $page == 'view') {
    $id = trim($_GET["id"]);
    $result = $contacts->get_by_id($id);
}

if ($dir == 'contacts' && $page == 'edit') {
    $id = trim($_GET["id"]);
    $result = $contacts->get_by_id($id);
    $contact_type = (int) $result['contact_type'];
    $first_name = $result['first_name'];
    $family_name = $result['family_name'];
    $cell_phone = $result['cell_phone'];
    $business_phone = $result['business_phone'];
    $alt_phone = $result['alt_phone'];
    $phones = $result['phones'];
    $personal_email = $result['personal_email'];
    $business_email = $result['business_email'];
    $alt_email = $result['alt_email'];
    $p_street_1 = $result['p_street_1'];
    $p_street_2 = $result['p_street_2'];
    $p_city = $result['p_city'];
    $p_state = $result['p_state'];
    $p_postcode = $result['p_postcode'];
    $p_country = $result['p_country'];
    $business_name = $result['business_name'];
    $b_street_1 = $result['b_street_1'];
    $b_street_2 = $result['b_street_2'];
    $b_city = $result['b_city'];
    $b_state = $result['b_state'];
    $b_postcode = $result['b_postcode'];
    $b_country = $result['b_country'];
    $m_street_1 = $result['m_street_1'];
    $m_street_2 = $result['m_street_2'];
    $m_city = $result['m_city'];
    $m_state = $result['m_state'];
    $m_postcode = $result['m_postcode'];
    $m_country = $result['m_country'];
}

if ($dir == 'contacts' && $page == 'delete') {
    $id = trim($_GET["id"]);
    $result = $contacts->get_by_id($id);
}
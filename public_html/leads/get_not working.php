<?php
/**
 * Leads GET Operations
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
/*
if (!Sessions::isLoggedIn()) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}
*/
// Create instances
$leads = new Leads();
$users = new Users();
$helpers = new Helpers();

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
        $lead_id = $_GET['id'] ?? null;
        
        switch ($action) {
            case 'list':
                // Get leads list
                $limit = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);
                $filter = $_GET['filter'] ?? null;
                
                if ($filter === 'lost') {
                    // Get only Closed Lost leads (stage 140)
                    $results = $leads->get_leads_by_stage(140);
                } else {
                    // Use stage remapping to get proper leads stages
                    require_once dirname(__DIR__, 2) . '/scripts/stage_remapping.php';
                    $moduleFilters = StageRemapping::getModuleStageFilters();
                    $results = $leads->get_leads_by_stages($moduleFilters['leads']);
                }
                
                // Return direct array for calendar.js compatibility
                echo json_encode($results);
                break;
                
            case 'view':
            case 'get':
                // Get single lead
                if (!$lead_id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Lead ID required']);
                    exit;
                }
                
                $result = $leads->get_lead_by_lead_id($lead_id);
                if (!$result || empty($result[0])) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Lead not found']);
                    exit;
                }
                
                $lead_data = $result[0];
                
                // Add user name if available
                if (!empty($lead_data['last_edited_by'])) {
                    $lead_data['last_edited_by_name'] = $users->get_name_by_id($lead_data['last_edited_by']);
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $lead_data
                ]);
                break;
                
            case 'lead_id':
                // Get the last lead ID
                $last_lead_id = $leads->get_last_lead_id();
                echo json_encode([
                    'success' => true,
                    'data' => ['last_lead_id' => $last_lead_id]
                ]);
                break;
                
            case 'lead_with_user':
                // Get lead by ID with user information
                if (!$lead_id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Lead ID required']);
                    exit;
                }
                
                $lead = $leads->get_lead_by_lead_id($lead_id);
                if ($lead && !empty($lead[0])) {
                    $lead_data = $lead[0];
                    if (!empty($lead_data['last_edited_by'])) {
                        $lead_data['last_edited_by_name'] = $users->get_name_by_id($lead_data['last_edited_by']);
                    }
                    echo json_encode([
                        'success' => true,
                        'data' => $lead_data
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Lead not found']);
                }
                break;
                
            case 'user_name':
                // Get user name by ID
                $user_id_param = $_GET['user_id'] ?? null;
                if (!$user_id_param) {
                    http_response_code(400);
                    echo json_encode(['error' => 'User ID required']);
                    exit;
                }
                
                $user_name = $users->get_name_by_id($user_id_param);
                echo json_encode([
                    'success' => true,
                    'data' => ['user_name' => $user_name ?: 'Unknown User']
                ]);
                break;
                
            case 'search':
                // Search leads
                $query = $_GET['q'] ?? $_GET['query'] ?? '';
                if (empty($query)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Search query required']);
                    exit;
                }
                
                $results = $leads->search($query);
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
        error_log('Leads GET API Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
    
    exit; // End API processing
}

// Traditional framework processing (backward compatibility)
// Handle different GET requests
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'lead_id':
            // Get the last lead ID
            $last_lead_id = $leads->get_last_lead_id();
            echo $last_lead_id;
            break;
            
        case 'lead_with_user':
            // Get lead by ID with user information
            if (isset($_GET['id'])) {
                $lead = $leads->get_lead_by_lead_id($_GET['id']);  // Use lead_id (external number) instead of internal id
                if ($lead && !empty($lead['last_edited_by'])) {
                    $lead['last_edited_by_name'] = $users->get_name_by_id($lead['last_edited_by']);
                }
                header('Content-Type: application/json');
                echo json_encode($lead);
            }
            break;
            
        case 'user_name':
            // Get user name by ID
            if (isset($_GET['user_id'])) {
                $user_name = $users->get_name_by_id($_GET['user_id']);
                echo $user_name ?: 'Unknown User';
            }
            break;
            
        default:
            // Default: Get the last lead ID
            $last_lead_id = $leads->get_last_lead_id();
            echo $last_lead_id;
            break;
    }
} else {
    // Default: Get the last lead ID for backward compatibility
    $last_lead_id = $leads->get_last_lead_id();
    // echo $last_lead_id;
}

// Handle page-specific logic
if ($dir == 'leads' && $page == 'list') {
    // Check for filter parameter
    $filter = $_GET['filter'] ?? null;
    
    if ($filter === 'lost') {
        // Get only Closed Lost leads (stage 140)
        $results = $leads->get_leads_by_stage(140);
    } else {
        // Use stage remapping to get proper leads stages
        require_once dirname(__DIR__, 2) . '/scripts/stage_remapping.php';
        $moduleFilters = StageRemapping::getModuleStageFilters();
        $results = $leads->get_leads_by_stages($moduleFilters['leads']);
    }
    
    $list = new LeadsList($results, $lang);
    $list->create_table();
}

if ($dir == 'leads' && $page == 'view') {
    $id = trim($_GET["id"]);
    $result = $leads->get_lead_by_lead_id($id);  // Use lead_id (external number) instead of internal id
    if ($result && !empty($result[0])) {
        $result = $result[0]; // get_lead_by_id returns array
        
        // Base lead data
        $lead_source = $result["lead_source"];
        $first_name = $result["first_name"];
        $family_name = $result["family_name"];
        $cell_phone = $result["cell_phone"];
        $email = $result["email"];
        $contact_type = $result["contact_type"];
        $lead_id = $result["lead_id"];
        $stage = $result["stage"];
        $created_at = $result["created_at"];
        $updated_at = $result["updated_at"];
        $last_edited_by = $result["last_edited_by"];
        $last_edited_by_name = !empty($last_edited_by) ? $users->get_name_by_id($last_edited_by) : null;
        
        // Bridge table data
        $structure_info = $result["structure_info"] ?? [];
        $structure_type = $structure_info["structure_type"] ?? null;
        $structure_description = $structure_info["structure_description"] ?? null;
        $structure_other = $structure_info["structure_other"] ?? null;
        $structure_additional = $structure_info["structure_additional"] ?? null;
        
        // Documents data
        $documents = $result["documents"] ?? [];
        $pictures = $documents["pictures"] ?? [];
        $plans = $documents["plans"] ?? [];
        
        // Prospect data for cost estimates
        $prospect_info = $result["prospect"] ?? [];
        
        // Referral data
        $referral_info = $result["referral"] ?? [];
        
        // Contracting data
        $contracting_info = $result["contracting"] ?? [];
    }
}

if ($dir == 'leads' && $page == 'edit') {
    $id = trim($_GET["id"]);
    $result = $leads->get_lead_by_lead_id($id);  // Use lead_id (external number) instead of internal id
    if ($result && !empty($result[0])) {
        $result = $result[0]; // get_lead_by_id returns array
        
        // Base lead data
        $internal_id = $result["id"]; // Internal database ID for notes system
        $lead_source = $result["lead_source"];
        $full_name = $result["full_name"];
        $business_name = $result["business_name"];
        $project_name = $result["project_name"];
        $cell_phone = $result["cell_phone"];
        $email = $result["email"];
        $contact_type = $result["contact_type"];
        $lead_id = $result["lead_id"];
        $form_street_1 = $result["form_street_1"];
        $form_street_2 = $result["form_street_2"];
        $form_city = $result["form_city"];
        $form_state = $result["form_state"];
        $form_postcode = $result["form_postcode"];
        $form_country = $result["form_country"];
        $timezone = $result["timezone"];
        // If timezone is not set, calculate it from location
        if (empty($timezone)) {
            $timezone = $helpers->get_timezone_from_location($form_state, $form_country);
        }
        $full_address = $result["full_address"];
        $services_interested_in = $result["services_interested_in"];
        $get_updates = $result["get_updates"];
        $stage = $result["stage"];
        $created_at = $result["created_at"];
        $updated_at = $result["updated_at"];
        $last_edited_by = $result["last_edited_by"];
        
        // Bridge table data
        $structure_info = $result["structure_info"] ?? [];
        $documents = $result["documents"] ?? [];
        $referral_info = $result["referral"] ?? [];
        $prospect_info = $result["prospect"] ?? [];
        $contracting_info = $result["contracting"] ?? [];
        
        // Extract specific data for form fields
        $structure_type = $structure_info["structure_type"] ?? null;
        $structure_description = $structure_info["structure_description"] ?? null;
        $structure_other = $structure_info["structure_other"] ?? null;
        $structure_additional = $structure_info["structure_additional"] ?? null;
        
        $pictures = $documents["pictures"] ?? [];
        $plans = $documents["plans"] ?? [];
        
        $hear_about = $referral_info["referral_source_type"] ?? null;
        $hear_about_other = $referral_info["referral_source_name"] ?? null;
        
        // Extract screening estimates data from prospect_info
        $eng_system_cost_low = $prospect_info["eng_system_cost_low"] ?? null;
        $eng_system_cost_high = $prospect_info["eng_system_cost_high"] ?? null;
        $eng_protected_area = $prospect_info["eng_protected_area"] ?? null;
        $eng_cabinets = $prospect_info["eng_cabinets"] ?? null;
        $eng_total_pumps = $prospect_info["eng_total_pumps"] ?? null;
        

    }
    
    // Get contacts associated with this lead for the contact dropdown in notes
    if ($page == 'edit' && isset($id)) {
        $contacts = new Contacts();
        $property_contacts = $contacts->get_contacts_by_lead_id($id);
        $multiple_contacts = count($property_contacts) > 1;
        
        // Set default selected contact (first one or primary contact)
        $selected_contact_id = null;
        if (!empty($property_contacts)) {
            $selected_contact_id = $property_contacts[0]['id'];
        }
    }
}

if ($dir == 'leads' && $page == 'new') {
    // Get the last lead ID for the new form
    $last_lead_id = $leads->get_last_lead_id();
}

if ($dir == 'leads' && $page == 'delete') {
    $id = trim($_GET["id"]);
    $result = $leads->get_lead_by_lead_id($id);  // Use lead_id (external number) instead of internal id
    if ($result && !empty($result[0])) {
        $result = $result[0]; // get_lead_by_id returns array
        $first_name = $result["first_name"];
        $family_name = $result["family_name"];
        $email = $result["email"];
        $cell_phone = $result["cell_phone"];
        $stage = $result["stage"];
        $created_at = $result["created_at"];
        $updated_at = $result["updated_at"];
        $last_edited_by = $result["last_edited_by"];
        $last_edited_by_name = !empty($last_edited_by) ? $users->get_name_by_id($last_edited_by) : null;
    }
}
<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check if user is logged in
$not->loggedin();

// Create instances
$leads = new Leads();
$users = new Users();
$helpers = new Helpers();

// Handle different GET requests
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'estimate_number':
            // Get the last estimate number
            $last_estimate_number = $leads->get_last_estimate_number();
            echo $last_estimate_number;
            break;
            
        case 'lead_with_user':
            // Get lead by ID with user information
            if (isset($_GET['id'])) {
                $lead = $leads->get_lead_by_id($_GET['id']);
                if ($lead && !empty($lead['edited_by'])) {
                    $lead['edited_by_name'] = $users->get_name_by_id($lead['edited_by']);
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
            // Default: Get the last estimate number for backward compatibility
            $last_estimate_number = $leads->get_last_estimate_number();
            echo $last_estimate_number;
            break;
    }
} else {
    // Default: Get the last estimate number for backward compatibility
    $last_estimate_number = $leads->get_last_estimate_number();
    echo $last_estimate_number;
}
?>
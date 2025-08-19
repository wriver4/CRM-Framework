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
            // Default: Get the last estimate number for backward compatibility
            $last_estimate_number = $leads->get_last_estimate_number();
            echo $last_estimate_number;
            break;
    }
} else {
    // Default: Get the last estimate number for backward compatibility
    $last_estimate_number = $leads->get_last_estimate_number();
    // echo $last_estimate_number;
}

// Handle page-specific logic
if ($dir == 'leads' && $page == 'list') {
    $results = $leads->get_all_active();
    $list = new LeadsListTable($results, $lang);
    $list->create_table();
}

if ($dir == 'leads' && $page == 'view') {
    $id = trim($_GET["id"]);
    $result = $leads->get_lead_by_id($id);
    if ($result && !empty($result[0])) {
        $result = $result[0]; // get_lead_by_id returns array
        $lead_source = $result["lead_source"];
        $first_name = $result["first_name"];
        $last_name = $result["family_name"];
        $cell_phone = $result["cell_phone"];
        $email = $result["email"];
        $ctype = $result["ctype"];
        $notes = $result["notes"];
        $estimate_number = $result["estimate_number"];
        $stage = $result["stage"];
        $structure_type = $result["structure_type"];
        $created_at = $result["created_at"];
        $updated_at = $result["updated_at"];
        $last_edited_by = $result["last_edited_by"];
        $last_edited_by_name = !empty($last_edited_by) ? $users->get_name_by_id($last_edited_by) : null;
    }
}

if ($dir == 'leads' && $page == 'edit') {
    $id = trim($_GET["id"]);
    $result = $leads->get_lead_by_id($id);
    if ($result && !empty($result[0])) {
        $result = $result[0]; // get_lead_by_id returns array
        $lead_source = $result["lead_source"];
        $first_name = $result["first_name"];
        $last_name = $result["family_name"];
        $business_name = $result["business_name"];
        $cell_phone = $result["cell_phone"];
        $email = $result["email"];
        $ctype = $result["ctype"];
        $notes = $result["notes"];
        $estimate_number = $result["estimate_number"];
        $form_street_1 = $result["form_street_1"];
        $form_street_2 = $result["form_street_2"];
        $form_city = $result["form_city"];
        $form_state = $result["form_state"];
        $form_postcode = $result["form_postcode"];
        $form_country = $result["form_country"];
        $services_interested_in = $result["services_interested_in"];
        $structure_type = $result["structure_type"];
        $structure_description = $result["structure_description"];
        $structure_other = $result["structure_other"];
        $structure_additional = $result["structure_additional"];
        $picture_submitted_1 = $result["picture_submitted_1"];
        $picture_submitted_2 = $result["picture_submitted_2"];
        $picture_submitted_3 = $result["picture_submitted_3"];
        $plans_submitted_1 = $result["plans_submitted_1"];
        $plans_submitted_2 = $result["plans_submitted_2"];
        $plans_submitted_3 = $result["plans_submitted_3"];
        $picture_upload_link = $result["picture_upload_link"];
        $plans_upload_link = $result["plans_upload_link"];
        $plans_and_pics = $result["plans_and_pics"];
        $get_updates = $result["get_updates"];
        $hear_about = $result["hear_about"];
        $hear_about_other = $result["hear_about_other"];
        $stage = $result["stage"];
        $created_at = $result["created_at"];
        $updated_at = $result["updated_at"];
        $last_edited_by = $result["last_edited_by"];
    }
}

if ($dir == 'leads' && $page == 'delete') {
    $id = trim($_GET["id"]);
    $result = $leads->get_lead_by_id($id);
    if ($result && !empty($result[0])) {
        $result = $result[0]; // get_lead_by_id returns array
        $first_name = $result["first_name"];
        $last_name = $result["family_name"];
        $email = $result["email"];
        $cell_phone = $result["cell_phone"];
        $stage = $result["stage"];
        $created_at = $result["created_at"];
        $updated_at = $result["updated_at"];
        $last_edited_by = $result["last_edited_by"];
        $last_edited_by_name = !empty($last_edited_by) ? $users->get_name_by_id($last_edited_by) : null;
    }
}
?>
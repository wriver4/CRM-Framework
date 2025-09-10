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
}

// Handle page-specific logic
if ($dir == 'prospecting' && $page == 'list') {
    // Prospecting includes stages 1-3 (New Lead, Contacted, Qualified)
    $results = $leads->get_leads_by_stages([1, 2, 3]);
    $list = new LeadsList($results, $lang);
    $list->create_table();
}

if ($dir == 'prospecting' && $page == 'view') {
    $id = trim($_GET["id"]);
    $result = $leads->get_lead_by_lead_id($id);  // Use lead_id (external number) instead of internal id
    if ($result && !empty($result[0])) {
        $result = $result[0]; // get_lead_by_id returns array
        $lead_source = $result["lead_source"];
        $first_name = $result["first_name"];
        $family_name = $result["family_name"];
        $cell_phone = $result["cell_phone"];
        $email = $result["email"];
        $contact_type = $result["contact_type"];
        $lead_id = $result["lead_id"];
        $stage = $result["stage"];
        $structure_type = $result["structure_type"];
        $structure_description = $result["structure_description"];
        $structure_other = $result["structure_other"];
        $structure_additional = $result["structure_additional"];
        
        // Screening Estimates fields
        $eng_system_cost_low = $result["eng_system_cost_low"] ?? null;
        $eng_system_cost_high = $result["eng_system_cost_high"] ?? null;
        $eng_protected_area = $result["eng_protected_area"] ?? null;
        $sales_system_cost_low = $result["sales_system_cost_low"] ?? null;
        $sales_system_cost_high = $result["sales_system_cost_high"] ?? null;
        $sales_protected_area = $result["sales_protected_area"] ?? null;
        
        $picture_upload_link = $result["picture_upload_link"];
        $plans_upload_link = $result["plans_upload_link"];
        $created_at = $result["created_at"];
        $updated_at = $result["updated_at"];
        $last_edited_by = $result["last_edited_by"];
        $last_edited_by_name = !empty($last_edited_by) ? $users->get_name_by_id($last_edited_by) : null;
    }
}

if ($dir == 'prospecting' && $page == 'edit') {
    $id = trim($_GET["id"]);
    $result = $leads->get_lead_by_lead_id($id);  // Use lead_id (external number) instead of internal id
    if ($result && !empty($result[0])) {
        $result = $result[0]; // get_lead_by_id returns array
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
        $structure_type = $result["structure_type"];
        $structure_description = $result["structure_description"];
        $structure_other = $result["structure_other"];
        $structure_additional = $result["structure_additional"];
        
        // Screening Estimates fields
        $eng_system_cost_low = $result["eng_system_cost_low"] ?? null;
        $eng_system_cost_high = $result["eng_system_cost_high"] ?? null;
        $eng_protected_area = $result["eng_protected_area"] ?? null;
        $sales_system_cost_low = $result["sales_system_cost_low"] ?? null;
        $sales_system_cost_high = $result["sales_system_cost_high"] ?? null;
        $sales_protected_area = $result["sales_protected_area"] ?? null;
        
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

if ($dir == 'prospecting' && $page == 'new') {
    // Get the last lead ID for the new form
    $last_lead_id = $leads->get_last_lead_id();
}

if ($dir == 'prospecting' && $page == 'delete') {
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
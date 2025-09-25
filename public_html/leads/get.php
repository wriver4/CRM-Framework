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
        // Get all active leads (default behavior)
        $results = $leads->get_all_active();
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
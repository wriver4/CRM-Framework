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
            if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
                $lead = $leads->get_lead_by_lead_id(trim($_GET['id']));  // Use lead_id (external number) instead of internal id
                if ($lead && !empty($lead['last_edited_by'])) {
                    $lead['last_edited_by_name'] = $users->get_name_by_id($lead['last_edited_by']);
                }
                header('Content-Type: application/json');
                echo json_encode($lead);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No valid ID provided']);
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

// Handle page-specific logic for admin list
if ($dir == 'admin' && $subdir == 'leads' && $page == 'list') {
    $results = $leads->get_all_active();
    
    // Create admin-specific leads list table - exactly like regular but only edit button
    class AdminLeadsListTable extends LeadsList {
        public function row_nav($value, $rid)
        {
            echo $this->row_nav_open;

            // Only edit button - no view, no delete
            echo $this->row_nav_button_open;
            echo $this->row_nav_button_edit_class_enabled;
            echo
            $this->row_nav_button_href_edit_open
              . urlencode($value)
              . $this->row_nav_button_href_close
              . $this->row_nav_button_edit_icon
              . $this->row_nav_button_close;

            echo $this->row_nav_close;
        }
    }
    
    $list = new AdminLeadsListTable($results, $lang);
    $list->create_table();
}

// Handle page-specific logic for admin edit
if ($dir == 'admin/leads' && $page == 'edit') {
    if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
        // Redirect to leads list if no ID provided
        header("Location: " . URL . "/leads/list");
        exit;
    }
    $id = trim($_GET["id"]);
    $result = $leads->get_lead_by_lead_id($id);  // Use lead_id (external number) instead of internal id
    
    // Get contacts for this lead
    $contacts = new Contacts();
    $lead_contacts = $contacts->get_contacts_by_lead_id($id);
    
    // Also get property contacts for the contact selector (same data, different variable name for compatibility)
    $property_contacts = $lead_contacts;
    $multiple_contacts = count($property_contacts) > 1;
    
    // Set default selected contact (first one or primary contact)
    $selected_contact_id = null;
    if (!empty($property_contacts)) {
        $selected_contact_id = $property_contacts[0]['id'];
    }
    
    if ($result && !empty($result[0])) {
        $result = $result[0]; // get_lead_by_id returns array
        $internal_id = $result["id"]; // Internal database ID for notes system
        
        // Basic lead info
        $lead_source = $result["lead_source"] ?? 1;
        $lead_id = $result["lead_id"] ?? '';
        $first_name = $result["first_name"] ?? '';
        $family_name = $result["family_name"] ?? '';
        $full_name = $result["full_name"] ?? '';
        $cell_phone = $result["cell_phone"] ?? '';
        $email = $result["email"] ?? '';
        $ctype = $result["ctype"] ?? 1;
        $business_name = $result["business_name"] ?? '';
        $project_name = $result["project_name"] ?? '';
        
        // Address fields
        $form_street_1 = $result["form_street_1"] ?? '';
        $form_street_2 = $result["form_street_2"] ?? '';
        $form_city = $result["form_city"] ?? '';
        $form_state = $result["form_state"] ?? '';
        $form_postcode = $result["form_postcode"] ?? '';
        $form_country = $result["form_country"] ?? 'US';
        $full_address = $result["full_address"] ?? '';
        $timezone = $result["timezone"] ?? '';
        
        // Clean up form_state - extract just the state code if it contains mixed data
        if (!empty($form_state)) {
            // If form_state contains spaces or other data, try to extract state code
            if (preg_match('/\b(US-[A-Z]{2})\b/', $form_state, $matches)) {
                $form_state = $matches[1];
            } elseif (preg_match('/\b([A-Z]{2})\s+\d/', $form_state, $matches)) {
                // Pattern like "CO 80517" - extract the state code
                $form_state = 'US-' . $matches[1];
            } elseif (strlen($form_state) == 2 && ctype_alpha($form_state)) {
                // Just a 2-letter state code
                $form_state = 'US-' . strtoupper($form_state);
            }
            // If it doesn't match expected patterns, leave it as is
        }
        
        // If timezone is not set, calculate it from location
        if (empty($timezone)) {
            $timezone = $helpers->get_timezone_from_location($form_state, $form_country);
        }
        
        // Services and structure
        $services_interested_in = $result["services_interested_in"] ?? '';
        $structure_type = $result["structure_type"] ?? 1;
        $structure_description = $result["structure_description"] ?? '';
        $structure_other = $result["structure_other"] ?? '';
        $structure_additional = $result["structure_additional"] ?? '';
        
        // Pictures and plans
        $picture_submitted_1 = $result["picture_submitted_1"] ?? '';
        $picture_submitted_2 = $result["picture_submitted_2"] ?? '';
        $picture_submitted_3 = $result["picture_submitted_3"] ?? '';
        $plans_submitted_1 = $result["plans_submitted_1"] ?? '';
        $plans_submitted_2 = $result["plans_submitted_2"] ?? '';
        $plans_submitted_3 = $result["plans_submitted_3"] ?? '';
        $picture_upload_link = $result["picture_upload_link"] ?? '';
        $plans_upload_link = $result["plans_upload_link"] ?? '';
        $plans_and_pics = $result["plans_and_pics"] ?? 0;
        
        // Additional fields
        $get_updates = $result["get_updates"] ?? 1;
        $hear_about = $result["hear_about"] ?? '';
        $hear_about_other = $result["hear_about_other"] ?? '';
        
        // Get notes from leads_notes bridge table
        $lead_notes = $leads->get_lead_notes($internal_id);
        
        // Get navigation info (previous/next leads)
        $navigation = $leads->get_lead_navigation($id);
        
        // System fields
        $stage_raw = $result["stage"] ?? 1;
        // Convert stage to number if it's a text value
        if (is_numeric($stage_raw)) {
            $stage = (int)$stage_raw;
        } else {
            // Convert text stage to number using the conversion method
            $stage = $leads->convert_text_stage_to_number($stage_raw);
        }
        $created_at = $result["created_at"] ?? '';
        $updated_at = $result["updated_at"] ?? '';
        $last_edited_by = $result["last_edited_by"] ?? null;
        $last_edited_by_name = !empty($last_edited_by) ? $users->get_name_by_id($last_edited_by) : null;
    } else {
        // Lead not found, redirect to leads list with error message
        $_SESSION['error_message'] = "Lead not found with ID: " . htmlspecialchars($id);
        header("Location: " . URL . "/leads/list");
        exit;
    }
}

<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check if user is logged in
$not->loggedin();

// Function to format phone number with dashes
function format_phone_number($phone) {
    if (empty($phone)) {
        return '';
    }
    
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Remove leading 1 for US numbers
    if (strlen($phone) == 11 && substr($phone, 0, 1) == '1') {
        $phone = substr($phone, 1);
    }
    
    // Format 10-digit US numbers as XXX-XXX-XXXX
    if (strlen($phone) == 10) {
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
    }
    
    // If not standard 10-digit format, return as-is
    return $phone;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php');
    exit;
}

// Handle different actions
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    // Handle delete action
    $id = $_POST['id'] ?? null;
    if ($id) {
        $result = $leads->delete_lead($id);
        if ($result) {
            $_SESSION['success_message'] = 'Lead deleted successfully';
        } else {
            $_SESSION['error_message'] = 'Error deleting lead';
        }
    }
    header('Location: list.php');
    exit;
}

// Handle edit action
if (isset($_POST['page']) && $_POST['page'] == 'edit') {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        $_SESSION['error_message'] = 'Invalid lead ID';
        header('Location: list.php');
        exit;
    }
    
    // Prepare update data
    $data = [
        'lead_source' => $_POST['lead_source'] ?? 1,
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'cell_phone' => format_phone_number($_POST['cell_phone'] ?? ''),
        'email' => $_POST['email'] ?? '',
        'ctype' => $_POST['ctype'] ?? 1,
        'notes' => $_POST['notes'] ?? '',
        'lead_number' => $_POST['lead_number'] ?? '',
        'business_name' => $_POST['business_name'] ?? '',
        'form_street_1' => $_POST['form_street_1'] ?? '',
        'form_street_2' => $_POST['form_street_2'] ?? '',
        'form_city' => $_POST['form_city'] ?? '',
        'form_state' => $_POST['form_state'] ?? '',
        'form_postcode' => $_POST['form_postcode'] ?? '',
        'form_country' => $_POST['form_country'] ?? 'US',
        'timezone' => $_POST['timezone'] ?? null,
        'full_address' => trim(implode('', array_filter([
            ($_POST['form_street_1'] ?? '') ? ($_POST['form_street_1'] . ', ') : '',
            ($_POST['form_street_2'] ?? '') ? ($_POST['form_street_2'] . ', ') : '',
            ($_POST['form_city'] ?? '') ? ($_POST['form_city'] . ', ') : '',
            ($_POST['form_state'] ?? '') ? ($_POST['form_state'] . ' ') : '',
            $_POST['form_postcode'] ?? '',
            ($_POST['form_country'] ?? 'US') !== 'US' ? (', ' . $_POST['form_country']) : ''
        ]))),
        'structure_type' => $_POST['structure_type'] ?? 1,
        'stage' => $_POST['stage'] ?? 1, // Default to stage 1 (Lead)
        'last_edited_by' => $_POST['last_edited_by'] ?? $_SESSION['user_id'] ?? null,
        
        // Set other fields to maintain existing values or defaults
        'services_interested_in' => '',
        'structure_description' => '',
        'structure_other' => '',
        'structure_additional' => '',
        'picture_submitted_1' => '',
        'picture_submitted_2' => '',
        'picture_submitted_3' => '',
        'plans_submitted_1' => '',
        'plans_submitted_2' => '',
        'plans_submitted_3' => '',
        'picture_upload_link' => '',
        'plans_upload_link' => '',
        'plans_and_pics' => 0,
        'get_updates' => 1,
        'hear_about' => '',
        'hear_about_other' => '',
        
        // Legacy fields
        'family_name' => $_POST['last_name'] ?? '',
        'fullname' => ($_POST['first_name'] ?? '') . ' ' . ($_POST['last_name'] ?? ''),
        'existing_client' => null,
        'address' => null,
        'proposal_sent_date' => null,
        'scheduled_date' => null,
        'lead_lost_notes' => null,
        'site_visit_by' => null,
        'referred_to' => null,
        'lead_notes' => null,
        'prospect_notes' => null,
        'lead_lost' => null,
        'site_visit_completed' => null,
        'closer' => null,
        'referred_services' => null,
        'assigned_to' => null,
        'referred' => null,
        'site_visit_date' => null,
        'date_qualified' => null,
        'contacted_date' => null,
        'referral_done' => null,
        'jd_referral_notes' => null,
        'closing_notes' => null,
        'prospect_lost' => null,
        'to_contracting' => null
    ];
    
    // Validate the data
    $validation_errors = $leads->validate_lead_data($data);
    if (!empty($validation_errors)) {
        $_SESSION['form_errors'] = $validation_errors;
        $_SESSION['form_data'] = $_POST;
        header('Location: edit.php?id=' . $id);
        exit;
    }
    
    // Update the lead
    $result = $leads->update_lead($id, $data);
    
    if ($result) {
        // Handle note creation if note_text is provided
        if (!empty(trim($_POST['note_text'] ?? ''))) {
            $notes = new Notes();
            $note_data = [
                'source' => $_POST['note_source'] ?? 1,
                'note_text' => trim($_POST['note_text']),
                'user_id' => $_SESSION['user_id'] ?? null,
                'form_source' => 'leads'
            ];
            
            $note_validation = $notes->validate_note_data($note_data);
            if (empty($note_validation)) {
                $notes->create_note_for_lead($id, $note_data);
            }
        }
        
        $_SESSION['success_message'] = 'Lead updated successfully';
        header('Location: view.php?id=' . $id);
        exit;
    } else {
        $_SESSION['error_message'] = 'Error updating lead. Please try again.';
        $_SESSION['form_data'] = $_POST;
        header('Location: edit.php?id=' . $id);
        exit;
    }
}

// Create instances
$leads = new Leads();
$users = new Users();

try {
    // Prepare data for insertion
    $data = [
        'lead_source' => $_POST['lead_source'] ?? 1,
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'cell_phone' => format_phone_number($_POST['cell_phone'] ?? ''),
        'email' => $_POST['email'] ?? '',
        'ctype' => $_POST['ctype'] ?? 1,
        'notes' => $_POST['notes'] ?? '',
        'lead_number' => $_POST['lead_number'] ?? '',
        'business_name' => $_POST['business_name'] ?? '',
        'form_street_1' => $_POST['form_street_1'] ?? '',
        'form_street_2' => $_POST['form_street_2'] ?? '',
        'form_city' => $_POST['form_city'] ?? '',
        'form_state' => $_POST['form_state'] ?? '',
        'form_postcode' => $_POST['form_postcode'] ?? '',
        'form_country' => $_POST['form_country'] ?? 'US',
        'timezone' => $_POST['timezone'] ?? null,
        'full_address' => trim(implode('', array_filter([
            ($_POST['form_street_1'] ?? '') ? ($_POST['form_street_1'] . ', ') : '',
            ($_POST['form_street_2'] ?? '') ? ($_POST['form_street_2'] . ', ') : '',
            ($_POST['form_city'] ?? '') ? ($_POST['form_city'] . ', ') : '',
            ($_POST['form_state'] ?? '') ? ($_POST['form_state'] . ' ') : '',
            $_POST['form_postcode'] ?? '',
            ($_POST['form_country'] ?? 'US') !== 'US' ? (', ' . $_POST['form_country']) : ''
        ]))),
        'services_interested_in' => is_array($_POST['services_interested_in']) ? implode(',', $_POST['services_interested_in']) : '',
        'structure_type' => $_POST['structure_type'] ?? 1,
        'structure_description' => is_array($_POST['structure_description']) ? implode(',', $_POST['structure_description']) : '',
        'structure_other' => $_POST['structure_other'] ?? '',
        'structure_additional' => $_POST['structure_additional'] ?? '',
        'picture_submitted_1' => $_POST['picture_submitted_1'] ?? '',
        'picture_submitted_2' => $_POST['picture_submitted_2'] ?? '',
        'picture_submitted_3' => $_POST['picture_submitted_3'] ?? '',
        'plans_submitted_1' => $_POST['plans_submitted_1'] ?? '',
        'plans_submitted_2' => $_POST['plans_submitted_2'] ?? '',
        'plans_submitted_3' => $_POST['plans_submitted_3'] ?? '',
        'picture_upload_link' => $_POST['picture_upload_link'] ?? '',
        'plans_upload_link' => $_POST['plans_upload_link'] ?? '',
        'plans_and_pics' => ($_POST['plans_and_pics'] == 'Yes') ? 1 : 0,
        'get_updates' => ($_POST['get_updates'] == 'Yes') ? 1 : 0,
        'hear_about' => is_array($_POST['hear_about']) ? implode(',', $_POST['hear_about']) : '',
        'hear_about_other' => $_POST['hear_about_other'] ?? '',
        'stage' => $_POST['stage'] ?? 1, // Default to stage 1 (Lead)
        'last_edited_by' => $_POST['last_edited_by'] ?? $_SESSION['user_id'] ?? null,
        
        // Legacy/backward compatibility fields - set to empty/null for new records
        'family_name' => $_POST['last_name'] ?? '', // Copy last_name to family_name
        'fullname' => ($_POST['first_name'] ?? '') . ' ' . ($_POST['last_name'] ?? ''),
        'existing_client' => null,
        'address' => null,
        'proposal_sent_date' => null,
        'scheduled_date' => null,
        'lead_lost_notes' => null,
        'site_visit_by' => null,
        'referred_to' => null,
        'lead_notes' => null,
        'prospect_notes' => null,
        'lead_lost' => null,
        'site_visit_completed' => null,
        'closer' => null,
        'referred_services' => null,
        'assigned_to' => null,
        'referred' => null,
        'site_visit_date' => null,
        'date_qualified' => null,
        'contacted_date' => null,
        'referral_done' => null,
        'jd_referral_notes' => null,
        'closing_notes' => null,
        'prospect_lost' => null,
        'to_contracting' => null
    ];
    
    // Validate the data
    $validation_errors = $leads->validate_lead_data($data);
    if (!empty($validation_errors)) {
        // Handle validation errors - you might want to redirect back with error messages
        $_SESSION['form_errors'] = $validation_errors;
        $_SESSION['form_data'] = $_POST;
        header('Location: new.php');
        exit;
    }
    
    // Create the lead
    $result = $leads->create_lead($data);
    
    if ($result) {
        // Success - redirect to list or success page
        $_SESSION['success_message'] = 'Lead created successfully';
        header('Location: list.php'); // Assuming you have a list.php
        exit;
    } else {
        // Error creating lead
        $_SESSION['error_message'] = 'Error creating lead. Please try again.';
        $_SESSION['form_data'] = $_POST;
        header('Location: new.php');
        exit;
    }
    
} catch (Exception $e) {
    // Handle any exceptions
    error_log('Lead creation error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'An unexpected error occurred. Please try again.';
    $_SESSION['form_data'] = $_POST;
    header('Location: new.php');
    exit;
}
?>
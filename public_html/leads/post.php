<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check if user is logged in
$not->loggedin();

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: new.php');
    exit;
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
        'cell_phone' => $_POST['cell_phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'ctype' => $_POST['ctype'] ?? 1,
        'notes' => $_POST['notes'] ?? '',
        'estimate_number' => $_POST['estimate_number'] ?? '',
        'p_street_1' => $_POST['p_street_1'] ?? '',
        'p_street_2' => $_POST['p_street_2'] ?? '',
        'p_city' => $_POST['p_city'] ?? '',
        'p_state' => $_POST['p_state'] ?? '',
        'p_postcode' => $_POST['p_postcode'] ?? '',
        'p_country' => $_POST['p_country'] ?? 'US',
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
        'stage' => $_POST['stage'] ?? 'Lead',
        'edited_by' => $_POST['edited_by'] ?? $_SESSION['user_id'] ?? null,
        
        // Legacy/backward compatibility fields - set to empty/null for new records
        'family_name' => '', // Will be populated by data migration script
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
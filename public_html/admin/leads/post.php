<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check if user is logged in
$not->loggedin();

// Function to format phone number with dashes (for storage)
function format_phone_number($phone, $country = 'US') {
    if (empty($phone)) {
        return '';
    }
    
    // For storage, we want to keep it simple - just clean and format for US numbers
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Remove leading 1 for US numbers
    if (strlen($phone) == 11 && substr($phone, 0, 1) == '1') {
        $phone = substr($phone, 1);
    }
    
    // Format 10-digit US numbers as XXX-XXX-XXXX for storage
    if (strlen($phone) == 10) {
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
    }
    
    // If not standard 10-digit format, return as-is
    return $phone;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /leads/list.php');
    exit;
}

// Create instances
$leads = new Leads();
$helpers = new Helpers();

// Get the lead ID
$id = $_POST['id'] ?? null;
if (!$id) {
    $_SESSION['error_message'] = 'Lead ID is required';
    header('Location: /leads/list.php');
    exit;
}

try {
    // Prepare data for update
    $data = [];
    
    // Basic lead information
    $data['lead_source'] = $_POST['lead_source'] ?? '';
    $data['lead_id'] = $_POST['lead_id'] ?? '';
    $data['first_name'] = $_POST['first_name'] ?? '';
    $data['family_name'] = $_POST['family_name'] ?? '';
    
    // For admin edit, use the provided full_name as-is (don't auto-generate)
    $data['full_name'] = $_POST['full_name'] ?? '';
    
    $data['cell_phone'] = format_phone_number($_POST['cell_phone'] ?? '');
    $data['email'] = $_POST['email'] ?? '';
    $data['contact_type'] = $_POST['ctype'] ?? '';  // Map ctype form field to contact_type database column
    $data['business_name'] = $_POST['business_name'] ?? '';
    $data['project_name'] = $_POST['project_name'] ?? '';
    
    // Address information
    $data['form_street_1'] = $_POST['form_street_1'] ?? '';
    $data['form_street_2'] = $_POST['form_street_2'] ?? '';
    $data['form_city'] = $_POST['form_city'] ?? '';
    $data['form_state'] = $_POST['form_state'] ?? '';
    $data['form_postcode'] = $_POST['form_postcode'] ?? '';
    $data['form_country'] = $_POST['form_country'] ?? '';
    $data['timezone'] = $_POST['timezone'] ?? '';
    
    // For admin edit, use the provided full_address as-is (don't auto-generate)
    $data['full_address'] = $_POST['full_address'] ?? '';
    
    // Services interested in (handle array)
    if (isset($_POST['services_interested_in']) && is_array($_POST['services_interested_in'])) {
        $data['services_interested_in'] = implode(',', $_POST['services_interested_in']);
    } else {
        $data['services_interested_in'] = '';
    }
    
    // Structure information
    $data['structure_type'] = $_POST['structure_type'] ?? '';
    
    // Structure description (handle array)
    if (isset($_POST['structure_description']) && is_array($_POST['structure_description'])) {
        $data['structure_description'] = implode(',', $_POST['structure_description']);
    } else {
        $data['structure_description'] = '';
    }
    
    $data['structure_other'] = $_POST['structure_other'] ?? '';
    $data['structure_additional'] = $_POST['structure_additional'] ?? '';
    
    // Pictures and plans
    $data['picture_submitted_1'] = $_POST['picture_submitted_1'] ?? '';
    $data['picture_submitted_2'] = $_POST['picture_submitted_2'] ?? '';
    $data['picture_submitted_3'] = $_POST['picture_submitted_3'] ?? '';
    $data['plans_submitted_1'] = $_POST['plans_submitted_1'] ?? '';
    $data['plans_submitted_2'] = $_POST['plans_submitted_2'] ?? '';
    $data['plans_submitted_3'] = $_POST['plans_submitted_3'] ?? '';
    $data['picture_upload_link'] = $_POST['picture_upload_link'] ?? '';
    $data['plans_upload_link'] = $_POST['plans_upload_link'] ?? '';
    
    // Convert plans_and_pics from Yes/No to 1/0 for database storage
    $data['plans_and_pics'] = $helpers->convert_yes_no_to_int($_POST['plans_and_pics'] ?? '');
    
    // Additional information
    // Convert get_updates from Yes/No to 1/0 for database storage
    $data['get_updates'] = $helpers->convert_yes_no_to_int($_POST['get_updates'] ?? '');
    
    // How did you hear about us (handle array)
    if (isset($_POST['hear_about']) && is_array($_POST['hear_about'])) {
        $data['hear_about'] = implode(',', $_POST['hear_about']);
    } else {
        $data['hear_about'] = '';
    }
    
    $data['hear_about_other'] = $_POST['hear_about_other'] ?? '';
    
    // System fields
    $data['stage'] = $_POST['stage'] ?? '10'; // Default to new Lead stage (10)
    $data['last_edited_by'] = $_SESSION['user_id'] ?? null;
    $data['updated_at'] = date('Y-m-d H:i:s');
    
    // Calculate timezone if not provided
    if (empty($data['timezone'])) {
        $data['timezone'] = $helpers->get_timezone_from_location($data['form_state'], $data['form_country']);
    }
    
    // Update the lead
    $result = $leads->update_lead($id, $data);
    
    if ($result) {
        $_SESSION['success_message'] = 'Lead updated successfully';
        header('Location: edit.php?id=' . $id);
    } else {
        $_SESSION['error_message'] = 'Error updating lead';
        header('Location: edit.php?id=' . $id);
    }
    
} catch (Exception $e) {
    // Enhanced error logging with form context
    $errorLogger = new SqlErrorLogger();
    $errorLogger->logFormError('admin_leads_edit', $e->getMessage(), [
        'lead_id' => $id,
        'user_id' => $_SESSION['user_id'] ?? 'anonymous',
        'form_data_keys' => array_keys($_POST),
        'error_type' => get_class($e),
        'error_code' => $e->getCode()
    ]);
    
    $_SESSION['error_message'] = 'Error updating lead: ' . $e->getMessage();
    header('Location: edit.php?id=' . $id);
}

exit;

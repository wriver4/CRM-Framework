<?php
/**
 * Enhanced Lead Creation with Contact Integration
 * This file handles lead creation with automatic contact creation/linking
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check if user is logged in
$not->loggedin();

// Initialize classes (autoloaded)
$leadsEnhanced = new Leads();
$contactsEnhanced = new Contacts();

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

// Function to sanitize input data
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Function to determine redirect URL based on stage
function getRedirectUrlForStage($stage, $lead_id) {
    switch ((int)$stage) {
        case 1: case 2: case 3: // New Lead, Contacted, Qualified
            return "/prospecting/view.php?id=" . $lead_id;
        case 4: // Referral
            return "/referrals/view.php?id=" . $lead_id;
        case 5: case 6: case 7: case 8: case 9: case 10: case 11: case 12: // Prospect stages
            return "/prospects/view.php?id=" . $lead_id;
        case 13: // Contracting
            return "/contracting/view.php?id=" . $lead_id;
        case 14: case 15: // Closed Won/Lost
            return "/leads/view.php?id=" . $lead_id; // Keep closed leads in main leads module
        default:
            return "/leads/view.php?id=" . $lead_id; // Default fallback
    }
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php');
    exit;
}

// Handle different actions
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    // Handle delete action (existing functionality)
    $id = $_POST['id'] ?? null;
    if ($id) {
        $result = $leadsEnhanced->delete_lead($id);
        if ($result) {
            $_SESSION['success_message'] = 'Lead deleted successfully';
        } else {
            $_SESSION['error_message'] = 'Error deleting lead';
        }
    }
    header('Location: list.php');
    exit;
}

// Handle lead creation/update
try {
    // Collect and sanitize form data
    $data = [
        // Basic lead information
        'lead_source' => (int)($_POST['lead_source'] ?? 1),
        'first_name' => sanitize_input($_POST['first_name'] ?? ''),
        'family_name' => sanitize_input($_POST['family_name'] ?? ''),
        'cell_phone' => format_phone_number($_POST['cell_phone'] ?? ''),
        'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
        'business_name' => sanitize_input($_POST['business_name'] ?? ''),
        'contact_type' => (int)($_POST['contact_type'] ?? 1),
        
        // Address information
        'form_street_1' => sanitize_input($_POST['form_street_1'] ?? ''),
        'form_street_2' => sanitize_input($_POST['form_street_2'] ?? ''),
        'form_city' => sanitize_input($_POST['form_city'] ?? ''),
        'form_state' => sanitize_input($_POST['form_state'] ?? ''),
        'form_postcode' => sanitize_input($_POST['form_postcode'] ?? ''),
        'form_country' => sanitize_input($_POST['form_country'] ?? 'US'),
        'timezone' => sanitize_input($_POST['timezone'] ?? ''),
        
        // Project information
        'services_interested_in' => sanitize_input($_POST['services_interested_in'] ?? ''),
        'structure_type' => (int)($_POST['structure_type'] ?? 1),
        'structure_description' => sanitize_input($_POST['structure_description'] ?? ''),
        'structure_other' => sanitize_input($_POST['structure_other'] ?? ''),
        'structure_additional' => sanitize_input($_POST['structure_additional'] ?? ''),
        
        // File uploads
        'picture_submitted_1' => sanitize_input($_POST['picture_submitted_1'] ?? ''),
        'picture_submitted_2' => sanitize_input($_POST['picture_submitted_2'] ?? ''),
        'picture_submitted_3' => sanitize_input($_POST['picture_submitted_3'] ?? ''),
        'plans_submitted_1' => sanitize_input($_POST['plans_submitted_1'] ?? ''),
        'plans_submitted_2' => sanitize_input($_POST['plans_submitted_2'] ?? ''),
        'plans_submitted_3' => sanitize_input($_POST['plans_submitted_3'] ?? ''),
        'picture_upload_link' => sanitize_input($_POST['picture_upload_link'] ?? ''),
        'plans_upload_link' => sanitize_input($_POST['plans_upload_link'] ?? ''),
        'plans_and_pics' => sanitize_input($_POST['plans_and_pics'] ?? ''),
        
        // Marketing information
        'get_updates' => isset($_POST['get_updates']) ? 1 : 0,
        'hear_about' => sanitize_input($_POST['hear_about'] ?? ''),
        'hear_about_other' => sanitize_input($_POST['hear_about_other'] ?? ''),
        
        // Lead management
        'stage' => (int)($_POST['stage'] ?? 1),
        'last_edited_by' => $_SESSION['user_id'] ?? 1,
        
        // Additional fields
        'full_name' => trim(($_POST['first_name'] ?? '') . ' ' . ($_POST['family_name'] ?? '')),
        'full_address' => sanitize_input($_POST['full_address'] ?? '')
    ];

    // Validate required fields
    $validation_errors = $leadsEnhanced->validate_lead_with_contact_data($data);
    
    if (!empty($validation_errors)) {
        $_SESSION['error_message'] = 'Validation errors: ' . implode(', ', $validation_errors);
        $_SESSION['form_data'] = $_POST; // Preserve form data
        header('Location: new.php');
        exit;
    }

    // Check if this is an update or create operation
    $lead_id = $_POST['id'] ?? null;
    
    if ($lead_id) {
        // Update existing lead
        $result = $leadsEnhanced->update_lead_with_contact($lead_id, $data);
        
        if ($result['success']) {
            $_SESSION['success_message'] = 'Lead and contact updated successfully';
            
            // Log the update
            $audit = new Audit();
            $audit->log(
                $_SESSION['user_id'] ?? 1,                    // user_id
                'lead_update',                                 // event
                "lead_{$lead_id}",                            // resource
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',     // useragent
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown',         // ip
                $lead_id,                                      // location
                "Lead updated with contact integration"        // data
            );
            
            // Determine redirect based on new stage
            $redirect_url = getRedirectUrlForStage($data['stage'], $lead_id);
            header('Location: ' . $redirect_url);
        } else {
            $_SESSION['error_message'] = $result['message'] . (isset($result['error']) ? ': ' . $result['error'] : '');
            $_SESSION['form_data'] = $_POST;
            header('Location: edit.php?id=' . $lead_id);
        }
        
    } else {
        // Generate lead ID
        $last_lead_id = $leadsEnhanced->get_last_lead_id();
        $data['lead_id'] = $last_lead_id + 1;
        
        // Create new lead with contact
        $result = $leadsEnhanced->create_lead_with_contact($data);
        
        if ($result['success']) {
            $_SESSION['success_message'] = 'Lead and contact created successfully';
            
            // Log the creation
            $audit = new Audit();
            $audit->log(
                $_SESSION['user_id'] ?? 1,                    // user_id
                'lead_create',                                 // event
                "lead_{$result['lead_id']}",                  // resource
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',     // useragent
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown',         // ip
                $result['lead_id'],                           // location
                "Lead created with contact integration (Contact ID: {$result['contact_id']})" // data
            );
            
            // phpList Integration - Add subscriber if they opted in for updates
            if ($data['get_updates'] == 1 && !empty($data['email'])) {
                try {
                    $phpListSubscribers = new PhpListSubscribers();
                    
                    // Check if phpList sync is enabled
                    if ($phpListSubscribers->isSyncEnabled()) {
                        // Prepare lead data for phpList integration
                        $leadDataForPhpList = array_merge($data, [
                            'contact_id' => $result['contact_id']
                        ]);
                        
                        // Create phpList subscriber record
                        $subscriberId = $phpListSubscribers->createSubscriberFromLead($result['lead_id'], $leadDataForPhpList);
                        
                        if ($subscriberId) {
                            // Log successful phpList subscriber creation
                            $audit->log(
                                $_SESSION['user_id'] ?? 1,
                                'phplist_subscriber_created',
                                "phplist_subscriber_{$subscriberId}",
                                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                                $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                                $result['lead_id'],
                                "phpList subscriber created for lead {$result['lead_id']} (Subscriber ID: {$subscriberId})"
                            );
                        } else {
                            // Log phpList subscriber creation failure
                            error_log("Failed to create phpList subscriber for lead {$result['lead_id']}");
                        }
                    }
                } catch (Exception $e) {
                    // Log phpList integration error but don't fail the lead creation
                    error_log("phpList integration error for lead {$result['lead_id']}: " . $e->getMessage());
                    
                    $audit->log(
                        $_SESSION['user_id'] ?? 1,
                        'phplist_integration_error',
                        "lead_{$result['lead_id']}",
                        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                        $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                        $result['lead_id'],
                        "phpList integration failed: " . $e->getMessage()
                    );
                }
            }
            
            // Clear any preserved form data
            unset($_SESSION['form_data']);
            
            // Check submit action to determine redirect
            $submit_action = $_POST['submit_action'] ?? 'submit';
            if ($submit_action === 'submit_and_next') {
                header('Location: new.php');
            } else {
                header('Location: list.php');
            }
        } else {
            $_SESSION['error_message'] = $result['message'] . (isset($result['error']) ? ': ' . $result['error'] : '');
            $_SESSION['form_data'] = $_POST; // Preserve form data
            header('Location: new.php');
        }
    }

} catch (Exception $e) {
    // Log the error
    error_log("Lead creation/update error: " . $e->getMessage());
    
    $_SESSION['error_message'] = 'An unexpected error occurred. Please try again.';
    $_SESSION['form_data'] = $_POST; // Preserve form data
    
    if (isset($_POST['id'])) {
        header('Location: edit.php?id=' . $_POST['id']);
    } else {
        header('Location: new.php');
    }
}

exit;

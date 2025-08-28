<?php
/**
 * Enhanced Lead Creation Handler with Contact Integration
 * Supports all 6 lead source form types with CSRF protection
 * Automatically creates and links contacts based on lead data
 * Multilingual support for Spanish and English users
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Ensure user is logged in
$not->loggedin();

// Initialize language support
require LANG . '/en.php';

// Initialize classes
$helpers = new Helpers();
$leadsEnhanced = new LeadsEnhanced();
$contactsEnhanced = new ContactsEnhanced();
$audit = new Audit();
$nonce = new Nonce();

// Response array for JSON responses
$response = [
    'success' => false,
    'message' => '',
    'lead_id' => null,
    'contact_id' => null,
    'errors' => [],
    'debug' => []
];

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception($lang['form_validation_failed'] ?? 'Invalid request method');
    }

    // CSRF Protection
    if (!isset($_POST['csrf_token']) || empty($_POST['csrf_token'])) {
        throw new Exception($lang['csrf_token_missing'] ?? 'CSRF token missing');
    }
    
    if (!$nonce->verify($_POST['csrf_token'])) {
        throw new Exception($lang['csrf_token_invalid'] ?? 'CSRF token invalid');
    }

    // Input validation and sanitization
    $leadData = [
        // Required fields
        'lead_id' => trim($_POST['lead_id'] ?? ''),
        'first_name' => trim($_POST['first_name'] ?? ''),
        'family_name' => trim($_POST['family_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'lead_source' => (int)($_POST['lead_source'] ?? 1),
        
        // Contact information
        'cell_phone' => trim($_POST['cell_phone'] ?? ''),
        'business_name' => trim($_POST['business_name'] ?? ''),
        'contact_type' => (int)($_POST['ctype'] ?? 1), // Default to Owner
        
        // Address information
        'form_street_1' => trim($_POST['form_street_1'] ?? ''),
        'form_street_2' => trim($_POST['form_street_2'] ?? ''),
        'form_city' => trim($_POST['form_city'] ?? ''),
        'form_state' => trim($_POST['form_state'] ?? ''),
        'form_postcode' => trim($_POST['form_postcode'] ?? ''),
        'form_country' => trim($_POST['form_country'] ?? 'US'),
        'timezone' => trim($_POST['timezone'] ?? 'UTC'),
        
        // Services and structure
        'services_interested_in' => $_POST['services_interested_in'] ?? [],
        'structure_type' => (int)($_POST['structure_type'] ?? 1),
        'structure_description' => $_POST['structure_description'] ?? [],
        'structure_other' => trim($_POST['structure_other'] ?? ''),
        'structure_additional' => trim($_POST['structure_additional'] ?? ''),
        
        // File uploads
        'picture_submitted_1' => trim($_POST['picture_submitted_1'] ?? ''),
        'picture_submitted_2' => trim($_POST['picture_submitted_2'] ?? ''),
        'picture_submitted_3' => trim($_POST['picture_submitted_3'] ?? ''),
        'plans_submitted_1' => trim($_POST['plans_submitted_1'] ?? ''),
        'plans_submitted_2' => trim($_POST['plans_submitted_2'] ?? ''),
        'plans_submitted_3' => trim($_POST['plans_submitted_3'] ?? ''),
        'picture_upload_link' => trim($_POST['picture_upload_link'] ?? ''),
        'plans_upload_link' => trim($_POST['plans_upload_link'] ?? ''),
        'plans_and_pics' => trim($_POST['plans_and_pics'] ?? 'No'),
        
        // Marketing and communication
        'notes' => trim($_POST['notes'] ?? ''),
        'get_updates' => trim($_POST['get_updates'] ?? 'Yes'),
        'hear_about' => $_POST['hear_about'] ?? [],
        'hear_about_other' => trim($_POST['hear_about_other'] ?? ''),
        
        // System fields
        'stage' => (int)($_POST['stage'] ?? 1),
        'last_edited_by' => (int)($_SESSION['user_id'] ?? 0),
        'contact_integration' => (int)($_POST['contact_integration'] ?? 1)
    ];

    // Lead source specific validation
    $leadSourceConfig = getLeadSourceValidationConfig($leadData['lead_source']);
    validateLeadData($leadData, $leadSourceConfig, $lang);

    // Convert arrays to JSON for database storage
    $leadData['services_interested_in'] = !empty($leadData['services_interested_in']) ? 
        json_encode($leadData['services_interested_in']) : null;
    $leadData['structure_description'] = !empty($leadData['structure_description']) ? 
        json_encode($leadData['structure_description']) : null;
    $leadData['hear_about'] = !empty($leadData['hear_about']) ? 
        json_encode($leadData['hear_about']) : null;

    // Start database transaction
    $pdo = Database::getInstance()->getConnection();
    $pdo->beginTransaction();

    try {
        // Create the lead
        $leadId = $leadsEnhanced->createLead($leadData);
        if (!$leadId) {
            throw new Exception($lang['form_processing_complete'] ?? 'Failed to create lead');
        }

        $response['lead_id'] = $leadId;
        $response['debug'][] = "Lead created with ID: $leadId";

        // Create and link contact if integration is enabled
        if ($leadData['contact_integration']) {
            $contactId = $contactsEnhanced->create_contact_from_lead($leadData);
            if (!$contactId) {
                throw new Exception($lang['contact_creation_failed'] ?? 'Failed to create contact');
            }

            $response['contact_id'] = $contactId;
            $response['debug'][] = "Contact created with ID: $contactId";

            // Update lead with contact_id
            $updateResult = $leadsEnhanced->updateLeadContactId($leadId, $contactId);
            if (!$updateResult) {
                throw new Exception($lang['lead_contact_relationship_created'] ?? 'Failed to link lead and contact');
            }

            // Create bridge table relationship
            $bridgeResult = $contactsEnhanced->createLeadContactRelationship($leadId, $contactId, 'primary');
            if (!$bridgeResult) {
                throw new Exception($lang['bridge_table_error'] ?? 'Failed to create relationship in bridge table');
            }

            $response['debug'][] = "Bridge table relationship created";
        }

        // Create audit log entry
        $auditData = [
            'entity_type' => 'lead',
            'entity_id' => $leadId,
            'action' => 'lead_created_with_contact_integration',
            'old_values' => null,
            'new_values' => json_encode($leadData),
            'user_id' => $_SESSION['user_id'] ?? 0,
            'created_at' => date('Y-m-d H:i:s'),
            'notes' => 'Lead created via form with automatic contact integration'
        ];

        if ($leadData['contact_integration'] && isset($contactId)) {
            $auditData['notes'] .= ". Contact ID: $contactId created and linked.";
        }

        $audit->create($auditData);
        $response['debug'][] = "Audit log created";

        // Commit transaction
        $pdo->commit();

        $response['success'] = true;
        $response['message'] = $lang['form_processing_complete'] ?? 'Lead created successfully';
        if ($leadData['contact_integration']) {
            $response['message'] .= '. ' . ($lang['contact_created_success'] ?? 'Contact created and linked');
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['errors'][] = $e->getMessage();
    
    // Log error
    error_log("Lead creation error: " . $e->getMessage());
    
    // Create error audit log
    if (isset($audit)) {
        $audit->create([
            'entity_type' => 'lead',
            'entity_id' => null,
            'action' => 'lead_creation_failed',
            'old_values' => null,
            'new_values' => json_encode($_POST),
            'user_id' => $_SESSION['user_id'] ?? 0,
            'created_at' => date('Y-m-d H:i:s'),
            'notes' => 'Lead creation failed: ' . $e->getMessage()
        ]);
    }
}

// Handle response based on request type
if (isset($_POST['ajax']) || 
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
    
    // AJAX/JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Traditional form response
    if ($response['success']) {
        // Redirect to success page or lead view
        $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : "view?id=" . $response['lead_id'];
        header("Location: $redirect");
        exit;
    } else {
        // Redirect back to form with error
        $error = urlencode($response['message']);
        header("Location: new?error=$error");
        exit;
    }
}

/**
 * Get validation configuration for specific lead source
 */
function getLeadSourceValidationConfig($leadSource) {
    return [
        1 => [ // Web Estimate
            'required' => ['lead_id', 'first_name', 'family_name', 'email', 'cell_phone'],
            'optional' => ['form_street_1', 'form_city', 'form_state', 'form_country']
        ],
        2 => [ // LTR Form
            'required' => ['lead_id', 'first_name', 'family_name', 'email', 'cell_phone', 'notes'],
            'optional' => ['form_street_1', 'form_city', 'form_state']
        ],
        3 => [ // Contact Form
            'required' => ['first_name', 'family_name', 'email'],
            'optional' => ['notes']
        ],
        4 => [ // Phone Inquiry
            'required' => ['first_name', 'family_name', 'cell_phone'],
            'optional' => ['email', 'notes']
        ],
        5 => [ // Cold Call
            'required' => ['first_name', 'family_name', 'cell_phone'],
            'optional' => ['email', 'notes']
        ],
        6 => [ // In Person
            'required' => ['first_name', 'family_name', 'email', 'cell_phone'],
            'optional' => ['form_street_1', 'form_city', 'form_state']
        ]
    ][$leadSource] ?? [
        'required' => ['lead_id', 'first_name', 'family_name', 'email'],
        'optional' => []
    ];
}

/**
 * Validate lead data based on source configuration
 */
function validateLeadData($data, $config, $lang) {
    $errors = [];

    // Check required fields
    foreach ($config['required'] as $field) {
        if (empty($data[$field])) {
            $fieldName = $lang["lead_$field"] ?? $field;
            $errors[] = sprintf($lang['required_field_missing'] ?? 'Required field missing: %s', $fieldName);
        }
    }

    // Validate email format if provided
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = $lang['email_format_invalid'] ?? 'Invalid email format';
    }

    // Validate phone format if provided
    if (!empty($data['cell_phone']) && !preg_match('/^[\d\s\-\(\)\+\.]{10,}$/', $data['cell_phone'])) {
        $errors[] = $lang['phone_format_invalid'] ?? 'Invalid phone format';
    }

    if (!empty($errors)) {
        throw new Exception(implode('. ', $errors));
    }
}
?>
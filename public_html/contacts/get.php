<?php
// Note: system.php and authentication are handled by api.php when called via API
// For direct access, we need to handle it here
/*if (!defined('SYSTEM_LOADED')) {
    require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
    
    // Check authentication - API version (returns JSON instead of redirecting)
    if (!Sessions::isLoggedIn()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
}*/
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$contacts = new Contacts();

// Handle different GET requests
/*if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'list':
            // Get list of contacts for API (used by calendar.js)
            header('Content-Type: application/json');
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            
            // Get contacts with proper fields for calendar dropdown
            $all_contacts = $contacts->get_list();
            
            // Limit results and format for API
            $limited_contacts = array_slice($all_contacts, 0, $limit);
            $formatted_contacts = [];
            
            foreach ($limited_contacts as $contact) {
                $formatted_contacts[] = [
                    'id' => $contact['id'],
                    'full_name' => ($contact['first_name'] ?? '') . ' ' . ($contact['family_name'] ?? ''),
                    'first_name' => $contact['first_name'] ?? '',
                    'family_name' => $contact['family_name'] ?? '',
                    'business_name' => $contact['business_name'] ?? ''
                ];
            }
            
            echo json_encode($formatted_contacts);
            break;
            
        default:
            // No default action for contacts
            break;
    }
} else {
  */
    // Handle page-specific logic (existing functionality)
    if ($dir == 'contacts' && $page == 'list') {
      $results = $contacts->get_list();
      $list = new ContactsList($results, $lang);
      $list->create_table();
    }
//}

if ($dir == 'contacts' && $page == 'view') {
  $id = (int)trim($_GET["id"]);
  $result = $contacts->get_by_id($id);
}

if ($dir == 'contacts' && $page == 'edit') {
  $id = (int)trim($_GET["id"]);
  $result = $contacts->get_by_id($id);
  $contact_type = (int) $result['contact_type'];
  $first_name = $result['first_name'];
  $family_name = $result['family_name'];
  $cell_phone = $result['cell_phone'];
  $business_phone = $result['business_phone'];
  $alt_phone = $result['alt_phone'];
  $phones = $result['phones'];
  $personal_email = $result['personal_email'];
  $business_email = $result['business_email'];
  $alt_email = $result['alt_email'];
  $p_street_1 = $result['p_street_1'];
  $p_street_2 = $result['p_street_2'];
  $p_city = $result['p_city'];
  $p_state = $result['p_state'];
  $p_postcode = $result['p_postcode'];
  $p_country = $result['p_country'];
  $business_name = $result['business_name'];
  $b_street_1 = $result['b_street_1'];
  $b_street_2 = $result['b_street_2'];
  $b_city = $result['b_city'];
  $b_state = $result['b_state'];
  $b_postcode = $result['b_postcode'];
  $b_country = $result['b_country'];
  $m_street_1 = $result['m_street_1'];
  $m_street_2 = $result['m_street_2'];
  $m_city = $result['m_city'];
  $m_state = $result['m_state'];
  $m_postcode = $result['m_postcode'];
  $m_country = $result['m_country'];
}

if ($dir == 'contacts' && $page == 'delete') {
  $id = (int)trim($_GET["id"]);
  $result = $contacts->get_by_id($id);
}
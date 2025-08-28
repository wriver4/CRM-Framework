<?php
/*
  * adminrnd contacts/post.php
  *
*/
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$contacts = new Contacts();
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['dir'] == 'contacts' && $_POST['page'] == 'new') {
  $lead_id = isset($_POST['lead_id']) ? htmlentities(trim($_POST['lead_id'])) : '';
  $ctype = htmlentities(trim($_POST['ctype']));
  
  // If creating contact from a lead, get lead data for address pre-population if needed
  $lead_data = null;
  if (!empty($lead_id)) {
    $leads = new Leads();
    $result = $leads->get_lead_by_id($lead_id);
    if (!empty($result) && isset($result[0])) {
      $lead_data = $result[0];
      // Note: ctype is NOT inherited - each contact can have different contact type
    }
  }
  
  $first_name = htmlentities(trim($_POST['first_name']));
  $family_name = htmlentities(trim($_POST['family_name']));
  $full_name = $first_name . ' ' . $family_name;
  $cell_phone = htmlentities(trim($_POST['cell_phone']));
  $business_phone = htmlentities(trim($_POST['business_phone']));
  $alt_phone = htmlentities(trim($_POST['alt_phone']));
  $phones = json_encode(['1' => $cell_phone, '2' => $business_phone, '3' => $alt_phone]);
  $personal_email = htmlentities(trim($_POST['personal_email']));
  $business_email = htmlentities(trim($_POST['business_email']));
  $alt_email = htmlentities(trim($_POST['alt_email']));
  $emails = json_encode(['1' => $personal_email, '2' => $business_email, '3' => $alt_email]);
  $p_street_1 = htmlentities(trim($_POST['p_street_1']));
  $p_street_2 = htmlentities(trim($_POST['p_street_2']));
  $p_city = htmlentities(trim($_POST['p_city']));
  $p_state = htmlentities(trim($_POST['p_state']));
  $p_postcode = htmlentities(trim($_POST['p_postcode']));
  $p_country = htmlentities(trim($_POST['p_country']));
  
  // If personal address is empty and we have lead data, use the lead's address
  if ($p_street_1 == '' && $p_street_2 == '' && $p_city == '' && $p_state == '' && $p_postcode == '' && $p_country == '' && $lead_data !== null) {
    $p_street_1 = $lead_data['form_street_1'] ?? '';
    $p_street_2 = $lead_data['form_street_2'] ?? '';
    $p_city = $lead_data['form_city'] ?? '';
    $p_state = $lead_data['form_state'] ?? '';
    $p_postcode = $lead_data['form_postcode'] ?? '';
    $p_country = $lead_data['form_country'] ?? '';
  }
  
  // Get business address information from form
  $business_name = htmlentities(trim($_POST['business_name']));
  $b_street_1 = htmlentities(trim($_POST['b_street_1']));
  $b_street_2 = htmlentities(trim($_POST['b_street_2']));
  $b_city = htmlentities(trim($_POST['b_city']));
  $b_state = htmlentities(trim($_POST['b_state']));
  $b_postcode = htmlentities(trim($_POST['b_postcode']));
  $b_country = htmlentities(trim($_POST['b_country']));
  $m_street_1 = htmlentities(trim($_POST['m_street_1']));
  $m_street_2 = htmlentities(trim($_POST['m_street_2']));
  $m_city = htmlentities(trim($_POST['m_city']));
  $m_state = htmlentities(trim($_POST['m_state']));
  $m_postcode = htmlentities(trim($_POST['m_postcode']));
  $m_country = htmlentities(trim($_POST['m_country']));
  $sql = "INSERT INTO contacts (lead_id, ctype, first_name, family_name, fullname, cell_phone, business_phone, alt_phone, phones, personal_email, business_email, alt_email, emails, p_street_1, p_street_2, p_city, p_state, p_postcode, p_country, business_name, b_street_1, b_street_2, b_city, b_state, b_postcode, b_country, m_street_1, m_street_2, m_city, m_state, m_postcode, m_country) VALUES (:lead_id, :ctype, :first_name, :family_name, :fullname, :cell_phone, :business_phone, :alt_phone, :phones, :personal_email, :business_email, :alt_email, :emails, :p_street_1, :p_street_2, :p_city, :p_state, :p_postcode, :p_country, :business_name, :b_street_1, :b_street_2, :b_city, :b_state, :b_postcode, :b_country, :m_street_1, :m_street_2, :m_city, :m_state, :m_postcode, :m_country)";
  $stmt = $dbcrm->prepare($sql);
  $stmt->bindValue(':lead_id', !empty($lead_id) ? (int)$lead_id : null, PDO::PARAM_INT);
  $stmt->bindValue(':ctype', $ctype, PDO::PARAM_INT);
  $stmt->bindValue(':first_name', $first_name, PDO::PARAM_STR);
  $stmt->bindValue(':family_name', $family_name, PDO::PARAM_STR);
  $stmt->bindValue(':fullname', $full_name, PDO::PARAM_STR);
  $stmt->bindValue(':cell_phone', $cell_phone, PDO::PARAM_STR);
  $stmt->bindValue(':business_phone', $business_phone, PDO::PARAM_STR);
  $stmt->bindValue(':alt_phone', $alt_phone, PDO::PARAM_STR);
  $stmt->bindValue(':phones', $phones, PDO::PARAM_STR);
  $stmt->bindValue(':personal_email', $personal_email, PDO::PARAM_STR);
  $stmt->bindValue(':business_email', $business_email, PDO::PARAM_STR);
  $stmt->bindValue(':alt_email', $alt_email, PDO::PARAM_STR);
  $stmt->bindValue(':emails', $emails, PDO::PARAM_STR);
  $stmt->bindValue(':p_street_1', $p_street_1, PDO::PARAM_STR);
  $stmt->bindValue(':p_street_2', $p_street_2, PDO::PARAM_STR);
  $stmt->bindValue(':p_city', $p_city, PDO::PARAM_STR);
  $stmt->bindValue(':p_state', $p_state, PDO::PARAM_STR);
  $stmt->bindValue(':p_postcode', $p_postcode, PDO::PARAM_STR);
  $stmt->bindValue(':p_country', $p_country, PDO::PARAM_STR);
  $stmt->bindValue(':business_name', $business_name, PDO::PARAM_STR);
  $stmt->bindValue(':b_street_1', $b_street_1, PDO::PARAM_STR);
  $stmt->bindValue(':b_street_2', $b_street_2, PDO::PARAM_STR);
  $stmt->bindValue(':b_city', $b_city, PDO::PARAM_STR);
  $stmt->bindValue(':b_state', $b_state, PDO::PARAM_STR);
  $stmt->bindValue(':b_postcode', $b_postcode, PDO::PARAM_STR);
  $stmt->bindValue(':b_country', $b_country, PDO::PARAM_STR);
  $stmt->bindValue(':m_street_1', $m_street_1, PDO::PARAM_STR);
  $stmt->bindValue(':m_street_2', $m_street_2, PDO::PARAM_STR);
  $stmt->bindValue(':m_city', $m_city, PDO::PARAM_STR);
  $stmt->bindValue(':m_state', $m_state, PDO::PARAM_STR);
  $stmt->bindValue(':m_postcode', $m_postcode, PDO::PARAM_STR);
  $stmt->bindValue(':m_country', $m_country, PDO::PARAM_STR);
  if ($stmt->execute()) {
    $contact_id = $dbcrm->lastInsertId();
    $stmt = null;
    
    // If contact was created from a lead, also create the relationship in leads_contacts table
    if (!empty($lead_id) && $contact_id) {
      $relationship_sql = "INSERT INTO leads_contacts (lead_id, contact_id, relationship_type, status) VALUES (:lead_id, :contact_id, 'primary', 1)";
      $relationship_stmt = $dbcrm->prepare($relationship_sql);
      $relationship_stmt->bindValue(':lead_id', (int)$lead_id, PDO::PARAM_INT);
      $relationship_stmt->bindValue(':contact_id', $contact_id, PDO::PARAM_INT);
      $relationship_stmt->execute();
      $relationship_stmt = null;
    }
    
    // If contact was created from a lead, redirect back to that lead's edit page
    if (!empty($lead_id)) {
      $_SESSION['success_message'] = "Contact created successfully and associated with Lead #" . $lead_id;
      header("location: " . URL . "/admin/leads/edit?id=" . urlencode($lead_id));
    } else {
      header("location: list");
    }
  } else {
    // If contact creation failed and came from a lead, redirect back with error
    if (!empty($lead_id)) {
      $_SESSION['error_message'] = "Failed to create contact. Please try again.";
      header("location: " . URL . "/admin/leads/edit?id=" . urlencode($lead_id));
    } else {
      echo "Something went wrong. Please try again later.";
    }
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['dir'] == 'contacts' && $_POST['page'] == 'edit') {
  $id = htmlentities(trim($_POST['id']));
  //$prop_id = htmlentities(trim($_POST['prop_id']));
  // $ctype = htmlentities(trim($_POST['ctype']));
  // $first_name = htmlentities(trim($_POST['first_name']));
  // $family_name = htmlentities(trim($_POST['family_name']));
  //$full_name = $first_name . ' ' . $family_name;
  $cell_phone = htmlentities(trim($_POST['cell_phone']));
  $business_phone = htmlentities(trim($_POST['business_phone']));
  $alt_phone = htmlentities(trim($_POST['alt_phone']));
  $phones = json_encode(['1' => $cell_phone, '2' => $business_phone, '3' => $alt_phone]);
  $personal_email = htmlentities(trim($_POST['personal_email']));
  $business_email = htmlentities(trim($_POST['business_email']));
  $alt_email = htmlentities(trim($_POST['alt_email']));
  $emails = json_encode(['1' => $personal_email, '2' => $business_email, '3' => $alt_email]);
  $p_street_1 = htmlentities(trim($_POST['p_street_1']));
  $p_street_2 = htmlentities(trim($_POST['p_street_2']));
  $p_city = htmlentities(trim($_POST['p_city']));
  $p_state = htmlentities(trim($_POST['p_state']));
  $p_postcode = htmlentities(trim($_POST['p_postcode']));
  $p_country = htmlentities(trim($_POST['p_country']));
  // Get business address information from form
  $business_name = htmlentities(trim($_POST['business_name']));
  $b_street_1 = htmlentities(trim($_POST['b_street_1']));
  $b_street_2 = htmlentities(trim($_POST['b_street_2']));
  $b_city = htmlentities(trim($_POST['b_city']));
  $b_state = htmlentities(trim($_POST['b_state']));
  $b_postcode = htmlentities(trim($_POST['b_postcode']));
  $b_country = htmlentities(trim($_POST['b_country']));
  $m_street_1 = htmlentities(trim($_POST['m_street_1']));
  $m_street_2 = htmlentities(trim($_POST['m_street_2']));
  $m_city = htmlentities(trim($_POST['m_city']));
  $m_state = htmlentities(trim($_POST['m_state']));
  $m_postcode = htmlentities(trim($_POST['m_postcode']));
  $m_country = htmlentities(trim($_POST['m_country']));

    $sql = "UPDATE contacts SET cell_phone = :cell_phone, business_phone = :business_phone, alt_phone = :alt_phone, phones = :phones, personal_email = :personal_email, business_email = :business_email, alt_email = :alt_email, emails = :emails, p_street_1 = :p_street_1, p_street_2 = :p_street_2, p_city = :p_city, p_state = :p_state, p_postcode = :p_postcode, p_country = :p_country, business_name = :business_name, b_street_1 = :b_street_1, b_street_2 = :b_street_2, b_city = :b_city, b_state = :b_state, b_postcode = :b_postcode, b_country = :b_country, m_street_1 = :m_street_1, m_street_2 = :m_street_2, m_city = :m_city, m_state = :m_state, m_postcode = :m_postcode, m_country = :m_country WHERE id = :id";
  $stmt = $dbcrm->prepare($sql);
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);

  $stmt->bindValue(':cell_phone', $cell_phone, PDO::PARAM_STR);
  $stmt->bindValue(':business_phone', $business_phone, PDO::PARAM_STR);
  $stmt->bindValue(':alt_phone', $alt_phone, PDO::PARAM_STR);
  $stmt->bindValue(':phones', $phones);
  $stmt->bindValue(':personal_email', $personal_email, PDO::PARAM_STR);
  $stmt->bindValue(':business_email', $business_email, PDO::PARAM_STR);
  $stmt->bindValue(':alt_email', $alt_email, PDO::PARAM_STR);
  $stmt->bindValue(':emails', $emails, PDO::PARAM_STR);
  $stmt->bindValue(':p_street_1', $p_street_1, PDO::PARAM_STR);
  $stmt->bindValue(':p_street_2', $p_street_2, PDO::PARAM_STR);
  $stmt->bindValue(':p_city', $p_city, PDO::PARAM_STR);
  $stmt->bindValue(':p_state', $p_state, PDO::PARAM_STR);
  $stmt->bindValue(':p_postcode', $p_postcode, PDO::PARAM_STR);
  $stmt->bindValue(':p_country', $p_country, PDO::PARAM_STR);
  $stmt->bindValue(':business_name', $business_name, PDO::PARAM_STR);
  $stmt->bindValue(':b_street_1', $b_street_1, PDO::PARAM_STR);
  $stmt->bindValue(':b_street_2', $b_street_2, PDO::PARAM_STR);
  $stmt->bindValue(':b_city', $b_city, PDO::PARAM_STR);
  $stmt->bindValue(':b_state', $b_state, PDO::PARAM_STR);
  $stmt->bindValue(':b_postcode', $b_postcode, PDO::PARAM_STR);
  $stmt->bindValue(':b_country', $b_country, PDO::PARAM_STR);
  $stmt->bindValue(':m_street_1', $m_street_1, PDO::PARAM_STR);
  $stmt->bindValue(':m_street_2', $m_street_2, PDO::PARAM_STR);
  $stmt->bindValue(':m_city', $m_city, PDO::PARAM_STR);
  $stmt->bindValue(':m_state', $m_state, PDO::PARAM_STR);
  $stmt->bindValue(':m_postcode', $m_postcode, PDO::PARAM_STR);
  $stmt->bindValue(':m_country', $m_country, PDO::PARAM_STR);
  if ($stmt->execute()) {
    $stmt = null;
    header("location: list");
  } else {
    echo "Something went wrong. Please try again later.";
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['dir'] == 'contacts' && $_POST['page'] == 'delete') {
  $status = 0;
  $id = trim($_POST["id"]);
  $sql = "UPDATE contacts SET status = :status WHERE id = :id";
  $stmt = $dbcrm->prepare($sql);
	$stmt->bindValue(':id', $id, PDO::PARAM_INT);
	$stmt->bindValue(':status', $status, PDO::PARAM_INT);
    if ($stmt->execute()) {
    $stmt = null;
    header("location: list");
  } else {
    echo "Something went wrong. Please try again later.";
  }
}
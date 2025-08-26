<?php
/*
  * adminrnd contacts/post.php
  *
*/
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$contacts = new Contacts();
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['dir'] == 'contacts' && $_POST['page'] == 'new') {
  $ctype = htmlentities(trim($_POST['ctype']));
  $call_order = htmlentities(trim($_POST['call_order']));
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
  if ($p_street_1 == '' && $p_street_2 == '' && $p_city == '' && $p_state == '' && $p_postcode == '' && $p_country == '' && $ctype == '1' || $ctype == '2' || $ctype == '3') {
    $property = new Properties();
    $result = $property->get_by_prop_address_by_prop_id($prop_id);
    $p_street_1 = $result['street_address_1'];
    $p_street_2 = $result['street_address_2'];
    $p_city = $result['city'];
    $p_state = $result['state'];
    $p_postcode = $result['postcode'];
    $p_country = $result['country'];
  }
  $b_nickname = $contacts->get_installer_nickname();
  $business_name = htmlentities(trim($_POST['business_name']));
  if (in_array($business_name, $b_nickname)) {
    $installer_id = array_search($business_name, $b_nickname);
    $result = $contacts->get_installer_by_id($installer_id);
    $business_name = $result['business_name'];
    $b_street_1 = $result['b_street_address_1'];
    $b_street_2 = $result['b_street_address_2'];
    $b_city = $result['b_city'];
    $b_state = $result['b_state'];
    $b_postcode = $result['b_postcode'];
    $b_country = $result['b_country'];
  } else {
    $business_name = htmlentities(trim($_POST['business_name']));
    $b_street_1 = htmlentities(trim($_POST['b_street_1']));
    $b_street_2 = htmlentities(trim($_POST['b_street_2']));
    $b_city = htmlentities(trim($_POST['b_city']));
    $b_state = htmlentities(trim($_POST['b_state']));
    $b_postcode = htmlentities(trim($_POST['b_postcode']));
    $b_country = htmlentities(trim($_POST['b_country']));
  }
  $m_street_1 = htmlentities(trim($_POST['m_street_1']));
  $m_street_2 = htmlentities(trim($_POST['m_street_2']));
  $m_city = htmlentities(trim($_POST['m_city']));
  $m_state = htmlentities(trim($_POST['m_state']));
  $m_postcode = htmlentities(trim($_POST['m_postcode']));
  $m_country = htmlentities(trim($_POST['m_country']));
  $sql = "INSERT INTO contacts (prop_id, ctype, call_order, first_name, family_name, full_name, cell_phone, business_phone, alt_phone, phones, personal_email, business_email, alt_email, emails, p_street_1, p_street_2, p_city, p_state, p_postcode, p_country, business_name, b_street_1, b_street_2, b_city, b_state, b_postcode, b_country, m_street_1, m_street_2, m_city, m_state, m_postcode, m_country) VALUES (:prop_id, :ctype, :call_order, :first_name, :family_name, :full_name, :cell_phone, :business_phone, :alt_phone, :phones, :personal_email, :business_email, :alt_email, :emails, :p_street_1, :p_street_2, :p_city, :p_state, :p_postcode, :p_country, :business_name, :b_street_1, :b_street_2, :b_city, :b_state, :b_postcode, :b_country, :m_street_1, :m_street_2, :m_city, :m_state, :m_postcode, :m_country)";
  $stmt = $dbcrm->prepare($sql);
  $stmt->bindParam(':ctype', $ctype, PDO::PARAM_INT);
  $stmt->bindParam(':call_order', $call_order, PDO::PARAM_INT);
  $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
  $stmt->bindParam(':family_name', $family_name, PDO::PARAM_STR);
  $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
  $stmt->bindParam(':cell_phone', $cell_phone, PDO::PARAM_STR);
  $stmt->bindParam(':business_phone', $business_phone, PDO::PARAM_STR);
  $stmt->bindParam(':alt_phone', $alt_phone, PDO::PARAM_STR);
  $stmt->bindParam(':phones', $phones, PDO::PARAM_STR);
  $stmt->bindParam(':personal_email', $personal_email, PDO::PARAM_STR);
  $stmt->bindParam(':business_email', $business_email, PDO::PARAM_STR);
  $stmt->bindParam(':alt_email', $alt_email, PDO::PARAM_STR);
  $stmt->bindParam(':emails', $emails, PDO::PARAM_STR);
  $stmt->bindParam(':p_street_1', $p_street_1, PDO::PARAM_STR);
  $stmt->bindParam(':p_street_2', $p_street_2, PDO::PARAM_STR);
  $stmt->bindParam(':p_city', $p_city, PDO::PARAM_STR);
  $stmt->bindParam(':p_state', $p_state, PDO::PARAM_STR);
  $stmt->bindParam(':p_postcode', $p_postcode, PDO::PARAM_STR);
  $stmt->bindParam(':p_country', $p_country, PDO::PARAM_STR);
  $stmt->bindParam(':business_name', $business_name, PDO::PARAM_STR);
  $stmt->bindParam(':b_street_1', $b_street_1, PDO::PARAM_STR);
  $stmt->bindParam(':b_street_2', $b_street_2, PDO::PARAM_STR);
  $stmt->bindParam(':b_city', $b_city, PDO::PARAM_STR);
  $stmt->bindParam(':b_state', $b_state, PDO::PARAM_STR);
  $stmt->bindParam(':b_postcode', $b_postcode, PDO::PARAM_STR);
  $stmt->bindParam(':b_country', $b_country, PDO::PARAM_STR);
  $stmt->bindParam(':m_street_1', $m_street_1, PDO::PARAM_STR);
  $stmt->bindParam(':m_street_2', $m_street_2, PDO::PARAM_STR);
  $stmt->bindParam(':m_city', $m_city, PDO::PARAM_STR);
  $stmt->bindParam(':m_state', $m_state, PDO::PARAM_STR);
  $stmt->bindParam(':m_postcode', $m_postcode, PDO::PARAM_STR);
  $stmt->bindParam(':m_country', $m_country, PDO::PARAM_STR);
  if ($stmt->execute()) {
    $stmt = null;
    header("location: list");
  } else {
    echo "Something went wrong. Please try again later.";
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['dir'] == 'contacts' && $_POST['page'] == 'edit') {
  $id = htmlentities(trim($_POST['id']));
  //$prop_id = htmlentities(trim($_POST['prop_id']));
  // $ctype = htmlentities(trim($_POST['ctype']));
  $call_order = htmlentities(trim($_POST['call_order']));
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
  $business_name = htmlentities(trim($_POST['business_name']));
  if ($business_name == 'AR') {
    $business_name = 'Accesible Renovations LLC';
    $b_street_1 = '5027 Bella Collina St';
    $b_street_2 = '';
    $b_city = 'Oceanside';
    $b_state = 'US-CA';
    $b_postcode = '92056-1924';
    $b_country = 'US';
  } elseif ($business_name == 'SEC') {
    $business_name = 'Shield Enterprise Co.';
    $b_street_1 = '326 Hemlock St';
    $b_street_2 = '';
    $b_city = 'Vacaville';
    $b_state = 'US-CA';
    $b_postcode = '95688';
    $b_country = 'US';
  } elseif ($business_name == 'WG') {
    $business_name = 'Waveguard Corporation';
    $b_street_1 = '7315 S Revere Pkwy';
    $b_street_2 = 'Ste. 602';
    $b_city = 'Centennial';
    $b_state = 'US-CO';
    $b_postcode = '80112';
    $b_country = 'US';
  } elseif ($business_name == 'PFM') {
    $business_name = 'Pacific Fire Management';
    $b_street_1 = '222 Ramona Ave';
    $b_street_2 = 'Unit 1';
    $b_city = ' Monterey';
    $b_state = 'US-CA';
    $b_postcode = '93940';
    $b_country = 'US';
  } elseif ($business_name == 'TPI') {
    $business_name = 'Tripoint Improvements';
    $b_street_1 = '7678 Sycamore Dr';
    $b_street_2 = '';
    $b_city = 'Citrus Heights';
    $b_state = 'US-CA';
    $b_postcode = '95610';
    $b_country = 'US';
  } elseif ($business_name == 'DLS') {
    $business_name = 'Dipallo, Lang, & Smith';
    $b_street_1 = '7315 S Revere Pkwy';
    $b_street_2 = 'Ste. 602';
    $b_city = 'Centennial';
    $b_state = 'US-CO';
    $b_postcode = '80112';
    $b_country = 'US';
  } else {
    $business_name = htmlentities(trim($_POST['business_name']));
    $b_street_1 = htmlentities(trim($_POST['b_street_1']));
    $b_street_2 = htmlentities(trim($_POST['b_street_2']));
    $b_city = htmlentities(trim($_POST['b_city']));
    $b_state = htmlentities(trim($_POST['b_state']));
    $b_postcode = htmlentities(trim($_POST['b_postcode']));
    $b_country = htmlentities(trim($_POST['b_country']));
  }
  $m_street_1 = htmlentities(trim($_POST['m_street_1']));
  $m_street_2 = htmlentities(trim($_POST['m_street_2']));
  $m_city = htmlentities(trim($_POST['m_city']));
  $m_state = htmlentities(trim($_POST['m_state']));
  $m_postcode = htmlentities(trim($_POST['m_postcode']));
  $m_country = htmlentities(trim($_POST['m_country']));
  //$sql = "UPDATE contacts SET prop_id = :prop_id, ctype = :ctype, call_order = :call_order, first_name = :first_name, family_name = :family_name, full_name = :full_name, cell_phone = :cell_phone, business_phone = :business_phone, alt_phone = :alt_phone, phones = :phones, personal_email = :personal_email, business_email = :business_email, alt_email = :alt_email, emails = :emails, p_street_1 = :p_street_1, p_street_2 = :p_street_2, p_city = :p_city, p_state = :p_state, p_postcode = :p_postcode, p_country = :p_country, business_name = :business_name, b_street_1 = :b_street_1, b_street_2 = :b_street_2, b_city = :b_city, b_state = :b_state, b_postcode = :b_postcode, b_country = :b_country, m_street_1 = :m_street_1, m_street_2 = :m_street_2, m_city = :m_city, m_state = :m_state, m_postcode = :m_postcode, m_country = :m_country WHERE id = :id";
    $sql = "UPDATE contacts SET cell_phone = :cell_phone, business_phone = :business_phone, alt_phone = :alt_phone, phones = :phones, personal_email = :personal_email, business_email = :business_email, alt_email = :alt_email, emails = :emails, p_street_1 = :p_street_1, p_street_2 = :p_street_2, p_city = :p_city, p_state = :p_state, p_postcode = :p_postcode, p_country = :p_country, business_name = :business_name, b_street_1 = :b_street_1, b_street_2 = :b_street_2, b_city = :b_city, b_state = :b_state, b_postcode = :b_postcode, b_country = :b_country, m_street_1 = :m_street_1, m_street_2 = :m_street_2, m_city = :m_city, m_state = :m_state, m_postcode = :m_postcode, m_country = :m_country WHERE id = :id";
  $stmt = $dbcrm->prepare($sql);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  //$stmt->bindParam(':prop_id', $prop_id, PDO::PARAM_INT);
  //$stmt->bindParam(':ctype', $ctype, PDO::PARAM_INT);
  // $stmt->bindParam(':call_order', $call_order, PDO::PARAM_INT);
  //$stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
  //$stmt->bindParam(':family_name', $family_name, PDO::PARAM_STR);
  //$stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
  $stmt->bindParam(':cell_phone', $cell_phone, PDO::PARAM_STR);
  $stmt->bindParam(':business_phone', $business_phone, PDO::PARAM_STR);
  $stmt->bindParam(':alt_phone', $alt_phone, PDO::PARAM_STR);
  $stmt->bindParam(':phones', $phones);
  $stmt->bindParam(':personal_email', $personal_email, PDO::PARAM_STR);
  $stmt->bindParam(':business_email', $business_email, PDO::PARAM_STR);
  $stmt->bindParam(':alt_email', $alt_email, PDO::PARAM_STR);
  $stmt->bindParam(':emails', $emails, PDO::PARAM_STR);
  $stmt->bindParam(':p_street_1', $p_street_1, PDO::PARAM_STR);
  $stmt->bindParam(':p_street_2', $p_street_2, PDO::PARAM_STR);
  $stmt->bindParam(':p_city', $p_city, PDO::PARAM_STR);
  $stmt->bindParam(':p_state', $p_state, PDO::PARAM_STR);
  $stmt->bindParam(':p_postcode', $p_postcode, PDO::PARAM_STR);
  $stmt->bindParam(':p_country', $p_country, PDO::PARAM_STR);
  $stmt->bindParam(':business_name', $business_name, PDO::PARAM_STR);
  $stmt->bindParam(':b_street_1', $b_street_1, PDO::PARAM_STR);
  $stmt->bindParam(':b_street_2', $b_street_2, PDO::PARAM_STR);
  $stmt->bindParam(':b_city', $b_city, PDO::PARAM_STR);
  $stmt->bindParam(':b_state', $b_state, PDO::PARAM_STR);
  $stmt->bindParam(':b_postcode', $b_postcode, PDO::PARAM_STR);
  $stmt->bindParam(':b_country', $b_country, PDO::PARAM_STR);
  $stmt->bindParam(':m_street_1', $m_street_1, PDO::PARAM_STR);
  $stmt->bindParam(':m_street_2', $m_street_2, PDO::PARAM_STR);
  $stmt->bindParam(':m_city', $m_city, PDO::PARAM_STR);
  $stmt->bindParam(':m_state', $m_state, PDO::PARAM_STR);
  $stmt->bindParam(':m_postcode', $m_postcode, PDO::PARAM_STR);
  $stmt->bindParam(':m_country', $m_country, PDO::PARAM_STR);
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
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
	$stmt->bindParam(':status', $status, PDO::PARAM_INT);
    if ($stmt->execute()) {
    $stmt = null;
    header("location: list");
  } else {
    echo "Something went wrong. Please try again later.";
  }
}
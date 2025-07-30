<?php
/** need to add trim and validation with error messages  */
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$users = new Users();
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['dir'] == 'users' && $_POST['page'] == 'new') {
  $rid = htmlentities(trim($_POST["rid"]));
  $prop_id = htmlentities(trim($_POST["prop_id"]));
  // $prop_id = str_replace(' ', '', $prop_id);
  // $prop_id = json_encode($prop_id, JSON_FORCE_OBJECT);
  $full_name = htmlentities(trim($_POST["full_name"]));
  $username = htmlentities(trim($_POST["username"]));
  $username = str_replace(' ', '', $username);
  $username = strtolower($username);
  $email = htmlentities(trim($_POST["email"]));
  $email = filter_var($email, FILTER_VALIDATE_EMAIL);
  $password = htmlentities(trim($_POST["password"]));
  $password = $helper->hash_password($password);
  $users->new($rid, $full_name, $username, $email, $password);
  $user_id = $users->last_row_id();
  $prop_result = $users->add_user_properties_by_id($user_id, $prop_id);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['dir'] == 'users' && $_POST['page'] == 'edit' && $_POST['form_name'] == 'user_profile') {
  $id = $_POST["id"];
  $full_name = htmlentities(trim($_POST["full_name"]));
  $password = trim($_POST["password"]);
  (int)$rid = htmlentities(trim($_POST["rid"]));
  $email = htmlentities(trim($_POST["email"]));
  $email = filter_var($email, FILTER_VALIDATE_EMAIL);
  print_r($_POST);
  $users->edit_profile($id, $full_name, $password, $rid, $email);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['dir'] == 'users' && $_POST['page'] == 'edit' && $_POST['form_name'] == 'add_prop_id') {

  $message = "";
  $location = "Location:" . URL . "/users/edit.php?id=" . $_POST["id"] . "&message=" . $message;
  $id = htmlentities($_POST["id"]);
  $new_prop_id = htmlentities(trim($_POST["new_prop_id"]));;
  $prop_result = $users->add_user_properties_by_id($id, $new_prop_id);
  /*
  $current_prop_ids_str = htmlentities(trim($_POST["current_prop_ids"]));
  $current_prop_ids_arr = explode(",", $current_prop_ids_str);
  $new_prop_id_str = $helper->clean_prop_id($new_prop_id);
  $new_prop_id_arr = explode(",", $new_prop_id_str);
  $valid_prop_id_arr = $properties->get_valid_prop_ids();
  if (count($new_prop_id_arr) >= "1" && in_array($new_prop_id, $valid_prop_id_arr)){
    $updated_prop_ids_arr = array_merge($current_prop_ids_arr, $new_prop_id_arr);
    $updated_prop_ids_arr = array_unique($updated_prop_ids_arr);
    $updated_prop_ids_arr = array_filter($updated_prop_ids_arr);
    $json_data = json_encode($updated_prop_ids_arr, JSON_FORCE_OBJECT);
    if (isset($json_data) && !is_null($json_data)){
      $users->add_prop_id($id, $json_data);
      $message = "Property ID added successfully.";
      header($location);
    } else {
      $message = "Property ID not added.";
      header($location);
    }
  }
  else {
    $message = "Property ID $value does not exist.";
    header($message);
  }
  */
  header($location);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['dir'] == 'users' && $_POST['page'] == 'delete') {
  $status = 0;
  $id = htmlentities(trim($_POST['id']));
  $users->delete($id, $status);;
  $users->delete_user_properties_by_id($id);
}

<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$users = new Users();

if ($dir == 'users' && $page == 'list') {
  $results = $users->get_all_active();
  $list = new UsersList($results, $lang);
  $list->create_table();
}

if ($dir == 'users' && $page == 'view') {
  $id = trim($_GET["id"]);
  $result = $users->get_by_id($id);
  $rname = $result["role"];
  $full_name = $result["full_name"];
  $username = $result["username"];
  $password = $result["password"];
  $email = $result["email"];
  $status = $result["status"];
  $updated_at = $result["updated_at"];
  $created_at = $result["created_at"];
}

if ($dir == 'users' && $page == 'edit') {
   if ($form == "profile"){
    $id = trim($_GET["id"]);
    $result = $users->get_by_id($id);
    $rid = $result["role_id"];
    $full_name = $result["full_name"];
    $username = $result["username"];
    $password = $result["password"];
    $email = $result["email"];
    $status = $result["status"];
  }
}

if ($dir == 'users' && $page == 'delete') {
$id = trim($_GET["id"]);
$result = $users->get_by_id($id);
$rid = $result["role"];
$full_name = $result["full_name"];
$username = $result["username"];
$password = $result["password"];
$email = $result["email"];
$status = $helper->get_status($result["status"]);
$updated_at = $result["updated_at"];
$created_at = $result["created_at"];
}
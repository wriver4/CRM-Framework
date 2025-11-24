<?php
require_once dirname(__DIR__) . '/config/system.php';

echo "=== LOGIN DEBUG ===\n";
echo "REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"] . "\n";
echo "POST login field: " . (isset($_POST["login"]) ? "SET" : "NOT SET") . "\n";
echo "POST username field: " . (isset($_POST["username"]) ? "SET: " . $_POST["username"] : "NOT SET") . "\n";
echo "POST password field: " . (isset($_POST["password"]) ? "SET" : "NOT SET") . "\n";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  echo "\n1. Checking form submission condition\n";
  
  if (!empty($_POST["login"])) {
    echo "   - login is not empty: YES\n";
  } else {
    echo "   - login is not empty: NO\n";
  }
  
  if ($_POST["username"] != '') {
    echo "   - username != '': YES (username='" . $_POST["username"] . "')\n";
  } else {
    echo "   - username != '': NO\n";
  }
  
  if ($_POST["password"] != '') {
    echo "   - password != '': YES\n";
  } else {
    echo "   - password != '': NO\n";
  }
  
  if ($_POST["username"] != 'system') {
    echo "   - username != 'system': YES\n";
  } else {
    echo "   - username != 'system': NO\n";
  }
  
  if (!empty($_POST["login"]) && $_POST["username"] != '' && $_POST["password"] != '' && $_POST["username"] != 'system') {
    echo "\n2. Condition passed! Proceeding with login...\n";
    
    $username = htmlentities(trim($_POST['username']));
    $password = trim($_POST['password']);
    $status = 1;
    
    echo "   - htmlentities(trim()) username: " . $username . "\n";
    echo "   - trim() password: " . $password . "\n";
    
    $sql = "SELECT * FROM users WHERE username= :username AND status= :status";
    $stmt = $dbcrm->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if (!$result) {
      echo "\n3. Query result: USER NOT FOUND\n";
    } else {
      echo "\n3. Query result: USER FOUND\n";
      echo "   - User ID: " . $result['id'] . "\n";
      echo "   - Username: " . $result['username'] . "\n";
      echo "   - Stored password hash: " . substr($result['password'], 0, 20) . "...\n";
      
      if ($helper->verify_password($password, $result['password'])) {
        echo "\n4. Password verification: SUCCESS\n";
        echo "   - Would set session and redirect to /\n";
      } else {
        echo "\n4. Password verification: FAILED\n";
        echo "   - Incoming password: " . $password . "\n";
        echo "   - Stored hash: " . $result['password'] . "\n";
      }
    }
  } else {
    echo "\n2. Condition FAILED - no login attempt\n";
  }
}
?>

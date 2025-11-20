<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

/**
 * isLoggedIn()
 * login()
 * logout()	
 * getUserInfo()
 * getColoumn($id, $column)    
 * listAllUsers()
 * getUserDetails()		
 * insertUser()     
 * updateUser()     
 * deleteUser()
 * makeUserInactive()
 * makeUserActive()
 * listLoggedIn() = using sessions
 */


class Users extends Database
{

	public function __construct()
	{
		parent::__construct($this->dbcrm());
	}

	public function loggedin()
	{
		// Use the modern Sessions class for authentication check
		if (!Sessions::isLoggedIn()) {
			// Try to send header redirect first (works if no output sent yet)
			if (!headers_sent()) {
				header("Location: /login.php");
				exit;
			} else {
				// If headers already sent, use JavaScript redirect
				echo '<script type="text/javascript">window.location.href="/login.php";</script>';
				echo '<noscript><meta http-equiv="refresh" content="0;url=/login.php" /></noscript>';
				exit;
			}
		}
	}

	public function logout()
	{
		// Use the modern Sessions class for clean logout
		Sessions::destroyClean();
		
		// Handle redirect properly
		if (!headers_sent()) {
			header('Location: /');
			exit;
		} else {
			// If headers already sent, use JavaScript redirect
			echo '<script type="text/javascript">window.location.href="/";</script>';
			echo '<noscript><meta http-equiv="refresh" content="0;url=/" /></noscript>';
			exit;
		}
	}

	public function new($rid, $full_name, $username, $email, $password)
	{
		$sql = "INSERT INTO users (role_id, full_name, username, email, password  ) VALUES (:role_id, :full_name, :username, :email, :password)";
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindValue(':role_id', $rid, PDO::PARAM_INT);
		$stmt->bindValue(':full_name', $full_name, PDO::PARAM_STR);
		$stmt->bindValue(':username', $username, PDO::PARAM_STR);
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		$stmt->bindValue(':password', $password, PDO::PARAM_STR);
		try {
			$stmt->execute();
			$user_id = $this->last_row_id();
			$this->addUserRole($user_id, $rid, true);
			$stmt = null;
			header("location: list");
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function edit_profile($id, $full_name, $password, $rid, $email, $language_id = null)
	{
		
		$helper = new Helpers();
		$sql = "UPDATE `users` SET `full_name` = :full_name, ";
		if (!$helper->is_password($password)) {
			$sql .= "`password` = :password, ";
		}
		$sql .= "role_id = :role_id, ";
		if (strlen($email) > 0) {
			$sql .= "`email` = :email, ";
		}
		if ($language_id !== null) {
			$sql .= "`language_id` = :language_id ";
		} else {
			$sql = rtrim($sql, ', ') . ' ';
		}
		$sql .= "WHERE `id` = :id";
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindValue(':id', $id);
		$stmt->bindValue(':full_name', $full_name);
		if (!$helper->is_password($password)) {
			$password = $helper->hash_password($password);
			$stmt->bindValue(':password', $password);
		}
		$stmt->bindValue(':role_id', $rid);
		if (strlen($email) > 0) {
			$stmt->bindValue(':email', $email);
		}
		if ($language_id !== null) {
			$stmt->bindValue(':language_id', $language_id, PDO::PARAM_INT);
		}
		if ($stmt->execute()) {
			$this->setUserPrimaryRole($id, $rid);
			$stmt = null;
			header("location: list");
		} else {
			echo "Something went wrong. Please try again later.";
		}
	}

	/**
	 * Update user's language preference
	 * @param int $userId
	 * @param int $languageId
	 * @return bool
	 */
	public function updateLanguagePreference($userId, $languageId)
	{
		$sql = "UPDATE users SET language_id = :language_id WHERE id = :user_id";
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindValue(':language_id', $languageId, PDO::PARAM_INT);
		$stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
		return $stmt->execute();
	}

	public function delete($id, $status)
	{
		$sql = "UPDATE users SET status = :status WHERE id = :id";
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->bindValue(':status', $status, PDO::PARAM_INT);
		if ($stmt->execute()) {
			$stmt = null;
			header("location: list");
		} else {
			echo "Something went wrong. Please try again later.";
		}
	}


	public function get_by_id($id)
	{
		$sql = 'SELECT u.*, r.role from users u LEFT JOIN roles r ON u.role_id = r.role_id WHERE u.id = :id';
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		return $result;
	}

	public function get_all()
	{
		$sql = 'SELECT u.id, u.full_name, u.name, u.username, r.role, u.status from users u LEFT JOIN roles r ON u.role_id = r.role_id';
		$sql .= " ORDER BY u.full_name ASC";
		$stmt = $this->dbcrm()->query($sql);
		$stmt->execute();
		$results = $stmt->fetchAll();
		return $results;
	}

	public function get_all_active()
	{
		$sql = 'SELECT u.id, u.full_name, u.username, r.role from users u LEFT JOIN roles r ON u.role_id = r.role_id WHERE u.status = 1 ORDER BY u.full_name ASC';
		$stmt = $this->dbcrm()->query($sql);
		$stmt->execute();
		$results = $stmt->fetchAll();
		return $results;
	}

	/** For new user password	 */
	public function last_row_id()
	{
		$sql = "SELECT MAX(id) AS last_id from users";
		$stmt = $this->dbcrm()->query($sql);
		$result = $stmt->fetch();
		return $result['last_id'];
	}

	/** Get user name by ID for edited_by display */
	public function get_name_by_id($id)
	{
		if (empty($id)) {
			return null;
		}
		$sql = 'SELECT full_name, username FROM users WHERE id = :id';
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		if ($result) {
			return !empty($result['full_name']) ? $result['full_name'] : $result['username'];
		}
		return null;
	}

	/* On login page for now
	public function login()
	{
		
		$errorMessage = '';
		if (!empty($_POST["login"]) && $_POST["username"] != '' && $_POST["password"] != '') {
			$username = trim($_POST['username']);
			$password = trim($_POST['password']);
			$status = 1;
			$sql = "SELECT * FROM users WHERE username = :username AND status = :status";
			$stmt = $this->dbcrm()->prepare($sql);
			$stmt->bindValue(':username', $username);
			$stmt->bindValue(':status', $status);
			$stmt->execute();
			$user = $stmt->fetch();
			$verify = verify_password($password, $user['password']);
			if ($stmt->rowCount() > 0 && $verify) {
				$_SESSION['user_id'] = $user['id'];
				$_SESSION['username'] = $user['username'];
				$_SESSION['role'] = $user['role'];
				$_SESSION['loggedin'] = true;
				$_SESSION["login_time_stamp"] = time();
				header('Location: /');
			} else {
				$errorMessage = 'Invalid username or password';
			}
		} else if (!empty($_POST["login"])) {
			$errorMessage = "Enter Both user and password!";
		}
		return $errorMessage;
		
	}
	*/

	/*  future use
	public function get_all_encoded()
	{
		$sql = 'SELECT u.id, u.full_name, u.username, r.role, u.prop_id, u.status from users u LEFT JOIN roles r ON u.role_id = r.role_id';
		$sql .= " ORDER BY u.full_name ASC";
		$stmt = $this->dbcrm()->query($sql);
		$stmt->execute();
		$results = $stmt->fetchAll();
		if ($stmt->rowCount() > 0) {
			$users = array();
			foreach ($results as $result) {
				$users[] = array(
					'id' => $result['id'],
					'full_name' => $result['full_name'],
					'username' => $result['username'],
					'role' => $result['role'],
					'status' => $result['status']
				);
			}
			return json_encode($users);
		} else {
			return false;
		}
	}
	
	public function listLoggedInUsers()
	{
	}
	*/

	public function addUserRole($user_id, $role_id, $is_primary = false)
	{
		$sql = "INSERT INTO user_roles (user_id, role_id, is_primary, assigned_at) 
				SELECT :user_id, r.id, :is_primary, NOW() FROM roles r 
				WHERE r.role_id = :role_id
				ON DUPLICATE KEY UPDATE is_active = TRUE, is_primary = :is_primary";
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
		$stmt->bindValue(':role_id', $role_id, PDO::PARAM_INT);
		$stmt->bindValue(':is_primary', $is_primary ? 1 : 0, PDO::PARAM_INT);
		return $stmt->execute();
	}

	public function removeUserRole($user_id, $role_id)
	{
		$sql = "DELETE FROM user_roles WHERE user_id = :user_id AND role_id = (
					SELECT id FROM roles WHERE role_id = :role_id
				)";
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
		$stmt->bindValue(':role_id', $role_id, PDO::PARAM_INT);
		return $stmt->execute();
	}

	public function getUserRoles($user_id)
	{
		$sql = "SELECT r.role_id, r.role, ur.is_primary, ur.is_active, ur.assigned_at 
				FROM user_roles ur 
				LEFT JOIN roles r ON ur.role_id = r.id 
				WHERE ur.user_id = :user_id AND ur.is_active = TRUE 
				ORDER BY ur.is_primary DESC, ur.assigned_at ASC";
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function getUserPrimaryRole($user_id)
	{
		$sql = "SELECT r.role_id FROM user_roles ur 
				INNER JOIN roles r ON ur.role_id = r.id 
				WHERE ur.user_id = :user_id AND ur.is_primary = TRUE AND ur.is_active = TRUE 
				LIMIT 1";
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		return $result ? $result['role_id'] : null;
	}

	public function setUserPrimaryRole($user_id, $role_id)
	{
		$sql_clear = "UPDATE user_roles SET is_primary = FALSE 
					  WHERE user_id = :user_id";
		$stmt_clear = $this->dbcrm()->prepare($sql_clear);
		$stmt_clear->bindValue(':user_id', $user_id, PDO::PARAM_INT);
		$stmt_clear->execute();

		$sql_set = "UPDATE user_roles SET is_primary = TRUE 
					WHERE user_id = :user_id AND role_id = (
						SELECT id FROM roles WHERE role_id = :role_id
					)";
		$stmt_set = $this->dbcrm()->prepare($sql_set);
		$stmt_set->bindValue(':user_id', $user_id, PDO::PARAM_INT);
		$stmt_set->bindValue(':role_id', $role_id, PDO::PARAM_INT);
		return $stmt_set->execute();
	}

	public function getUserRolesAsJson($user_id)
	{
		$roles = $this->getUserRoles($user_id);
		return json_encode($roles);
	}
}
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
		if (!isset($_SESSION) || !isset($_SESSION['loggedin'])) {
			header("Location: /login");
		}
	}

	public function logout()
	{
		// set user loggedin in database
		session_destroy();
		header('Location: /');
	}

	public function new($rid, $full_name, $username, $email, $password)
	{
		$sql = "INSERT INTO users (rid, full_name, username, email, password  ) VALUES (:rid, :full_name, :username, :email, :password)";
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindParam(':rid', $rid, PDO::PARAM_INT);
		$stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$stmt->bindParam(':email', $email, PDO::PARAM_STR);
		$stmt->bindParam(':password', $password, PDO::PARAM_STR);
		try {
			$stmt->execute();
			$stmt = null;
			header("location: list");
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function edit_profile($id, $full_name, $password, $rid, $email)
	{
		
		$helper = new Helpers();
		$sql = "UPDATE `users` SET `full_name` = :full_name, ";
		if (!$helper->is_password($password)) {
			$sql .= "`password` = :password, ";
		}
		$sql .= "rid = :rid, ";
		if (strlen($email) > 0) {
			$sql .= "`email` = :email ";
		}
		$sql .= "WHERE `id` = :id";
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':full_name', $full_name);
		if (!$helper->is_password($password)) {
			$password = $helper->hash_password($password);
			$stmt->bindParam(':password', $password);
		}
		$stmt->bindParam(':rid', $rid);
		if (strlen($email) > 0) {
			$stmt->bindParam(':email', $email);
		}
		if ($stmt->execute()) {
			header("location: list");
		} else {
			echo "Something went wrong. Please try again later.";
		}
		$stmt = null;
	}

	public function delete($id, $status)
	{
		$sql = "UPDATE users SET status = :status WHERE id = :id";
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->bindParam(':status', $status, PDO::PARAM_INT);
		if ($stmt->execute()) {
			$stmt = null;
			header("location: list");
		} else {
			echo "Something went wrong. Please try again later.";
		}
	}


	public function get_by_id($id)
	{
		$sql = 'SELECT u.*, r.rname from users u LEFT JOIN roles r ON u.rid = r.rid WHERE u.id = :id';
		$stmt = $this->dbcrm()->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		return $result;
	}

	public function get_all()
	{
		$sql = 'SELECT u.id, u.full_name, u.name, u.username, r.rname, u.status from users u LEFT JOIN roles r ON u.rid = r.rid';
		$sql .= " ORDER BY u.full_name ASC";
		$stmt = $this->dbcrm()->query($sql);
		$stmt->execute();
		$results = $stmt->fetchAll();
		return $results;
	}

	public function get_all_active()
	{
		$sql = 'SELECT u.id, u.full_name, u.username, r.rname from users u LEFT JOIN roles r ON u.rid = r.rid WHERE u.status = 1 ORDER BY u.full_name ASC';
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
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
			$stmt->bindParam(':username', $username);
			$stmt->bindParam(':status', $status);
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
		$sql = 'SELECT u.id, u.full_name, u.username, r.rname, u.prop_id, u.status from users u LEFT JOIN roles r ON u.rid = r.rid';
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
					'rname' => $result['rname'],
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
}
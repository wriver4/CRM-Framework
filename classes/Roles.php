<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class Roles extends Database
{

  /**
   * Table roles
   * id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY
   * rid int(11) NOT NULL UNIQUE KEY
   * rname varchar(15) NOT NULL UNIQUE KEY
   * updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   * created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
   * 
   * todo: restrict role to equal to or less than current user's role
   * also to group by manager
   * create role update routine
   *
   */

  public function __construct()
  {
    parent::__construct();
  }

  public function get_all()
  {
    $sql = "SELECT id, rid, rname FROM roles";
    $stmt = $this->dbadmin()->query($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
    return $results;
  }

  public function get_role_name($rid)
  {
    $sql = "SELECT * FROM roles where id = ?";
    $stmt = $this->dbadmin()->prepare($sql);
    $stmt->execute([$rid]);
    $role = $stmt->fetch();
    $role_name = $role['rname'];
    return $role_name;
  }
  public function get_role_names()
  {
    $sql = "SELECT * FROM roles ORDER BY rid DESC";
    $stmt = $this->dbadmin()->prepare($sql);
    $stmt->execute();
    $roles = $stmt->fetchAll();
    foreach ($roles as $role) {
      echo '<option value="' . $role['id'] . '">' . $role['rname'] . '</option>';
    }
  }

  public function set_role_name($rid)
  {
    $sql = "SELECT * FROM roles where id = ?";
    $stmt = $this->dbadmin->prepare($sql);
    $stmt->execute([$rid]);
    $stmt->fetch();
    echo '<option value="' . $rid . '">' . $this->get_role_name($this->dbadmin(), $rid) . '</option>';
    $this->get_role_names($this->dbadmin());
  }

  public function update_role_array($lang)
  {
  }

  public function get_role_array($lang)
  {
    $role_array = [
      '21' => $lang['role_id_21'],
      '20' => $lang['role_id_20'],
      '19' => $lang['role_id_19'],
      '18' => $lang['role_id_18'],
      '17' => $lang['role_id_17'],
      '16' => $lang['role_id_16'],
      '15' => $lang['role_id_15'],
      '14' => $lang['role_id_14'],
      '13' => $lang['role_id_13'],
      '12' => $lang['role_id_12'],
      '11' => $lang['role_id_11'],
      '10' => $lang['role_id_10'],
      '9' => $lang['role_id_9'],
      '8' => $lang['role_id_8'],
      '7' => $lang['role_id_7'],
      '6' => $lang['role_id_6'],
      '5' => $lang['role_id_5'],
      '4' => $lang['role_id_4'],
      '3' => $lang['role_id_3'],
      '2' => $lang['role_id_2'],
      '1' => $lang['role_id_1'],
      '22' => $lang['role_id_22'],
    ];
    return $role_array;
  }

  public function select_role($lang, $rid = null)
  {
    $roles = $this->get_role_array($lang);
    foreach ($roles as $key => $value) {
      if ($key != 22) {
        echo '<option value="'
          . $key
          . '"'
          . ($rid == $key ? ' selected="selected">' : '>')
          . $value
          . '</option>';
      }
    }
  }
}

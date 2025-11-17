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
   * role_id int(11) NOT NULL UNIQUE KEY
   * role varchar(15) NOT NULL UNIQUE KEY
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
    $sql = "SELECT id, role_id, role FROM roles";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
    return $results;
  }

  public function get_role_name($rid)
  {
    $sql = "SELECT * FROM roles where id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$rid]);
    $role = $stmt->fetch();
    $role_name = $role['role'];
    return $role_name;
  }
  public function get_role_names()
  {
    $sql = "SELECT * FROM roles ORDER BY role_id DESC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute();
    $roles = $stmt->fetchAll();
    foreach ($roles as $role) {
      echo '<option value="' . $role['id'] . '">' . $role['role'] . '</option>';
    }
  }

  public function set_role_name($rid)
  {
    $sql = "SELECT * FROM roles where id = ?";
    $stmt = $this->dbcrm->prepare($sql);
    $stmt->execute([$rid]);
    $stmt->fetch();
    echo '<option value="' . $rid . '">' . $this->get_role_name($this->dbcrm(), $rid) . '</option>';
    $this->get_role_names($this->dbcrm());
  }

  public function update_role_array($lang)
  {
  }

  public function get_role_array($lang)
  {
    $role_ids = [
      '1',
      '2',
      '10',
      '11',
      '12',
      '13',
      '14',
      '30',
      '35',
      '40',
      '41',
      '42',
      '43',
      '50',
      '51',
      '52',
      '60',
      '70',
      '72',
      '80',
      '82',
      '90',
      '100',
      '110',
      '120',
      '130',
      '140',
      '150',
      '160',
      '161',
      '162',
      '163',
    ];

    $role_array = [];

    foreach ($role_ids as $role_id) {
      $role_array[$role_id] = $lang['role_id_' . $role_id];
    }

    return $role_array;
  }

  public function select_role($lang, $rid = null)
  {
    $roles = $this->get_role_array($lang);
    $exclude_roles = [1, 2];

    foreach ($roles as $key => $value) {
      if (in_array($key, $exclude_roles, true)) {
        continue;
      }

      echo '<option value="'
        . $key
        . '"'
        . ($rid == $key ? ' selected="selected">' : '>')
        . $value
        . '</option>';
    }
  }
}
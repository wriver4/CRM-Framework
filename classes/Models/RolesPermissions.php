<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class RolesPermissions extends Database
{
  /**
   * Table roles_permissions
   * role_id int(11) NOT NULL PRIMARY KEY
   * pid int(11) NOT NULL PRIMARY KEY
   * updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   * created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
   */

  public function __construct()
  {
    parent::__construct();
  }

  public function get_session_roles_permissions($role_id)
  {
    $session_roles_permissions = [];
    $sql = "SELECT pid FROM roles_permissions where role_id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    $role_permissions = $stmt->fetchAll();
    array_walk($role_permissions, function ($value) use (&$session_roles_permissions) {
      $session_roles_permissions[] = $value['pid'];
    });
    return $session_roles_permissions;
  }

  /**
   * Get all role permissions
   * @return array
   */

  public function get_all()
  {
    $sql = "SELECT * FROM roles_permissions";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
    return $results;
  }

  /**
   * Get all permissions for a role
   * @param  int $role_id
   * @return array
   */
  public function get_roles_permissions($role_id)
  {
    $sql = "SELECT pid FROM roles_permissions where role_id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    $role_permissions = $stmt->fetchAll();
    return $role_permissions;
  }
}

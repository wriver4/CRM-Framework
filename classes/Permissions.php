<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class Permissions extends Database
{
  /*
  * Table permissions
  * id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY
  * pid int(11) NOT NULL UNIQUE KEY
  * pobject varchar(15) NOT NULL KEY
  * pdescription varchar(100) NOT NULL
  * updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  * created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
  */

  public function __construct()
  {
    parent::__construct();
  }

  public function get_all()
  {
    $sql = "SELECT * FROM permissions";
    $stmt = $this->dbadmin()->query($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
    return $results;
  }
}

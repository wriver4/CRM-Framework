<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class Contacts extends Database
{

  public function __construct()
  {
    parent::__construct();
  }

  public function get_active_list()
  {
    $sql = 'SELECT id, ctype, fullname, phones, call_order, emails from contacts WHERE status = 1';
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
    return $results;
  }

  public function get_list()
  {
    $sql = 'SELECT id, ctype, fullname, phones, emails from contacts';
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
    return $results;
  }

  public function get_by_id($id)
  {
    $sql = 'SELECT * from contacts where id = :id';
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result;
  }
  
  public function get_primary_contact_by_prop_id($prop_id)
  {
    $sql = 'SELECT * from contacts where prop_id = :prop_id';
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->bindParam(':prop_id', $prop_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result;
  }

  public function get_installer_nickname()
  {
    $sql = "SELECT b_nickname from installers";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    return $result;
  }

  public function get_installer_by_id($id)
  {
    $sql = 'SELECT * from installers where id = :id';
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result;
  }
}
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
    $sql = 'SELECT id, ctype, fullname, phones, emails from contacts WHERE status = 1';
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
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result;
  }
  
  public function get_primary_contact_by_prop_id($prop_id)
  {
    $sql = 'SELECT * from contacts where prop_id = :prop_id';
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->bindValue(':prop_id', $prop_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result;
  }

  public function get_installer_nickname()
  {
    // Installer functionality removed - return empty array to prevent errors
    return [];
  }

  public function get_installer_by_id($id)
  {
    // Installer functionality removed - return empty array to prevent errors
    return [];
  }

  public function get_contacts_by_lead_id($lead_id)
  {
    // Use junction table to get contacts associated with a lead
    $sql = 'SELECT c.* FROM contacts c 
            INNER JOIN leads_contacts lc ON c.id = lc.contact_id 
            WHERE lc.lead_id = :lead_id AND c.status = 1 AND lc.status = 1 
            ORDER BY lc.relationship_type DESC, c.id ASC';
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->bindValue(':lead_id', (int)$lead_id, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();
    return $results;
  }
}
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

  public function get_contacts_by_lead_id($lead_number)
  {
    // Use junction table to get contacts associated with a lead
    // Note: lead_number is the external lead ID (e.g., 1318), not the internal table ID
    $sql = 'SELECT c.*, lc.relationship_type, c.fullname as full_name 
            FROM contacts c 
            INNER JOIN leads_contacts lc ON c.id = lc.contact_id 
            INNER JOIN leads l ON lc.lead_id = l.id
            WHERE l.lead_id = :lead_number AND c.status = 1 AND lc.status = 1 
            ORDER BY lc.relationship_type DESC, c.id ASC';
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->bindValue(':lead_number', (int)$lead_number, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();
    return $results;
  }
}
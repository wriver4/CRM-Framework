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
    $role_array = [
      // System Maintenance (1-2)
      '1' => $lang['role_id_1'] ?? 'Super Admin',
      '2' => $lang['role_id_2'] ?? 'Admin',
      
      // Executive (10-19)
      '10' => $lang['role_id_10'] ?? 'President',
      '11' => $lang['role_id_11'] ?? 'CTO',
      '12' => $lang['role_id_12'] ?? 'CFO',
      '13' => $lang['role_id_13'] ?? 'COO',
      '14' => $lang['role_id_14'] ?? 'VP Operations',
      '15' => $lang['role_id_15'] ?? 'VP Sales',
      '16' => $lang['role_id_16'] ?? 'VP Engineering',
      '17' => $lang['role_id_17'] ?? 'VP Administration',
      '18' => $lang['role_id_18'] ?? 'VP Manufacturing',
      '19' => $lang['role_id_19'] ?? 'VP Field Operations',
      
      // Internal Sales (20-29)
      '20' => $lang['role_id_20'] ?? 'Sales Manager',
      '21' => $lang['role_id_21'] ?? 'Partner Manager',
      '22' => $lang['role_id_22'] ?? 'Sales Lead',
      '23' => $lang['role_id_23'] ?? 'Sales Lead 2',
      '25' => $lang['role_id_25'] ?? 'Sales User',
      '26' => $lang['role_id_26'] ?? 'Partner Sales',
      
      // Engineering (30-39)
      '30' => $lang['role_id_30'] ?? 'Engineering Manager',
      '31' => $lang['role_id_31'] ?? 'Tech Lead',
      '32' => $lang['role_id_32'] ?? 'Technician 1',
      '33' => $lang['role_id_33'] ?? 'Technician 2',
      '34' => $lang['role_id_34'] ?? 'Translator',
      
      // Manufacturing (40-49)
      '40' => $lang['role_id_40'] ?? 'Manufacturing Manager',
      '41' => $lang['role_id_41'] ?? 'Production Lead',
      '42' => $lang['role_id_42'] ?? 'Quality Lead',
      '43' => $lang['role_id_43'] ?? 'Production Tech',
      '44' => $lang['role_id_44'] ?? 'Quality Tech',
      '47' => $lang['role_id_47'] ?? 'Installer',
      
      // Field Operations (50-59)
      '50' => $lang['role_id_50'] ?? 'Field Manager',
      '51' => $lang['role_id_51'] ?? 'Service Lead',
      '52' => $lang['role_id_52'] ?? 'Field Technician',
      '53' => $lang['role_id_53'] ?? 'Installer Lead',
      '54' => $lang['role_id_54'] ?? 'Field Installer',
      
      // Administration (60-69)
      '60' => $lang['role_id_60'] ?? 'HR Manager',
      '61' => $lang['role_id_61'] ?? 'Compliance Manager',
      '62' => $lang['role_id_62'] ?? 'Office Manager',
      '63' => $lang['role_id_63'] ?? 'HR Specialist',
      '64' => $lang['role_id_64'] ?? 'Compliance Officer',
      
      // Finance (70-79)
      '70' => $lang['role_id_70'] ?? 'Accounting Manager',
      '71' => $lang['role_id_71'] ?? 'Bookkeeper',
      '72' => $lang['role_id_72'] ?? 'AP/AR Clerk',
      '73' => $lang['role_id_73'] ?? 'Accountant',
      '74' => $lang['role_id_74'] ?? 'Finance Analyst',
      '75' => $lang['role_id_75'] ?? 'Auditor',
      
      // Support (80-89)
      '80' => $lang['role_id_80'] ?? 'Translator',
      '81' => $lang['role_id_81'] ?? 'Technical Writer',
      '82' => $lang['role_id_82'] ?? 'Training Specialist',
      '83' => $lang['role_id_83'] ?? 'Support Manager',
      '84' => $lang['role_id_84'] ?? 'Support Agent',
      '85' => $lang['role_id_85'] ?? 'QA Specialist',
      
      // External Partners (90-99)
      '90' => $lang['role_id_90'] ?? 'Vendor',
      '91' => $lang['role_id_91'] ?? 'Strategic Partner',
      '92' => $lang['role_id_92'] ?? 'Contractor',
      '93' => $lang['role_id_93'] ?? 'Guest',
      '99' => $lang['role_id_99'] ?? 'Viewer',
      
      // External Sales Partners (141-143)
      '141' => $lang['role_id_141'] ?? 'Distributor',
      '142' => $lang['role_id_142'] ?? 'Installer',
      '143' => $lang['role_id_143'] ?? 'Applicator',
      
      // Clients (150)
      '150' => $lang['role_id_150'] ?? 'Client',
    ];
    return $role_array;
  }

  public function select_role($lang, $rid = null)
  {
    $roles = $this->get_role_array($lang);
    // Filter out system roles (1-9) and reserved roles for user selection
    $exclude_roles = [1, 2, 3, 4, 5, 6, 7, 8, 9]; // System maintenance roles
    
    foreach ($roles as $key => $value) {
      if (!in_array($key, $exclude_roles)) {
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
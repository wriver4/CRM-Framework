<?php

class RoleHierarchy extends Database
{

  public function __construct()
  {
    parent::__construct();
  }

  public function get_all()
  {
    $sql = "SELECT rh.*, 
            pr.role as parent_role_name,
            cr.role as child_role_name
            FROM role_inheritance rh
            LEFT JOIN roles pr ON rh.parent_role_id = pr.id
            LEFT JOIN roles cr ON rh.child_role_id = cr.id
            ORDER BY rh.depth ASC, pr.role ASC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_by_id($id)
  {
    $sql = "SELECT rh.*, 
            pr.role as parent_role_name,
            cr.role as child_role_name
            FROM role_inheritance rh
            LEFT JOIN roles pr ON rh.parent_role_id = pr.id
            LEFT JOIN roles cr ON rh.child_role_id = cr.id
            WHERE rh.id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public function get_ancestors($role_id)
  {
    $sql = "WITH RECURSIVE ancestors AS (
              SELECT id, parent_role_id, role, hierarchy_level, 0 as depth
              FROM roles
              WHERE id = ?
              UNION
              SELECT r.id, r.parent_role_id, r.role, r.hierarchy_level, ancestors.depth + 1
              FROM roles r
              JOIN ancestors ON r.id = ancestors.parent_role_id
              WHERE r.parent_role_id IS NOT NULL
            )
            SELECT * FROM ancestors WHERE id != ?
            ORDER BY depth ASC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id, $role_id]);
    return $stmt->fetchAll();
  }

  public function get_descendants($role_id)
  {
    $sql = "WITH RECURSIVE descendants AS (
              SELECT id, parent_role_id, role, hierarchy_level, 0 as depth
              FROM roles
              WHERE parent_role_id = ?
              UNION
              SELECT r.id, r.parent_role_id, r.role, r.hierarchy_level, descendants.depth + 1
              FROM roles r
              JOIN descendants ON r.parent_role_id = descendants.id
            )
            SELECT * FROM descendants
            ORDER BY depth ASC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    return $stmt->fetchAll();
  }

  public function get_direct_children($role_id)
  {
    $sql = "SELECT * FROM roles WHERE parent_role_id = ? ORDER BY role ASC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    return $stmt->fetchAll();
  }

  public function get_hierarchy_tree()
  {
    $sql = "SELECT rh.*, 
            pr.role as parent_role_name,
            cr.role as child_role_name
            FROM role_inheritance rh
            LEFT JOIN roles pr ON rh.parent_role_id = pr.id
            LEFT JOIN roles cr ON rh.child_role_id = cr.id
            WHERE rh.is_active = 1
            ORDER BY rh.depth ASC, pr.role ASC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_hierarchy_level($role_id)
  {
    $sql = "SELECT hierarchy_level FROM roles WHERE id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    $result = $stmt->fetch();
    return $result['hierarchy_level'] ?? 0;
  }

  public function check_circular_hierarchy($parent_id, $child_id)
  {
    $descendants = $this->get_descendants($child_id);
    foreach ($descendants as $desc) {
      if ($desc['id'] == $parent_id) {
        return true;
      }
    }
    return false;
  }

  public function add_inheritance_relationship($parent_id, $child_id, $inheritance_type = 'full')
  {
    if ($this->check_circular_hierarchy($parent_id, $child_id)) {
      return false;
    }
    
    $sql = "INSERT INTO role_inheritance 
            (parent_role_id, child_role_id, inheritance_type, depth, is_active)
            VALUES (?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE 
            inheritance_type = VALUES(inheritance_type), 
            is_active = 1";
    
    $depth = $this->calculate_depth($parent_id, $child_id);
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([$parent_id, $child_id, $inheritance_type, $depth]);
  }

  public function calculate_depth($parent_id, $child_id)
  {
    $ancestors = $this->get_ancestors($child_id);
    $count = count($ancestors) + 1;
    return $count;
  }

  public function update_inheritance_type($parent_id, $child_id, $inheritance_type)
  {
    $sql = "UPDATE role_inheritance 
            SET inheritance_type = ?
            WHERE parent_role_id = ? AND child_role_id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([$inheritance_type, $parent_id, $child_id]);
  }

  public function remove_inheritance_relationship($parent_id, $child_id)
  {
    $sql = "UPDATE role_inheritance 
            SET is_active = 0
            WHERE parent_role_id = ? AND child_role_id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([$parent_id, $child_id]);
  }

  public function get_inherited_permissions($role_id)
  {
    $sql = "SELECT DISTINCT rpi.*, p.pdescription as permission_name
            FROM role_permission_inheritance rpi
            LEFT JOIN permissions p ON rpi.permission_id = p.id
            WHERE rpi.role_id = ? AND rpi.inheritance_method = 'inherited'
            ORDER BY p.pdescription ASC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    return $stmt->fetchAll();
  }

  public function get_direct_permissions($role_id)
  {
    $sql = "SELECT DISTINCT rpi.*, p.pdescription as permission_name
            FROM role_permission_inheritance rpi
            LEFT JOIN permissions p ON rpi.permission_id = p.id
            WHERE rpi.role_id = ? AND rpi.inheritance_method = 'direct'
            ORDER BY p.pdescription ASC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    return $stmt->fetchAll();
  }

  public function get_all_effective_permissions($role_id)
  {
    $sql = "SELECT DISTINCT rpi.*, p.pdescription as permission_name, p.pobject as permission_object
            FROM role_permission_inheritance rpi
            LEFT JOIN permissions p ON rpi.permission_id = p.id
            WHERE rpi.role_id = ? AND rpi.is_active = 1
            ORDER BY p.pobject ASC, p.pdescription ASC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    return $stmt->fetchAll();
  }

  public function get_role_hierarchy_depth_analysis()
  {
    $sql = "SELECT depth, COUNT(*) as relationship_count, 
            AVG(depth) as avg_depth,
            MIN(depth) as min_depth,
            MAX(depth) as max_depth
            FROM role_inheritance
            WHERE is_active = 1
            GROUP BY depth
            ORDER BY depth ASC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_role_coverage($role_id)
  {
    $sql = "SELECT COUNT(DISTINCT rpi.permission_id) as total_permissions,
            SUM(CASE WHEN rpi.inheritance_method = 'direct' THEN 1 ELSE 0 END) as direct_permissions,
            SUM(CASE WHEN rpi.inheritance_method = 'inherited' THEN 1 ELSE 0 END) as inherited_permissions,
            SUM(CASE WHEN rpi.inheritance_method = 'delegated' THEN 1 ELSE 0 END) as delegated_permissions
            FROM role_permission_inheritance rpi
            WHERE rpi.role_id = ? AND rpi.is_active = 1";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    return $stmt->fetch();
  }

  public function delete_inheritance_relationship($id)
  {
    $sql = "DELETE FROM role_inheritance WHERE id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([$id]);
  }
}

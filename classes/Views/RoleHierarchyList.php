<?php

class RoleHierarchyList extends EditDeleteTable
{

  public function __construct($results, $lang)
  {

    parent::__construct($results, $this->column_names, "role-hierarchy-list");
    $this->column_names = [
      'action' => $lang['action'],
      'parent_role_name' => $lang['parent_role'],
      'child_role_name' => $lang['child_role'],
      'inheritance_type' => $lang['inheritance_type'],
      'depth' => $lang['depth'],
    ];
  }

  public function table_row_columns($results)
  {
    foreach ($results as $key => $value) {
      switch ($key) {
        case 'id':
          echo '<td>';
          $this->row_nav($value, $this->row_id);
          echo '</td>';
          break;
        case 'inheritance_type':
          echo '<td>';
          $type_class = 'badge-secondary';
          if ($value === 'full') $type_class = 'badge-success';
          elseif ($value === 'partial') $type_class = 'badge-warning';
          elseif ($value === 'none') $type_class = 'badge-danger';
          echo '<span class="badge ' . $type_class . '">' . ucfirst($value) . '</span>';
          echo '</td>';
          break;
        case 'depth':
          echo '<td>';
          echo '<span class="badge badge-info">' . $value . '</span>';
          echo '</td>';
          break;
        case 'created_at':
        case 'updated_at':
        case 'is_active':
          break;
        default:
          echo '<td>';
          echo htmlspecialchars($value);
          echo '</td>';
          break;
      }
    }
  }
}

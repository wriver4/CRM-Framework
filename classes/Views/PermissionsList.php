<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class PermissionsList extends EditDeleteTable
{

  public function __construct($results, $lang)
  {

    parent::__construct($results, $this->column_names, "permissions-list");
    $this->column_names = [
      'action' => $lang['action'],
      'pid' => $lang['permission_id'],
      'pobject' => $lang['permission_object'],
      'pdescription' => $lang['permission_description'],
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
        case 'updated_at':
        case 'created_at':
          break;
        default:
          echo '<td>';
          echo $value;
          echo '</td>';
          break;
      }
    }
  }
}

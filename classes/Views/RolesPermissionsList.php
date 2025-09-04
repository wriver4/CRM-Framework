<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class RolesPermissionsList extends EditDeleteTable
{

  public function __construct($results, $lang)
  {

    parent::__construct($results, $this->column_names, "roles-permissions-list");
    $this->column_names = [
      'rid' => $lang['role_id'],
      'pid' => $lang['permission_id'],
    ];
  }

  public function table_row_columns($results)
  {
    foreach ($results as $key => $value) {
      switch ($key) {
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

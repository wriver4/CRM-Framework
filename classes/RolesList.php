<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class RolesList extends ActionTable
{
  public function __construct($results, $lang)
  {
    parent::__construct($results, $this->column_names, "roles-list");
    $this->column_names = [
      'action' => $lang['action'],
      'rid' => $lang['role_id'],
      'rname' => $lang['rname']
    ];
  }
}

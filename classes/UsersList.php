<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class UsersList extends ActionTable
{

  public function __construct($results, $lang)
  {

    parent::__construct($results, $this->column_names, "users-list");
    $this->column_names = [
      'action' => $lang['action'],
      'full_name' => $lang['full_name'],
      'username' => $lang['username'],
      'rname' => $lang['rname']
    ];
    $this->lang = $lang;
    $this->users = new Users();
  }

  public function table_row_columns($results)
  {
      foreach ($results as $key => $value) {
        switch ($key) {
          case 'id':
            $id = $value;
            echo '<td>';
            $this->row_nav($value, $this->row_id = null);
            echo '</td>';
            break;
          case 'prop_id':
            $prop_results = $this->users->get_user_properties_by_id($id);
            $prop_id = implode(', ', $prop_results);
            echo '<td>';
            echo $prop_id;
            /*if (is_array($jdcv)) {
              $jdvc_s = implode(', ', $jdcv);
              echo $jdvc_s;
            } else {
              echo $jdcv;
            }*/
            echo '</td>';
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
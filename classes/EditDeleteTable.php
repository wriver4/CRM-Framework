<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class EditDeleteTable extends Table
{
  public $column_names;
  public $rid;
  public $table_id;

  protected $header_column_action_open;
  protected $header_column_action_close;
  protected $row_nav_open;
  protected $row_nav_close;

  public function __construct($results, $column_names, $table_id)
  {
    parent::__construct($results, $column_names, $table_id);
    //$this->row_id = $_SESSION['rid'];
    $this->table_id = $table_id;

    $this->column_names = $column_names;

    $this->header_column_action_open = '<th class="col-2  text-center" scope="col">';
    $this->header_column_action_close = '</th>';
    $this->header_column_open_state = '<th class="text-center select-filter">';
    $this->header_column_close = '</th>';
    $this->row_nav_open = '<div class="row">';

    $this->row_nav_button_open = '<div class="col py-1 "><a type="button" ';
   /* $this->row_nav_button_view_class_enabled = 'class="btn nav-link btn-info link-light" ';
    $this->row_nav_button_href_view_open = 'href="view?id=';
    $this->row_nav_button_href_close = '" tabindex="0" role="button" aria-pressed="false">';
    $this->row_nav_button_view_icon = '<i class="far fa-eye my-3" aria-hidden="true"></i>';*/
    $this->row_nav_button_close = '</a></div>';

    $this->row_nav_button_edit_class_enabled = 'class="btn nav-link btn-warning link-light" ';
    $this->row_nav_button_edit_class_disabled = 'class="btn nav-link btn-warning link-light disabled" ';
    $this->row_nav_button_href_edit_open = 'href="edit?id=';
    $this->row_nav_button_edit_icon = '<i class="far fa-edit my-3" aria-hidden="true"></i>';
    $this->row_nav_button_delete_class_enabled = 'class="btn nav-link btn-danger link-light" ';
    $this->row_nav_button_delete_class_disabled = 'class="btn nav-link btn-danger link-light disabled" ';
    $this->row_nav_button_href_delete_open = 'href="delete?id=';
    $this->row_nav_button_delete_icon = '<i class="far fa-trash-alt my-3" aria-hidden="true"></i>';

    $this->row_nav_close = '</div>';
  }

  public function table_header($column_names)
  {
    echo $this->header_open;
    foreach ($this->column_names as $key => $value) {
      if ($key == 'action') {
        echo
        $this->header_column_action_open
          . $value
          . $this->header_column_action_close;
      } elseif ($key == 'state') {
        echo
        $this->header_column_open_state
          . $value
          // . '&emsp;<span class="text-secondary">Filter:</span>&ensp;'
          . $this->header_column_close;
      } else {
        echo
        $this->header_columns_open
          . $value
          . $this->header_columns_link_close
          . $this->header_columns_close;
      }
    }
    echo $this->header_close;
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
        default:
          echo '<td>';
          echo $value;
          echo '</td>';
          break;
      }
    }
  }


  public function row_nav($value, $rid)
  {
    echo $this->row_nav_open;

   /* echo
    $this->row_nav_button_open
      . $this->row_nav_button_view_class_enabled
      . $this->row_nav_button_href_view_open
      . urlencode($value)
      . $this->row_nav_button_href_close
      . $this->row_nav_button_view_icon
      . $this->row_nav_button_close;*/

    echo $this->row_nav_button_open;
    echo $this->row_nav_button_edit_class_enabled;
    echo $this->row_nav_button_href_edit_open
      . urlencode($value)
      . $this->row_nav_button_href_close
      . $this->row_nav_button_edit_icon
      . $this->row_nav_button_close;

    echo $this->row_nav_button_open;
    echo $this->row_nav_button_delete_class_enabled;
    echo
    $this->row_nav_button_href_delete_open
      . urlencode($value)
      . $this->row_nav_button_href_close
      . $this->row_nav_button_delete_icon
      . $this->row_nav_button_close;

    echo $this->row_nav_close;
  }
}

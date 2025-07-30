<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class Table
{
  public $column_names;
  public $results;
  public $table_id;


  protected $body_open;
  protected $header_open;
  protected $header_columns_open;
  protected $header_columns_link_open;
  protected $header_columns_link_end;
  protected $header_columns_link_close;
  protected $header_columns_close;
  protected $header_close;
  protected $row_open;
  protected $row_close;
  protected $body_close;

  public function __construct($results, $column_names, $table_id)
  {
    $this->column_names = $column_names;
    $this->results = $results;
    $this->table_id = $table_id;
    $this->body_open = '<div class="container-lg"><div class="table-responsive"><table id="' . $this->table_id . '" class="table table-bordered table-striped align-middle">';
    $this->header_open = '<thead><tr scope="row">';
    $this->header_columns_open = '<th class="text-center" scope="col">';
    $this->header_columns_close = '</th>';
    $this->header_close = '</tr></thead><tbody>';
    $this->row_open = '<tr scope="row">';
    $this->row_close = '</tr>';
    $this->body_close = '</tbody></table></div></div>';
  }

  public function create_table($column_names = null)
  {
    echo $this->body_open;
    $this->table_header($column_names);
    foreach ($this->results as $result) {
      $this->table_row($result);
    }
    echo $this->body_close;
  }
  
  public function table_header($column_names)
  {
    echo $this->header_open;
    foreach ($column_names as $key => $value) {
      echo $this->header_columns_open
        . $value
        . $this->header_columns_close;
    }
    echo $this->header_close;
  }

  public function table_row($result)
  {
    echo $this->row_open;
    $this->table_row_columns($result);
    echo $this->row_close;
  }

  public function table_row_columns($result)
  {
    foreach ($result as $key => $value) {
      echo '<td>';
      if ($key !== 'id') {
        echo htmlspecialchars($value);
      }
      echo '</td>';
    }
  }
}

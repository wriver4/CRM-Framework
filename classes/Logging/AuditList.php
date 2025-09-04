<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class AuditList extends ViewTable
{
public function __construct($results, $lang)
  {
    parent::__construct($results, $this->column_names, "audit-list");
    $this->column_names = [
      
    ];
    $this->lang = $lang;
    $this->helper = new Helpers();
  }

}

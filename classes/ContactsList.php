<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class ContactsList extends ActionTable
{
  public function __construct($results, $lang)
  {
    parent::__construct($results, $this->column_names, "contacts-list");
    $this->column_names = [
      'action' => $lang['action'],
      'ctype' => $lang['ctype'],
      'full_name' => $lang['full_name'] ?? $lang['fullname'] ?? 'Full Name',
      'phones' => $lang['phones'],
      'call_order' => $lang['contact_call_order'],
      'emails' => $lang['emails'],
    ];
    $this->lang = $lang;
  }

  public function table_row_columns($results)
  {
    $helper = new Helpers();
    foreach ($results as $key => $value) {
      switch ($key) {
        case 'id':
          echo '<td>';
          $this->row_nav($value, $rid = null);
          echo '</td>';
          break;
        case 'ctype':
          echo '<td>';
          $helper->get_contact_type($this->lang, $value);
          echo '</td>';
          break;
        case 'phones':
          $dphones = json_decode($value);
          echo '<td>';
          foreach ($dphones as $key => $value) {
            if ($value == '') {
              continue;
            }
            $phone_type = match ($key) {
              '1' => $this->lang['cell'],
              '2' => $this->lang['bus'],
              '3' => $this->lang['alt'],
            };
            echo $phone_type . ': <br>'. $value . '<br>';
          }
          echo '</td>';
          break;
        case 'emails':
          $demails = json_decode($value);
          echo '<td class="contacts-list-email";>';
          foreach ($demails as $key => $value) {
            if ($value == '') {
              continue;
            }
            $email_type = match ($key) {
              '1' => $this->lang['personal'],
              '2' => $this->lang['business'],
              '3' => $this->lang['alt'],
            };
            echo $email_type . ': <br>' . $value . '<br>';
          }
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
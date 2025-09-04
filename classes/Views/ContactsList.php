<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class ContactsList extends EditDeleteTable
{
  public function __construct($results, $lang)
  {
    parent::__construct($results, $this->column_names, "contacts-list");
    $this->column_names = [
      'action' => $lang['action'],
      'contact_type' => $lang['contact_type'],
      'full_name' => $lang['full_name'] ?? $lang['fullname'] ?? 'Full Name',
      'phones' => $lang['phones'],
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
        case 'contact_type':
          echo '<td>';
          $helper->get_contact_type($this->lang, $value);
          echo '</td>';
          break;
        case 'phones':
          $dphones = json_decode($value, true); // Decode as associative array
          echo '<td>';
          if ($dphones && is_array($dphones)) {
            foreach ($dphones as $key => $phone_value) {
              if (empty($phone_value)) {
                continue;
              }
              $phone_type = match ($key) {
                '1' => $this->lang['cell'] ?? 'Cell',
                '2' => $this->lang['bus'] ?? 'Business',
                '3' => $this->lang['alt'] ?? 'Alt',
                default => 'Phone'
              };
              echo $phone_type . ': ' . htmlspecialchars($phone_value) . '<br>';
            }
          } else {
            echo '<small class="text-muted">No phones</small>';
          }
          echo '</td>';
          break;
        case 'emails':
          $demails = json_decode($value, true); // Decode as associative array
          echo '<td class="contacts-list-email">';
          if ($demails && is_array($demails)) {
            foreach ($demails as $key => $email_value) {
              if (empty($email_value)) {
                continue;
              }
              $email_type = match ($key) {
                '1' => $this->lang['personal'] ?? 'Personal',
                '2' => $this->lang['business'] ?? 'Business',
                '3' => $this->lang['alt'] ?? 'Alt',
                default => 'Email'
              };
              echo $email_type . ': ' . htmlspecialchars($email_value) . '<br>';
            }
          } else {
            echo '<small class="text-muted">No emails</small>';
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
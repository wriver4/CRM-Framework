<?php

class AuditLogList extends EditDeleteTable
{

  public function __construct($results, $lang)
  {

    parent::__construct($results, $this->column_names, "audit-log-list");
    $this->column_names = [
      'user_name' => $lang['user'],
      'action' => $lang['action'],
      'target_type' => $lang['type'],
      'target_id' => $lang['target_id'],
      'ip_address' => $lang['ip_address'],
      'created_at' => $lang['date'],
    ];
  }

  public function table_row_columns($results)
  {
    foreach ($results as $key => $value) {
      switch ($key) {
        case 'id':
          break;
        case 'action':
          echo '<td>';
          $action_class = 'badge-secondary';
          if ($value === 'grant') $action_class = 'badge-success';
          elseif ($value === 'revoke') $action_class = 'badge-danger';
          elseif ($value === 'delete') $action_class = 'badge-danger';
          elseif ($value === 'update') $action_class = 'badge-info';
          elseif ($value === 'deny') $action_class = 'badge-warning';
          echo '<span class="badge ' . $action_class . '">' . ucfirst($value) . '</span>';
          echo '</td>';
          break;
        case 'created_at':
          echo '<td>';
          echo date('Y-m-d H:i', strtotime($value ?? 'now'));
          echo '</td>';
          break;
        case 'old_value':
        case 'new_value':
        case 'user_agent':
        case 'user_id':
        case 'user_email':
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

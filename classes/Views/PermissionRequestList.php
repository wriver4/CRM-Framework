<?php

class PermissionRequestList extends EditDeleteTable
{

  public function __construct($results, $lang)
  {

    parent::__construct($results, $this->column_names, "permission-requests-list");
    $this->column_names = [
      'action' => $lang['action'],
      'requesting_user' => $lang['user'],
      'permission_name' => $lang['permission'],
      'approval_status' => $lang['status'],
      'created_at' => $lang['date_created'],
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
        case 'approval_status':
          echo '<td>';
          $status_class = 'badge-secondary';
          if ($value === 'approved') $status_class = 'badge-success';
          elseif ($value === 'rejected') $status_class = 'badge-danger';
          elseif ($value === 'pending') $status_class = 'badge-warning';
          elseif ($value === 'expired') $status_class = 'badge-info';
          echo '<span class="badge ' . $status_class . '">' . ucfirst($value) . '</span>';
          echo '</td>';
          break;
        case 'created_at':
        case 'updated_at':
          echo '<td>';
          echo date('Y-m-d H:i', strtotime($value ?? 'now'));
          echo '</td>';
          break;
        default:
          if (!in_array($key, ['id', 'requesting_user_id', 'permission_id', 'requested_role_id', 'current_approver_user_id', 'approved_by_user_id', 'rejection_reason', 'approval_notes', 'request_expiration', 'escalation_level', 'rejection_date', 'approval_date', 'rejected_by_user_id'])) {
            echo '<td>';
            echo $value;
            echo '</td>';
          }
          break;
      }
    }
  }
}

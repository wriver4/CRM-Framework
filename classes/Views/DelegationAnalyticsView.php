<?php

class DelegationAnalyticsView extends EditDeleteTable
{

  public function render_analytics_dashboard($analytics_data)
  {
    $html = '<div class="analytics-dashboard">';
    
    $html .= $this->render_summary_cards($analytics_data['summary']);
    $html .= $this->render_trends_chart($analytics_data['trends']);
    $html .= $this->render_top_delegators($analytics_data['top_delegators']);
    $html .= $this->render_top_receivers($analytics_data['top_receivers']);
    $html .= $this->render_permission_breakdown($analytics_data['delegation_by_permission']);
    $html .= $this->render_expiration_analysis($analytics_data['expiration_analysis']);
    
    $html .= '</div>';
    return $html;
  }

  private function render_summary_cards($summary)
  {
    $html = '<div class="summary-cards">';
    
    $cards = [
      ['label' => 'Total Delegations', 'value' => $summary['total_delegations'], 'color' => 'blue'],
      ['label' => 'Active Delegations', 'value' => $summary['active_delegations'], 'color' => 'green'],
      ['label' => 'Pending Review', 'value' => $summary['pending_count'], 'color' => 'orange'],
      ['label' => 'Rejected', 'value' => $summary['rejected_count'], 'color' => 'red'],
      ['label' => 'Expired', 'value' => $summary['expired_delegations'], 'color' => 'gray']
    ];
    
    foreach ($cards as $card) {
      $html .= sprintf(
        '<div class="card card-%s"><h3>%s</h3><p class="value">%d</p></div>',
        $card['color'],
        $card['label'],
        $card['value']
      );
    }
    
    $html .= '</div>';
    return $html;
  }

  private function render_trends_chart($trends)
  {
    $html = '<div class="chart-container">';
    $html .= '<h3>Delegation Trends (Last 30 Days)</h3>';
    $html .= '<table class="trends-table">';
    $html .= '<thead><tr><th>Date</th><th>Total</th><th>Approved</th><th>Pending</th><th>Rejected</th></tr></thead>';
    $html .= '<tbody>';
    
    foreach ($trends as $day) {
      $html .= sprintf(
        '<tr><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>',
        $day['delegation_date'],
        $day['count'],
        $day['approved'] ?? 0,
        $day['pending'] ?? 0,
        $day['rejected'] ?? 0
      );
    }
    
    $html .= '</tbody></table></div>';
    return $html;
  }

  private function render_top_delegators($delegators)
  {
    $html = '<div class="chart-container"><h3>Top Delegators</h3>';
    $html .= '<table class="data-table">';
    $html .= '<thead><tr><th>User</th><th>Delegations</th><th>Receivers</th><th>Approved</th><th>Rejected</th></tr></thead>';
    $html .= '<tbody>';
    
    foreach ($delegators as $delegator) {
      $html .= sprintf(
        '<tr><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>',
        htmlspecialchars($delegator['username']),
        $delegator['delegation_count'],
        $delegator['unique_receivers'],
        $delegator['approved_count'],
        $delegator['rejected_count']
      );
    }
    
    $html .= '</tbody></table></div>';
    return $html;
  }

  private function render_top_receivers($receivers)
  {
    $html = '<div class="chart-container"><h3>Top Permission Recipients</h3>';
    $html .= '<table class="data-table">';
    $html .= '<thead><tr><th>User</th><th>Received</th><th>Delegators</th><th>Approved</th><th>Active</th></tr></thead>';
    $html .= '<tbody>';
    
    foreach ($receivers as $receiver) {
      $html .= sprintf(
        '<tr><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>',
        htmlspecialchars($receiver['username']),
        $receiver['received_delegation_count'],
        $receiver['unique_delegators'],
        $receiver['approved_count'],
        $receiver['active_count']
      );
    }
    
    $html .= '</tbody></table></div>';
    return $html;
  }

  private function render_permission_breakdown($permissions)
  {
    $html = '<div class="chart-container"><h3>Permissions Most Frequently Delegated</h3>';
    $html .= '<table class="data-table">';
    $html .= '<thead><tr><th>Permission</th><th>Module.Action</th><th>Delegations</th><th>Approved</th><th>Users</th></tr></thead>';
    $html .= '<tbody>';
    
    foreach ($permissions as $perm) {
      $html .= sprintf(
        '<tr><td>%s</td><td>%s.%s</td><td>%d</td><td>%d</td><td>%d</td></tr>',
        htmlspecialchars($perm['permission_name']),
        $perm['module'],
        $perm['action'],
        $perm['delegation_count'],
        $perm['approved_count'],
        $perm['receivers']
      );
    }
    
    $html .= '</tbody></table></div>';
    return $html;
  }

  private function render_expiration_analysis($expiration)
  {
    $html = '<div class="expiration-analysis">';
    $html .= '<h3>Delegation Expiration Status</h3>';
    
    $items = [
      ['label' => 'Indefinite Delegations', 'value' => $expiration['indefinite_delegations']],
      ['label' => 'Expiring in 7 Days', 'value' => $expiration['expiring_in_7_days'], 'highlight' => true],
      ['label' => 'Expiring in 30 Days', 'value' => $expiration['expiring_in_30_days']],
      ['label' => 'Already Expired', 'value' => $expiration['already_expired']],
    ];
    
    foreach ($items as $item) {
      $class = isset($item['highlight']) ? 'highlight' : '';
      $html .= sprintf(
        '<p class="%s"><strong>%s:</strong> %d</p>',
        $class,
        $item['label'],
        $item['value']
      );
    }
    
    $html .= '</div>';
    return $html;
  }

  public function render_analytics_table($data, $columns = [])
  {
    if (empty($columns)) {
      $columns = ['id', 'username', 'permission_name', 'delegation_type', 'approval_status', 'created_at'];
    }
    
    $html = '<table class="analytics-table">';
    $html .= '<thead><tr>';
    
    foreach ($columns as $column) {
      $html .= '<th>' . ucfirst(str_replace('_', ' ', $column)) . '</th>';
    }
    
    $html .= '</tr></thead><tbody>';
    
    foreach ($data as $row) {
      $html .= '<tr>';
      foreach ($columns as $column) {
        $value = $row[$column] ?? '';
        
        if ($column === 'approval_status') {
          $badge_class = $value === 'approved' ? 'badge-success' : ($value === 'rejected' ? 'badge-danger' : 'badge-warning');
          $html .= sprintf('<td><span class="badge %s">%s</span></td>', $badge_class, ucfirst($value));
        } else if ($column === 'created_at') {
          $html .= '<td>' . date('Y-m-d H:i', strtotime($value)) . '</td>';
        } else {
          $html .= '<td>' . htmlspecialchars($value) . '</td>';
        }
      }
      $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    return $html;
  }
}

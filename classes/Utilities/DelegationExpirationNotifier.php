<?php

class DelegationExpirationNotifier
{
  private $delegations;
  private $audit;

  public function __construct()
  {
    $this->delegations = new PermissionDelegations();
    $this->audit = new PermissionAuditLog();
  }

  public function send_expiration_notifications($days_before = 7)
  {
    $expiring_delegations = $this->delegations->get_expiring_delegations($days_before);
    $sent_count = 0;

    foreach ($expiring_delegations as $delegation) {
      if ($this->send_notification($delegation)) {
        $sent_count++;
        
        $this->audit->log_action([
          'user_id' => 1,
          'action' => 'notify_expiration',
          'target_type' => 'permission_delegation',
          'target_id' => $delegation['id'],
          'new_value' => 'Expiration notification sent to ' . $delegation['receiving_user_email']
        ]);
      }
    }

    return $sent_count;
  }

  public function send_notification($delegation)
  {
    if (!$delegation['receiving_user_email']) {
      return false;
    }

    $days_until_expiration = ceil((strtotime($delegation['end_date']) - time()) / (60 * 60 * 24));
    
    $subject = "Permission Delegation Expiring in {$days_until_expiration} Days";
    
    $message = "Hello {$delegation['receiving_user']},\n\n";
    $message .= "Your delegated permission is expiring soon:\n\n";
    $message .= "Permission: {$delegation['permission_name']}\n";
    $message .= "Granted by: {$delegation['delegating_user']}\n";
    $message .= "Expires on: " . date('Y-m-d H:i:s', strtotime($delegation['end_date'])) . "\n";
    $message .= "Days remaining: {$days_until_expiration}\n\n";
    $message .= "If you need this permission to be extended, please request a new delegation.\n\n";
    $message .= "Best regards,\n";
    $message .= "Permission Management System";

    return $this->send_email(
      $delegation['receiving_user_email'],
      $subject,
      $message,
      $delegation['id']
    );
  }

  public function send_email($to_email, $subject, $message, $delegation_id)
  {
    try {
      $headers = "From: noreply@democrm.local\r\n";
      $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

      $result = mail($to_email, $subject, $message, $headers);

      if ($result) {
        $this->audit->log_action([
          'user_id' => 1,
          'action' => 'email_sent',
          'target_type' => 'permission_delegation',
          'target_id' => $delegation_id,
          'new_value' => 'Email sent to: ' . $to_email
        ]);
      }

      return $result;
    } catch (Exception $e) {
      $this->audit->log_action([
        'user_id' => 1,
        'action' => 'email_failed',
        'target_type' => 'permission_delegation',
        'target_id' => $delegation_id,
        'new_value' => 'Error: ' . $e->getMessage()
      ]);
      return false;
    }
  }

  public function revoke_expired_delegations()
  {
    $db = new Database();
    $pdo = $db->dbcrm();

    $sql = "UPDATE permission_delegations 
            SET approval_status = 'revoked', updated_at = NOW()
            WHERE approval_status = 'approved' 
            AND end_date IS NOT NULL 
            AND end_date < NOW()";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute();

    if ($result) {
      $affected_rows = $stmt->rowCount();
      
      $this->audit->log_action([
        'user_id' => 1,
        'action' => 'revoke_expired',
        'target_type' => 'permission_delegations',
        'target_id' => 0,
        'new_value' => 'Revoked ' . $affected_rows . ' expired delegations'
      ]);

      return $affected_rows;
    }

    return 0;
  }

  public function get_delegation_status_summary()
  {
    $db = new Database();
    $pdo = $db->dbcrm();

    $sql = "SELECT 
              approval_status,
              COUNT(*) as count,
              MAX(end_date) as latest_expiration
            FROM permission_delegations
            GROUP BY approval_status";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
  }
}

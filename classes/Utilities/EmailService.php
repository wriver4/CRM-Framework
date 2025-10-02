<?php

/**
 * EmailService Class
 * 
 * Handles sending emails via SMTP using PHPMailer
 * Manages SMTP configurations and email logging
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService extends Database
{
    private $pdo;
    private $audit;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->pdo = $this->dbcrm();
        $this->audit = new Audit();
    }
    
    /**
     * Get SMTP configuration for a user
     * 
     * @param int|null $user_id User ID (null for system default)
     * @param int|null $smtp_config_id Specific SMTP config ID (optional)
     * @return array|null SMTP configuration or null if not found
     */
    public function get_smtp_config($user_id = null, $smtp_config_id = null)
    {
        try {
            // If specific config ID is provided, use that
            if ($smtp_config_id) {
                $stmt = $this->pdo->prepare("
                    SELECT * FROM smtp_config 
                    WHERE id = ? AND is_active = 1
                ");
                $stmt->execute([$smtp_config_id]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Try to get user-specific default configuration
            if ($user_id) {
                $stmt = $this->pdo->prepare("
                    SELECT * FROM smtp_config 
                    WHERE user_id = ? AND is_default = 1 AND is_active = 1
                    LIMIT 1
                ");
                $stmt->execute([$user_id]);
                $config = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($config) {
                    return $config;
                }
            }
            
            // Fall back to system default configuration
            $stmt = $this->pdo->prepare("
                SELECT * FROM smtp_config 
                WHERE user_id IS NULL AND is_default = 1 AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting SMTP config: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Send email using configured SMTP server
     * 
     * @param array $params Email parameters
     *   - to_email (required): Recipient email address
     *   - to_name (optional): Recipient name
     *   - subject (required): Email subject
     *   - body_html (required): HTML email body
     *   - body_text (optional): Plain text email body
     *   - user_id (optional): User ID for SMTP config selection
     *   - smtp_config_id (optional): Specific SMTP config to use
     *   - lead_id (optional): Lead ID for logging
     *   - contact_id (optional): Contact ID for logging
     *   - email_type (optional): Type of email (e.g., 'lead_thank_you')
     *   - lead_source_id (optional): Lead source ID for logging
     * 
     * @return array Result array with 'success' boolean and 'message' string
     */
    public function send_email($params)
    {
        $to_email = $params['to_email'] ?? null;
        $to_name = $params['to_name'] ?? '';
        $subject = $params['subject'] ?? '';
        $body_html = $params['body_html'] ?? '';
        $body_text = $params['body_text'] ?? strip_tags($body_html);
        $user_id = $params['user_id'] ?? null;
        $smtp_config_id = $params['smtp_config_id'] ?? null;
        $lead_id = $params['lead_id'] ?? null;
        $contact_id = $params['contact_id'] ?? null;
        $email_type = $params['email_type'] ?? 'general';
        $lead_source_id = $params['lead_source_id'] ?? null;
        
        // Validate required parameters
        if (!$to_email || !$subject || !$body_html) {
            return [
                'success' => false,
                'message' => 'Missing required email parameters (to_email, subject, body_html)'
            ];
        }
        
        // Get SMTP configuration
        $smtp_config = $this->get_smtp_config($user_id, $smtp_config_id);
        
        if (!$smtp_config) {
            $this->log_email_send(
                null, $lead_id, $contact_id, $user_id, $email_type, $lead_source_id,
                $to_email, $to_name, $subject, $body_html, $body_text,
                'failed', 'No active SMTP configuration found'
            );
            
            return [
                'success' => false,
                'message' => 'No active SMTP configuration found'
            ];
        }
        
        // Create log entry with pending status
        $log_id = $this->log_email_send(
            $smtp_config['id'], $lead_id, $contact_id, $user_id, $email_type, $lead_source_id,
            $to_email, $to_name, $subject, $body_html, $body_text,
            'pending', null
        );
        
        try {
            // Initialize PHPMailer
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $smtp_config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_config['smtp_username'];
            $mail->Password = base64_decode($smtp_config['smtp_password']);
            $mail->SMTPSecure = $smtp_config['smtp_encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtp_config['smtp_port'];
            $mail->CharSet = 'UTF-8';
            
            // Recipients
            $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
            $mail->addAddress($to_email, $to_name);
            
            // Reply-To if configured
            if (!empty($smtp_config['reply_to_email'])) {
                $mail->addReplyTo($smtp_config['reply_to_email'], $smtp_config['from_name']);
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body_html;
            $mail->AltBody = $body_text;
            
            // Send email
            $mail->send();
            
            // Update log entry to sent status
            $this->update_email_log_status($log_id, 'sent', null);
            
            // Audit log
            $this->audit->log_action(
                'email_sent',
                'email_send_log',
                $log_id,
                "Email sent to {$to_email}: {$subject}",
                $user_id
            );
            
            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'log_id' => $log_id
            ];
            
        } catch (Exception $e) {
            $error_message = $mail->ErrorInfo ?? $e->getMessage();
            
            // Update log entry to failed status
            $this->update_email_log_status($log_id, 'failed', $error_message);
            
            // Audit log
            $this->audit->log_action(
                'email_failed',
                'email_send_log',
                $log_id,
                "Failed to send email to {$to_email}: {$error_message}",
                $user_id
            );
            
            error_log("Email send error: " . $error_message);
            
            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $error_message,
                'log_id' => $log_id
            ];
        }
    }
    
    /**
     * Log email send attempt
     * 
     * @param int|null $smtp_config_id SMTP configuration ID
     * @param int|null $lead_id Lead ID
     * @param int|null $contact_id Contact ID
     * @param int|null $user_id User ID
     * @param string $email_type Email type
     * @param int|null $lead_source_id Lead source ID
     * @param string $recipient_email Recipient email
     * @param string $recipient_name Recipient name
     * @param string $subject Email subject
     * @param string $body_html HTML body
     * @param string $body_text Plain text body
     * @param string $status Status (pending, sent, failed, bounced)
     * @param string|null $error_message Error message if failed
     * 
     * @return int Log entry ID
     */
    private function log_email_send(
        $smtp_config_id, $lead_id, $contact_id, $user_id, $email_type, $lead_source_id,
        $recipient_email, $recipient_name, $subject, $body_html, $body_text,
        $status, $error_message
    ) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO email_send_log (
                    smtp_config_id, lead_id, contact_id, user_id, email_type, lead_source_id,
                    recipient_email, recipient_name, subject, body_html, body_text,
                    status, error_message, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $smtp_config_id,
                $lead_id,
                $contact_id,
                $user_id,
                $email_type,
                $lead_source_id,
                $recipient_email,
                $recipient_name,
                $subject,
                $body_html,
                $body_text,
                $status,
                $error_message
            ]);
            
            return $this->pdo->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Error logging email send: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Update email log status
     * 
     * @param int $log_id Log entry ID
     * @param string $status New status
     * @param string|null $error_message Error message if failed
     */
    private function update_email_log_status($log_id, $status, $error_message)
    {
        try {
            if ($status === 'sent') {
                $stmt = $this->pdo->prepare("
                    UPDATE email_send_log 
                    SET status = ?, sent_at = NOW(), error_message = NULL
                    WHERE id = ?
                ");
                $stmt->execute([$status, $log_id]);
            } else {
                $stmt = $this->pdo->prepare("
                    UPDATE email_send_log 
                    SET status = ?, error_message = ?
                    WHERE id = ?
                ");
                $stmt->execute([$status, $error_message, $log_id]);
            }
        } catch (Exception $e) {
            error_log("Error updating email log status: " . $e->getMessage());
        }
    }
    
    /**
     * Get email send history for a lead
     * 
     * @param int $lead_id Lead ID
     * @return array Email send history
     */
    public function get_lead_email_history($lead_id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT esl.*, sc.config_name, sc.from_email
                FROM email_send_log esl
                LEFT JOIN smtp_config sc ON esl.smtp_config_id = sc.id
                WHERE esl.lead_id = ?
                ORDER BY esl.created_at DESC
            ");
            $stmt->execute([$lead_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting lead email history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get email send history for a contact
     * 
     * @param int $contact_id Contact ID
     * @return array Email send history
     */
    public function get_contact_email_history($contact_id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT esl.*, sc.config_name, sc.from_email
                FROM email_send_log esl
                LEFT JOIN smtp_config sc ON esl.smtp_config_id = sc.id
                WHERE esl.contact_id = ?
                ORDER BY esl.created_at DESC
            ");
            $stmt->execute([$contact_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting contact email history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Test SMTP configuration
     * 
     * @param int $smtp_config_id SMTP configuration ID
     * @param string $test_email Test recipient email
     * @return array Result with success status and message
     */
    public function test_smtp_config($smtp_config_id, $test_email)
    {
        return $this->send_email([
            'to_email' => $test_email,
            'to_name' => 'Test Recipient',
            'subject' => 'SMTP Configuration Test',
            'body_html' => '<p>This is a test email to verify your SMTP configuration is working correctly.</p>',
            'body_text' => 'This is a test email to verify your SMTP configuration is working correctly.',
            'smtp_config_id' => $smtp_config_id,
            'email_type' => 'smtp_test'
        ]);
    }
}
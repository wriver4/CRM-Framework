<?php

/**
 * EmailQueueManager Utility
 * Manages email queue, approval workflow, and sending
 * Uses full_name from source tables for recipient_name
 */

class EmailQueueManager extends Database
{
    private $emailTemplate;
    private $emailRenderer;
    private $emailService;

    public function __construct()
    {
        parent::__construct();
        $this->emailTemplate = new EmailTemplate();
        $this->emailRenderer = new EmailRenderer();
        $this->emailService = new EmailService();
    }

    /**
     * Queue an email for sending
     * @param int $templateId
     * @param string $module
     * @param int $recordId
     * @param string $recipientEmail
     * @param string $recipientName Uses full_name from source table
     * @param string $languageCode
     * @param bool $requiresApproval
     * @return int|false Queue ID or false on failure
     */
    public function queueEmail($templateId, $module, $recordId, $recipientEmail, $recipientName, $languageCode = 'en', $requiresApproval = null)
    {
        try {
            // Get template
            $template = $this->emailTemplate->getTemplateById($templateId);
            if (!$template) {
                throw new Exception("Template not found: $templateId");
            }

            // Determine if approval is required
            if ($requiresApproval === null) {
                $requiresApproval = $template['requires_approval'];
            }

            // Render email
            $rendered = $this->emailRenderer->renderEmail($templateId, $module, $recordId, $languageCode);

            // Insert into queue
            $sql = "INSERT INTO email_queue 
                    (template_id, module, record_id, recipient_email, recipient_name, 
                     language_code, subject, body_html, body_plain_text, variables_json, 
                     status, requires_approval, created_by)
                    VALUES 
                    (:template_id, :module, :record_id, :recipient_email, :recipient_name,
                     :language_code, :subject, :body_html, :body_plain_text, :variables_json,
                     :status, :requires_approval, :created_by)";
            
            $stmt = $this->conn()->prepare($sql);
            $stmt->execute([
                ':template_id' => $templateId,
                ':module' => $module,
                ':record_id' => $recordId,
                ':recipient_email' => $recipientEmail,
                ':recipient_name' => $recipientName, // Uses full_name from source table
                ':language_code' => $languageCode,
                ':subject' => $rendered['subject'],
                ':body_html' => $rendered['html'],
                ':body_plain_text' => $rendered['plain_text'],
                ':variables_json' => json_encode($rendered['variables']),
                ':status' => $requiresApproval ? 'pending' : 'approved',
                ':requires_approval' => $requiresApproval ? 1 : 0,
                ':created_by' => $_SESSION['user_id'] ?? null
            ]);

            $queueId = $this->conn()->lastInsertId();

            // If no approval required, send immediately
            if (!$requiresApproval) {
                $this->sendQueuedEmail($queueId);
            }

            return $queueId;
        } catch (Exception $e) {
            error_log("EmailQueueManager::queueEmail() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get queued emails pending approval
     * @param int|null $limit
     * @return array
     */
    public function getPendingApprovals($limit = null)
    {
        try {
            $sql = "SELECT eq.*, et.template_name, et.module
                    FROM email_queue eq
                    JOIN email_templates et ON eq.template_id = et.id
                    WHERE eq.status = 'pending' AND eq.requires_approval = 1
                    ORDER BY eq.created_at ASC";
            
            if ($limit) {
                $sql .= " LIMIT :limit";
            }

            $stmt = $this->conn()->prepare($sql);
            if ($limit) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("EmailQueueManager::getPendingApprovals() Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Approve queued email
     * @param int $queueId
     * @param int|null $approvedBy
     * @return bool
     */
    public function approveEmail($queueId, $approvedBy = null)
    {
        try {
            if ($approvedBy === null) {
                $approvedBy = $_SESSION['user_id'] ?? null;
            }

            $sql = "UPDATE email_queue 
                    SET status = 'approved', 
                        approved_by = :approved_by, 
                        approved_at = NOW()
                    WHERE id = :id AND status = 'pending'";
            
            $stmt = $this->conn()->prepare($sql);
            $stmt->execute([
                ':id' => $queueId,
                ':approved_by' => $approvedBy
            ]);

            // Send the email
            if ($stmt->rowCount() > 0) {
                return $this->sendQueuedEmail($queueId);
            }

            return false;
        } catch (PDOException $e) {
            error_log("EmailQueueManager::approveEmail() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject/cancel queued email
     * @param int $queueId
     * @return bool
     */
    public function cancelEmail($queueId)
    {
        try {
            $sql = "UPDATE email_queue SET status = 'cancelled' WHERE id = :id";
            $stmt = $this->conn()->prepare($sql);
            return $stmt->execute([':id' => $queueId]);
        } catch (PDOException $e) {
            error_log("EmailQueueManager::cancelEmail() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send queued email
     * @param int $queueId
     * @return bool
     */
    public function sendQueuedEmail($queueId)
    {
        try {
            // Get queued email
            $sql = "SELECT * FROM email_queue WHERE id = :id AND status IN ('pending', 'approved')";
            $stmt = $this->conn()->prepare($sql);
            $stmt->execute([':id' => $queueId]);
            $queuedEmail = $stmt->fetch();

            if (!$queuedEmail) {
                throw new Exception("Queued email not found or already sent: $queueId");
            }

            // Send email using EmailService
            $success = $this->emailService->sendEmail(
                $queuedEmail['recipient_email'],
                $queuedEmail['subject'],
                $queuedEmail['body_html'],
                $queuedEmail['body_plain_text']
            );

            if ($success) {
                // Update queue status
                $sql = "UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = :id";
                $stmt = $this->conn()->prepare($sql);
                $stmt->execute([':id' => $queueId]);

                // Log to email_send_log
                $this->logSentEmail($queuedEmail, true);

                // Log to communications table if configured
                $template = $this->emailTemplate->getTemplateById($queuedEmail['template_id']);
                if ($template && $template['log_to_communications']) {
                    $this->logToCommunications($queuedEmail);
                }

                return true;
            } else {
                // Update queue with error
                $sql = "UPDATE email_queue 
                        SET status = 'failed', 
                            error_message = 'Failed to send email'
                        WHERE id = :id";
                $stmt = $this->conn()->prepare($sql);
                $stmt->execute([':id' => $queueId]);

                // Log failure
                $this->logSentEmail($queuedEmail, false, 'Failed to send email');

                return false;
            }
        } catch (Exception $e) {
            error_log("EmailQueueManager::sendQueuedEmail() Error: " . $e->getMessage());
            
            // Update queue with error
            $sql = "UPDATE email_queue 
                    SET status = 'failed', 
                        error_message = :error
                    WHERE id = :id";
            $stmt = $this->conn()->prepare($sql);
            $stmt->execute([
                ':id' => $queueId,
                ':error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Log sent email to email_send_log
     * @param array $queuedEmail
     * @param bool $success
     * @param string|null $errorMessage
     * @return bool
     */
    private function logSentEmail($queuedEmail, $success, $errorMessage = null)
    {
        try {
            $sql = "INSERT INTO email_send_log 
                    (template_id, queue_id, module, record_id, recipient_email, recipient_name,
                     subject, body_html, body_plain_text, sent_by, send_method, 
                     logged_to_communications, success, error_message)
                    VALUES 
                    (:template_id, :queue_id, :module, :record_id, :recipient_email, :recipient_name,
                     :subject, :body_html, :body_plain_text, :sent_by, :send_method,
                     :logged_to_communications, :success, :error_message)";
            
            $template = $this->emailTemplate->getTemplateById($queuedEmail['template_id']);
            
            $stmt = $this->conn()->prepare($sql);
            return $stmt->execute([
                ':template_id' => $queuedEmail['template_id'],
                ':queue_id' => $queuedEmail['id'],
                ':module' => $queuedEmail['module'],
                ':record_id' => $queuedEmail['record_id'],
                ':recipient_email' => $queuedEmail['recipient_email'],
                ':recipient_name' => $queuedEmail['recipient_name'], // Uses full_name
                ':subject' => $queuedEmail['subject'],
                ':body_html' => $queuedEmail['body_html'],
                ':body_plain_text' => $queuedEmail['body_plain_text'],
                ':sent_by' => $queuedEmail['approved_by'] ?? $queuedEmail['created_by'],
                ':send_method' => $queuedEmail['requires_approval'] ? 'manual' : 'automatic',
                ':logged_to_communications' => $template['log_to_communications'] ?? 0,
                ':success' => $success ? 1 : 0,
                ':error_message' => $errorMessage
            ]);
        } catch (PDOException $e) {
            error_log("EmailQueueManager::logSentEmail() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log email to communications table
     * @param array $queuedEmail
     * @return bool
     */
    private function logToCommunications($queuedEmail)
    {
        try {
            // Check if communications table exists and has required structure
            $sql = "INSERT INTO communications 
                    (lead_id, contact_id, user_id, communication_type, subject, 
                     message, direction, created_at)
                    VALUES 
                    (:lead_id, :contact_id, :user_id, :communication_type, :subject,
                     :message, :direction, NOW())";
            
            $leadId = null;
            $contactId = null;
            
            // Determine lead_id and contact_id based on module
            if ($queuedEmail['module'] === 'leads') {
                $leadId = $queuedEmail['record_id'];
            } elseif ($queuedEmail['module'] === 'contacts') {
                $contactId = $queuedEmail['record_id'];
                // Get associated lead_id
                $leadSql = "SELECT lead_id FROM contacts WHERE id = :id";
                $leadStmt = $this->conn()->prepare($leadSql);
                $leadStmt->execute([':id' => $contactId]);
                $leadData = $leadStmt->fetch();
                $leadId = $leadData['lead_id'] ?? null;
            }

            $stmt = $this->conn()->prepare($sql);
            return $stmt->execute([
                ':lead_id' => $leadId,
                ':contact_id' => $contactId,
                ':user_id' => $queuedEmail['created_by'],
                ':communication_type' => 'email',
                ':subject' => $queuedEmail['subject'],
                ':message' => $queuedEmail['body_plain_text'] ?? strip_tags($queuedEmail['body_html']),
                ':direction' => 'outbound'
            ]);
        } catch (PDOException $e) {
            error_log("EmailQueueManager::logToCommunications() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email send history for a record
     * @param string $module
     * @param int $recordId
     * @param int|null $limit
     * @return array
     */
    public function getSendHistory($module, $recordId, $limit = null)
    {
        try {
            $sql = "SELECT esl.*, et.template_name
                    FROM email_send_log esl
                    LEFT JOIN email_templates et ON esl.template_id = et.id
                    WHERE esl.module = :module AND esl.record_id = :record_id
                    ORDER BY esl.sent_at DESC";
            
            if ($limit) {
                $sql .= " LIMIT :limit";
            }

            $stmt = $this->conn()->prepare($sql);
            $stmt->bindValue(':module', $module);
            $stmt->bindValue(':record_id', $recordId, PDO::PARAM_INT);
            if ($limit) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("EmailQueueManager::getSendHistory() Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Process scheduled emails (for future cron job)
     * @return int Number of emails sent
     */
    public function processScheduledEmails()
    {
        try {
            $sql = "SELECT id FROM email_queue 
                    WHERE status = 'approved' 
                    AND scheduled_send_at IS NOT NULL 
                    AND scheduled_send_at <= NOW()
                    LIMIT 50";
            
            $stmt = $this->conn()->prepare($sql);
            $stmt->execute();
            $queuedEmails = $stmt->fetchAll();

            $sentCount = 0;
            foreach ($queuedEmails as $email) {
                if ($this->sendQueuedEmail($email['id'])) {
                    $sentCount++;
                }
            }

            return $sentCount;
        } catch (PDOException $e) {
            error_log("EmailQueueManager::processScheduledEmails() Error: " . $e->getMessage());
            return 0;
        }
    }
}
<?php

/**
 * EmailTriggerHandler Utility
 * Handles automatic email triggering based on events (stage changes, assignments, etc.)
 * Uses full_name from source tables for recipient names
 */

class EmailTriggerHandler extends Database
{
    private $emailTemplate;
    private $emailQueueManager;

    public function __construct()
    {
        parent::__construct();
        $this->emailTemplate = new EmailTemplate();
        $this->emailQueueManager = new EmailQueueManager();
    }

    /**
     * Handle stage change trigger
     * @param string $module
     * @param int $recordId
     * @param int $oldStage
     * @param int $newStage
     * @param bool $isNewRecord
     * @return array Array of queued email IDs
     */
    public function handleStageChange($module, $recordId, $oldStage, $newStage, $isNewRecord = false)
    {
        try {
            // Get trigger rules for stage changes
            $rules = $this->emailTemplate->getTriggerRulesByType($module, 'stage_change');
            
            $queuedEmails = [];
            
            foreach ($rules as $rule) {
                $condition = json_decode($rule['trigger_condition'], true);
                
                // Check if this rule matches the stage change
                $shouldTrigger = false;
                
                if ($isNewRecord && isset($condition['on_create']) && $condition['on_create']) {
                    // Trigger on new record creation
                    if (isset($condition['stage_to']) && $condition['stage_to'] == $newStage) {
                        $shouldTrigger = true;
                    }
                } else {
                    // Trigger on stage change
                    if (isset($condition['stage_to']) && $condition['stage_to'] == $newStage) {
                        // Check if stage_from is specified
                        if (isset($condition['stage_from'])) {
                            if ($condition['stage_from'] == $oldStage) {
                                $shouldTrigger = true;
                            }
                        } else {
                            // No stage_from specified, trigger for any change to stage_to
                            $shouldTrigger = true;
                        }
                    }
                }
                
                if ($shouldTrigger) {
                    $queuedId = $this->triggerEmail($rule, $module, $recordId);
                    if ($queuedId) {
                        $queuedEmails[] = $queuedId;
                    }
                }
            }
            
            return $queuedEmails;
        } catch (Exception $e) {
            error_log("EmailTriggerHandler::handleStageChange() Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Handle assignment trigger
     * @param string $module
     * @param int $recordId
     * @param int|null $oldUserId
     * @param int|null $newUserId
     * @return array Array of queued email IDs
     */
    public function handleAssignment($module, $recordId, $oldUserId, $newUserId)
    {
        try {
            // Get trigger rules for assignments
            $rules = $this->emailTemplate->getTriggerRulesByType($module, 'assignment');
            
            $queuedEmails = [];
            
            foreach ($rules as $rule) {
                $condition = json_decode($rule['trigger_condition'], true);
                
                // Check if this rule matches
                $shouldTrigger = false;
                
                if (isset($condition['on_change']) && $condition['on_change']) {
                    // Trigger on any assignment change
                    if ($oldUserId != $newUserId) {
                        $shouldTrigger = true;
                    }
                } elseif (isset($condition['on_assign']) && $condition['on_assign']) {
                    // Trigger only when assigning (not unassigning)
                    if ($newUserId && $oldUserId != $newUserId) {
                        $shouldTrigger = true;
                    }
                }
                
                if ($shouldTrigger) {
                    $queuedId = $this->triggerEmail($rule, $module, $recordId);
                    if ($queuedId) {
                        $queuedEmails[] = $queuedId;
                    }
                }
            }
            
            return $queuedEmails;
        } catch (Exception $e) {
            error_log("EmailTriggerHandler::handleAssignment() Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Handle field update trigger
     * @param string $module
     * @param int $recordId
     * @param string $fieldName
     * @param mixed $oldValue
     * @param mixed $newValue
     * @return array Array of queued email IDs
     */
    public function handleFieldUpdate($module, $recordId, $fieldName, $oldValue, $newValue)
    {
        try {
            // Get trigger rules for field updates
            $rules = $this->emailTemplate->getTriggerRulesByType($module, 'field_update');
            
            $queuedEmails = [];
            
            foreach ($rules as $rule) {
                $condition = json_decode($rule['trigger_condition'], true);
                
                // Check if this rule matches the field
                if (isset($condition['field']) && $condition['field'] === $fieldName) {
                    $shouldTrigger = false;
                    
                    // Check value conditions
                    if (isset($condition['value_to'])) {
                        if ($condition['value_to'] == $newValue) {
                            $shouldTrigger = true;
                        }
                    } elseif (isset($condition['on_change']) && $condition['on_change']) {
                        if ($oldValue != $newValue) {
                            $shouldTrigger = true;
                        }
                    }
                    
                    if ($shouldTrigger) {
                        $queuedId = $this->triggerEmail($rule, $module, $recordId);
                        if ($queuedId) {
                            $queuedEmails[] = $queuedId;
                        }
                    }
                }
            }
            
            return $queuedEmails;
        } catch (Exception $e) {
            error_log("EmailTriggerHandler::handleFieldUpdate() Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Trigger email based on rule
     * Uses full_name from source tables for recipient names
     * @param array $rule
     * @param string $module
     * @param int $recordId
     * @return int|false Queue ID or false on failure
     */
    private function triggerEmail($rule, $module, $recordId)
    {
        try {
            // Get record data to determine recipient
            $recordData = $this->getRecordData($module, $recordId);
            if (!$recordData) {
                throw new Exception("Record not found: $module ID $recordId");
            }

            // Determine recipient based on rule
            $recipients = $this->determineRecipients($rule, $recordData, $module);
            
            if (empty($recipients)) {
                error_log("EmailTriggerHandler: No recipients found for rule ID {$rule['id']}");
                return false;
            }

            // Determine language (from record or default to English)
            $languageCode = $this->determineLanguage($recordData);

            // Queue email for each recipient
            $queuedIds = [];
            foreach ($recipients as $recipient) {
                $queuedId = $this->emailQueueManager->queueEmail(
                    $rule['template_id'],
                    $module,
                    $recordId,
                    $recipient['email'],
                    $recipient['name'], // Uses full_name from source table
                    $languageCode,
                    $rule['requires_approval']
                );
                
                if ($queuedId) {
                    $queuedIds[] = $queuedId;
                }
            }

            return !empty($queuedIds) ? $queuedIds[0] : false;
        } catch (Exception $e) {
            error_log("EmailTriggerHandler::triggerEmail() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get record data from database
     * @param string $module
     * @param int $recordId
     * @return array|null
     */
    private function getRecordData($module, $recordId)
    {
        try {
            switch ($module) {
                case 'leads':
                    $sql = "SELECT l.*, u.full_name as assigned_user_name, u.email as assigned_user_email
                            FROM leads l
                            LEFT JOIN users u ON l.last_edited_by = u.id
                            WHERE l.id = :id";
                    break;
                
                case 'contacts':
                    $sql = "SELECT c.*, u.full_name as assigned_user_name, u.email as assigned_user_email
                            FROM contacts c
                            LEFT JOIN leads l ON c.lead_id = l.id
                            LEFT JOIN users u ON l.last_edited_by = u.id
                            WHERE c.id = :id";
                    break;
                
                case 'referrals':
                    $sql = "SELECT r.*, u.full_name as assigned_user_name, u.email as assigned_user_email
                            FROM referrals r
                            LEFT JOIN users u ON r.last_edited_by = u.id
                            WHERE r.id = :id";
                    break;
                
                case 'prospects':
                    $sql = "SELECT p.*, u.full_name as assigned_user_name, u.email as assigned_user_email
                            FROM prospects p
                            LEFT JOIN users u ON p.last_edited_by = u.id
                            WHERE p.id = :id";
                    break;
                
                default:
                    return null;
            }

            $stmt = $this->conn()->prepare($sql);
            $stmt->execute([':id' => $recordId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("EmailTriggerHandler::getRecordData() Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Determine recipients based on rule and record data
     * Uses full_name from source tables
     * @param array $rule
     * @param array $recordData
     * @param string $module
     * @return array Array of ['email' => '', 'name' => ''] using full_name
     */
    private function determineRecipients($rule, $recordData, $module)
    {
        $recipients = [];
        
        switch ($rule['recipient_type']) {
            case 'lead_contact':
                // Send to the lead/contact email
                if (!empty($recordData['email'])) {
                    $recipients[] = [
                        'email' => $recordData['email'],
                        'name' => $recordData['full_name'] ?? '' // Uses full_name from table
                    ];
                }
                break;
            
            case 'assigned_user':
                // Send to assigned user
                if (!empty($recordData['assigned_user_email'])) {
                    $recipients[] = [
                        'email' => $recordData['assigned_user_email'],
                        'name' => $recordData['assigned_user_name'] ?? '' // Uses users.full_name
                    ];
                }
                break;
            
            case 'custom_email':
                // Send to custom email address
                if (!empty($rule['custom_recipient_email'])) {
                    $recipients[] = [
                        'email' => $rule['custom_recipient_email'],
                        'name' => 'Team Member'
                    ];
                }
                break;
            
            case 'both':
                // Send to both lead/contact and assigned user
                if (!empty($recordData['email'])) {
                    $recipients[] = [
                        'email' => $recordData['email'],
                        'name' => $recordData['full_name'] ?? '' // Uses full_name
                    ];
                }
                if (!empty($recordData['assigned_user_email'])) {
                    $recipients[] = [
                        'email' => $recordData['assigned_user_email'],
                        'name' => $recordData['assigned_user_name'] ?? '' // Uses users.full_name
                    ];
                }
                break;
        }
        
        return $recipients;
    }

    /**
     * Determine language for email
     * @param array $recordData
     * @return string Language code (en, es)
     */
    private function determineLanguage($recordData)
    {
        // Check if record has language preference
        if (isset($recordData['language'])) {
            // Map language ID to code
            switch ($recordData['language']) {
                case 1:
                    return 'en';
                case 2:
                    return 'es';
                default:
                    return 'en';
            }
        }
        
        // Default to English
        return 'en';
    }

    /**
     * Manual trigger for testing or admin use
     * @param string $templateKey
     * @param string $module
     * @param int $recordId
     * @param string|null $recipientEmail Override recipient
     * @param string|null $recipientName Override recipient name (uses full_name if not provided)
     * @return int|false Queue ID or false on failure
     */
    public function manualTrigger($templateKey, $module, $recordId, $recipientEmail = null, $recipientName = null)
    {
        try {
            // Get template
            $template = $this->emailTemplate->getTemplateByKey($templateKey);
            if (!$template) {
                throw new Exception("Template not found: $templateKey");
            }

            // Get record data
            $recordData = $this->getRecordData($module, $recordId);
            if (!$recordData) {
                throw new Exception("Record not found: $module ID $recordId");
            }

            // Use provided recipient or default to record email
            if (!$recipientEmail) {
                $recipientEmail = $recordData['email'] ?? null;
            }
            if (!$recipientName) {
                $recipientName = $recordData['full_name'] ?? ''; // Uses full_name from table
            }

            if (!$recipientEmail) {
                throw new Exception("No recipient email available");
            }

            // Determine language
            $languageCode = $this->determineLanguage($recordData);

            // Queue email
            return $this->emailQueueManager->queueEmail(
                $template['id'],
                $module,
                $recordId,
                $recipientEmail,
                $recipientName, // Uses full_name
                $languageCode,
                false // Manual triggers don't require approval by default
            );
        } catch (Exception $e) {
            error_log("EmailTriggerHandler::manualTrigger() Error: " . $e->getMessage());
            return false;
        }
    }
}
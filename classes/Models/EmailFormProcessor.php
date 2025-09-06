<?php

/**
 * Email Form Processing Model
 * Integrates with existing CRM framework patterns
 * Handles email form imports for leads system
 */

class EmailFormProcessor extends Database
{
    private $leads;
    private $helpers;
    
    public function __construct()
    {
        parent::__construct();
        $this->leads = new Leads();
        $this->helpers = new Helpers();
    }
    
    /**
     * Process all active email accounts
     * Called by cron job
     */
    public function processAllEmails()
    {
        $results = [];
        $accounts = $this->getActiveEmailAccounts();
        
        foreach ($accounts as $account) {
            try {
                $count = $this->processEmailAccount($account);
                $results[$account['email_address']] = $count;
                
                // Update last check time
                $this->updateLastCheckTime($account['id']);
                
            } catch (Exception $e) {
                error_log("Email processing error for {$account['email_address']}: " . $e->getMessage());
                $results[$account['email_address']] = 'error: ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Get active email accounts from configuration
     */
    private function getActiveEmailAccounts()
    {
        $pdo = $this->dbcrm();
        $stmt = $pdo->prepare("
            SELECT * FROM email_accounts_config 
            WHERE is_active = ? 
            ORDER BY form_type
        ");
        $stmt->bindValue(1, 1, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt = null;
        
        return $result;
    }
    
    /**
     * Process emails from specific account
     */
    private function processEmailAccount($account)
    {
        // Decrypt password
        $password = $this->decryptPassword($account['password']);
        
        // IMAP connection
        $hostname = "{{$account['imap_host']}:{$account['imap_port']}/imap/{$account['imap_encryption']}}INBOX";
        $username = $account['username'];
        
        $inbox = imap_open($hostname, $username, $password);
        
        if (!$inbox) {
            throw new Exception("Cannot connect to email: " . imap_last_error());
        }
        
        // Get unread emails from last 24 hours
        $searchCriteria = 'UNSEEN SINCE "' . date('d-M-Y', strtotime('-1 day')) . '"';
        $emails = imap_search($inbox, $searchCriteria);
        $processedCount = 0;
        
        if ($emails) {
            foreach ($emails as $emailNumber) {
                try {
                    $processed = $this->processEmail($inbox, $emailNumber, $account);
                    if ($processed) {
                        $processedCount++;
                        // Mark as read
                        imap_setflag_full($inbox, $emailNumber, "\\Seen");
                    }
                } catch (Exception $e) {
                    error_log("Error processing email {$emailNumber}: " . $e->getMessage());
                    // Continue processing other emails
                }
            }
        }
        
        imap_close($inbox);
        return $processedCount;
    }
    
    /**
     * Process individual email
     */
    private function processEmail($inbox, $emailNumber, $account)
    {
        // Get email details
        $header = imap_headerinfo($inbox, $emailNumber);
        $body = imap_body($inbox, $emailNumber);
        
        // Extract email data
        $emailData = [
            'message_id' => $header->message_id ?? '',
            'subject' => $header->subject ?? '',
            'sender_email' => $header->from[0]->mailbox . '@' . $header->from[0]->host,
            'received_at' => date('Y-m-d H:i:s', $header->udate),
            'raw_content' => $body
        ];
        
        // Check for duplicate processing
        if ($this->isEmailAlreadyProcessed($emailData['message_id'])) {
            $this->logEmailProcessing($account, $emailData, 'duplicate', null, 'Email already processed');
            return false;
        }
        
        // Parse form data from email content
        $formData = $this->parseEmailContent($body, $account['form_type']);
        
        if (empty($formData['email'])) {
            $this->logEmailProcessing($account, $emailData, 'failed', null, 'No valid email address found');
            return false;
        }
        
        try {
            // Create or update lead
            $leadId = $this->createLeadFromEmail($formData, $account['form_type']);
            
            // Log successful processing
            $this->logEmailProcessing($account, $emailData, 'success', $leadId, null, $formData);
            
            return true;
            
        } catch (Exception $e) {
            $this->logEmailProcessing($account, $emailData, 'failed', null, $e->getMessage(), $formData);
            throw $e;
        }
    }
    
    /**
     * Parse email content to extract form data
     */
    private function parseEmailContent($content, $formType)
    {
        // Load form mapping configuration
        $mapper = new EmailFormMapper();
        $mapping = $mapper->getFormMapping($formType);
        
        $formData = [];
        
        // Parse using field patterns
        foreach ($mapping['field_patterns'] as $field => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    $formData[$field] = trim($matches[1]);
                    break; // Use first match
                }
            }
        }
        
        return $formData;
    }
    
    /**
     * Create lead from parsed email data
     */
    private function createLeadFromEmail($formData, $formType)
    {
        // Map form type to lead_source values
        $leadSourceMap = [
            'estimate' => 1,  // Web
            'ltr' => 2,       // Referral
            'contact' => 4    // Email
        ];
        
        // Prepare lead data following existing CRM structure
        $leadData = [
            'first_name' => $this->extractFirstName($formData['name'] ?? ''),
            'family_name' => $this->extractLastName($formData['name'] ?? ''),
            'full_name' => $formData['name'] ?? '',
            'email' => $formData['email'] ?? '',
            'cell_phone' => $this->cleanPhone($formData['phone'] ?? ''),
            'business_name' => $formData['company'] ?? '',
            'project_name' => $this->generateProjectName($formData, $formType),
            'contact_type' => $this->determineContactType($formType),
            'form_street_1' => $formData['property_address'] ?? $formData['address'] ?? '',
            'form_city' => $formData['property_city'] ?? $formData['city'] ?? '',
            'form_state' => $formData['property_state'] ?? $formData['state'] ?? '',
            'form_postcode' => $formData['property_zip'] ?? $formData['zip'] ?? '',
            'form_country' => 'US',
            'services_interested_in' => $this->mapServicesFromFormType($formType),
            'structure_type' => $this->determineStructureType($formData),
            'structure_description' => $formData['property_type'] ?? $formData['land_type'] ?? '',
            'structure_additional' => $this->combineAdditionalInfo($formData),
            'lead_source' => $leadSourceMap[$formType] ?? 4,
            'stage' => 'Lead',
            'get_updates' => 1,
            'hear_about' => 'Email Form',
            'timezone' => $this->determineTimezone($formData['state'] ?? '')
        ];
        
        // Check for existing lead
        $existingLead = $this->findExistingLead($leadData['email']);
        if ($existingLead) {
            $this->updateExistingLead($existingLead['id'], $leadData, $formType, $formData);
            return $existingLead['id'];
        }
        
        // Create new lead using existing Leads class
        $leadId = $this->leads->create_lead($leadData);
        
        // Add initial note
        $this->addLeadNote($leadId, $this->generateInitialNote($formData, $formType));
        
        return $leadId;
    }
    
    /**
     * Find existing lead by email
     */
    private function findExistingLead($email)
    {
        $pdo = $this->dbcrm();
        $stmt = $pdo->prepare("SELECT id, email, stage FROM leads WHERE email = ? LIMIT 1");
        $stmt->bindValue(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt = null;
        
        return $result;
    }
    
    /**
     * Update existing lead with new form submission
     */
    private function updateExistingLead($leadId, $newData, $formType, $originalFormData)
    {
        $pdo = $this->dbcrm();
        
        // Update relevant fields if they're empty or new submission has more info
        $updateFields = [];
        $params = [];
        
        $updatableFields = ['cell_phone', 'business_name', 'form_street_1', 'form_city', 
                           'form_state', 'form_postcode', 'structure_additional'];
        
        foreach ($updatableFields as $field) {
            if (!empty($newData[$field])) {
                $updateFields[] = "{$field} = ?";
                $params[] = $newData[$field];
            }
        }
        
        if (!empty($updateFields)) {
            $params[] = $leadId;
            $sql = "UPDATE leads SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $stmt = null;
        }
        
        // Always add a note for the new submission
        $note = "New {$formType} form submission received via email with updated information.";
        if (!empty($newData['structure_additional'])) {
            $note .= "\n\nAdditional Details: " . $newData['structure_additional'];
        }
        $this->addLeadNote($leadId, $note);
    }
    
    /**
     * Add note to lead using existing notes system
     */
    private function addLeadNote($leadId, $noteText, $userId = null)
    {
        $notes = new Notes();
        return $notes->create_note([
            'source' => $leadId,
            'note_text' => $noteText,
            'user_id' => $userId,
            'form_source' => 'leads'
        ]);
    }
    
    /**
     * Log email processing results
     */
    private function logEmailProcessing($account, $emailData, $status, $leadId = null, $errorMessage = null, $parsedData = null)
    {
        $pdo = $this->dbcrm();
        $stmt = $pdo->prepare("
            INSERT INTO email_form_processing (
                email_account, form_type, message_id, subject, sender_email, 
                received_at, processing_status, lead_id, raw_email_content, 
                parsed_form_data, error_message
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bindValue(1, $account['email_address'], PDO::PARAM_STR);
        $stmt->bindValue(2, $account['form_type'], PDO::PARAM_STR);
        $stmt->bindValue(3, $emailData['message_id'], PDO::PARAM_STR);
        $stmt->bindValue(4, $emailData['subject'], PDO::PARAM_STR);
        $stmt->bindValue(5, $emailData['sender_email'], PDO::PARAM_STR);
        $stmt->bindValue(6, $emailData['received_at'], PDO::PARAM_STR);
        $stmt->bindValue(7, $status, PDO::PARAM_STR);
        $stmt->bindValue(8, $leadId, PDO::PARAM_INT);
        $stmt->bindValue(9, $emailData['raw_content'], PDO::PARAM_STR);
        $stmt->bindValue(10, $parsedData ? json_encode($parsedData) : null, PDO::PARAM_STR);
        $stmt->bindValue(11, $errorMessage, PDO::PARAM_STR);
        
        $stmt->execute();
        $stmt = null;
    }
    
    /**
     * Check if email was already processed
     */
    private function isEmailAlreadyProcessed($messageId)
    {
        if (empty($messageId)) return false;
        
        $pdo = $this->dbcrm();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM email_form_processing 
            WHERE message_id = ? AND processing_status != 'failed'
        ");
        $stmt->bindValue(1, $messageId, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        $stmt = null;
        
        return $count > 0;
    }
    
    /**
     * Update last check time for email account
     */
    private function updateLastCheckTime($accountId)
    {
        $pdo = $this->dbcrm();
        $stmt = $pdo->prepare("
            UPDATE email_accounts_config 
            SET last_check = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->bindValue(1, (int)$accountId, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
    }
    
    /**
     * Decrypt password (implement based on your encryption method)
     */
    private function decryptPassword($encryptedPassword)
    {
        // For now, assume base64 encoding - implement proper decryption as needed
        return base64_decode($encryptedPassword);
    }
    
    // Helper methods for data processing
    private function extractFirstName($fullName)
    {
        $parts = explode(' ', trim($fullName));
        return $parts[0] ?? '';
    }
    
    private function extractLastName($fullName)
    {
        $parts = explode(' ', trim($fullName));
        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }
    
    private function cleanPhone($phone)
    {
        return preg_replace('/[^0-9+\-\.\(\)\s]/', '', $phone);
    }
    
    private function determineContactType($formType)
    {
        $typeMap = [
            'estimate' => 1,  // Homeowner
            'ltr' => 2,       // Property Manager
            'contact' => 1    // Homeowner
        ];
        
        return $typeMap[$formType] ?? 1;
    }
    
    private function mapServicesFromFormType($formType)
    {
        $serviceMap = [
            'estimate' => 'full_system',
            'ltr' => 'fire_retardant',
            'contact' => 'general_inquiry'
        ];
        
        return $serviceMap[$formType] ?? 'general_inquiry';
    }
    
    private function determineStructureType($formData)
    {
        // Map common property types to structure IDs
        $propertyType = strtolower($formData['property_type'] ?? $formData['land_type'] ?? '');
        
        if (strpos($propertyType, 'residential') !== false || strpos($propertyType, 'home') !== false) {
            return 1; // Residential
        } elseif (strpos($propertyType, 'commercial') !== false || strpos($propertyType, 'business') !== false) {
            return 2; // Commercial
        } elseif (strpos($propertyType, 'agricultural') !== false || strpos($propertyType, 'farm') !== false) {
            return 3; // Agricultural
        }
        
        return 1; // Default to residential
    }
    
    private function generateProjectName($formData, $formType)
    {
        $city = $formData['property_city'] ?? $formData['city'] ?? 'Property';
        
        switch ($formType) {
            case 'estimate':
                return "Fire Protection System - {$city}";
            case 'ltr':
                $acres = $formData['total_acreage'] ?? 'Unknown';
                return "LTR Application - {$acres} acres";
            case 'contact':
                return "General Inquiry - {$city}";
            default:
                return "Email Form Submission - {$city}";
        }
    }
    
    private function combineAdditionalInfo($formData)
    {
        $info = [];
        
        // Collect various additional information fields
        $fields = ['message', 'special_requirements', 'structure_details', 
                  'application_type', 'timeline', 'budget_range'];
        
        foreach ($fields as $field) {
            if (!empty($formData[$field])) {
                $info[] = $formData[$field];
            }
        }
        
        return implode("\n\n", $info);
    }
    
    private function generateInitialNote($formData, $formType)
    {
        $mapper = new EmailFormMapper();
        $mapping = $mapper->getFormMapping($formType);
        
        $note = $mapping['note_template'] ?? "New {$formType} form submission received via email.";
        
        // Replace placeholders with actual data
        foreach ($formData as $key => $value) {
            $note = str_replace("{{$key}}", $value, $note);
        }
        
        return $note;
    }
    
    private function determineTimezone($state)
    {
        // Simple timezone mapping based on state
        $timezoneMap = [
            'CA' => 'America/Los_Angeles',
            'NY' => 'America/New_York',
            'TX' => 'America/Chicago',
            'FL' => 'America/New_York',
            'CO' => 'America/Denver'
        ];
        
        return $timezoneMap[strtoupper($state)] ?? 'America/Denver';
    }
    
    /**
     * Get processing statistics for dashboard
     */
    public function getProcessingStats()
    {
        $pdo = $this->dbcrm();
        
        // Total processed
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM email_form_processing");
        $stmt->execute();
        $totalProcessed = $stmt->fetchColumn();
        $stmt = null;
        
        // Successful
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM email_form_processing WHERE processing_status = ?");
        $stmt->bindValue(1, 'success', PDO::PARAM_STR);
        $stmt->execute();
        $successful = $stmt->fetchColumn();
        $stmt = null;
        
        // Failed
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM email_form_processing WHERE processing_status = ?");
        $stmt->bindValue(1, 'failed', PDO::PARAM_STR);
        $stmt->execute();
        $failed = $stmt->fetchColumn();
        $stmt = null;
        
        // Today
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM email_form_processing WHERE DATE(processed_at) = CURDATE()");
        $stmt->execute();
        $today = $stmt->fetchColumn();
        $stmt = null;
        
        return [
            'total_processed' => $totalProcessed,
            'successful' => $successful,
            'failed' => $failed,
            'today' => $today
        ];
    }
    
    /**
     * Get recent processing records
     */
    public function getRecentProcessing($limit = 20)
    {
        $pdo = $this->dbcrm();
        $stmt = $pdo->prepare("
            SELECT * FROM email_form_processing 
            ORDER BY processed_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt = null;
        
        return $result;
    }
    
    /**
     * Get email accounts configuration
     */
    public function getEmailAccounts()
    {
        $pdo = $this->dbcrm();
        $stmt = $pdo->prepare("SELECT * FROM email_accounts_config ORDER BY form_type, email_address");
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt = null;
        
        return $result;
    }
    
    /**
     * Test email connection for specific account
     */
    public function testEmailConnection($accountId)
    {
        $pdo = $this->dbcrm();
        $stmt = $pdo->prepare("SELECT * FROM email_accounts_config WHERE id = ?");
        $stmt->bindValue(1, (int)$accountId, PDO::PARAM_INT);
        $stmt->execute();
        $account = $stmt->fetch();
        $stmt = null;
        
        if (!$account) {
            throw new Exception("Email account not found");
        }
        
        // Decrypt password
        $password = $this->decryptPassword($account['password']);
        
        // IMAP connection test
        $hostname = "{{$account['imap_host']}:{$account['imap_port']}/imap/{$account['imap_encryption']}}INBOX";
        $username = $account['username'];
        
        $inbox = imap_open($hostname, $username, $password);
        
        if (!$inbox) {
            throw new Exception("Cannot connect to email: " . imap_last_error());
        }
        
        $status = imap_status($inbox, $hostname, SA_ALL);
        imap_close($inbox);
        
        return "Connection successful. Messages: {$status->messages}, Unread: {$status->unseen}";
    }
    
    /**
     * Create lead directly from form data (for API submissions)
     */
    public function createLeadFromFormData($formData, $formType)
    {
        // Validate required fields
        if (empty($formData['email'])) {
            throw new Exception('Email address is required');
        }
        
        if (empty($formData['name'])) {
            throw new Exception('Name is required');
        }
        
        // Use the existing createLeadFromEmail method
        return $this->createLeadFromEmail($formData, $formType);
    }
}
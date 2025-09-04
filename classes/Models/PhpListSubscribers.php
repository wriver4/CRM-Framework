<?php

/**
 * PhpListSubscribers Model
 * 
 * Manages phpList subscriber integration and synchronization
 * Handles subscriber creation, updates, and list management
 */
class PhpListSubscribers extends Database
{
    private $config = [];
    
    public function __construct()
    {
        parent::__construct();
        $this->loadConfig();
    }
    
    /**
     * Load phpList configuration from database
     */
    private function loadConfig()
    {
        try {
            $stmt = $this->dbcrm()->prepare("SELECT config_key, config_value, is_encrypted FROM phplist_config");
            $stmt->execute();
            $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($configs as $config) {
                $value = $config['config_value'];
                
                // Decrypt encrypted values (implement your encryption method)
                if ($config['is_encrypted'] == 1) {
                    // TODO: Implement decryption for sensitive data like passwords
                    // $value = $this->decrypt($value);
                }
                
                $this->config[$config['config_key']] = $value;
            }
        } catch (PDOException $e) {
            error_log("PhpListSubscribers: Failed to load config - " . $e->getMessage());
        }
    }
    
    /**
     * Create a new phpList subscriber record from lead data
     * 
     * @param int $leadId Lead ID
     * @param array $leadData Lead data array
     * @return int|false Subscriber ID or false on failure
     */
    public function createSubscriberFromLead($leadId, $leadData)
    {
        try {
            // Check if subscriber already exists
            if ($this->subscriberExists($leadId, $leadData['email'])) {
                return $this->updateSubscriberFromLead($leadId, $leadData);
            }
            
            // Prepare segmentation data
            $segmentationData = $this->prepareSegmentationData($leadData);
            
            // Determine which lists this subscriber should join
            $phplistLists = $this->determineSubscriberLists($leadData);
            
            $stmt = $this->dbcrm()->prepare("
                INSERT INTO phplist_subscribers (
                    lead_id, contact_id, email, first_name, last_name,
                    sync_status, segmentation_data, phplist_lists, opt_in_date
                ) VALUES (
                    :lead_id, :contact_id, :email, :first_name, :last_name,
                    :sync_status, :segmentation_data, :phplist_lists, :opt_in_date
                )
            ");
            
            $stmt->bindValue(':lead_id', (int)$leadId, PDO::PARAM_INT);
            $stmt->bindValue(':contact_id', isset($leadData['contact_id']) ? (int)$leadData['contact_id'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':email', $leadData['email'], PDO::PARAM_STR);
            $stmt->bindValue(':first_name', $leadData['first_name'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':last_name', $leadData['family_name'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':sync_status', 'pending', PDO::PARAM_STR);
            $stmt->bindValue(':segmentation_data', json_encode($segmentationData), PDO::PARAM_STR);
            $stmt->bindValue(':phplist_lists', json_encode($phplistLists), PDO::PARAM_STR);
            $stmt->bindValue(':opt_in_date', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            
            $stmt->execute();
            return $this->dbcrm()->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("PhpListSubscribers: Failed to create subscriber - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update existing subscriber record
     */
    public function updateSubscriberFromLead($leadId, $leadData)
    {
        try {
            $segmentationData = $this->prepareSegmentationData($leadData);
            $phplistLists = $this->determineSubscriberLists($leadData);
            
            $stmt = $this->dbcrm()->prepare("
                UPDATE phplist_subscribers SET
                    first_name = :first_name,
                    last_name = :last_name,
                    segmentation_data = :segmentation_data,
                    phplist_lists = :phplist_lists,
                    sync_status = 'pending',
                    updated_at = CURRENT_TIMESTAMP
                WHERE lead_id = :lead_id AND email = :email
            ");
            
            $stmt->bindValue(':lead_id', (int)$leadId, PDO::PARAM_INT);
            $stmt->bindValue(':email', $leadData['email'], PDO::PARAM_STR);
            $stmt->bindValue(':first_name', $leadData['first_name'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':last_name', $leadData['family_name'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':segmentation_data', json_encode($segmentationData), PDO::PARAM_STR);
            $stmt->bindValue(':phplist_lists', json_encode($phplistLists), PDO::PARAM_STR);
            
            $stmt->execute();
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("PhpListSubscribers: Failed to update subscriber - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if subscriber already exists
     */
    private function subscriberExists($leadId, $email)
    {
        try {
            $stmt = $this->dbcrm()->prepare("
                SELECT id FROM phplist_subscribers 
                WHERE lead_id = :lead_id AND email = :email
            ");
            $stmt->bindValue(':lead_id', (int)$leadId, PDO::PARAM_INT);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("PhpListSubscribers: Failed to check subscriber existence - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Prepare segmentation data for targeting
     */
    private function prepareSegmentationData($leadData)
    {
        return [
            'state' => $leadData['form_state'] ?? null,
            'country' => $leadData['form_country'] ?? 'US',
            'city' => $leadData['form_city'] ?? null,
            'service_type' => $leadData['services_interested_in'] ?? null,
            'structure_type' => $leadData['structure_type'] ?? null,
            'lead_source' => $leadData['hear_about'] ?? null,
            'business_name' => $leadData['business_name'] ?? null,
            'contact_type' => $leadData['ctype'] ?? 1
        ];
    }
    
    /**
     * Determine which phpList lists subscriber should join based on segmentation
     */
    private function determineSubscriberLists($leadData)
    {
        $lists = [];
        
        // Always add to default list
        if (!empty($this->config['phplist_default_list_id'])) {
            $lists[] = (int)$this->config['phplist_default_list_id'];
        }
        
        // Geographic segmentation
        if (!empty($leadData['form_state']) && !empty($this->config['phplist_geographic_lists'])) {
            $geoLists = json_decode($this->config['phplist_geographic_lists'], true);
            $stateKey = ($leadData['form_country'] ?? 'US') . '-' . $leadData['form_state'];
            if (isset($geoLists[$stateKey])) {
                $lists[] = (int)$geoLists[$stateKey];
            }
        }
        
        // Service-based segmentation
        if (!empty($leadData['structure_type']) && !empty($this->config['phplist_service_lists'])) {
            $serviceLists = json_decode($this->config['phplist_service_lists'], true);
            if (isset($serviceLists[$leadData['structure_type']])) {
                $lists[] = (int)$serviceLists[$leadData['structure_type']];
            }
        }
        
        // Source-based segmentation
        if (!empty($leadData['hear_about']) && !empty($this->config['phplist_source_lists'])) {
            $sourceLists = json_decode($this->config['phplist_source_lists'], true);
            if (isset($sourceLists[$leadData['hear_about']])) {
                $lists[] = (int)$sourceLists[$leadData['hear_about']];
            }
        }
        
        return array_unique($lists);
    }
    
    /**
     * Get pending subscribers for sync
     */
    public function getPendingSubscribers($limit = 50)
    {
        try {
            $maxAttempts = (int)($this->config['max_sync_attempts'] ?? 3);
            
            $stmt = $this->dbcrm()->prepare("
                SELECT ps.*, l.get_updates
                FROM phplist_subscribers ps
                JOIN leads l ON ps.lead_id = l.id
                WHERE ps.sync_status = 'pending' 
                AND ps.sync_attempts < :max_attempts
                AND l.get_updates = 1
                ORDER BY ps.created_at ASC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':max_attempts', $maxAttempts, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PhpListSubscribers: Failed to get pending subscribers - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update sync status after sync attempt
     */
    public function updateSyncStatus($subscriberId, $status, $phplistSubscriberId = null, $errorMessage = null)
    {
        try {
            $stmt = $this->dbcrm()->prepare("
                UPDATE phplist_subscribers SET
                    sync_status = :status,
                    sync_attempts = sync_attempts + 1,
                    last_sync_attempt = CURRENT_TIMESTAMP,
                    phplist_subscriber_id = :phplist_id,
                    error_message = :error_message,
                    last_successful_sync = CASE WHEN :status = 'synced' THEN CURRENT_TIMESTAMP ELSE last_successful_sync END
                WHERE id = :id
            ");
            
            $stmt->bindValue(':id', (int)$subscriberId, PDO::PARAM_INT);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':phplist_id', $phplistSubscriberId, PDO::PARAM_INT);
            $stmt->bindValue(':error_message', $errorMessage, PDO::PARAM_STR);
            
            $stmt->execute();
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("PhpListSubscribers: Failed to update sync status - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log sync operation
     */
    public function logSyncOperation($subscriberId, $syncType, $status, $phplistResponse = null, $errorDetails = null, $processingTime = null)
    {
        try {
            $stmt = $this->dbcrm()->prepare("
                INSERT INTO phplist_sync_log (
                    subscriber_id, sync_type, status, phplist_response, 
                    error_details, processing_time_ms
                ) VALUES (
                    :subscriber_id, :sync_type, :status, :phplist_response,
                    :error_details, :processing_time_ms
                )
            ");
            
            $stmt->bindValue(':subscriber_id', $subscriberId ? (int)$subscriberId : null, PDO::PARAM_INT);
            $stmt->bindValue(':sync_type', $syncType, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':phplist_response', $phplistResponse, PDO::PARAM_STR);
            $stmt->bindValue(':error_details', $errorDetails, PDO::PARAM_STR);
            $stmt->bindValue(':processing_time_ms', $processingTime, PDO::PARAM_INT);
            
            $stmt->execute();
            return true;
            
        } catch (PDOException $e) {
            error_log("PhpListSubscribers: Failed to log sync operation - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get configuration value
     */
    public function getConfig($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Check if sync is enabled
     */
    public function isSyncEnabled()
    {
        return $this->getConfig('sync_enabled', '0') === '1';
    }
    
    /**
     * Get subscriber statistics
     */
    public function getSubscriberStats()
    {
        try {
            $stmt = $this->dbcrm()->prepare("
                SELECT 
                    sync_status,
                    COUNT(*) as count
                FROM phplist_subscribers 
                GROUP BY sync_status
            ");
            $stmt->execute();
            
            $stats = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats[$row['sync_status']] = (int)$row['count'];
            }
            
            return $stats;
        } catch (PDOException $e) {
            error_log("PhpListSubscribers: Failed to get subscriber stats - " . $e->getMessage());
            return [];
        }
    }
}
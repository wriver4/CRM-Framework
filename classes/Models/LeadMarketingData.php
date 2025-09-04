<?php

/**
 * LeadMarketingData Class
 * 
 * Handles marketing attribution data for leads
 * Supports multiple marketing channels per lead
 * Provides methods for CRUD operations and reporting
 */

require_once dirname(__DIR__) . '/Core/Database.php';

class LeadMarketingData extends Database
{
    /**
     * Create marketing data entries for a lead
     * 
     * @param int $leadId Lead ID
     * @param array $marketingChannels Array of marketing channels
     * @param string $otherDetails Custom marketing channel details
     * @return bool Success status
     */
    public function createMarketingData($leadId, $marketingChannels = [], $otherDetails = '')
    {
        try {
            $pdo = $this->dbcrm();
            
            // If no marketing channels provided, return true (no error)
            if (empty($marketingChannels)) {
                return true;
            }
            
            // Calculate attribution weight (equal distribution across channels)
            $attributionWeight = count($marketingChannels) > 0 ? 1.00 / count($marketingChannels) : 1.00;
            
            // Prepare insert statement
            $stmt = $pdo->prepare("
                INSERT INTO lead_marketing_data 
                (lead_id, marketing_channel, marketing_channel_other, attribution_weight) 
                VALUES (:lead_id, :marketing_channel, :marketing_channel_other, :attribution_weight)
            ");
            
            // Insert each marketing channel
            foreach ($marketingChannels as $channel) {
                $stmt->bindValue(':lead_id', (int)$leadId, PDO::PARAM_INT);
                $stmt->bindValue(':marketing_channel', $channel, PDO::PARAM_STR);
                $stmt->bindValue(':marketing_channel_other', 
                    ($channel === 'other') ? $otherDetails : null, PDO::PARAM_STR);
                $stmt->bindValue(':attribution_weight', $attributionWeight, PDO::PARAM_STR);
                
                $stmt->execute();
                
                // Log if this channel requires special automation
                if ($this->requiresMarketingAutomation($channel)) {
                    $automationDetails = $this->getMarketingAutomationDetails($channel);
                    error_log("Marketing automation required: Lead ID {$leadId}, Channel: {$channel}, Type: {$automationDetails['automation_type']}");
                }
            }
            
            $stmt = null; // Close statement
            return true;
            
        } catch (PDOException $e) {
            error_log("LeadMarketingData::createMarketingData() Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get marketing data for a specific lead
     * 
     * @param int $leadId Lead ID
     * @return array Marketing data records
     */
    public function getMarketingDataByLead($leadId)
    {
        try {
            $pdo = $this->dbcrm();
            
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    marketing_channel,
                    marketing_channel_other,
                    attribution_weight,
                    campaign_source,
                    referral_details,
                    created_at,
                    updated_at
                FROM lead_marketing_data 
                WHERE lead_id = :lead_id 
                ORDER BY created_at ASC
            ");
            
            $stmt->bindValue(':lead_id', (int)$leadId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetchAll();
            $stmt = null;
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("LeadMarketingData::getMarketingDataByLead() Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update marketing data for a lead
     * 
     * @param int $leadId Lead ID
     * @param array $marketingChannels New marketing channels
     * @param string $otherDetails Custom marketing channel details
     * @return bool Success status
     */
    public function updateMarketingData($leadId, $marketingChannels = [], $otherDetails = '')
    {
        try {
            $pdo = $this->dbcrm();
            
            // Start transaction
            $pdo->beginTransaction();
            
            // Delete existing marketing data for this lead
            $deleteStmt = $pdo->prepare("DELETE FROM lead_marketing_data WHERE lead_id = :lead_id");
            $deleteStmt->bindValue(':lead_id', (int)$leadId, PDO::PARAM_INT);
            $deleteStmt->execute();
            $deleteStmt = null;
            
            // Insert new marketing data
            $success = $this->createMarketingData($leadId, $marketingChannels, $otherDetails);
            
            if ($success) {
                $pdo->commit();
                return true;
            } else {
                $pdo->rollBack();
                return false;
            }
            
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("LeadMarketingData::updateMarketingData() Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete marketing data for a lead
     * 
     * @param int $leadId Lead ID
     * @return bool Success status
     */
    public function deleteMarketingData($leadId)
    {
        try {
            $pdo = $this->dbcrm();
            
            $stmt = $pdo->prepare("DELETE FROM lead_marketing_data WHERE lead_id = :lead_id");
            $stmt->bindValue(':lead_id', (int)$leadId, PDO::PARAM_INT);
            $stmt->execute();
            
            $stmt = null;
            return true;
            
        } catch (PDOException $e) {
            error_log("LeadMarketingData::deleteMarketingData() Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get marketing attribution report
     * 
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Marketing attribution data
     */
    public function getMarketingAttributionReport($startDate = null, $endDate = null)
    {
        try {
            $pdo = $this->dbcrm();
            
            $whereClause = '';
            if ($startDate && $endDate) {
                $whereClause = "WHERE lmd.created_at BETWEEN :start_date AND :end_date";
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    lmd.marketing_channel,
                    COUNT(DISTINCT lmd.lead_id) as lead_count,
                    SUM(lmd.attribution_weight) as total_attribution,
                    AVG(lmd.attribution_weight) as avg_attribution,
                    COUNT(lmd.id) as total_entries,
                    MIN(lmd.created_at) as first_occurrence,
                    MAX(lmd.created_at) as last_occurrence
                FROM lead_marketing_data lmd
                INNER JOIN leads l ON lmd.lead_id = l.id
                {$whereClause}
                GROUP BY lmd.marketing_channel
                ORDER BY lead_count DESC, total_attribution DESC
            ");
            
            if ($startDate && $endDate) {
                $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
                $stmt->bindValue(':end_date', $endDate, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $result = $stmt->fetchAll();
            $stmt = null;
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("LeadMarketingData::getMarketingAttributionReport() Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get leads with multiple marketing channels (multi-touch attribution)
     * 
     * @return array Leads with multiple marketing touchpoints
     */
    public function getMultiTouchLeads()
    {
        try {
            $pdo = $this->dbcrm();
            
            $stmt = $pdo->prepare("
                SELECT 
                    l.id as lead_id,
                    l.lead_id as lead_number,
                    l.full_name,
                    l.email,
                    COUNT(lmd.id) as marketing_touchpoints,
                    GROUP_CONCAT(lmd.marketing_channel ORDER BY lmd.created_at) as marketing_channels,
                    l.created_at as lead_created_at
                FROM leads l
                INNER JOIN lead_marketing_data lmd ON l.id = lmd.lead_id
                GROUP BY l.id, l.lead_id, l.full_name, l.email, l.created_at
                HAVING COUNT(lmd.id) > 1
                ORDER BY marketing_touchpoints DESC, l.created_at DESC
            ");
            
            $stmt->execute();
            $result = $stmt->fetchAll();
            $stmt = null;
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("LeadMarketingData::getMultiTouchLeads() Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get marketing channel options for forms
     * 
     * @param array $lang Language array for translations
     * @return array Marketing channel options
     */
    public function getMarketingChannelOptions($lang)
    {
        return [
            'mass_mailing' => $lang['marketing_channel_mass_mailing'] ?? 'Mass Mailing',
            'tv_radio' => $lang['marketing_channel_tv_radio'] ?? 'TV/Radio Ad',
            'internet' => $lang['marketing_channel_internet'] ?? 'Internet Search',
            'neighbor' => $lang['marketing_channel_neighbor'] ?? 'Neighbor/Friend',
            'trade_show' => $lang['marketing_channel_trade_show'] ?? 'Trade Show',
            'insurance' => $lang['marketing_channel_insurance'] ?? 'Insurance Company',
            'referral' => $lang['marketing_channel_referral'] ?? 'Professional Referral',
            'other' => $lang['marketing_channel_other'] ?? 'Other'
        ];
    }
    
    /**
     * Get leads that require referral thank you automation
     * 
     * @param int $daysBack Number of days to look back
     * @return array Leads requiring referral thank you
     */
    public function getLeadsRequiringReferralThankYou($daysBack = 7)
    {
        try {
            $pdo = $this->dbcrm();
            
            $stmt = $pdo->prepare("
                SELECT DISTINCT 
                    l.id as lead_id,
                    l.lead_id as lead_number,
                    l.full_name,
                    l.email,
                    lmd.marketing_channel,
                    lmd.marketing_channel_other,
                    lmd.referral_details,
                    lmd.created_at as marketing_data_created
                FROM leads l
                INNER JOIN lead_marketing_data lmd ON l.id = lmd.lead_id
                WHERE lmd.marketing_channel = 'referral'
                    AND lmd.created_at >= DATE_SUB(NOW(), INTERVAL :days_back DAY)
                ORDER BY lmd.created_at DESC
            ");
            
            $stmt->bindValue(':days_back', (int)$daysBack, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();
            $stmt = null;
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("LeadMarketingData::getLeadsRequiringReferralThankYou() Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get leads that require insurance follow-up
     * 
     * @param int $daysBack Number of days to look back
     * @return array Leads requiring insurance follow-up
     */
    public function getLeadsRequiringInsuranceFollowup($daysBack = 3)
    {
        try {
            $pdo = $this->dbcrm();
            
            $stmt = $pdo->prepare("
                SELECT DISTINCT 
                    l.id as lead_id,
                    l.lead_id as lead_number,
                    l.full_name,
                    l.email,
                    lmd.marketing_channel,
                    lmd.marketing_channel_other,
                    lmd.referral_details,
                    lmd.created_at as marketing_data_created
                FROM leads l
                INNER JOIN lead_marketing_data lmd ON l.id = lmd.lead_id
                WHERE lmd.marketing_channel = 'insurance'
                    AND lmd.created_at >= DATE_SUB(NOW(), INTERVAL :days_back DAY)
                ORDER BY lmd.created_at DESC
            ");
            
            $stmt->bindValue(':days_back', (int)$daysBack, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();
            $stmt = null;
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("LeadMarketingData::getLeadsRequiringInsuranceFollowup() Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if a marketing channel requires special automation
     * 
     * @param string $marketingChannel Marketing channel to check
     * @return bool True if automation is required
     */
    public function requiresMarketingAutomation($marketingChannel)
    {
        $specialChannels = ['referral', 'insurance'];
        return in_array($marketingChannel, $specialChannels);
    }
    
    /**
     * Get automation details for a marketing channel
     * 
     * @param string $marketingChannel Marketing channel
     * @return array|null Automation details or null if not applicable
     */
    public function getMarketingAutomationDetails($marketingChannel)
    {
        $automationConfig = [
            'referral' => [
                'automation_type' => 'referral_thank_you',
                'requires_followup' => true,
                'followup_days' => 7,
                'automation_template' => 'referral_thank_you_email',
                'description' => 'Send thank you note for referral'
            ],
            'insurance' => [
                'automation_type' => 'insurance_followup',
                'requires_followup' => true,
                'followup_days' => 3,
                'automation_template' => 'insurance_followup_email',
                'description' => 'Follow up on insurance company lead'
            ]
        ];
        
        return $automationConfig[$marketingChannel] ?? null;
    }
}
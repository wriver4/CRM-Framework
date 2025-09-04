<?php
/**
 * Marketing Automation Script
 * 
 * Processes marketing automation tasks for special channels:
 * - Referral thank you notes
 * - Insurance company follow-ups
 * 
 * This script can be run manually or via cron job
 */

// Include required classes
require_once dirname(__DIR__) . '/classes/Models/LeadMarketingData.php';
require_once dirname(__DIR__) . '/classes/Utilities/Helpers.php';
require_once dirname(__DIR__) . '/classes/Logging/Audit.php';

// Load language file
$lang = include dirname(__DIR__) . '/public_html/admin/languages/en.php';

class MarketingAutomation
{
    private $leadMarketingData;
    private $helpers;
    private $audit;
    private $lang;
    
    public function __construct($lang)
    {
        $this->leadMarketingData = new LeadMarketingData();
        $this->helpers = new Helpers();
        $this->audit = new Audit();
        $this->lang = $lang;
    }
    
    /**
     * Process all marketing automation tasks
     */
    public function processAll()
    {
        echo "=== Marketing Automation Processing Started ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        $this->processReferralThankYou();
        $this->processInsuranceFollowup();
        
        echo "\n=== Marketing Automation Processing Completed ===\n";
    }
    
    /**
     * Process referral thank you automation
     */
    public function processReferralThankYou()
    {
        echo "--- Processing Referral Thank You Automation ---\n";
        
        $leads = $this->leadMarketingData->getLeadsRequiringReferralThankYou(7);
        
        if (empty($leads)) {
            echo "No leads requiring referral thank you found.\n";
            return;
        }
        
        echo "Found " . count($leads) . " leads requiring referral thank you:\n";
        
        foreach ($leads as $lead) {
            echo "- Lead #{$lead['lead_number']}: {$lead['full_name']} ({$lead['email']})\n";
            echo "  Marketing Channel: {$lead['marketing_channel']}\n";
            echo "  Created: {$lead['marketing_data_created']}\n";
            
            // TODO: Implement actual thank you note sending
            // This could integrate with:
            // - Email system (phpmailer, sendgrid, etc.)
            // - CRM task creation
            // - External marketing automation platform
            
            $this->logAutomationAction($lead['lead_id'], 'referral_thank_you', 'processed');
            echo "  Status: Thank you automation logged\n\n";
        }
    }
    
    /**
     * Process insurance follow-up automation
     */
    public function processInsuranceFollowup()
    {
        echo "--- Processing Insurance Follow-up Automation ---\n";
        
        $leads = $this->leadMarketingData->getLeadsRequiringInsuranceFollowup(3);
        
        if (empty($leads)) {
            echo "No leads requiring insurance follow-up found.\n";
            return;
        }
        
        echo "Found " . count($leads) . " leads requiring insurance follow-up:\n";
        
        foreach ($leads as $lead) {
            echo "- Lead #{$lead['lead_number']}: {$lead['full_name']} ({$lead['email']})\n";
            echo "  Marketing Channel: {$lead['marketing_channel']}\n";
            echo "  Created: {$lead['marketing_data_created']}\n";
            
            // TODO: Implement actual insurance follow-up
            // This could integrate with:
            // - Email system for follow-up emails
            // - Task creation for sales team
            // - CRM workflow triggers
            // - Insurance company notification system
            
            $this->logAutomationAction($lead['lead_id'], 'insurance_followup', 'processed');
            echo "  Status: Insurance follow-up automation logged\n\n";
        }
    }
    
    /**
     * Log automation action (placeholder for future automation log table)
     */
    private function logAutomationAction($leadId, $automationType, $status)
    {
        // For now, just log to error log
        // In the future, this could write to a marketing_automation_log table
        error_log("Marketing Automation: Lead ID {$leadId}, Type: {$automationType}, Status: {$status}");
        
        // Also create audit trail
        $this->audit->log(
            null, // No user ID for automated actions
            'marketing_automation',
            'lead_marketing_data',
            $leadId,
            null, // No old values
            json_encode([
                'automation_type' => $automationType,
                'status' => $status,
                'processed_at' => date('Y-m-d H:i:s')
            ]),
            '127.0.0.1' // System IP for automated actions
        );
    }
    
    /**
     * Generate marketing automation report
     */
    public function generateReport()
    {
        echo "=== Marketing Automation Report ===\n";
        echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Referral leads summary
        $referralLeads = $this->leadMarketingData->getLeadsRequiringReferralThankYou(30);
        echo "Referral Leads (Last 30 days): " . count($referralLeads) . "\n";
        
        // Insurance leads summary
        $insuranceLeads = $this->leadMarketingData->getLeadsRequiringInsuranceFollowup(30);
        echo "Insurance Leads (Last 30 days): " . count($insuranceLeads) . "\n";
        
        // Marketing attribution summary
        $attribution = $this->leadMarketingData->getMarketingAttributionReport(
            date('Y-m-d', strtotime('-30 days')),
            date('Y-m-d')
        );
        
        echo "\nMarketing Channel Performance (Last 30 days):\n";
        foreach ($attribution as $channel) {
            $channelName = $this->helpers->get_marketing_channel_options($this->lang)[$channel['marketing_channel']] ?? $channel['marketing_channel'];
            echo "- {$channelName}: {$channel['lead_count']} leads\n";
        }
        
        echo "\n=== End Report ===\n";
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $automation = new MarketingAutomation($lang);
    
    $command = $argv[1] ?? 'help';
    
    switch ($command) {
        case 'process':
            $automation->processAll();
            break;
            
        case 'referral':
            $automation->processReferralThankYou();
            break;
            
        case 'insurance':
            $automation->processInsuranceFollowup();
            break;
            
        case 'report':
            $automation->generateReport();
            break;
            
        case 'help':
        default:
            echo "Marketing Automation Script\n";
            echo "Usage: php marketing_automation.php [command]\n\n";
            echo "Commands:\n";
            echo "  process   - Process all automation tasks\n";
            echo "  referral  - Process referral thank you automation only\n";
            echo "  insurance - Process insurance follow-up automation only\n";
            echo "  report    - Generate marketing automation report\n";
            echo "  help      - Show this help message\n\n";
            echo "Examples:\n";
            echo "  php marketing_automation.php process\n";
            echo "  php marketing_automation.php report\n";
            break;
    }
} else {
    // Web interface (basic)
    echo "<h1>Marketing Automation</h1>";
    echo "<p>This script should be run from command line.</p>";
    echo "<p>Usage: <code>php marketing_automation.php [command]</code></p>";
}
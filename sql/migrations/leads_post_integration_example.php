<?php
/**
 * EXAMPLE: Integration of LeadMarketingData into leads/post.php
 * 
 * This file shows how to modify the existing leads/post.php to use the new
 * lead_marketing_data table instead of storing marketing data directly in leads table.
 * 
 * INSTRUCTIONS:
 * 1. First run create_lead_marketing_data_table.sql to create the table
 * 2. Add the LeadMarketingData class to your autoloader or require it
 * 3. Integrate the code below into your existing leads/post.php
 */

// Add this near the top of leads/post.php after other class includes
require_once dirname(__DIR__, 2) . '/classes/Models/LeadMarketingData.php';

// Example of how to modify the data processing section:

// BEFORE (current approach):
/*
$data = [
    // ... other fields ...
    'hear_about' => sanitize_input($_POST['hear_about'] ?? ''),
    'hear_about_other' => sanitize_input($_POST['hear_about_other'] ?? ''),
    // ... other fields ...
];
*/

// AFTER (new approach with marketing data table):
$data = [
    // ... other fields (remove hear_about and hear_about_other) ...
    
    // Marketing information - processed separately
    'get_updates' => isset($_POST['get_updates']) ? 1 : 0,
    
    // ... other fields ...
];

// Process marketing data separately
$marketingChannels = $_POST['hear_about'] ?? [];
$marketingOtherDetails = sanitize_input($_POST['hear_about_other'] ?? '');

// Ensure marketing channels is an array
if (!is_array($marketingChannels)) {
    $marketingChannels = !empty($marketingChannels) ? [$marketingChannels] : [];
}

// Sanitize marketing channels
$marketingChannels = array_map('sanitize_input', $marketingChannels);

// Example of integration after successful lead creation:

// AFTER the lead is successfully created (around line 180 in current post.php):
/*
if ($result['success']) {
    // ... existing success handling ...
    
    // NEW: Add marketing data handling
    try {
        $leadMarketingData = new LeadMarketingData();
        
        // Create marketing data entries
        $marketingSuccess = $leadMarketingData->createMarketingData(
            $result['lead_id'], 
            $marketingChannels, 
            $marketingOtherDetails
        );
        
        if ($marketingSuccess) {
            // Log successful marketing data creation
            $audit->log(
                $_SESSION['user_id'] ?? 1,
                'marketing_data_created',
                "lead_{$result['lead_id']}",
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                $result['lead_id'],
                "Marketing data created for lead {$result['lead_id']}: " . implode(', ', $marketingChannels)
            );
        } else {
            // Log marketing data creation failure (but don't fail the lead creation)
            error_log("Failed to create marketing data for lead {$result['lead_id']}");
        }
        
    } catch (Exception $e) {
        // Log marketing data error but don't fail the lead creation
        error_log("Marketing data integration error for lead {$result['lead_id']}: " . $e->getMessage());
        
        $audit->log(
            $_SESSION['user_id'] ?? 1,
            'marketing_data_error',
            "lead_{$result['lead_id']}",
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $result['lead_id'],
            "Marketing data integration failed: " . $e->getMessage()
        );
    }
    
    // ... continue with existing phpList integration and other processing ...
}
*/

// Example of how to display marketing data in lead edit/view pages:

function displayLeadMarketingData($leadId, $lang) {
    try {
        $leadMarketingData = new LeadMarketingData();
        $marketingData = $leadMarketingData->getMarketingDataByLead($leadId);
        
        if (!empty($marketingData)) {
            echo '<div class="marketing-data-section">';
            echo '<h5>' . ($lang['marketing_attribution'] ?? 'Marketing Attribution') . '</h5>';
            
            foreach ($marketingData as $data) {
                $channelName = $leadMarketingData->getMarketingChannelOptions($lang)[$data['marketing_channel']] ?? $data['marketing_channel'];
                
                echo '<div class="marketing-channel-item">';
                echo '<strong>' . htmlspecialchars($channelName) . '</strong>';
                
                if (!empty($data['marketing_channel_other'])) {
                    echo ' - ' . htmlspecialchars($data['marketing_channel_other']);
                }
                
                if ($data['attribution_weight'] < 1.00) {
                    echo ' <small class="text-muted">(' . round($data['attribution_weight'] * 100) . '% attribution)</small>';
                }
                
                echo '<br><small class="text-muted">Added: ' . date('M j, Y', strtotime($data['created_at'])) . '</small>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        
    } catch (Exception $e) {
        error_log("Error displaying marketing data for lead {$leadId}: " . $e->getMessage());
    }
}

// Example of marketing attribution report:

function generateMarketingReport($startDate = null, $endDate = null) {
    try {
        $leadMarketingData = new LeadMarketingData();
        $reportData = $leadMarketingData->getMarketingAttributionReport($startDate, $endDate);
        
        echo '<div class="marketing-report">';
        echo '<h3>Marketing Attribution Report</h3>';
        
        if ($startDate && $endDate) {
            echo '<p>Period: ' . date('M j, Y', strtotime($startDate)) . ' - ' . date('M j, Y', strtotime($endDate)) . '</p>';
        }
        
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Marketing Channel</th>';
        echo '<th>Lead Count</th>';
        echo '<th>Total Attribution</th>';
        echo '<th>Avg Attribution</th>';
        echo '<th>First/Last Occurrence</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($reportData as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['marketing_channel']) . '</td>';
            echo '<td>' . $row['lead_count'] . '</td>';
            echo '<td>' . round($row['total_attribution'], 2) . '</td>';
            echo '<td>' . round($row['avg_attribution'], 2) . '</td>';
            echo '<td>' . date('M j', strtotime($row['first_occurrence'])) . ' - ' . date('M j', strtotime($row['last_occurrence'])) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
    } catch (Exception $e) {
        error_log("Error generating marketing report: " . $e->getMessage());
        echo '<div class="alert alert-danger">Error generating marketing report.</div>';
    }
}

/**
 * MIGRATION NOTES:
 * 
 * 1. Database Changes:
 *    - Run create_lead_marketing_data_table.sql
 *    - Optionally migrate existing hear_about data using the migration query in that file
 *    - Consider keeping the original hear_about fields for backward compatibility initially
 * 
 * 2. Form Changes:
 *    - The form in leads/new.php should continue to work as-is
 *    - The hear_about[] checkbox array will be properly handled by the new system
 * 
 * 3. Reporting Benefits:
 *    - Better attribution tracking for multi-channel leads
 *    - Easier to generate marketing ROI reports
 *    - Support for campaign tracking and referral details
 * 
 * 4. Backward Compatibility:
 *    - Keep original hear_about fields in leads table initially
 *    - Gradually migrate reports and displays to use new table
 *    - Remove old fields after full migration and testing
 */
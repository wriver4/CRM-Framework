<?php

/**
 * EmailRenderer Utility
 * Renders email templates with variable substitution
 * Uses full_name from source tables instead of concatenating first_name + family_name
 */

class EmailRenderer extends Database
{
    private $emailTemplate;

    public function __construct()
    {
        parent::__construct();
        $this->emailTemplate = new EmailTemplate();
    }

    /**
     * Render complete email (header + body + footer)
     * @param int $templateId
     * @param string $module
     * @param int $recordId
     * @param string $languageCode
     * @return array ['subject' => '', 'html' => '', 'plain_text' => '']
     */
    public function renderEmail($templateId, $module, $recordId, $languageCode = 'en')
    {
        try {
            // Get template content
            $content = $this->emailTemplate->getTemplateContent($templateId, $languageCode);
            if (!$content) {
                throw new Exception("Template content not found for template ID: $templateId");
            }

            // Get global header and footer
            $header = $this->emailTemplate->getGlobalTemplate('header', $languageCode);
            $footer = $this->emailTemplate->getGlobalTemplate('footer', $languageCode);

            // Get variables for this template
            $variables = $this->emailTemplate->getTemplateVariables($templateId);
            
            // Fetch data and build variable map
            $variableMap = $this->buildVariableMap($module, $recordId, $variables);

            // Render subject
            $subject = $this->replaceVariables($content['subject'], $variableMap);

            // Render HTML body
            $bodyHtml = $this->replaceVariables($content['body_html'], $variableMap);
            $fullHtml = ($header['html_content'] ?? '') . $bodyHtml . ($footer['html_content'] ?? '');

            // Render plain text body
            $bodyPlainText = $this->replaceVariables($content['body_plain_text'] ?? '', $variableMap);
            $fullPlainText = ($header['plain_text_content'] ?? '') . $bodyPlainText . ($footer['plain_text_content'] ?? '');

            return [
                'subject' => $subject,
                'html' => $fullHtml,
                'plain_text' => $fullPlainText,
                'variables' => $variableMap
            ];
        } catch (Exception $e) {
            error_log("EmailRenderer::renderEmail() Error: " . $e->getMessage());
            return [
                'subject' => 'Error rendering email',
                'html' => 'An error occurred while rendering this email.',
                'plain_text' => 'An error occurred while rendering this email.',
                'variables' => []
            ];
        }
    }

    /**
     * Build variable map from database
     * Uses full_name from source tables (leads.full_name, users.full_name, contacts.full_name)
     * @param string $module
     * @param int $recordId
     * @param array $variables
     * @return array
     */
    private function buildVariableMap($module, $recordId, $variables)
    {
        $variableMap = [];
        
        // Fetch record data based on module
        $recordData = $this->fetchRecordData($module, $recordId);
        if (!$recordData) {
            return $variableMap;
        }

        // Process each variable
        foreach ($variables as $variable) {
            $value = $this->resolveVariableValue($variable, $recordData, $module, $recordId);
            $variableMap[$variable['variable_key']] = $value;
        }

        return $variableMap;
    }

    /**
     * Fetch record data from database
     * @param string $module
     * @param int $recordId
     * @return array|null
     */
    private function fetchRecordData($module, $recordId)
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
                    $sql = "SELECT c.*, u.full_name as assigned_user_name
                            FROM contacts c
                            LEFT JOIN leads l ON c.lead_id = l.id
                            LEFT JOIN users u ON l.last_edited_by = u.id
                            WHERE c.id = :id";
                    break;
                
                case 'referrals':
                    $sql = "SELECT r.*, u.full_name as assigned_user_name
                            FROM referrals r
                            LEFT JOIN users u ON r.last_edited_by = u.id
                            WHERE r.id = :id";
                    break;
                
                case 'prospects':
                    $sql = "SELECT p.*, u.full_name as assigned_user_name
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
            error_log("EmailRenderer::fetchRecordData() Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Resolve variable value from record data
     * @param array $variable
     * @param array $recordData
     * @param string $module
     * @param int $recordId
     * @return string
     */
    private function resolveVariableValue($variable, $recordData, $module, $recordId)
    {
        $source = $variable['variable_source'];
        
        // Handle direct database fields (e.g., leads.full_name, users.full_name)
        if (strpos($source, '.') !== false) {
            list($table, $field) = explode('.', $source, 2);
            
            // Map table.field to actual record data
            switch ($table) {
                case 'leads':
                case 'contacts':
                case 'referrals':
                case 'prospects':
                    // Direct field from main record
                    return $recordData[$field] ?? '';
                
                case 'users':
                    // Assigned user fields
                    if ($field === 'full_name') {
                        return $recordData['assigned_user_name'] ?? '';
                    } elseif ($field === 'email') {
                        return $recordData['assigned_user_email'] ?? '';
                    }
                    break;
            }
        }
        
        // Handle generated/computed values
        if (strpos($source, 'generated.') === 0) {
            $generatedField = str_replace('generated.', '', $source);
            return $this->generateValue($generatedField, $module, $recordId, $recordData);
        }
        
        // Handle config values
        if (strpos($source, 'config.') === 0) {
            $configField = str_replace('config.', '', $source);
            return $this->getConfigValue($configField);
        }
        
        return '';
    }

    /**
     * Generate computed values (URLs, formatted dates, etc.)
     * @param string $field
     * @param string $module
     * @param int $recordId
     * @param array $recordData
     * @return string
     */
    private function generateValue($field, $module, $recordId, $recordData)
    {
        global $config;
        
        switch ($field) {
            case 'lead_url':
                $baseUrl = $config['base_url'] ?? 'https://crm.waveguard.com';
                return $baseUrl . "/leads/view.php?id=" . $recordId;
            
            case 'contact_url':
                $baseUrl = $config['base_url'] ?? 'https://crm.waveguard.com';
                return $baseUrl . "/contacts/view.php?id=" . $recordId;
            
            case 'referral_url':
                $baseUrl = $config['base_url'] ?? 'https://crm.waveguard.com';
                return $baseUrl . "/referrals/view.php?id=" . $recordId;
            
            case 'prospect_url':
                $baseUrl = $config['base_url'] ?? 'https://crm.waveguard.com';
                return $baseUrl . "/prospects/view.php?id=" . $recordId;
            
            case 'current_date':
                return date('F j, Y');
            
            case 'current_year':
                return date('Y');
            
            default:
                return '';
        }
    }

    /**
     * Get configuration value
     * @param string $key
     * @return string
     */
    private function getConfigValue($key)
    {
        global $config;
        
        switch ($key) {
            case 'homeowner_package_url':
                return $config['homeowner_package_url'] ?? 'https://waveguard.com/homeowner-package';
            
            case 'company_name':
                return $config['company_name'] ?? 'WaveGuard';
            
            case 'company_phone':
                return $config['company_phone'] ?? '';
            
            case 'company_email':
                return $config['company_email'] ?? 'info@waveguard.com';
            
            case 'support_email':
                return $config['support_email'] ?? 'support@waveguard.com';
            
            default:
                return $config[$key] ?? '';
        }
    }

    /**
     * Replace variables in text with actual values
     * @param string $text
     * @param array $variableMap
     * @return string
     */
    private function replaceVariables($text, $variableMap)
    {
        foreach ($variableMap as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        
        // Remove any unreplaced variables
        $text = preg_replace('/\{\{[^}]+\}\}/', '', $text);
        
        return $text;
    }

    /**
     * Preview email with sample data (for admin interface)
     * @param int $templateId
     * @param string $languageCode
     * @param array $sampleData Optional sample data override
     * @return array
     */
    public function previewEmail($templateId, $languageCode = 'en', $sampleData = [])
    {
        try {
            // Get template content
            $content = $this->emailTemplate->getTemplateContent($templateId, $languageCode);
            if (!$content) {
                throw new Exception("Template content not found");
            }

            // Get global header and footer
            $header = $this->emailTemplate->getGlobalTemplate('header', $languageCode);
            $footer = $this->emailTemplate->getGlobalTemplate('footer', $languageCode);

            // Get variables
            $variables = $this->emailTemplate->getTemplateVariables($templateId);
            
            // Build sample variable map
            $variableMap = [];
            foreach ($variables as $variable) {
                if (isset($sampleData[$variable['variable_key']])) {
                    $variableMap[$variable['variable_key']] = $sampleData[$variable['variable_key']];
                } else {
                    // Generate sample data
                    $variableMap[$variable['variable_key']] = $this->generateSampleValue($variable);
                }
            }

            // Render
            $subject = $this->replaceVariables($content['subject'], $variableMap);
            $bodyHtml = $this->replaceVariables($content['body_html'], $variableMap);
            $fullHtml = ($header['html_content'] ?? '') . $bodyHtml . ($footer['html_content'] ?? '');
            $bodyPlainText = $this->replaceVariables($content['body_plain_text'] ?? '', $variableMap);
            $fullPlainText = ($header['plain_text_content'] ?? '') . $bodyPlainText . ($footer['plain_text_content'] ?? '');

            return [
                'subject' => $subject,
                'html' => $fullHtml,
                'plain_text' => $fullPlainText,
                'variables' => $variableMap
            ];
        } catch (Exception $e) {
            error_log("EmailRenderer::previewEmail() Error: " . $e->getMessage());
            return [
                'subject' => 'Preview Error',
                'html' => 'Error generating preview',
                'plain_text' => 'Error generating preview',
                'variables' => []
            ];
        }
    }

    /**
     * Generate sample value for preview
     * @param array $variable
     * @return string
     */
    private function generateSampleValue($variable)
    {
        switch ($variable['variable_type']) {
            case 'text':
                if (strpos($variable['variable_key'], 'name') !== false) {
                    return 'John Smith';
                } elseif (strpos($variable['variable_key'], 'address') !== false) {
                    return '123 Main Street, Los Angeles, CA 90001';
                } elseif (strpos($variable['variable_key'], 'company') !== false) {
                    return 'Sample Company Inc.';
                }
                return 'Sample Text';
            
            case 'email':
                return 'sample@example.com';
            
            case 'phone':
                return '(555) 123-4567';
            
            case 'url':
                return 'https://example.com/sample';
            
            case 'date':
                return date('F j, Y');
            
            case 'currency':
                return '$10,000';
            
            default:
                return 'Sample Value';
        }
    }
}
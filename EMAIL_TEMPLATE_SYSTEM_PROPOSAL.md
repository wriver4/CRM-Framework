# 📧 Automated Email Template System - Complete Proposal

## Executive Summary

A comprehensive, multilingual email template system for automatically sending stage-based emails across all CRM modules (leads, referrals, prospects, etc.) with configurable approval workflows, inherited header/footer templates, selective communication logging, and future SMS support.

---

## 1. System Architecture

### Core Features
✅ **Automatic Email Triggers** - Send emails based on stage changes, assignments, and events  
✅ **Stage-Based Recipients** - Different receivers based on lead/referral stage  
✅ **Configurable Approval** - Per-template automatic or manual approval workflow  
✅ **Module-Specific Templates** - Each module has its own templates  
✅ **Inherited Header/Footer** - Shared branding across all modules  
✅ **Selective Logging** - Log important emails in communications table  
✅ **Multilingual Support** - Full English/Spanish translation support  
✅ **Future SMS Ready** - Database structure supports SMS expansion  

---

## 2. Database Schema

### 2.1 Email Template Tables

```sql
-- =====================================================
-- Email Template System Database Schema
-- Date: 2025-01-12
-- Purpose: Automated multilingual email system
-- =====================================================

-- Global Email Header/Footer (Inherited by all modules)
CREATE TABLE `email_global_templates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_type` ENUM('header', 'footer') NOT NULL,
  `language_code` VARCHAR(5) NOT NULL DEFAULT 'en',
  `html_content` TEXT NOT NULL,
  `plain_text_content` TEXT,
  `active` TINYINT(1) DEFAULT 1,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` INT(11),
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_language` (`template_type`, `language_code`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Global header/footer templates inherited by all modules';

-- Email Templates (Module-specific)
CREATE TABLE `email_templates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_key` VARCHAR(100) NOT NULL COMMENT 'Unique identifier (e.g., lead_welcome, lead_assigned)',
  `template_name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `module` VARCHAR(50) NOT NULL COMMENT 'leads, referrals, prospects, contacts, users',
  `category` VARCHAR(50) DEFAULT 'general' COMMENT 'welcome, status_change, assignment, reminder',
  `trigger_event` VARCHAR(100) COMMENT 'stage_change, assignment, manual, scheduled',
  `trigger_conditions` JSON COMMENT 'Conditions for automatic sending',
  `requires_approval` TINYINT(1) DEFAULT 0 COMMENT '0=auto send, 1=requires approval',
  `log_to_communications` TINYINT(1) DEFAULT 1 COMMENT '1=log in communications table',
  `supports_sms` TINYINT(1) DEFAULT 0 COMMENT 'Future: can be sent as SMS',
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT(11),
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_key` (`template_key`),
  KEY `module` (`module`),
  KEY `trigger_event` (`trigger_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Module-specific email templates with trigger configuration';

-- Email Template Content (Multilingual body content)
CREATE TABLE `email_template_content` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_id` INT(11) NOT NULL,
  `language_code` VARCHAR(5) NOT NULL DEFAULT 'en',
  `subject` VARCHAR(255) NOT NULL,
  `body_html` TEXT NOT NULL COMMENT 'Main email body with shortcodes',
  `body_plain_text` TEXT COMMENT 'Plain text version',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` INT(11),
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_language` (`template_id`, `language_code`),
  FOREIGN KEY (`template_id`) REFERENCES `email_templates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Multilingual content for email templates (body only, header/footer inherited)';

-- Email Template Variables (Available shortcodes per template)
CREATE TABLE `email_template_variables` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_id` INT(11) NOT NULL,
  `variable_key` VARCHAR(100) NOT NULL COMMENT 'e.g., lead_name, company_name, assigned_user',
  `variable_label` VARCHAR(255) NOT NULL,
  `variable_description` TEXT,
  `variable_type` VARCHAR(50) DEFAULT 'text' COMMENT 'text, date, currency, url, phone',
  `variable_source` VARCHAR(100) COMMENT 'Database field or method to get value',
  `sort_order` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  FOREIGN KEY (`template_id`) REFERENCES `email_templates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Available variables/shortcodes for each template';

-- Email Queue (Pending emails awaiting approval or scheduled sending)
CREATE TABLE `email_queue` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_id` INT(11) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `record_id` INT(11) NOT NULL COMMENT 'ID of lead, referral, etc.',
  `recipient_email` VARCHAR(255) NOT NULL,
  `recipient_name` VARCHAR(255),
  `language_code` VARCHAR(5) DEFAULT 'en',
  `subject` VARCHAR(255) NOT NULL,
  `body_html` TEXT NOT NULL,
  `body_plain_text` TEXT,
  `variables_json` JSON COMMENT 'Rendered variables for this email',
  `status` ENUM('pending', 'approved', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
  `requires_approval` TINYINT(1) DEFAULT 0,
  `approved_by` INT(11),
  `approved_at` DATETIME,
  `scheduled_send_at` DATETIME COMMENT 'For future scheduled emails',
  `sent_at` DATETIME,
  `error_message` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_by` INT(11),
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `module_record` (`module`, `record_id`),
  KEY `scheduled_send_at` (`scheduled_send_at`),
  FOREIGN KEY (`template_id`) REFERENCES `email_templates`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Queue for emails pending approval or scheduled sending';

-- Email Send Log (History of all sent emails)
CREATE TABLE `email_send_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_id` INT(11),
  `queue_id` INT(11) COMMENT 'Reference to email_queue if applicable',
  `module` VARCHAR(50) NOT NULL,
  `record_id` INT(11) NOT NULL,
  `recipient_email` VARCHAR(255) NOT NULL,
  `recipient_name` VARCHAR(255),
  `subject` VARCHAR(255) NOT NULL,
  `body_html` TEXT,
  `body_plain_text` TEXT,
  `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `sent_by` INT(11) COMMENT 'User who triggered/approved',
  `send_method` ENUM('automatic', 'manual', 'scheduled') DEFAULT 'automatic',
  `logged_to_communications` TINYINT(1) DEFAULT 0,
  `communication_id` INT(11) COMMENT 'Reference to communications table if logged',
  `success` TINYINT(1) DEFAULT 1,
  `error_message` TEXT,
  PRIMARY KEY (`id`),
  KEY `module_record` (`module`, `record_id`),
  KEY `sent_at` (`sent_at`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Complete history of all sent emails';

-- Email Trigger Rules (Stage-based automatic email triggers)
CREATE TABLE `email_trigger_rules` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_id` INT(11) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `trigger_type` ENUM('stage_change', 'assignment', 'field_update', 'time_based') NOT NULL,
  `trigger_condition` JSON NOT NULL COMMENT 'Conditions: stage_from, stage_to, field_name, etc.',
  `recipient_type` ENUM('lead_contact', 'assigned_user', 'custom_email', 'both') DEFAULT 'lead_contact',
  `custom_recipient_email` VARCHAR(255) COMMENT 'For custom_email recipient type',
  `delay_minutes` INT(11) DEFAULT 0 COMMENT 'Delay before sending (0=immediate)',
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_by` INT(11),
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  KEY `module_trigger` (`module`, `trigger_type`),
  FOREIGN KEY (`template_id`) REFERENCES `email_templates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Rules for automatic email triggering based on events';
```

### 2.2 Sample Data - Initial Templates

```sql
-- =====================================================
-- Sample Email Templates for Leads Module
-- =====================================================

-- Global Header (English)
INSERT INTO `email_global_templates` (`template_type`, `language_code`, `html_content`, `plain_text_content`) VALUES
('header', 'en', 
'<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background-color: #0066cc; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>WaveGuard CRM</h1>
    </div>
    <div class="content">',
'WaveGuard CRM\n\n');

-- Global Header (Spanish)
INSERT INTO `email_global_templates` (`template_type`, `language_code`, `html_content`, `plain_text_content`) VALUES
('header', 'es', 
'<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background-color: #0066cc; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>WaveGuard CRM</h1>
    </div>
    <div class="content">',
'WaveGuard CRM\n\n');

-- Global Footer (English)
INSERT INTO `email_global_templates` (`template_type`, `language_code`, `html_content`, `plain_text_content`) VALUES
('footer', 'en',
'    </div>
    <div style="background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #666;">
        <p>© 2025 WaveGuard. All rights reserved.</p>
        <p>This email was sent from an automated system. Please do not reply directly to this email.</p>
    </div>
</body>
</html>',
'\n\n---\n© 2025 WaveGuard. All rights reserved.\nThis email was sent from an automated system.');

-- Global Footer (Spanish)
INSERT INTO `email_global_templates` (`template_type`, `language_code`, `html_content`, `plain_text_content`) VALUES
('footer', 'es',
'    </div>
    <div style="background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #666;">
        <p>© 2025 WaveGuard. Todos los derechos reservados.</p>
        <p>Este correo fue enviado desde un sistema automatizado. Por favor no responda directamente a este correo.</p>
    </div>
</body>
</html>',
'\n\n---\n© 2025 WaveGuard. Todos los derechos reservados.\nEste correo fue enviado desde un sistema automatizado.');

-- Lead Welcome Email Template
INSERT INTO `email_templates` 
(`template_key`, `template_name`, `description`, `module`, `category`, `trigger_event`, `requires_approval`, `log_to_communications`, `active`) 
VALUES
('lead_welcome', 'Lead Welcome Email', 'Sent when a new lead is created', 'leads', 'welcome', 'stage_change', 0, 1, 1);

SET @lead_welcome_id = LAST_INSERT_ID();

-- Lead Welcome Content (English)
INSERT INTO `email_template_content` (`template_id`, `language_code`, `subject`, `body_html`, `body_plain_text`) VALUES
(@lead_welcome_id, 'en', 'Welcome to WaveGuard - {{lead_name}}',
'<h2>Welcome {{lead_name}}!</h2>
<p>Thank you for your interest in WaveGuard wildfire protection systems.</p>
<p>We have received your inquiry for your property at:</p>
<p><strong>{{property_address}}</strong></p>
<p>Your assigned representative is <strong>{{assigned_user}}</strong> who will be in contact with you shortly.</p>
<p>In the meantime, feel free to review our <a href="{{homeowner_package_link}}">Homeowner Information Package</a>.</p>
<p>Best regards,<br>The WaveGuard Team</p>',
'Welcome {{lead_name}}!\n\nThank you for your interest in WaveGuard wildfire protection systems.\n\nWe have received your inquiry for your property at:\n{{property_address}}\n\nYour assigned representative is {{assigned_user}} who will be in contact with you shortly.\n\nBest regards,\nThe WaveGuard Team');

-- Lead Welcome Content (Spanish)
INSERT INTO `email_template_content` (`template_id`, `language_code`, `subject`, `body_html`, `body_plain_text`) VALUES
(@lead_welcome_id, 'es', 'Bienvenido a WaveGuard - {{lead_name}}',
'<h2>¡Bienvenido {{lead_name}}!</h2>
<p>Gracias por su interés en los sistemas de protección contra incendios forestales de WaveGuard.</p>
<p>Hemos recibido su consulta para su propiedad en:</p>
<p><strong>{{property_address}}</strong></p>
<p>Su representante asignado es <strong>{{assigned_user}}</strong> quien se pondrá en contacto con usted en breve.</p>
<p>Mientras tanto, puede revisar nuestro <a href="{{homeowner_package_link}}">Paquete de Información para Propietarios</a>.</p>
<p>Saludos cordiales,<br>El Equipo de WaveGuard</p>',
'¡Bienvenido {{lead_name}}!\n\nGracias por su interés en los sistemas de protección contra incendios forestales de WaveGuard.\n\nHemos recibido su consulta para su propiedad en:\n{{property_address}}\n\nSu representante asignado es {{assigned_user}} quien se pondrá en contacto con usted en breve.\n\nSaludos cordiales,\nEl Equipo de WaveGuard');

-- Lead Welcome Variables
INSERT INTO `email_template_variables` (`template_id`, `variable_key`, `variable_label`, `variable_description`, `variable_type`, `variable_source`, `sort_order`) VALUES
(@lead_welcome_id, 'lead_name', 'Lead Name', 'Full name of the lead', 'text', 'leads.full_name', 1),
(@lead_welcome_id, 'property_address', 'Property Address', 'Full property address', 'text', 'leads.full_address', 2),
(@lead_welcome_id, 'assigned_user', 'Assigned User', 'Name of assigned sales representative', 'text', 'users.full_name', 3),
(@lead_welcome_id, 'homeowner_package_link', 'Homeowner Package Link', 'Link to homeowner information', 'url', 'static:homeowner_package', 4);

-- Lead Welcome Trigger Rule
INSERT INTO `email_trigger_rules` 
(`template_id`, `module`, `trigger_type`, `trigger_condition`, `recipient_type`, `delay_minutes`, `active`) 
VALUES
(@lead_welcome_id, 'leads', 'stage_change', 
'{"stage_to": 1, "stage_from": null}', 
'lead_contact', 0, 1);

-- Lead Assignment Email Template
INSERT INTO `email_templates` 
(`template_key`, `template_name`, `description`, `module`, `category`, `trigger_event`, `requires_approval`, `log_to_communications`, `active`) 
VALUES
('lead_assigned', 'Lead Assignment Notification', 'Sent to sales rep when lead is assigned', 'leads', 'assignment', 'assignment', 0, 0, 1);

SET @lead_assigned_id = LAST_INSERT_ID();

-- Lead Assignment Content (English)
INSERT INTO `email_template_content` (`template_id`, `language_code`, `subject`, `body_html`, `body_plain_text`) VALUES
(@lead_assigned_id, 'en', 'New Lead Assigned: {{lead_name}}',
'<h2>New Lead Assignment</h2>
<p>Hello {{assigned_user}},</p>
<p>A new lead has been assigned to you:</p>
<ul>
    <li><strong>Name:</strong> {{lead_name}}</li>
    <li><strong>Email:</strong> {{lead_email}}</li>
    <li><strong>Phone:</strong> {{lead_phone}}</li>
    <li><strong>Property:</strong> {{property_address}}</li>
    <li><strong>Lead Source:</strong> {{lead_source}}</li>
</ul>
<p><a href="{{lead_view_link}}" style="background-color: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Lead Details</a></p>
<p>Please follow up with this lead within 24 hours.</p>',
'New Lead Assignment\n\nHello {{assigned_user}},\n\nA new lead has been assigned to you:\n\nName: {{lead_name}}\nEmail: {{lead_email}}\nPhone: {{lead_phone}}\nProperty: {{property_address}}\nLead Source: {{lead_source}}\n\nView Lead: {{lead_view_link}}\n\nPlease follow up with this lead within 24 hours.');

-- Lead Assignment Variables
INSERT INTO `email_template_variables` (`template_id`, `variable_key`, `variable_label`, `variable_description`, `variable_type`, `variable_source`, `sort_order`) VALUES
(@lead_assigned_id, 'assigned_user', 'Assigned User', 'Name of assigned sales representative', 'text', 'users.full_name', 1),
(@lead_assigned_id, 'lead_name', 'Lead Name', 'Full name of the lead', 'text', 'leads.full_name', 2),
(@lead_assigned_id, 'lead_email', 'Lead Email', 'Email address of the lead', 'text', 'leads.email', 3),
(@lead_assigned_id, 'lead_phone', 'Lead Phone', 'Phone number of the lead', 'phone', 'leads.cell_phone', 4),
(@lead_assigned_id, 'property_address', 'Property Address', 'Full property address', 'text', 'leads.full_address', 5),
(@lead_assigned_id, 'lead_source', 'Lead Source', 'How the lead found us', 'text', 'leads.hear_about', 6),
(@lead_assigned_id, 'lead_view_link', 'Lead View Link', 'Direct link to view lead', 'url', 'dynamic:lead_view_url', 7);

-- Lead Assignment Trigger Rule
INSERT INTO `email_trigger_rules` 
(`template_id`, `module`, `trigger_type`, `trigger_condition`, `recipient_type`, `delay_minutes`, `active`) 
VALUES
(@lead_assigned_id, 'leads', 'assignment', 
'{"field": "last_edited_by", "action": "changed"}', 
'assigned_user', 0, 1);
```

---

## 3. Core PHP Classes

### 3.1 EmailTemplate Model (`classes/Models/EmailTemplate.php`)

```php
<?php
/**
 * EmailTemplate Model
 * Handles email template operations
 */
class EmailTemplate extends Database {
    
    /**
     * Get complete template with header, body, footer
     */
    public function getCompleteTemplate($template_key, $language_code = 'en') {
        // Get template info
        $sql = "SELECT et.*, etc.subject, etc.body_html, etc.body_plain_text
                FROM email_templates et
                LEFT JOIN email_template_content etc ON et.id = etc.template_id
                WHERE et.template_key = :template_key 
                AND etc.language_code = :language_code
                AND et.active = 1";
        
        $stmt = self::$DBCRM->prepare($sql);
        $stmt->execute([
            ':template_key' => $template_key,
            ':language_code' => $language_code
        ]);
        
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template) {
            return false;
        }
        
        // Get global header
        $header = $this->getGlobalTemplate('header', $language_code);
        
        // Get global footer
        $footer = $this->getGlobalTemplate('footer', $language_code);
        
        // Combine
        $template['header_html'] = $header['html_content'];
        $template['footer_html'] = $footer['html_content'];
        $template['header_plain'] = $header['plain_text_content'];
        $template['footer_plain'] = $footer['plain_text_content'];
        
        return $template;
    }
    
    /**
     * Get global header or footer template
     */
    private function getGlobalTemplate($type, $language_code = 'en') {
        $sql = "SELECT * FROM email_global_templates 
                WHERE template_type = :type 
                AND language_code = :language_code
                AND active = 1";
        
        $stmt = self::$DBCRM->prepare($sql);
        $stmt->execute([
            ':type' => $type,
            ':language_code' => $language_code
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get available variables for template
     */
    public function getTemplateVariables($template_id) {
        $sql = "SELECT * FROM email_template_variables 
                WHERE template_id = :template_id 
                ORDER BY sort_order ASC";
        
        $stmt = self::$DBCRM->prepare($sql);
        $stmt->execute([':template_id' => $template_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Save template content (body only)
     */
    public function saveTemplateContent($template_id, $language_code, $data) {
        $sql = "INSERT INTO email_template_content 
                (template_id, language_code, subject, body_html, body_plain_text, updated_by)
                VALUES (:template_id, :language_code, :subject, :body_html, :body_plain_text, :updated_by)
                ON DUPLICATE KEY UPDATE
                subject = VALUES(subject),
                body_html = VALUES(body_html),
                body_plain_text = VALUES(body_plain_text),
                updated_by = VALUES(updated_by)";
        
        $stmt = self::$DBCRM->prepare($sql);
        return $stmt->execute([
            ':template_id' => $template_id,
            ':language_code' => $language_code,
            ':subject' => $data['subject'],
            ':body_html' => $data['body_html'],
            ':body_plain_text' => $data['body_plain_text'] ?? '',
            ':updated_by' => $_SESSION['user_id']
        ]);
    }
    
    /**
     * Save global header/footer
     */
    public function saveGlobalTemplate($type, $language_code, $html_content, $plain_text_content) {
        $sql = "INSERT INTO email_global_templates 
                (template_type, language_code, html_content, plain_text_content, updated_by)
                VALUES (:type, :language_code, :html_content, :plain_text_content, :updated_by)
                ON DUPLICATE KEY UPDATE
                html_content = VALUES(html_content),
                plain_text_content = VALUES(plain_text_content),
                updated_by = VALUES(updated_by)";
        
        $stmt = self::$DBCRM->prepare($sql);
        return $stmt->execute([
            ':type' => $type,
            ':language_code' => $language_code,
            ':html_content' => $html_content,
            ':plain_text_content' => $plain_text_content,
            ':updated_by' => $_SESSION['user_id']
        ]);
    }
    
    /**
     * Get trigger rules for template
     */
    public function getTriggerRules($template_id) {
        $sql = "SELECT * FROM email_trigger_rules 
                WHERE template_id = :template_id 
                AND active = 1";
        
        $stmt = self::$DBCRM->prepare($sql);
        $stmt->execute([':template_id' => $template_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get templates by module
     */
    public function getTemplatesByModule($module) {
        $sql = "SELECT * FROM email_templates 
                WHERE module = :module 
                AND active = 1
                ORDER BY category, template_name";
        
        $stmt = self::$DBCRM->prepare($sql);
        $stmt->execute([':module' => $module]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

### 3.2 EmailRenderer Utility (`classes/Utilities/EmailRenderer.php`)

```php
<?php
/**
 * EmailRenderer Utility
 * Renders email templates with variable substitution
 */
class EmailRenderer {
    
    private $emailTemplate;
    
    public function __construct() {
        $this->emailTemplate = new EmailTemplate();
    }
    
    /**
     * Render complete email with header, body, footer
     */
    public function render($template_key, $variables = [], $language_code = 'en') {
        $template = $this->emailTemplate->getCompleteTemplate($template_key, $language_code);
        
        if (!$template) {
            throw new Exception("Email template not found: {$template_key}");
        }
        
        // Combine header + body + footer
        $fullHtml = $template['header_html'] . $template['body_html'] . $template['footer_html'];
        $fullPlain = $template['header_plain'] . $template['body_plain_text'] . $template['footer_plain'];
        
        // Replace shortcodes with actual values
        $renderedHtml = $this->replaceShortcodes($fullHtml, $variables);
        $renderedPlain = $this->replaceShortcodes($fullPlain, $variables);
        $renderedSubject = $this->replaceShortcodes($template['subject'], $variables);
        
        return [
            'template_id' => $template['id'],
            'subject' => $renderedSubject,
            'html' => $renderedHtml,
            'plain_text' => $renderedPlain,
            'requires_approval' => $template['requires_approval'],
            'log_to_communications' => $template['log_to_communications']
        ];
    }
    
    /**
     * Replace shortcodes like {{lead_name}} with actual values
     */
    private function replaceShortcodes($content, $variables) {
        foreach ($variables as $key => $value) {
            $shortcode = '{{' . $key . '}}';
            $content = str_replace($shortcode, htmlspecialchars($value), $content);
        }
        
        // Remove any unreplaced shortcodes
        $content = preg_replace('/\{\{[^}]+\}\}/', '', $content);
        
        return $content;
    }
    
    /**
     * Get variables for a specific record (lead, referral, etc.)
     */
    public function getRecordVariables($module, $record_id) {
        $variables = [];
        
        switch ($module) {
            case 'leads':
                $variables = $this->getLeadVariables($record_id);
                break;
            case 'referrals':
                $variables = $this->getReferralVariables($record_id);
                break;
            case 'prospects':
                $variables = $this->getProspectVariables($record_id);
                break;
            // Add more modules as needed
        }
        
        return $variables;
    }
    
    /**
     * Get lead-specific variables
     */
    private function getLeadVariables($lead_id) {
        $leads = new Leads();
        $lead = $leads->getLeadById($lead_id);
        
        if (!$lead) {
            return [];
        }
        
        // Get assigned user info
        $users = new Users();
        $assignedUser = $users->getUserById($lead['last_edited_by']);
        
        return [
            'lead_name' => $lead['full_name'] ?? '',
            'lead_email' => $lead['email'] ?? '',
            'lead_phone' => $lead['cell_phone'] ?? '',
            'property_address' => $lead['full_address'] ?? '',
            'business_name' => $lead['business_name'] ?? '',
            'assigned_user' => $assignedUser['full_name'] ?? '',
            'assigned_user_email' => $assignedUser['email'] ?? '',
            'assigned_user_phone' => $assignedUser['cell_phone'] ?? '',
            'lead_source' => $lead['hear_about'] ?? '',
            'structure_type' => $lead['structure_description'] ?? '',
            'homeowner_package_link' => $lead['picture_upload_link'] ?? '',
            'lead_view_link' => 'https://democrm.waveguardco.net/leads/view.php?id=' . $lead_id,
            'current_date' => date('F j, Y'),
            'current_year' => date('Y')
        ];
    }
    
    /**
     * Get referral-specific variables
     */
    private function getReferralVariables($referral_id) {
        // Similar to getLeadVariables but for referrals
        // TODO: Implement when referrals module is ready
        return [];
    }
    
    /**
     * Get prospect-specific variables
     */
    private function getProspectVariables($prospect_id) {
        // Similar to getLeadVariables but for prospects
        // TODO: Implement when prospects module is ready
        return [];
    }
}
```

### 3.3 EmailQueue Manager (`classes/Utilities/EmailQueueManager.php`)

```php
<?php
/**
 * EmailQueueManager
 * Manages email queue, approval workflow, and sending
 */
class EmailQueueManager {
    
    private $emailRenderer;
    private $emailTemplate;
    
    public function __construct() {
        $this->emailRenderer = new EmailRenderer();
        $this->emailTemplate = new EmailTemplate();
    }
    
    /**
     * Queue email for sending (with or without approval)
     */
    public function queueEmail($template_key, $module, $record_id, $recipient_email, $recipient_name = null, $language_code = 'en') {
        try {
            // Get variables for this record
            $variables = $this->emailRenderer->getRecordVariables($module, $record_id);
            
            // Render email
            $rendered = $this->emailRenderer->render($template_key, $variables, $language_code);
            
            // Insert into queue
            $sql = "INSERT INTO email_queue 
                    (template_id, module, record_id, recipient_email, recipient_name, language_code, 
                     subject, body_html, body_plain_text, variables_json, status, requires_approval, created_by)
                    VALUES 
                    (:template_id, :module, :record_id, :recipient_email, :recipient_name, :language_code,
                     :subject, :body_html, :body_plain_text, :variables_json, :status, :requires_approval, :created_by)";
            
            $stmt = Database::$DBCRM->prepare($sql);
            $result = $stmt->execute([
                ':template_id' => $rendered['template_id'],
                ':module' => $module,
                ':record_id' => $record_id,
                ':recipient_email' => $recipient_email,
                ':recipient_name' => $recipient_name,
                ':language_code' => $language_code,
                ':subject' => $rendered['subject'],
                ':body_html' => $rendered['html'],
                ':body_plain_text' => $rendered['plain_text'],
                ':variables_json' => json_encode($variables),
                ':status' => $rendered['requires_approval'] ? 'pending' : 'approved',
                ':requires_approval' => $rendered['requires_approval'],
                ':created_by' => $_SESSION['user_id'] ?? 1
            ]);
            
            $queue_id = Database::$DBCRM->lastInsertId();
            
            // If no approval required, send immediately
            if (!$rendered['requires_approval']) {
                $this->sendQueuedEmail($queue_id);
            }
            
            return [
                'success' => true,
                'queue_id' => $queue_id,
                'requires_approval' => $rendered['requires_approval']
            ];
            
        } catch (Exception $e) {
            // Log error
            error_log("Email queue error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send queued email
     */
    public function sendQueuedEmail($queue_id) {
        // Get email from queue
        $sql = "SELECT * FROM email_queue WHERE id = :queue_id";
        $stmt = Database::$DBCRM->prepare($sql);
        $stmt->execute([':queue_id' => $queue_id]);
        $email = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$email) {
            throw new Exception("Email not found in queue");
        }
        
        // Check if requires approval and not approved
        if ($email['requires_approval'] && $email['status'] !== 'approved') {
            throw new Exception("Email requires approval before sending");
        }
        
        // Send email using EmailSender
        $emailSender = new EmailSender();
        $result = $emailSender->send(
            $email['recipient_email'],
            $email['subject'],
            $email['body_html'],
            $email['body_plain_text']
        );
        
        if ($result['success']) {
            // Update queue status
            $this->updateQueueStatus($queue_id, 'sent');
            
            // Log to email_send_log
            $this->logSentEmail($email, $result);
            
            // Log to communications table if configured
            if ($email['log_to_communications']) {
                $this->logToCommunications($email);
            }
            
            return ['success' => true];
        } else {
            // Update queue with error
            $this->updateQueueStatus($queue_id, 'failed', $result['error']);
            return ['success' => false, 'error' => $result['error']];
        }
    }
    
    /**
     * Approve queued email
     */
    public function approveEmail($queue_id, $user_id) {
        $sql = "UPDATE email_queue 
                SET status = 'approved', 
                    approved_by = :user_id, 
                    approved_at = NOW()
                WHERE id = :queue_id";
        
        $stmt = Database::$DBCRM->prepare($sql);
        $stmt->execute([
            ':queue_id' => $queue_id,
            ':user_id' => $user_id
        ]);
        
        // Send the email
        return $this->sendQueuedEmail($queue_id);
    }
    
    /**
     * Update queue status
     */
    private function updateQueueStatus($queue_id, $status, $error_message = null) {
        $sql = "UPDATE email_queue 
                SET status = :status, 
                    error_message = :error_message,
                    sent_at = " . ($status === 'sent' ? 'NOW()' : 'NULL') . "
                WHERE id = :queue_id";
        
        $stmt = Database::$DBCRM->prepare($sql);
        $stmt->execute([
            ':queue_id' => $queue_id,
            ':status' => $status,
            ':error_message' => $error_message
        ]);
    }
    
    /**
     * Log sent email
     */
    private function logSentEmail($email, $result) {
        $sql = "INSERT INTO email_send_log 
                (template_id, queue_id, module, record_id, recipient_email, recipient_name,
                 subject, body_html, body_plain_text, sent_by, send_method, success)
                VALUES 
                (:template_id, :queue_id, :module, :record_id, :recipient_email, :recipient_name,
                 :subject, :body_html, :body_plain_text, :sent_by, :send_method, :success)";
        
        $stmt = Database::$DBCRM->prepare($sql);
        $stmt->execute([
            ':template_id' => $email['template_id'],
            ':queue_id' => $email['id'],
            ':module' => $email['module'],
            ':record_id' => $email['record_id'],
            ':recipient_email' => $email['recipient_email'],
            ':recipient_name' => $email['recipient_name'],
            ':subject' => $email['subject'],
            ':body_html' => $email['body_html'],
            ':body_plain_text' => $email['body_plain_text'],
            ':sent_by' => $_SESSION['user_id'] ?? 1,
            ':send_method' => 'automatic',
            ':success' => $result['success'] ? 1 : 0
        ]);
    }
    
    /**
     * Log to communications table (if applicable)
     */
    private function logToCommunications($email) {
        // TODO: Implement based on your communications table structure
        // This would create a record in the communications table
        // linking the email to the lead/referral/prospect
    }
    
    /**
     * Get pending emails requiring approval
     */
    public function getPendingApprovals($user_id = null) {
        $sql = "SELECT eq.*, et.template_name, et.module
                FROM email_queue eq
                JOIN email_templates et ON eq.template_id = et.id
                WHERE eq.status = 'pending' 
                AND eq.requires_approval = 1";
        
        if ($user_id) {
            $sql .= " AND eq.created_by = :user_id";
        }
        
        $sql .= " ORDER BY eq.created_at DESC";
        
        $stmt = Database::$DBCRM->prepare($sql);
        if ($user_id) {
            $stmt->execute([':user_id' => $user_id]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

### 3.4 EmailTriggerHandler (`classes/Utilities/EmailTriggerHandler.php`)

```php
<?php
/**
 * EmailTriggerHandler
 * Handles automatic email triggering based on events
 */
class EmailTriggerHandler {
    
    private $emailQueueManager;
    private $emailTemplate;
    
    public function __construct() {
        $this->emailQueueManager = new EmailQueueManager();
        $this->emailTemplate = new EmailTemplate();
    }
    
    /**
     * Handle stage change trigger
     */
    public function handleStageChange($module, $record_id, $old_stage, $new_stage) {
        // Find matching trigger rules
        $sql = "SELECT etr.*, et.template_key
                FROM email_trigger_rules etr
                JOIN email_templates et ON etr.template_id = et.id
                WHERE etr.module = :module
                AND etr.trigger_type = 'stage_change'
                AND etr.active = 1
                AND JSON_EXTRACT(etr.trigger_condition, '$.stage_to') = :new_stage";
        
        $stmt = Database::$DBCRM->prepare($sql);
        $stmt->execute([
            ':module' => $module,
            ':new_stage' => $new_stage
        ]);
        
        $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($triggers as $trigger) {
            $this->executeTrigger($trigger, $module, $record_id);
        }
    }
    
    /**
     * Handle assignment trigger
     */
    public function handleAssignment($module, $record_id, $old_user_id, $new_user_id) {
        // Find matching trigger rules
        $sql = "SELECT etr.*, et.template_key
                FROM email_trigger_rules etr
                JOIN email_templates et ON etr.template_id = et.id
                WHERE etr.module = :module
                AND etr.trigger_type = 'assignment'
                AND etr.active = 1";
        
        $stmt = Database::$DBCRM->prepare($sql);
        $stmt->execute([':module' => $module]);
        
        $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($triggers as $trigger) {
            $this->executeTrigger($trigger, $module, $record_id, $new_user_id);
        }
    }
    
    /**
     * Execute trigger
     */
    private function executeTrigger($trigger, $module, $record_id, $assigned_user_id = null) {
        // Determine recipient
        $recipient = $this->getRecipient($trigger, $module, $record_id, $assigned_user_id);
        
        if (!$recipient) {
            error_log("No recipient found for trigger: " . $trigger['id']);
            return;
        }
        
        // Queue email
        $this->emailQueueManager->queueEmail(
            $trigger['template_key'],
            $module,
            $record_id,
            $recipient['email'],
            $recipient['name'],
            $recipient['language'] ?? 'en'
        );
    }
    
    /**
     * Get recipient based on trigger configuration
     */
    private function getRecipient($trigger, $module, $record_id, $assigned_user_id = null) {
        switch ($trigger['recipient_type']) {
            case 'lead_contact':
                return $this->getLeadContactRecipient($module, $record_id);
                
            case 'assigned_user':
                return $this->getAssignedUserRecipient($assigned_user_id);
                
            case 'custom_email':
                return [
                    'email' => $trigger['custom_recipient_email'],
                    'name' => 'Custom Recipient'
                ];
                
            case 'both':
                // Send to both lead and assigned user
                $leadRecipient = $this->getLeadContactRecipient($module, $record_id);
                $userRecipient = $this->getAssignedUserRecipient($assigned_user_id);
                
                // Queue for both (this will need to be called twice)
                return [$leadRecipient, $userRecipient];
                
            default:
                return null;
        }
    }
    
    /**
     * Get lead/referral/prospect contact as recipient
     */
    private function getLeadContactRecipient($module, $record_id) {
        switch ($module) {
            case 'leads':
                $leads = new Leads();
                $record = $leads->getLeadById($record_id);
                return [
                    'email' => $record['email'] ?? null,
                    'name' => $record['full_name'] ?? null,
                    'language' => 'en' // TODO: Get from user preferences
                ];
                
            // Add other modules as needed
            default:
                return null;
        }
    }
    
    /**
     * Get assigned user as recipient
     */
    private function getAssignedUserRecipient($user_id) {
        if (!$user_id) {
            return null;
        }
        
        $users = new Users();
        $user = $users->getUserById($user_id);
        
        return [
            'email' => $user['email'] ?? null,
            'name' => $user['full_name'] ?? null,
            'language' => $user['lang'] ?? 'en'
        ];
    }
}
```

---

## 4. Integration Points

### 4.1 Leads Module Integration

Add to `/public_html/leads/post.php` (after lead creation/update):

```php
// After successful lead creation or stage change
if ($lead_created || $stage_changed) {
    $emailTrigger = new EmailTriggerHandler();
    
    if ($lead_created) {
        // Trigger welcome email
        $emailTrigger->handleStageChange('leads', $lead_id, null, 1);
    }
    
    if ($stage_changed) {
        // Trigger stage change email
        $emailTrigger->handleStageChange('leads', $lead_id, $old_stage, $new_stage);
    }
    
    if ($assignment_changed) {
        // Trigger assignment email
        $emailTrigger->handleAssignment('leads', $lead_id, $old_user_id, $new_user_id);
    }
}
```

### 4.2 Admin Email Management Interface

Create `/public_html/admin/email/templates/list.php`:

```php
<?php
session_start();
require_once '../../../classes/Core/Database.php';
require_once '../../../classes/Models/EmailTemplate.php';
require_once '../../../classes/Utilities/Helpers.php';

// Framework variables
$dir = 'email';
$page = 'list';
$table_page = true;

// Authentication
$not = new Helpers();
$not->loggedin();

// Get templates
$emailTemplate = new EmailTemplate();
$templates = $emailTemplate->getTemplatesByModule('leads'); // Can filter by module

// Load language
require LANG . '/en.php';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo $lang['email_templates']; ?></h2>
            
            <a href="new.php" class="btn btn-primary mb-3">
                <i class="fas fa-plus"></i> <?php echo $lang['create_template']; ?>
            </a>
            
            <table id="templatesTable" class="table table-striped">
                <thead>
                    <tr>
                        <th><?php echo $lang['template_name']; ?></th>
                        <th><?php echo $lang['module']; ?></th>
                        <th><?php echo $lang['category']; ?></th>
                        <th><?php echo $lang['trigger_event']; ?></th>
                        <th><?php echo $lang['requires_approval']; ?></th>
                        <th><?php echo $lang['active']; ?></th>
                        <th><?php echo $lang['actions']; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($templates as $template): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($template['template_name']); ?></td>
                        <td><?php echo htmlspecialchars($template['module']); ?></td>
                        <td><?php echo htmlspecialchars($template['category']); ?></td>
                        <td><?php echo htmlspecialchars($template['trigger_event']); ?></td>
                        <td>
                            <?php if ($template['requires_approval']): ?>
                                <span class="badge bg-warning">Yes</span>
                            <?php else: ?>
                                <span class="badge bg-success">No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($template['active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $template['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="view.php?id=<?php echo $template['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require SECTIONCLOSE;
require FOOTER;
?>
```

---

## 5. Language Keys to Add

Add to `/public_html/admin/languages/en.php` and `es.php`:

```php
// English (en.php)
$lang['email_templates'] = 'Email Templates';
$lang['email_template'] = 'Email Template';
$lang['edit_email_template'] = 'Edit Email Template';
$lang['create_template'] = 'Create Template';
$lang['template_name'] = 'Template Name';
$lang['template_key'] = 'Template Key';
$lang['template_category'] = 'Category';
$lang['template_description'] = 'Description';
$lang['email_subject'] = 'Email Subject';
$lang['email_body'] = 'Email Body';
$lang['email_header'] = 'Email Header (Global)';
$lang['email_footer'] = 'Email Footer (Global)';
$lang['available_shortcodes'] = 'Available Shortcodes';
$lang['email_preview'] = 'Email Preview';
$lang['insert_shortcode'] = 'Insert Shortcode';
$lang['trigger_event'] = 'Trigger Event';
$lang['requires_approval'] = 'Requires Approval';
$lang['log_to_communications'] = 'Log to Communications';
$lang['email_queue'] = 'Email Queue';
$lang['pending_approvals'] = 'Pending Approvals';
$lang['approve_email'] = 'Approve Email';
$lang['send_email'] = 'Send Email';
$lang['email_sent'] = 'Email Sent';
$lang['email_failed'] = 'Email Failed';

// Spanish (es.php)
$lang['email_templates'] = 'Plantillas de Correo';
$lang['email_template'] = 'Plantilla de Correo';
$lang['edit_email_template'] = 'Editar Plantilla de Correo';
$lang['create_template'] = 'Crear Plantilla';
$lang['template_name'] = 'Nombre de Plantilla';
$lang['template_key'] = 'Clave de Plantilla';
$lang['template_category'] = 'Categoría';
$lang['template_description'] = 'Descripción';
$lang['email_subject'] = 'Asunto del Correo';
$lang['email_body'] = 'Cuerpo del Correo';
$lang['email_header'] = 'Encabezado del Correo (Global)';
$lang['email_footer'] = 'Pie de Página del Correo (Global)';
$lang['available_shortcodes'] = 'Códigos Cortos Disponibles';
$lang['email_preview'] = 'Vista Previa del Correo';
$lang['insert_shortcode'] = 'Insertar Código Corto';
$lang['trigger_event'] = 'Evento Disparador';
$lang['requires_approval'] = 'Requiere Aprobación';
$lang['log_to_communications'] = 'Registrar en Comunicaciones';
$lang['email_queue'] = 'Cola de Correos';
$lang['pending_approvals'] = 'Aprobaciones Pendientes';
$lang['approve_email'] = 'Aprobar Correo';
$lang['send_email'] = 'Enviar Correo';
$lang['email_sent'] = 'Correo Enviado';
$lang['email_failed'] = 'Correo Fallido';
```

---

## 6. Implementation Roadmap

### Phase 1: Database & Core Classes (Week 1)
- [ ] Create database tables
- [ ] Insert sample data
- [ ] Create EmailTemplate model
- [ ] Create EmailRenderer utility
- [ ] Test basic rendering

### Phase 2: Queue & Trigger System (Week 2)
- [ ] Create EmailQueueManager
- [ ] Create EmailTriggerHandler
- [ ] Integrate with leads module
- [ ] Test automatic triggering

### Phase 3: Admin Interface (Week 3)
- [ ] Create template list page
- [ ] Create template edit page with Summernote
- [ ] Create global header/footer editor
- [ ] Create preview functionality
- [ ] Create approval queue interface

### Phase 4: Testing & Refinement (Week 4)
- [ ] Test all trigger scenarios
- [ ] Test multilingual support
- [ ] Test approval workflow
- [ ] Performance optimization
- [ ] Documentation

### Phase 5: Expansion (Future)
- [ ] Add referrals module support
- [ ] Add prospects module support
- [ ] Add SMS support
- [ ] Add scheduled/drip campaigns
- [ ] Add analytics dashboard

---

## 7. Key Benefits

✅ **Automated Communication** - Emails sent automatically based on stage changes  
✅ **Flexible Approval** - Configure per-template approval requirements  
✅ **Consistent Branding** - Global header/footer inherited by all modules  
✅ **Multilingual** - Full English/Spanish support  
✅ **Audit Trail** - Complete logging of all sent emails  
✅ **Future-Proof** - Ready for SMS and scheduled campaigns  
✅ **Module Agnostic** - Easy to add new modules (referrals, prospects, etc.)  
✅ **User-Friendly** - Summernote editor with shortcode buttons  
✅ **Framework Compliant** - Follows DemoCRM non-MVC architecture  

---

## 8. Next Steps

Would you like me to:

1. **Start implementation** with Phase 1 (database schema)?
2. **Create a specific template** (e.g., lead welcome email)?
3. **Build the admin interface** first for testing?
4. **Focus on a specific module** (leads, referrals, etc.)?

Let me know how you'd like to proceed!
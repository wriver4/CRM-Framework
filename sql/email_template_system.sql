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
  `recipient_name` VARCHAR(255) COMMENT 'Uses full_name from source table',
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
  `recipient_name` VARCHAR(255) COMMENT 'Uses full_name from source table',
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

-- =====================================================
-- Sample Data - Initial Templates
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

-- Lead Welcome Variables (using full_name from leads table)
INSERT INTO `email_template_variables` (`template_id`, `variable_key`, `variable_label`, `variable_description`, `variable_type`, `variable_source`, `sort_order`) VALUES
(@lead_welcome_id, 'lead_name', 'Lead Name', 'Full name of the lead', 'text', 'leads.full_name', 1),
(@lead_welcome_id, 'property_address', 'Property Address', 'Full property address', 'text', 'leads.full_address', 2),
(@lead_welcome_id, 'assigned_user', 'Assigned User', 'Name of assigned sales representative', 'text', 'users.full_name', 3),
(@lead_welcome_id, 'homeowner_package_link', 'Homeowner Package Link', 'Link to homeowner information package', 'url', 'config.homeowner_package_url', 4);

-- Lead Welcome Trigger Rule (send when new lead created - stage 1)
INSERT INTO `email_trigger_rules` (`template_id`, `module`, `trigger_type`, `trigger_condition`, `recipient_type`, `delay_minutes`, `active`) VALUES
(@lead_welcome_id, 'leads', 'stage_change', '{"stage_to": 1, "on_create": true}', 'lead_contact', 0, 1);

-- Lead Assignment Notification Template
INSERT INTO `email_templates` 
(`template_key`, `template_name`, `description`, `module`, `category`, `trigger_event`, `requires_approval`, `log_to_communications`, `active`) 
VALUES
('lead_assigned', 'Lead Assignment Notification', 'Sent to sales rep when a lead is assigned to them', 'leads', 'assignment', 'assignment', 0, 0, 1);

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
<p><a href="{{lead_url}}" style="background-color: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">View Lead Details</a></p>
<p>Please follow up with this lead within 24 hours.</p>',
'New Lead Assignment\n\nHello {{assigned_user}},\n\nA new lead has been assigned to you:\n\nName: {{lead_name}}\nEmail: {{lead_email}}\nPhone: {{lead_phone}}\nProperty: {{property_address}}\nLead Source: {{lead_source}}\n\nView Lead: {{lead_url}}\n\nPlease follow up with this lead within 24 hours.');

-- Lead Assignment Content (Spanish)
INSERT INTO `email_template_content` (`template_id`, `language_code`, `subject`, `body_html`, `body_plain_text`) VALUES
(@lead_assigned_id, 'es', 'Nuevo Lead Asignado: {{lead_name}}',
'<h2>Nueva Asignación de Lead</h2>
<p>Hola {{assigned_user}},</p>
<p>Se te ha asignado un nuevo lead:</p>
<ul>
    <li><strong>Nombre:</strong> {{lead_name}}</li>
    <li><strong>Email:</strong> {{lead_email}}</li>
    <li><strong>Teléfono:</strong> {{lead_phone}}</li>
    <li><strong>Propiedad:</strong> {{property_address}}</li>
    <li><strong>Fuente del Lead:</strong> {{lead_source}}</li>
</ul>
<p><a href="{{lead_url}}" style="background-color: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Ver Detalles del Lead</a></p>
<p>Por favor, haz seguimiento a este lead dentro de las próximas 24 horas.</p>',
'Nueva Asignación de Lead\n\nHola {{assigned_user}},\n\nSe te ha asignado un nuevo lead:\n\nNombre: {{lead_name}}\nEmail: {{lead_email}}\nTeléfono: {{lead_phone}}\nPropiedad: {{property_address}}\nFuente del Lead: {{lead_source}}\n\nVer Lead: {{lead_url}}\n\nPor favor, haz seguimiento a este lead dentro de las próximas 24 horas.');

-- Lead Assignment Variables (using full_name from both leads and users tables)
INSERT INTO `email_template_variables` (`template_id`, `variable_key`, `variable_label`, `variable_description`, `variable_type`, `variable_source`, `sort_order`) VALUES
(@lead_assigned_id, 'assigned_user', 'Assigned User', 'Name of assigned sales representative', 'text', 'users.full_name', 1),
(@lead_assigned_id, 'lead_name', 'Lead Name', 'Full name of the lead', 'text', 'leads.full_name', 2),
(@lead_assigned_id, 'lead_email', 'Lead Email', 'Email address of the lead', 'text', 'leads.email', 3),
(@lead_assigned_id, 'lead_phone', 'Lead Phone', 'Phone number of the lead', 'phone', 'leads.cell_phone', 4),
(@lead_assigned_id, 'property_address', 'Property Address', 'Full property address', 'text', 'leads.full_address', 5),
(@lead_assigned_id, 'lead_source', 'Lead Source', 'Source of the lead', 'text', 'leads.lead_source', 6),
(@lead_assigned_id, 'lead_url', 'Lead URL', 'Direct link to lead details page', 'url', 'generated.lead_url', 7);

-- Lead Assignment Trigger Rule (send to assigned user when lead is assigned)
INSERT INTO `email_trigger_rules` (`template_id`, `module`, `trigger_type`, `trigger_condition`, `recipient_type`, `delay_minutes`, `active`) VALUES
(@lead_assigned_id, 'leads', 'assignment', '{"field": "last_edited_by", "on_change": true}', 'assigned_user', 0, 1);
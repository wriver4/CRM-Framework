-- =====================================================
-- Email Template System - Missing Table and Sample Data
-- Date: 2025-01-09
-- Description: Creates email_trigger_rules table and adds sample data
-- =====================================================
-- Email Trigger Rules (Stage-based automatic email triggers)
CREATE TABLE
  IF NOT EXISTS `email_trigger_rules` (
    `id` INT (11) NOT NULL AUTO_INCREMENT,
    `template_id` INT (11) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `trigger_type` ENUM (
      'stage_change',
      'assignment',
      'field_update',
      'time_based'
    ) NOT NULL,
    `trigger_condition` JSON NOT NULL COMMENT 'Conditions: stage_from, stage_to, field_name, etc.',
    `recipient_type` ENUM (
      'lead_contact',
      'assigned_user',
      'custom_email',
      'both'
    ) DEFAULT 'lead_contact',
    `custom_recipient_email` VARCHAR(255) COMMENT 'For custom_email recipient type',
    `delay_minutes` INT (11) DEFAULT 0 COMMENT 'Delay before sending (0=immediate)',
    `active` TINYINT (1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `created_by` INT (11),
    PRIMARY KEY (`id`),
    KEY `template_id` (`template_id`),
    KEY `module_trigger` (`module`, `trigger_type`),
    FOREIGN KEY (`template_id`) REFERENCES `email_templates` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Rules for automatic email triggering based on events';

-- =====================================================
-- Sample Data - Check if already exists before inserting
-- =====================================================
-- Check and insert Global Header (English)
INSERT IGNORE INTO `email_global_templates` (
  `id`,
  `template_type`,
  `language_code`,
  `html_content`,
  `plain_text_content`
)
VALUES
  (
    1,
    'header',
    'en',
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
    'WaveGuard CRM\n\n'
  );

-- Check and insert Global Header (Spanish)
INSERT IGNORE INTO `email_global_templates` (
  `id`,
  `template_type`,
  `language_code`,
  `html_content`,
  `plain_text_content`
)
VALUES
  (
    2,
    'header',
    'es',
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
    'WaveGuard CRM\n\n'
  );

-- Check and insert Global Footer (English)
INSERT IGNORE INTO `email_global_templates` (
  `id`,
  `template_type`,
  `language_code`,
  `html_content`,
  `plain_text_content`
)
VALUES
  (
    3,
    'footer',
    'en',
    '    </div>
    <div style="background-color: #f4f4f4; padding: 15px; text-align: center; font-size: 12px; color: #666;">
        <p>© 2025 WaveGuard CRM. All rights reserved.</p>
        <p>This email was sent from an automated system. Please do not reply directly to this email.</p>
    </div>
</body>
</html>',
    '\n\n---\n© 2025 WaveGuard CRM. All rights reserved.\nThis email was sent from an automated system. Please do not reply directly to this email.'
  );

-- Check and insert Global Footer (Spanish)
INSERT IGNORE INTO `email_global_templates` (
  `id`,
  `template_type`,
  `language_code`,
  `html_content`,
  `plain_text_content`
)
VALUES
  (
    4,
    'footer',
    'es',
    '    </div>
    <div style="background-color: #f4f4f4; padding: 15px; text-align: center; font-size: 12px; color: #666;">
        <p>© 2025 WaveGuard CRM. Todos los derechos reservados.</p>
        <p>Este correo fue enviado desde un sistema automatizado. Por favor no responda directamente a este correo.</p>
    </div>
</body>
</html>',
    '\n\n---\n© 2025 WaveGuard CRM. Todos los derechos reservados.\nEste correo fue enviado desde un sistema automatizado. Por favor no responda directamente a este correo.'
  );

-- Sample Template 1: Lead Welcome Email
INSERT IGNORE INTO `email_templates` (
  `id`,
  `template_key`,
  `template_name`,
  `module`,
  `description`,
  `category`,
  `trigger_event`,
  `requires_approval`,
  `log_to_communications`,
  `active`
)
VALUES
  (
    1,
    'lead_welcome',
    'Lead Welcome Email',
    'leads',
    'Welcome email sent to new leads',
    'general',
    'stage_change',
    0,
    1,
    1
  );

-- Template Content (English)
INSERT IGNORE INTO `email_template_content` (
  `template_id`,
  `language_code`,
  `subject`,
  `body_html`,
  `body_plain_text`
)
VALUES
  (
    1,
    'en',
    'Welcome to WaveGuard - {{lead_full_name}}',
    '<h2>Welcome, {{lead_full_name}}!</h2>
<p>Thank you for your interest in WaveGuard. We are excited to help you with your needs.</p>
<p><strong>Your Information:</strong></p>
<ul>
    <li>Email: {{lead_email}}</li>
    <li>Phone: {{lead_phone}}</li>
    <li>Company: {{lead_company}}</li>
</ul>
<p>Your assigned representative is <strong>{{assigned_user_name}}</strong> ({{assigned_user_email}}).</p>
<p>We will be in touch with you shortly!</p>
<p><a href="{{lead_view_url}}" style="background-color: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Your Profile</a></p>',
    'Welcome, {{lead_full_name}}!

Thank you for your interest in WaveGuard. We are excited to help you with your needs.

Your Information:
- Email: {{lead_email}}
- Phone: {{lead_phone}}
- Company: {{lead_company}}

Your assigned representative is {{assigned_user_name}} ({{assigned_user_email}}).

We will be in touch with you shortly!

View Your Profile: {{lead_view_url}}'
  );

-- Template Content (Spanish)
INSERT IGNORE INTO `email_template_content` (
  `template_id`,
  `language_code`,
  `subject`,
  `body_html`,
  `body_plain_text`
)
VALUES
  (
    1,
    'es',
    '¡Bienvenido a WaveGuard - {{lead_full_name}}!',
    '<h2>¡Bienvenido, {{lead_full_name}}!</h2>
<p>Gracias por su interés en WaveGuard. Estamos emocionados de ayudarle con sus necesidades.</p>
<p><strong>Su Información:</strong></p>
<ul>
    <li>Correo: {{lead_email}}</li>
    <li>Teléfono: {{lead_phone}}</li>
    <li>Empresa: {{lead_company}}</li>
</ul>
<p>Su representante asignado es <strong>{{assigned_user_name}}</strong> ({{assigned_user_email}}).</p>
<p>¡Nos pondremos en contacto con usted pronto!</p>
<p><a href="{{lead_view_url}}" style="background-color: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Ver Su Perfil</a></p>',
    '¡Bienvenido, {{lead_full_name}}!

Gracias por su interés en WaveGuard. Estamos emocionados de ayudarle con sus necesidades.

Su Información:
- Correo: {{lead_email}}
- Teléfono: {{lead_phone}}
- Empresa: {{lead_company}}

Su representante asignado es {{assigned_user_name}} ({{assigned_user_email}}).

¡Nos pondremos en contacto con usted pronto!

Ver Su Perfil: {{lead_view_url}}'
  );

-- Template Variables for Lead Welcome
INSERT IGNORE INTO `email_template_variables` (
  `template_id`,
  `variable_key`,
  `variable_label`,
  `variable_source`,
  `variable_description`,
  `variable_type`,
  `sort_order`
)
VALUES
  (
    1,
    'lead_full_name',
    'Lead Full Name',
    'leads.full_name',
    'Full name of the lead from leads table',
    'text',
    1
  ),
  (
    1,
    'lead_email',
    'Lead Email',
    'leads.email',
    'Email address of the lead',
    'text',
    2
  ),
  (
    1,
    'lead_phone',
    'Lead Phone',
    'leads.phone',
    'Phone number of the lead',
    'text',
    3
  ),
  (
    1,
    'lead_company',
    'Lead Company',
    'leads.company',
    'Company name of the lead',
    'text',
    4
  ),
  (
    1,
    'assigned_user_name',
    'Assigned User Name',
    'users.full_name',
    'Full name of assigned sales rep from users table',
    'text',
    5
  ),
  (
    1,
    'assigned_user_email',
    'Assigned User Email',
    'users.email',
    'Email of assigned sales rep',
    'text',
    6
  ),
  (
    1,
    'lead_view_url',
    'Lead View URL',
    'generated',
    'URL to view lead details',
    'url',
    7
  ),
  (
    1,
    'current_date',
    'Current Date',
    'generated',
    'Current date when email is sent',
    'date',
    8
  );

-- Sample Template 2: Lead Assignment Notification
INSERT IGNORE INTO `email_templates` (
  `id`,
  `template_key`,
  `template_name`,
  `module`,
  `description`,
  `category`,
  `trigger_event`,
  `requires_approval`,
  `log_to_communications`,
  `active`
)
VALUES
  (
    2,
    'lead_assignment',
    'Lead Assignment Notification',
    'leads',
    'Notification sent to sales rep when lead is assigned',
    'general',
    'assignment',
    0,
    0,
    1
  );

-- Template Content (English)
INSERT IGNORE INTO `email_template_content` (
  `template_id`,
  `language_code`,
  `subject`,
  `body_html`,
  `body_plain_text`
)
VALUES
  (
    2,
    'en',
    'New Lead Assigned: {{lead_full_name}}',
    '<h2>New Lead Assigned to You</h2>
<p>Hello {{assigned_user_name}},</p>
<p>A new lead has been assigned to you:</p>
<p><strong>Lead Details:</strong></p>
<ul>
    <li>Name: {{lead_full_name}}</li>
    <li>Email: {{lead_email}}</li>
    <li>Phone: {{lead_phone}}</li>
    <li>Company: {{lead_company}}</li>
    <li>Stage: {{lead_stage}}</li>
</ul>
<p><a href="{{lead_view_url}}" style="background-color: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Lead Details</a></p>
<p>Please follow up with this lead as soon as possible.</p>',
    'New Lead Assigned to You

Hello {{assigned_user_name}},

A new lead has been assigned to you:

Lead Details:
- Name: {{lead_full_name}}
- Email: {{lead_email}}
- Phone: {{lead_phone}}
- Company: {{lead_company}}
- Stage: {{lead_stage}}

View Lead Details: {{lead_view_url}}

Please follow up with this lead as soon as possible.'
  );

-- Template Content (Spanish)
INSERT IGNORE INTO `email_template_content` (
  `template_id`,
  `language_code`,
  `subject`,
  `body_html`,
  `body_plain_text`
)
VALUES
  (
    2,
    'es',
    'Nuevo Prospecto Asignado: {{lead_full_name}}',
    '<h2>Nuevo Prospecto Asignado</h2>
<p>Hola {{assigned_user_name}},</p>
<p>Se le ha asignado un nuevo prospecto:</p>
<p><strong>Detalles del Prospecto:</strong></p>
<ul>
    <li>Nombre: {{lead_full_name}}</li>
    <li>Correo: {{lead_email}}</li>
    <li>Teléfono: {{lead_phone}}</li>
    <li>Empresa: {{lead_company}}</li>
    <li>Etapa: {{lead_stage}}</li>
</ul>
<p><a href="{{lead_view_url}}" style="background-color: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Ver Detalles del Prospecto</a></p>
<p>Por favor haga seguimiento con este prospecto lo antes posible.</p>',
    'Nuevo Prospecto Asignado

Hola {{assigned_user_name}},

Se le ha asignado un nuevo prospecto:

Detalles del Prospecto:
- Nombre: {{lead_full_name}}
- Correo: {{lead_email}}
- Teléfono: {{lead_phone}}
- Empresa: {{lead_company}}
- Etapa: {{lead_stage}}

Ver Detalles del Prospecto: {{lead_view_url}}

Por favor haga seguimiento con este prospecto lo antes posible.'
  );

-- Template Variables for Lead Assignment
INSERT IGNORE INTO `email_template_variables` (
  `template_id`,
  `variable_key`,
  `variable_label`,
  `variable_source`,
  `variable_description`,
  `variable_type`,
  `sort_order`
)
VALUES
  (
    2,
    'lead_full_name',
    'Lead Full Name',
    'leads.full_name',
    'Full name of the lead from leads table',
    'text',
    1
  ),
  (
    2,
    'lead_email',
    'Lead Email',
    'leads.email',
    'Email address of the lead',
    'text',
    2
  ),
  (
    2,
    'lead_phone',
    'Lead Phone',
    'leads.phone',
    'Phone number of the lead',
    'text',
    3
  ),
  (
    2,
    'lead_company',
    'Lead Company',
    'leads.company',
    'Company name of the lead',
    'text',
    4
  ),
  (
    2,
    'lead_stage',
    'Lead Stage',
    'leads.stage',
    'Current stage of the lead',
    'text',
    5
  ),
  (
    2,
    'assigned_user_name',
    'Assigned User Name',
    'users.full_name',
    'Full name of assigned sales rep from users table',
    'text',
    6
  ),
  (
    2,
    'assigned_user_email',
    'Assigned User Email',
    'users.email',
    'Email of assigned sales rep',
    'text',
    7
  ),
  (
    2,
    'lead_view_url',
    'Lead View URL',
    'generated',
    'URL to view lead details',
    'url',
    8
  );

-- Trigger Rules
INSERT IGNORE INTO `email_trigger_rules` (
  `template_id`,
  `module`,
  `trigger_type`,
  `trigger_condition`,
  `recipient_type`,
  `delay_minutes`,
  `active`
)
VALUES
  (
    1,
    'leads',
    'stage_change',
    '{"stage_to": 1}',
    'lead_contact',
    0,
    1
  ),
  (
    2,
    'leads',
    'assignment',
    '{}',
    'assigned_user',
    0,
    1
  );

-- =====================================================
-- Verification
-- =====================================================
SELECT
  'Email template system update completed successfully!' as status;

SELECT
  COUNT(*) as email_trigger_rules_count
FROM
  email_trigger_rules;

SELECT
  COUNT(*) as email_templates_count
FROM
  email_templates;

SELECT
  COUNT(*) as email_global_templates_count
FROM
  email_global_templates;
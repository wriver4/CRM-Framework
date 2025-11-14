<?php

/**
 * EmailTemplate Model
 * Manages email templates with multilingual support and trigger configuration
 * Uses full_name from source tables (leads.full_name, users.full_name, contacts.full_name)
 */

class EmailTemplate extends Database
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all email templates
     * @param string|null $module Filter by module (leads, referrals, prospects, etc.)
     * @param bool $activeOnly Only return active templates
     * @return array
     */
    public function getAllTemplates($module = null, $activeOnly = true)
    {
        try {
            $sql = "SELECT * FROM email_templates WHERE 1=1";
            $params = [];
            
            if ($module) {
                $sql .= " AND module = :module";
                $params[':module'] = $module;
            }
            
            if ($activeOnly) {
                $sql .= " AND active = 1";
            }
            
            $sql .= " ORDER BY module, category, template_name";
            
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("EmailTemplate::getAllTemplates() Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get template by ID
     * @param int $templateId
     * @return array|null
     */
    public function getTemplateById($templateId)
    {
        try {
            $sql = "SELECT * FROM email_templates WHERE id = :id";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute([':id' => $templateId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("EmailTemplate::getTemplateById() Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get template by key
     * @param string $templateKey
     * @return array|null
     */
    public function getTemplateByKey($templateKey)
    {
        try {
            $sql = "SELECT * FROM email_templates WHERE template_key = :key AND active = 1";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute([':key' => $templateKey]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("EmailTemplate::getTemplateByKey() Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get template content for specific language
     * @param int $templateId
     * @param string $languageCode (en, es)
     * @return array|null
     */
    public function getTemplateContent($templateId, $languageCode = 'en')
    {
        try {
            $sql = "SELECT * FROM email_template_content 
                    WHERE template_id = :template_id AND language_code = :lang";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute([
                ':template_id' => $templateId,
                ':lang' => $languageCode
            ]);
            $content = $stmt->fetch();
            
            // Fallback to English if translation not found
            if (!$content && $languageCode !== 'en') {
                return $this->getTemplateContent($templateId, 'en');
            }
            
            return $content;
        } catch (PDOException $e) {
            error_log("EmailTemplate::getTemplateContent() Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get global header/footer template
     * @param string $type ('header' or 'footer')
     * @param string $languageCode
     * @return array|null
     */
    public function getGlobalTemplate($type, $languageCode = 'en')
    {
        try {
            $sql = "SELECT * FROM email_global_templates 
                    WHERE template_type = :type AND language_code = :lang AND active = 1";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute([
                ':type' => $type,
                ':lang' => $languageCode
            ]);
            $template = $stmt->fetch();
            
            // Fallback to English if translation not found
            if (!$template && $languageCode !== 'en') {
                return $this->getGlobalTemplate($type, 'en');
            }
            
            return $template;
        } catch (PDOException $e) {
            error_log("EmailTemplate::getGlobalTemplate() Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get template variables
     * @param int $templateId
     * @return array
     */
    public function getTemplateVariables($templateId)
    {
        try {
            $sql = "SELECT * FROM email_template_variables 
                    WHERE template_id = :template_id 
                    ORDER BY sort_order, variable_label";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute([':template_id' => $templateId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("EmailTemplate::getTemplateVariables() Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get trigger rules for a template
     * @param int $templateId
     * @param bool $activeOnly
     * @return array
     */
    public function getTriggerRules($templateId, $activeOnly = true)
    {
        try {
            $sql = "SELECT * FROM email_trigger_rules WHERE template_id = :template_id";
            if ($activeOnly) {
                $sql .= " AND active = 1";
            }
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute([':template_id' => $templateId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("EmailTemplate::getTriggerRules() Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get trigger rules by module and trigger type
     * @param string $module
     * @param string $triggerType
     * @return array
     */
    public function getTriggerRulesByType($module, $triggerType)
    {
        try {
            $sql = "SELECT etr.*, et.* 
                    FROM email_trigger_rules etr
                    JOIN email_templates et ON etr.template_id = et.id
                    WHERE etr.module = :module 
                    AND etr.trigger_type = :trigger_type 
                    AND etr.active = 1 
                    AND et.active = 1";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute([
                ':module' => $module,
                ':trigger_type' => $triggerType
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("EmailTemplate::getTriggerRulesByType() Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new email template
     * @param array $data
     * @return int|false Template ID or false on failure
     */
    public function createTemplate($data)
    {
        try {
            $sql = "INSERT INTO email_templates 
                    (template_key, template_name, description, module, category, 
                     trigger_event, trigger_conditions, requires_approval, 
                     log_to_communications, supports_sms, active, created_by)
                    VALUES 
                    (:template_key, :template_name, :description, :module, :category,
                     :trigger_event, :trigger_conditions, :requires_approval,
                     :log_to_communications, :supports_sms, :active, :created_by)";
            
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute([
                ':template_key' => $data['template_key'],
                ':template_name' => $data['template_name'],
                ':description' => $data['description'] ?? null,
                ':module' => $data['module'],
                ':category' => $data['category'] ?? 'general',
                ':trigger_event' => $data['trigger_event'] ?? null,
                ':trigger_conditions' => $data['trigger_conditions'] ?? null,
                ':requires_approval' => $data['requires_approval'] ?? 0,
                ':log_to_communications' => $data['log_to_communications'] ?? 1,
                ':supports_sms' => $data['supports_sms'] ?? 0,
                ':active' => $data['active'] ?? 1,
                ':created_by' => $data['created_by'] ?? $_SESSION['user_id'] ?? null
            ]);
            
            return $this->dbcrm()->lastInsertId();
        } catch (PDOException $e) {
            error_log("EmailTemplate::createTemplate() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update email template
     * @param int $templateId
     * @param array $data
     * @return bool
     */
    public function updateTemplate($templateId, $data)
    {
        try {
            $sql = "UPDATE email_templates SET 
                    template_name = :template_name,
                    description = :description,
                    category = :category,
                    trigger_event = :trigger_event,
                    trigger_conditions = :trigger_conditions,
                    requires_approval = :requires_approval,
                    log_to_communications = :log_to_communications,
                    supports_sms = :supports_sms,
                    active = :active
                    WHERE id = :id";
            
            $stmt = $this->dbcrm()->prepare($sql);
            return $stmt->execute([
                ':id' => $templateId,
                ':template_name' => $data['template_name'],
                ':description' => $data['description'] ?? null,
                ':category' => $data['category'] ?? 'general',
                ':trigger_event' => $data['trigger_event'] ?? null,
                ':trigger_conditions' => $data['trigger_conditions'] ?? null,
                ':requires_approval' => $data['requires_approval'] ?? 0,
                ':log_to_communications' => $data['log_to_communications'] ?? 1,
                ':supports_sms' => $data['supports_sms'] ?? 0,
                ':active' => $data['active'] ?? 1
            ]);
        } catch (PDOException $e) {
            error_log("EmailTemplate::updateTemplate() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save template content (insert or update)
     * @param int $templateId
     * @param string $languageCode
     * @param array $content
     * @return bool
     */
    public function saveTemplateContent($templateId, $languageCode, $content)
    {
        try {
            $sql = "INSERT INTO email_template_content 
                    (template_id, language_code, subject, body_html, body_plain_text, updated_by)
                    VALUES (:template_id, :language_code, :subject, :body_html, :body_plain_text, :updated_by)
                    ON DUPLICATE KEY UPDATE
                    subject = VALUES(subject),
                    body_html = VALUES(body_html),
                    body_plain_text = VALUES(body_plain_text),
                    updated_by = VALUES(updated_by)";
            
            $stmt = $this->dbcrm()->prepare($sql);
            return $stmt->execute([
                ':template_id' => $templateId,
                ':language_code' => $languageCode,
                ':subject' => $content['subject'],
                ':body_html' => $content['body_html'],
                ':body_plain_text' => $content['body_plain_text'] ?? null,
                ':updated_by' => $_SESSION['user_id'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("EmailTemplate::saveTemplateContent() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete template (and cascade delete content, variables, triggers)
     * @param int $templateId
     * @return bool
     */
    public function deleteTemplate($templateId)
    {
        try {
            $sql = "DELETE FROM email_templates WHERE id = :id";
            $stmt = $this->dbcrm()->prepare($sql);
            return $stmt->execute([':id' => $templateId]);
        } catch (PDOException $e) {
            error_log("EmailTemplate::deleteTemplate() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add template variable
     * @param int $templateId
     * @param array $variable
     * @return bool
     */
    public function addTemplateVariable($templateId, $variable)
    {
        try {
            $sql = "INSERT INTO email_template_variables 
                    (template_id, variable_key, variable_label, variable_description, 
                     variable_type, variable_source, sort_order)
                    VALUES 
                    (:template_id, :variable_key, :variable_label, :variable_description,
                     :variable_type, :variable_source, :sort_order)";
            
            $stmt = $this->dbcrm()->prepare($sql);
            return $stmt->execute([
                ':template_id' => $templateId,
                ':variable_key' => $variable['variable_key'],
                ':variable_label' => $variable['variable_label'],
                ':variable_description' => $variable['variable_description'] ?? null,
                ':variable_type' => $variable['variable_type'] ?? 'text',
                ':variable_source' => $variable['variable_source'] ?? null,
                ':sort_order' => $variable['sort_order'] ?? 0
            ]);
        } catch (PDOException $e) {
            error_log("EmailTemplate::addTemplateVariable() Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add trigger rule
     * @param int $templateId
     * @param array $rule
     * @return bool
     */
    public function addTriggerRule($templateId, $rule)
    {
        try {
            $sql = "INSERT INTO email_trigger_rules 
                    (template_id, module, trigger_type, trigger_condition, 
                     recipient_type, custom_recipient_email, delay_minutes, active, created_by)
                    VALUES 
                    (:template_id, :module, :trigger_type, :trigger_condition,
                     :recipient_type, :custom_recipient_email, :delay_minutes, :active, :created_by)";
            
            $stmt = $this->dbcrm()->prepare($sql);
            return $stmt->execute([
                ':template_id' => $templateId,
                ':module' => $rule['module'],
                ':trigger_type' => $rule['trigger_type'],
                ':trigger_condition' => is_array($rule['trigger_condition']) 
                    ? json_encode($rule['trigger_condition']) 
                    : $rule['trigger_condition'],
                ':recipient_type' => $rule['recipient_type'] ?? 'lead_contact',
                ':custom_recipient_email' => $rule['custom_recipient_email'] ?? null,
                ':delay_minutes' => $rule['delay_minutes'] ?? 0,
                ':active' => $rule['active'] ?? 1,
                ':created_by' => $_SESSION['user_id'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("EmailTemplate::addTriggerRule() Error: " . $e->getMessage());
            return false;
        }
    }
}
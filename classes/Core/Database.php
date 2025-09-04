<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */
/** Warning - Warning
 * This File will show errors that are not errors following $this->
 * In VSCode not sure about other editors
 */

/**
 * Database Connection Class
 * 
 * SCHEMA OVERVIEW:
 * 
 * Core Entities:
 * - users: User accounts (id, username, full_name, email, role_id, state_id)
 * - leads: Lead management (id, contact_id, service_id, source_id, structure_id, notes)
 * - contacts: Contact info (id, full_name, email, phone, address, city, state, zip)
 * - notes: Linked notes (id, lead_id, contact_id, user_id, note_text, created_at)
 * - communications: Communication history (id, lead_id, contact_id, user_id, type, content, created_at)
 * - sales: Sales pipeline (id, lead_id, amount, status, close_date)
 * 
 * Security:
 * - roles: User roles (id, role_name, description, permissions)
 * - permissions: System permissions (id, permission_name, description, module)  
 * - roles_permissions: Role-permission mapping (role_id, permission_id) - BRIDGE TABLE
 * - user_sessions: Session management (id, user_id, session_token, expires_at)
 * 
 * System:
 * - audit: Activity logging (id, user_id, action, table_name, record_id, old_values, new_values, ip_address, created_at)
 * - internal_errors: Error tracking (id, error_message, file_path, line_number, user_id, created_at)
 * - php_error_log: PHP error tracking (id, error_type, message, file, line, created_at)
 * 
 * Lookup Tables:
 * - lead_sources: Lead source options (id, source_name, description)
 * - lead_services: Available services (id, service_name, description, active)
 * - lead_structures: Structure types (id, structure_name, description)
 * - contact_types: Contact classifications (id, type_name, description)
 * - system_states: Active/inactive states (id, state_name, description)
 * 
 * Bridge Tables:
 * - roles_permissions: roles ↔ permissions (many-to-many)
 * - lead_contacts: leads ↔ contacts (if multiple contacts per lead)
 * - user_permissions: direct user permissions override (user_id, permission_id)
 * 
 * Key Foreign Key Relationships:
 * - users.role_id → roles.id
 * - users.state_id → system_states.id
 * - leads.contact_id → contacts.id
 * - leads.service_id → lead_services.id
 * - leads.source_id → lead_sources.id
 * - leads.structure_id → lead_structures.id
 * - notes.lead_id → leads.id
 * - notes.contact_id → contacts.id
 * - notes.user_id → users.id
 * - audit.user_id → users.id
 * - roles_permissions.role_id → roles.id
 * - roles_permissions.permission_id → permissions.id
 */

class Database
{
    public function __construct()
    {
        // server database connection information
        $this->crm_host = 'localhost';
        $this->crm_database = 'democrm_democrm';
        $this->crm_username = 'democrm_democrm';
        $this->crm_password = 'b3J2sy5T4JNm60';

        $this->character_set = 'utf8mb4';
        $this->options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }
    public function dbcrm()
    {
        static $DBCRM = null;
        if (is_null($DBCRM)) {
            $dsn = 'mysql:host=' . $this->crm_host . ';dbname=' . $this->crm_database . ';charset=' . $this->character_set;
            try {
                $pdo = new \PDO($dsn, $this->crm_username, $this->crm_password, $this->options);
                //echo "Connected successfully";
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
                echo "Connection failed: " . $e->getMessage();
                exit;
            }
            $DBCRM = $pdo;
        }
        return $DBCRM;
    }
}
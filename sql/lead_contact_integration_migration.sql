-- Lead-Contact Integration Migration Script
-- This script adds the necessary database changes to integrate Leads with Contacts

-- Step 1: Add contact_id column to leads table
ALTER TABLE leads ADD COLUMN contact_id INT(11) NULL AFTER id;

-- Step 2: Add index for performance
ALTER TABLE leads ADD INDEX idx_contact_id (contact_id);

-- Step 3: Add foreign key constraint (optional - can be added later)
-- ALTER TABLE leads ADD CONSTRAINT fk_leads_contact_id 
--     FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL;

-- Step 4: Add missing columns to contacts table if they don't exist
-- Check if status column exists, if not add it
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'contacts' 
     AND column_name = 'status' 
     AND table_schema = DATABASE()) > 0,
    'SELECT "status column exists"',
    'ALTER TABLE contacts ADD COLUMN status TINYINT(1) DEFAULT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if created_at column exists, if not add it
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'contacts' 
     AND column_name = 'created_at' 
     AND table_schema = DATABASE()) > 0,
    'SELECT "created_at column exists"',
    'ALTER TABLE contacts ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if updated_at column exists, if not add it
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'contacts' 
     AND column_name = 'updated_at' 
     AND table_schema = DATABASE()) > 0,
    'SELECT "updated_at column exists"',
    'ALTER TABLE contacts ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 5: Create lead_contacts many-to-many relationship table (optional)
CREATE TABLE IF NOT EXISTS lead_contacts (
    id INT(11) NOT NULL AUTO_INCREMENT,
    lead_id INT(11) NOT NULL,
    contact_id INT(11) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    contact_role ENUM('primary', 'secondary', 'decision_maker', 'technical', 'billing') DEFAULT 'primary',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_lead_contact (lead_id, contact_id),
    INDEX idx_lead_id (lead_id),
    INDEX idx_contact_id (contact_id),
    INDEX idx_is_primary (is_primary)
);

-- Step 6: Add foreign key constraints for lead_contacts table (optional)
-- ALTER TABLE lead_contacts ADD CONSTRAINT fk_lead_contacts_lead_id 
--     FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE;
-- ALTER TABLE lead_contacts ADD CONSTRAINT fk_lead_contacts_contact_id 
--     FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE;

-- Step 7: Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_contacts_email ON contacts(personal_email);
CREATE INDEX IF NOT EXISTS idx_contacts_phone ON contacts(cell_phone);
CREATE INDEX IF NOT EXISTS idx_contacts_fullname ON contacts(fullname);
CREATE INDEX IF NOT EXISTS idx_leads_email ON leads(email);
CREATE INDEX IF NOT EXISTS idx_leads_phone ON leads(cell_phone);

-- Step 8: Update existing contacts to have status = 1 if NULL
UPDATE contacts SET status = 1 WHERE status IS NULL;

-- Step 9: Create a view for easy lead-contact queries
CREATE OR REPLACE VIEW lead_contact_view AS
SELECT 
    l.id as lead_id,
    l.lead_number,
    l.stage,
    l.first_name as lead_first_name,
    l.last_name as lead_last_name,
    l.email as lead_email,
    l.cell_phone as lead_phone,
    l.business_name as lead_business_name,
    l.lead_source,
    l.created_at as lead_created_at,
    l.updated_at as lead_updated_at,
    c.id as contact_id,
    c.fullname as contact_fullname,
    c.personal_email as contact_email,
    c.cell_phone as contact_phone,
    c.business_name as contact_business_name,
    c.ctype as contact_type,
    c.p_street_1,
    c.p_street_2,
    c.p_city,
    c.p_state,
    c.p_postcode,
    c.p_country,
    c.created_at as contact_created_at,
    c.updated_at as contact_updated_at
FROM leads l
LEFT JOIN contacts c ON l.contact_id = c.id;

-- Step 10: Create stored procedure for lead-contact migration
DELIMITER //

CREATE PROCEDURE MigrateLeadsToContacts(IN batch_size INT DEFAULT 100)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE lead_id INT;
    DECLARE contact_id INT;
    DECLARE lead_cursor CURSOR FOR 
        SELECT id FROM leads WHERE contact_id IS NULL LIMIT batch_size;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    OPEN lead_cursor;
    
    read_loop: LOOP
        FETCH lead_cursor INTO lead_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Create contact from lead data
        INSERT INTO contacts (
            ctype, first_name, family_name, fullname, 
            cell_phone, personal_email, business_name,
            p_street_1, p_street_2, p_city, p_state, p_postcode, p_country,
            m_street_1, m_street_2, m_city, m_state, m_postcode, m_country,
            phones, emails, status, created_at, updated_at
        )
        SELECT 
            l.ctype,
            l.first_name,
            l.last_name,
            CONCAT(COALESCE(l.first_name, ''), ' ', COALESCE(l.last_name, '')),
            l.cell_phone,
            l.email,
            l.business_name,
            l.form_street_1,
            l.form_street_2,
            l.form_city,
            l.form_state,
            l.form_postcode,
            l.form_country,
            l.form_street_1,
            l.form_street_2,
            l.form_city,
            l.form_state,
            l.form_postcode,
            l.form_country,
            JSON_OBJECT('1', COALESCE(l.cell_phone, ''), '2', '', '3', ''),
            JSON_OBJECT('1', COALESCE(l.email, ''), '2', '', '3', ''),
            1,
            NOW(),
            NOW()
        FROM leads l
        WHERE l.id = lead_id;
        
        SET contact_id = LAST_INSERT_ID();
        
        -- Update lead with contact_id
        UPDATE leads SET contact_id = contact_id WHERE id = lead_id;
        
    END LOOP;
    
    CLOSE lead_cursor;
    COMMIT;
    
    SELECT CONCAT('Migrated ', ROW_COUNT(), ' leads to contacts') as result;
    
END //

DELIMITER ;

-- Step 11: Create function to find duplicate contacts
DELIMITER //

CREATE FUNCTION FindDuplicateContact(p_email VARCHAR(255), p_phone VARCHAR(15))
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE contact_id INT DEFAULT NULL;
    
    -- First try to find by email
    IF p_email IS NOT NULL AND p_email != '' THEN
        SELECT id INTO contact_id 
        FROM contacts 
        WHERE personal_email = p_email 
           OR business_email = p_email 
           OR alt_email = p_email
        LIMIT 1;
    END IF;
    
    -- If not found by email, try by phone
    IF contact_id IS NULL AND p_phone IS NOT NULL AND p_phone != '' THEN
        SELECT id INTO contact_id 
        FROM contacts 
        WHERE REPLACE(REPLACE(REPLACE(cell_phone, '-', ''), '(', ''), ')', '') = REPLACE(REPLACE(REPLACE(p_phone, '-', ''), '(', ''), ')', '')
           OR REPLACE(REPLACE(REPLACE(business_phone, '-', ''), '(', ''), ')', '') = REPLACE(REPLACE(REPLACE(p_phone, '-', ''), '(', ''), ')', '')
        LIMIT 1;
    END IF;
    
    RETURN contact_id;
END //

DELIMITER ;

-- Step 12: Create trigger to automatically create contact when lead is inserted
DELIMITER //

CREATE TRIGGER after_lead_insert
AFTER INSERT ON leads
FOR EACH ROW
BEGIN
    DECLARE existing_contact_id INT;
    DECLARE new_contact_id INT;
    
    -- Only create contact if contact_id is not already set
    IF NEW.contact_id IS NULL THEN
        -- Check for existing contact
        SET existing_contact_id = FindDuplicateContact(NEW.email, NEW.cell_phone);
        
        IF existing_contact_id IS NOT NULL THEN
            -- Link to existing contact
            UPDATE leads SET contact_id = existing_contact_id WHERE id = NEW.id;
        ELSE
            -- Create new contact
            INSERT INTO contacts (
                ctype, first_name, family_name, fullname, 
                cell_phone, personal_email, business_name,
                p_street_1, p_street_2, p_city, p_state, p_postcode, p_country,
                m_street_1, m_street_2, m_city, m_state, m_postcode, m_country,
                phones, emails, status, created_at, updated_at
            ) VALUES (
                NEW.ctype,
                NEW.first_name,
                NEW.last_name,
                CONCAT(COALESCE(NEW.first_name, ''), ' ', COALESCE(NEW.last_name, '')),
                NEW.cell_phone,
                NEW.email,
                NEW.business_name,
                NEW.form_street_1,
                NEW.form_street_2,
                NEW.form_city,
                NEW.form_state,
                NEW.form_postcode,
                NEW.form_country,
                NEW.form_street_1,
                NEW.form_street_2,
                NEW.form_city,
                NEW.form_state,
                NEW.form_postcode,
                NEW.form_country,
                JSON_OBJECT('1', COALESCE(NEW.cell_phone, ''), '2', '', '3', ''),
                JSON_OBJECT('1', COALESCE(NEW.email, ''), '2', '', '3', ''),
                1,
                NOW(),
                NOW()
            );
            
            SET new_contact_id = LAST_INSERT_ID();
            
            -- Link lead to new contact
            UPDATE leads SET contact_id = new_contact_id WHERE id = NEW.id;
        END IF;
    END IF;
END //

DELIMITER ;

-- Step 13: Add comments to document the changes
ALTER TABLE leads COMMENT = 'Leads table with contact integration - contact_id links to contacts table';
ALTER TABLE contacts COMMENT = 'Contacts table - normalized contact information for leads and other entities';
ALTER TABLE lead_contacts COMMENT = 'Many-to-many relationship between leads and contacts (optional)';

-- Migration completed
SELECT 'Lead-Contact integration migration completed successfully' as status;
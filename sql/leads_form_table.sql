CREATE TABLE
    leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lead_source TINYINT NOT NULL DEFAULT 1, -- 1-6: Uses get_lead_source_array keys
        -- Contact Information
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        cell_phone VARCHAR(15),
        email VARCHAR(255) NOT NULL,
        ctype TINYINT DEFAULT 1, -- 1-5: Uses get_lead_contact_type_array keys
        -- Message/Notes
        notes TEXT,
        -- Estimate Information
        estimate_number VARCHAR(50),
        -- Address Information
        p_street_1 VARCHAR(100),
        p_street_2 VARCHAR(50),
        p_city VARCHAR(50),
        p_state VARCHAR(10),
        p_postcode VARCHAR(15),
        p_country VARCHAR(5) DEFAULT 'US',
        -- Services Interested In (stored as comma-separated numeric values: 1,2,3,etc.)
        services_interested_in VARCHAR(20), -- Stores values like "1,3,5" from helper array keys
        -- Structure Information
        structure_type TINYINT DEFAULT 1, -- 1-6: Uses get_lead_structure_type_array keys
        structure_description VARCHAR(20), -- Stores values like "1,2,4" from helper array keys
        structure_other VARCHAR(255),
        structure_additional TEXT,
        -- Pictures and Plans
        picture_submitted_1 VARCHAR(255),
        picture_submitted_2 VARCHAR(255),
        picture_submitted_3 VARCHAR(255),
        plans_submitted_1 VARCHAR(255),
        plans_submitted_2 VARCHAR(255),
        plans_submitted_3 VARCHAR(255),
        -- File Upload Links
        picture_upload_link VARCHAR(500),
        plans_upload_link VARCHAR(500),
        plans_and_pics INT(1) DEFAULT 0,
        -- Communication Preferences
        get_updates INT(1) DEFAULT 1,
        -- Marketing Information (stored as comma-separated numeric values: 1,2,3,etc.)
        hear_about VARCHAR(20), -- Stores values like "1,4,6" from helper array keys
        hear_about_other VARCHAR(255),
        -- Lead Status
        stage VARCHAR(20) DEFAULT 'Lead',
        -- Timestamps
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        -- Indexes
        INDEX idx_lead_source (lead_source),
        INDEX idx_email (email),
        INDEX idx_stage (stage),
        INDEX idx_created_at (created_at),
        INDEX idx_state (p_state),
        INDEX idx_country (p_country),
        INDEX idx_structure_type (structure_type)
    );
-- CRM Tasks/Events Database Schema
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    task_type ENUM('call', 'email', 'meeting', 'follow_up') NOT NULL DEFAULT 'call',
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    contact_name VARCHAR(255),
    contact_phone VARCHAR(50),
    contact_email VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample data
INSERT INTO tasks (title, description, task_type, start_datetime, end_datetime, contact_name, contact_phone, contact_email, priority) VALUES
('Call John Smith', 'Follow up on product demo', 'call', '2024-12-10 10:00:00', '2024-12-10 10:30:00', 'John Smith', '+1-555-0123', 'john@example.com', 'high'),
('Send proposal email', 'Send pricing proposal to ABC Corp', 'email', '2024-12-11 14:00:00', '2024-12-11 14:30:00', 'Sarah Johnson', NULL, 'sarah@abccorp.com', 'medium'),
('Client meeting', 'Quarterly review meeting', 'meeting', '2024-12-12 09:00:00', '2024-12-12 10:00:00', 'Mike Davis', '+1-555-0456', 'mike@clientcorp.com', 'high');
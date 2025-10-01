-- Calendar Integration Migration
-- Priority: 1-10 integer system for future features
-- Full integration with existing CRM framework
-- Calendar Events/Tasks Table
CREATE TABLE
  `calendar_events` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `user_id` int (11) NOT NULL,
    `lead_id` int (11) DEFAULT NULL,
    `contact_id` int (11) DEFAULT NULL,
    `title` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `event_type` int (11) NOT NULL DEFAULT 1 COMMENT '1=call, 2=email, 3=text, 4=internal, 5=virtual_meeting, 6=in_person',
    `start_datetime` datetime NOT NULL,
    `end_datetime` datetime DEFAULT NULL,
    `all_day` tinyint (1) NOT NULL DEFAULT 0,
    `status` int (11) NOT NULL DEFAULT 1 COMMENT '1=pending, 2=completed, 3=cancelled, 4=in_progress',
    `priority` int (11) NOT NULL DEFAULT 5 COMMENT '1-10 priority system (1=lowest, 10=highest)',
    `location` varchar(255) DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `reminder_minutes` int (11) DEFAULT NULL COMMENT 'Minutes before event to remind',
    `is_recurring` tinyint (1) NOT NULL DEFAULT 0,
    `recurrence_rule` text DEFAULT NULL COMMENT 'RRULE for recurring events',
    `timezone` varchar(50) DEFAULT 'UTC',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_by` int (11) NOT NULL,
    `updated_by` int (11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_lead_id` (`lead_id`),
    KEY `idx_contact_id` (`contact_id`),
    KEY `idx_start_datetime` (`start_datetime`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_status` (`status`),
    KEY `idx_priority` (`priority`),
    CONSTRAINT `fk_calendar_events_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_calendar_events_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_calendar_events_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_calendar_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
    CONSTRAINT `fk_calendar_events_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Calendar Event Attendees (for meetings)
CREATE TABLE
  `calendar_event_attendees` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `event_id` int (11) NOT NULL,
    `contact_id` int (11) DEFAULT NULL,
    `user_id` int (11) DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    `name` varchar(255) DEFAULT NULL,
    `response_status` int (11) NOT NULL DEFAULT 1 COMMENT '1=pending, 2=accepted, 3=declined, 4=tentative',
    `is_organizer` tinyint (1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_event_id` (`event_id`),
    KEY `idx_contact_id` (`contact_id`),
    KEY `idx_user_id` (`user_id`),
    CONSTRAINT `fk_attendees_event` FOREIGN KEY (`event_id`) REFERENCES `calendar_events` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_attendees_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_attendees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Calendar Event Reminders
CREATE TABLE
  `calendar_event_reminders` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `event_id` int (11) NOT NULL,
    `user_id` int (11) NOT NULL,
    `reminder_datetime` datetime NOT NULL,
    `reminder_type` int (11) NOT NULL DEFAULT 1 COMMENT '1=email, 2=sms, 3=push, 4=popup',
    `is_sent` tinyint (1) NOT NULL DEFAULT 0,
    `sent_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_event_id` (`event_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_reminder_datetime` (`reminder_datetime`),
    KEY `idx_is_sent` (`is_sent`),
    CONSTRAINT `fk_reminders_event` FOREIGN KEY (`event_id`) REFERENCES `calendar_events` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reminders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Calendar Settings per User
CREATE TABLE
  `calendar_user_settings` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `user_id` int (11) NOT NULL,
    `default_view` varchar(20) NOT NULL DEFAULT 'month' COMMENT 'month, week, day, list',
    `work_hours_start` time NOT NULL DEFAULT '09:00:00',
    `work_hours_end` time NOT NULL DEFAULT '17:00:00',
    `work_days` varchar(20) NOT NULL DEFAULT '1,2,3,4,5' COMMENT 'Comma-separated day numbers (0=Sunday)',
    `default_event_duration` int (11) NOT NULL DEFAULT 60 COMMENT 'Default duration in minutes',
    `timezone` varchar(50) NOT NULL DEFAULT 'UTC',
    `email_reminders` tinyint (1) NOT NULL DEFAULT 1,
    `popup_reminders` tinyint (1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_id` (`user_id`),
    CONSTRAINT `fk_calendar_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Insert default settings for existing users
INSERT INTO
  `calendar_user_settings` (`user_id`, `timezone`)
SELECT
  `id`,
  COALESCE(`timezone`, 'UTC')
FROM
  `users`;

-- Event Type Definitions (matching existing Next Action types)
CREATE TABLE
  `calendar_event_types` (
    `id` int (11) NOT NULL,
    `name` varchar(50) NOT NULL,
    `description` varchar(255) DEFAULT NULL,
    `color` varchar(7) NOT NULL DEFAULT '#007bff',
    `icon` varchar(50) DEFAULT NULL,
    `is_active` tinyint (1) NOT NULL DEFAULT 1,
    `sort_order` int (11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Insert default event types (matching Next Action types)
INSERT INTO
  `calendar_event_types` (
    `id`,
    `name`,
    `description`,
    `color`,
    `icon`,
    `sort_order`
  )
VALUES
  (
    1,
    'Phone Call',
    'Phone call with contact',
    '#007bff',
    'fas fa-phone',
    1
  ),
  (
    2,
    'Email',
    'Email communication',
    '#28a745',
    'fas fa-envelope',
    2
  ),
  (
    3,
    'Text Message',
    'SMS/Text message',
    '#17a2b8',
    'fas fa-sms',
    3
  ),
  (
    4,
    'Internal Note',
    'Internal team note',
    '#6c757d',
    'fas fa-sticky-note',
    6
  ),
  (
    5,
    'Virtual Meeting',
    'Online meeting/video call',
    '#ffc107',
    'fas fa-video',
    4
  ),
  (
    6,
    'In-Person Meeting',
    'Face-to-face meeting',
    '#fd7e14',
    'fas fa-users',
    5
  );

-- Priority Definitions (1-10 system)
CREATE TABLE
  `calendar_priorities` (
    `id` int (11) NOT NULL,
    `name` varchar(50) NOT NULL,
    `description` varchar(255) DEFAULT NULL,
    `color` varchar(7) NOT NULL,
    `is_active` tinyint (1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Insert priority definitions
INSERT INTO
  `calendar_priorities` (`id`, `name`, `description`, `color`)
VALUES
  (
    1,
    'Lowest',
    'Lowest priority - can be delayed',
    '#e3f2fd'
  ),
  (2, 'Very Low', 'Very low priority', '#bbdefb'),
  (3, 'Low', 'Low priority', '#90caf9'),
  (
    4,
    'Below Normal',
    'Below normal priority',
    '#64b5f6'
  ),
  (
    5,
    'Normal',
    'Normal priority - default',
    '#42a5f5'
  ),
  (
    6,
    'Above Normal',
    'Above normal priority',
    '#2196f3'
  ),
  (7, 'High', 'High priority', '#1976d2'),
  (8, 'Very High', 'Very high priority', '#1565c0'),
  (9, 'Critical', 'Critical priority', '#0d47a1'),
  (
    10,
    'Urgent',
    'Urgent - highest priority',
    '#b71c1c'
  );
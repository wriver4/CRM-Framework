-- Fix Calendar Priorities for Multilingual Support
-- Remove hardcoded English text and use language keys instead
-- This follows the framework's multilingual pattern
-- Drop the existing calendar_priorities table
DROP TABLE IF EXISTS `calendar_priorities`;

-- Recreate calendar_priorities table without hardcoded text
CREATE TABLE
  `calendar_priorities` (
    `id` int (11) NOT NULL,
    `language_key` varchar(50) NOT NULL COMMENT 'Language key for translation (e.g., priority_lowest)',
    `color` varchar(7) NOT NULL,
    `is_active` tinyint (1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_language_key` (`language_key`),
    KEY `idx_is_active` (`is_active`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Insert priority definitions with language keys
INSERT INTO
  `calendar_priorities` (`id`, `language_key`, `color`)
VALUES
  (1, 'priority_lowest', '#e3f2fd'),
  (2, 'priority_very_low', '#bbdefb'),
  (3, 'priority_low', '#90caf9'),
  (4, 'priority_below_normal', '#64b5f6'),
  (5, 'priority_normal', '#42a5f5'),
  (6, 'priority_above_normal', '#2196f3'),
  (7, 'priority_high', '#1976d2'),
  (8, 'priority_very_high', '#1565c0'),
  (9, 'priority_critical', '#0d47a1'),
  (10, 'priority_urgent', '#b71c1c');

-- Also fix calendar_event_types table to use language keys
DROP TABLE IF EXISTS `calendar_event_types`;

CREATE TABLE
  `calendar_event_types` (
    `id` int (11) NOT NULL,
    `language_key` varchar(50) NOT NULL COMMENT 'Language key for translation (e.g., event_type_phone_call)',
    `color` varchar(7) NOT NULL DEFAULT '#007bff',
    `icon` varchar(50) DEFAULT NULL,
    `is_active` tinyint (1) NOT NULL DEFAULT 1,
    `sort_order` int (11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_language_key` (`language_key`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_sort_order` (`sort_order`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Insert event types with language keys (matching Next Action types)
INSERT INTO
  `calendar_event_types` (
    `id`,
    `language_key`,
    `color`,
    `icon`,
    `sort_order`
  )
VALUES
  (
    1,
    'event_type_phone_call',
    '#007bff',
    'fas fa-phone',
    1
  ),
  (
    2,
    'event_type_email',
    '#28a745',
    'fas fa-envelope',
    2
  ),
  (
    3,
    'event_type_text_message',
    '#17a2b8',
    'fas fa-sms',
    3
  ),
  (
    4,
    'event_type_internal_note',
    '#6c757d',
    'fas fa-sticky-note',
    6
  ),
  (
    5,
    'event_type_virtual_meeting',
    '#ffc107',
    'fas fa-video',
    4
  ),
  (
    6,
    'event_type_in_person_meeting',
    '#fd7e14',
    'fas fa-users',
    5
  );
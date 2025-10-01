-- Drop Calendar Priorities Table Migration
-- Replace hardcoded priority table with helper class approach
-- This follows the CRM's standard pattern for integer-based labels
-- Drop the calendar_priorities table since we'll use helper methods instead
DROP TABLE IF EXISTS `calendar_priorities`;

-- Also drop calendar_event_types table to be consistent with helper class pattern
DROP TABLE IF EXISTS `calendar_event_types`;

-- The calendar_events table already has integer priority field (1-10)
-- and integer event_type field (1-6) which will be handled by helper methods
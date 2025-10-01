-- Add timezone column to users table
-- This will be used for calendar integration and user preferences
ALTER TABLE `users`
ADD COLUMN `timezone` varchar(50) DEFAULT 'UTC' COMMENT 'User timezone (e.g., America/New_York)' AFTER `language`;

-- Add index for timezone lookups
ALTER TABLE `users` ADD KEY `idx_users_timezone` (`timezone`);

-- Update existing users with default timezone
UPDATE `users`
SET
  `timezone` = 'UTC'
WHERE
  `timezone` IS NULL;
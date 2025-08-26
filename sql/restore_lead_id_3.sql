-- SQL to restore lead record ID 3 to original values
-- Execute this in phpMyAdmin to fix the corrupted record

UPDATE `leads` SET 
    `lead_number` = 1320,
    `stage` = '8',
    `first_name` = 'Patrick',
    `family_name` = 'Lai',
    `full_name` = 'Patrick Lai',
    `cell_phone` = '303-547-2397',
    `email` = 'patrick@laionline.com',
    `business_name` = NULL,
    `project_name` = NULL,
    `ctype` = 1,
    `form_street_1` = '334 Red Lily Place',
    `form_street_2` = '',
    `form_city` = 'Evergreen',
    `form_state` = 'CO',
    `form_postcode` = '80439',
    `form_country` = 'US',
    `timezone` = NULL,
    `full_address` = '334 Red Lily Place,   Evergreen,  CO 80439,   US',
    `services_interested_in` = '',
    `structure_type` = 1,
    `structure_description` = 'Two Story',
    `structure_other` = NULL,
    `structure_additional` = NULL,
    `picture_submitted_1` = 'true',
    `picture_submitted_2` = NULL,
    `picture_submitted_3` = NULL,
    `plans_submitted_1` = NULL,
    `plans_submitted_2` = NULL,
    `plans_submitted_3` = NULL,
    `picture_submitted` = 'true',
    `plans_submitted` = NULL,
    `get_updates` = 0,
    `hear_about` = NULL,
    `hear_about_other` = NULL,
    `picture_upload_link` = NULL,
    `plans_upload_link` = 'https://cloud.waveguardco.net/index.php/s/SbNLPLTWm7bbjGC',
    `plans_and_pics` = 1,
    `lead_source` = 1,
    `last_edited_by` = 45,
    `updated_at` = '2025-08-24 18:30:39'
WHERE `id` = 3;

-- Verify the update
SELECT * FROM `leads` WHERE `id` = 3;
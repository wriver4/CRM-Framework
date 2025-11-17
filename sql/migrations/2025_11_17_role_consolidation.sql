START TRANSACTION;

CREATE TABLE IF NOT EXISTS roles_backup_2025_11_17 AS
SELECT * FROM roles;

DELETE FROM roles
WHERE role_id NOT IN (
  1, 2, 10, 11, 12, 13, 14,
  30, 35,
  40, 41, 42, 43,
  50, 51, 52,
  60,
  70, 72,
  80, 82,
  90,
  100, 110, 120, 130, 140, 150,
  160, 161, 162, 163
);

INSERT INTO roles (role_id, role)
VALUES
  (1, 'Super Administrator'),
  (2, 'Administrator'),
  (10, 'President'),
  (11, 'Vice President'),
  (12, 'Chief Information Officer'),
  (13, 'Chief Technology Officer'),
  (14, 'Chief Marketing Officer'),
  (30, 'Sales Manager'),
  (35, 'Sales Assistant'),
  (40, 'Engineering Manager'),
  (41, 'Tech Lead'),
  (42, 'Technician 1'),
  (43, 'Technician 2'),
  (50, 'Manufacturing Manager'),
  (51, 'Manufacturing Tech 1'),
  (52, 'Manufacturing Tech 2'),
  (60, 'Field Service Manager'),
  (70, 'HR Manager'),
  (72, 'Office Manager'),
  (80, 'Accounting Manager'),
  (82, 'AP/AR Clerk'),
  (90, 'Support Manager'),
  (100, 'Strategic Partner'),
  (110, 'Vendor'),
  (120, 'Distributor'),
  (130, 'Installer'),
  (140, 'Applicator'),
  (150, 'Contractor'),
  (160, 'Client Standard'),
  (161, 'Client Restricted'),
  (162, 'Client Advanced'),
  (163, 'Client Status')
ON DUPLICATE KEY UPDATE
  role = VALUES(role),
  updated_at = NOW();

COMMIT;

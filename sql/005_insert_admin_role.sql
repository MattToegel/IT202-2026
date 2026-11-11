INSERT INTO Roles (id, name, description, is_active)
VALUES (-1, 'Admin', 'Administrator', 1)
ON DUPLICATE KEY UPDATE description = 'Administrator', is_active = 1;

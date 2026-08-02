INSERT INTO settings (setting_key, setting_value) VALUES
('contact_phone',     '0906 088 4920'),
('contact_phone_alt', '0806 142 8556'),
('whatsapp',          '2348061428556'),
('address',           'Odakpo Close, Doctor Street, off Specialist Hospital, Asaba')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

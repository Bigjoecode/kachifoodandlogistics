INSERT INTO settings (setting_key, setting_value) VALUES
('whatsapp',   '2349060884920'),
('cac_number', 'RC: 9651491')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Migration v2: Bangladesh-wide polytechnic site
-- Add section/group support to credits table
ALTER TABLE credits ADD COLUMN IF NOT EXISTS section VARCHAR(100) DEFAULT 'সাধারণ';

-- Create polytechnics table
CREATE TABLE IF NOT EXISTS polytechnics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    location VARCHAR(500),
    website VARCHAR(500),
    image VARCHAR(500),
    status TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'পলিটেকনিক শিক্ষা বাংলাদেশ'),
('site_tagline', 'বাংলাদেশের পলিটেকনিক শিক্ষা তথ্য পোর্টাল'),
('site_email', 'info@polytechnic.edu.bd'),
('site_address', 'বাংলাদেশ')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

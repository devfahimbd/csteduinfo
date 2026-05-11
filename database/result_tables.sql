-- Result System Tables for BTEB Diploma Engineering Results
-- Add this to existing cst_department database

-- Result Batches (each PDF upload creates a batch)
CREATE TABLE IF NOT EXISTS result_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_year VARCHAR(10) NOT NULL,
    regulation_year VARCHAR(10) NOT NULL,
    semester VARCHAR(50) NOT NULL,
    program VARCHAR(100) NOT NULL DEFAULT 'Diploma In Engineering',
    pdf_file VARCHAR(255),
    total_students INT DEFAULT 0,
    total_passed INT DEFAULT 0,
    total_failed INT DEFAULT 0,
    status ENUM('processing','completed','failed') DEFAULT 'processing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_batch_search (exam_year, regulation_year, semester, program)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subject Codes (BTEB subject code to name mapping)
CREATE TABLE IF NOT EXISTS result_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    subject_name VARCHAR(200) NOT NULL,
    t_full_name VARCHAR(100) DEFAULT 'Theory',
    p_full_name VARCHAR(100) DEFAULT 'Practical',
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Student Results
CREATE TABLE IF NOT EXISTS result_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    roll VARCHAR(20) NOT NULL,
    college_code VARCHAR(20) NOT NULL,
    college_name VARCHAR(300) NOT NULL,
    gpa DECIMAL(4,2) DEFAULT NULL,
    result_type ENUM('passed','referred','failed_4plus') NOT NULL DEFAULT 'passed',
    failed_subjects_json TEXT,
    failed_subjects_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES result_batches(id) ON DELETE CASCADE,
    INDEX idx_roll (roll),
    INDEX idx_college (college_code),
    INDEX idx_batch_roll (batch_id, roll),
    INDEX idx_result_type (result_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default BTEB 1st Semester 2022 Regulation Subject Codes
INSERT INTO result_subjects (subject_code, subject_name, t_full_name, p_full_name) VALUES
('21011', 'Bangla (Compulsory)', 'Theory', 'Practical'),
('25711', 'Mathematics-1', 'Theory', 'Practical'),
('25712', 'Mathematics-1 (Sessional)', 'Theory', 'Practical'),
('25811', 'Physics-1', 'Theory', 'Practical'),
('25812', 'Physics-1 (Sessional)', 'Theory', 'Practical'),
('25831', 'Chemistry', 'Theory', 'Practical'),
('25841', 'Chemistry (Sessional)', 'Theory', 'Practical'),
('25911', 'Engineering Drawing-1', 'Theory', 'Practical'),
('25912', 'Engineering Drawing-1 (Sessional)', 'Theory', 'Practical'),
('25913', 'Electrical Engineering Fundamentals', 'Theory', 'Practical'),
('26111', 'Basic Electronics', 'Theory', 'Practical'),
('26211', 'Basic Workshop Practice-1', 'Theory', 'Practical'),
('26311', 'Computer Fundamentals', 'Theory', 'Practical'),
('26312', 'Computer Fundamentals (Sessional)', 'Theory', 'Practical'),
('26411', 'Mechanical Engineering Fundamentals', 'Theory', 'Practical'),
('26711', 'Workshop Practice-1', 'Theory', 'Practical'),
('26712', 'Workshop Practice-1 (Sessional)', 'Theory', 'Practical'),
('26811', 'Civil Engineering Drawing', 'Theory', 'Practical'),
('26911', 'Business Organization & Communication', 'Theory', 'Practical'),
('26912', 'Business Organization & Communication (Sessional)', 'Theory', 'Practical'),
('27011', 'Accounting', 'Theory', 'Practical'),
('27012', 'Accounting (Sessional)', 'Theory', 'Practical'),
('27111', 'Environmental Science', 'Theory', 'Practical'),
('27211', 'Physical Education & Life Skill Development', 'Theory', 'Practical'),
('27611', 'Basic Electrical Engineering', 'Theory', 'Practical'),
('27711', 'Basic Electronics Engineering', 'Theory', 'Practical'),
('28211', 'Programming with C', 'Theory', 'Practical'),
('28511', 'Islamic Studies / Bangladesh & World Civilization', 'Theory', 'Practical'),
('28611', 'Engineering Materials & Processes', 'Theory', 'Practical'),
('29011', 'Basic Mechanical Engineering', 'Theory', 'Practical'),
('29311', 'Surveying', 'Theory', 'Practical'),
('29411', 'Construction Materials & Practice', 'Theory', 'Practical'),
('29511', 'Applied Mechanics', 'Theory', 'Practical'),
('29911', 'Civil Engineering Materials', 'Theory', 'Practical'),
('29912', 'Civil Engineering Materials (Sessional)', 'Theory', 'Practical');

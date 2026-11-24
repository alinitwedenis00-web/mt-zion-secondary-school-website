USE mt_zion_school;

-- Safe to run more than once. Adds a reference table for admission classes.
CREATE TABLE IF NOT EXISTS admission_classes (
    class_code VARCHAR(30) PRIMARY KEY,
    display_name VARCHAR(80) NOT NULL,
    level_group VARCHAR(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO admission_classes (class_code, display_name, level_group) VALUES
('S.1', 'Senior One', 'O-Level'),
('S.2', 'Senior Two', 'O-Level'),
('S.3', 'Senior Three', 'O-Level'),
('S.4', 'Senior Four', 'O-Level'),
('S.5', 'Senior Five', 'A-Level'),
('S.6', 'Senior Six', 'A-Level');

-- Existing admission_enquiries.class_applying values are retained.
-- The admin report joins applications to this reference table.

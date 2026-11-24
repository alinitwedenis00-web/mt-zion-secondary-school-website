USE mt_zion_school;
CREATE TABLE IF NOT EXISTS board_members (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(150) NOT NULL,
 role VARCHAR(150),
 image VARCHAR(255),
 bio TEXT,
 sort_order INT DEFAULT 0
);
-- Add bio to staff if your existing database was created from the earlier starter version.
-- Run this only if the staff table does not already have a bio column:
-- ALTER TABLE staff ADD COLUMN bio TEXT;

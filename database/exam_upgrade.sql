USE mt_zion_school;

-- Run once on an existing installation. If the status column already exists,
-- skip the ALTER TABLE statement and run the CREATE TABLE statement.
ALTER TABLE admission_enquiries
  ADD COLUMN status VARCHAR(30) NOT NULL DEFAULT 'Pending';

CREATE TABLE IF NOT EXISTS admin_login_attempts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  ip_hash CHAR(64) NOT NULL,
  attempted_at DATETIME NOT NULL,
  INDEX idx_login_ip_time (ip_hash, attempted_at)
);

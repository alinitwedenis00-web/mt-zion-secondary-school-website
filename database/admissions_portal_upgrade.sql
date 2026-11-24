-- Mt Zion Secondary School admissions portal upgrade
-- BACK UP mt_zion_school before running. Select mt_zion_school in phpMyAdmin.
USE mt_zion_school;

-- Ensure the status field exists even if the earlier repair was not completed.
SET @status_column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='admission_enquiries' AND COLUMN_NAME='status');
SET @status_sql = IF(@status_column_exists=0,
  'ALTER TABLE admission_enquiries ADD COLUMN status VARCHAR(30) NOT NULL DEFAULT ''Submitted''',
  'SELECT ''status column already exists''');
PREPARE status_stmt FROM @status_sql;
EXECUTE status_stmt;
DEALLOCATE PREPARE status_stmt;

CREATE TABLE IF NOT EXISTS admission_classes (
  class_code VARCHAR(30) PRIMARY KEY,
  display_name VARCHAR(80) NOT NULL,
  level_group VARCHAR(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO admission_classes(class_code,display_name,level_group) VALUES
('S.1','Senior One','O-Level'),('S.2','Senior Two','O-Level'),('S.3','Senior Three','O-Level'),
('S.4','Senior Four','O-Level'),('S.5','Senior Five','A-Level'),('S.6','Senior Six','A-Level');

ALTER TABLE admission_enquiries
  ADD COLUMN application_no VARCHAR(30) NULL AFTER id,
  ADD COLUMN date_of_birth DATE NULL,
  ADD COLUMN gender VARCHAR(30) NULL,
  ADD COLUMN parent_name VARCHAR(150) NULL,
  ADD COLUMN parent_phone VARCHAR(50) NULL,
  ADD COLUMN parent_email VARCHAR(190) NULL,
  ADD COLUMN applicant_address VARCHAR(1000) NULL,
  ADD COLUMN previous_school VARCHAR(190) NULL,
  ADD COLUMN last_class VARCHAR(50) NULL,
  ADD COLUMN year_completed SMALLINT NULL,
  ADD COLUMN results_summary TEXT NULL,
  ADD COLUMN admin_comment TEXT NULL,
  ADD COLUMN updated_at DATETIME NULL;

UPDATE admission_enquiries SET status='Submitted' WHERE status IS NULL OR status='Pending';
UPDATE admission_enquiries SET application_no=CONCAT('MZS-',YEAR(created_at),'-LEGACY',LPAD(id,6,'0')) WHERE application_no IS NULL OR application_no='';
ALTER TABLE admission_enquiries ADD UNIQUE KEY uq_admission_application_no (application_no);

CREATE TABLE IF NOT EXISTS admission_documents (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  application_id INT NOT NULL,
  document_type VARCHAR(40) NOT NULL,
  stored_name VARCHAR(100) NOT NULL,
  original_name VARCHAR(190) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  file_size INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admission_documents_application (application_id),
  CONSTRAINT fk_admission_documents_application FOREIGN KEY (application_id) REFERENCES admission_enquiries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admission_status_history (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  application_id INT NOT NULL,
  old_status VARCHAR(30) NULL,
  new_status VARCHAR(30) NOT NULL,
  changed_by VARCHAR(100) NOT NULL,
  comment TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admission_history_application (application_id),
  CONSTRAINT fk_admission_history_application FOREIGN KEY (application_id) REFERENCES admission_enquiries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admission_notification_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  application_id INT NOT NULL,
  channel VARCHAR(20) NOT NULL,
  recipient VARCHAR(190) NOT NULL,
  notification_status VARCHAR(30) NOT NULL,
  provider_response TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admission_notification_application (application_id),
  CONSTRAINT fk_admission_notification_application FOREIGN KEY (application_id) REFERENCES admission_enquiries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Preserve existing applications in the progress timeline.
INSERT INTO admission_status_history(application_id,old_status,new_status,changed_by,comment)
SELECT id,NULL,COALESCE(NULLIF(status,''),'Submitted'),'System','Existing application imported into the admissions portal'
FROM admission_enquiries
WHERE id NOT IN (SELECT application_id FROM admission_status_history);

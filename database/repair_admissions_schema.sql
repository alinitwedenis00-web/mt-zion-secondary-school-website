-- Mt Zion Secondary School: consolidated admissions + admin schema repair
-- BACK UP the mt_zion_school database before running this script.
-- Safe to re-run: existing applications and their data are retained.
USE mt_zion_school;

-- The admissions tables use foreign keys, so the parent must use InnoDB.
ALTER TABLE admission_enquiries ENGINE=InnoDB;

DELIMITER $$
DROP PROCEDURE IF EXISTS mz_ensure_column$$
CREATE PROCEDURE mz_ensure_column(
    IN p_table VARCHAR(64),
    IN p_column VARCHAR(64),
    IN p_definition TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=p_table AND COLUMN_NAME=p_column
    ) THEN
        SET @mz_sql=CONCAT('ALTER TABLE `',REPLACE(p_table,'`','``'),'` ADD COLUMN `',
                           REPLACE(p_column,'`','``'),'` ',p_definition);
        PREPARE mz_stmt FROM @mz_sql;
        EXECUTE mz_stmt;
        DEALLOCATE PREPARE mz_stmt;
    END IF;
END$$
CREATE PROCEDURE mz_ensure_index(
    IN p_table VARCHAR(64),
    IN p_index VARCHAR(64),
    IN p_definition TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=p_table AND INDEX_NAME=p_index
    ) THEN
        SET @mz_sql=CONCAT('ALTER TABLE `',REPLACE(p_table,'`','``'),'` ADD ',p_definition);
        PREPARE mz_stmt FROM @mz_sql;
        EXECUTE mz_stmt;
        DEALLOCATE PREPARE mz_stmt;
    END IF;
END$$
DELIMITER ;

-- Columns required by the live admissions PHP code.
CALL mz_ensure_column('admission_enquiries','status',"VARCHAR(30) NOT NULL DEFAULT 'Submitted'");
CALL mz_ensure_column('admission_enquiries','application_no','VARCHAR(30) NULL');
CALL mz_ensure_column('admission_enquiries','date_of_birth','DATE NULL');
CALL mz_ensure_column('admission_enquiries','gender','VARCHAR(30) NULL');
CALL mz_ensure_column('admission_enquiries','parent_name','VARCHAR(150) NULL');
CALL mz_ensure_column('admission_enquiries','parent_phone','VARCHAR(50) NULL');
CALL mz_ensure_column('admission_enquiries','parent_email','VARCHAR(190) NULL');
CALL mz_ensure_column('admission_enquiries','applicant_address','VARCHAR(1000) NULL');
CALL mz_ensure_column('admission_enquiries','previous_school','VARCHAR(190) NULL');
CALL mz_ensure_column('admission_enquiries','last_class','VARCHAR(50) NULL');
CALL mz_ensure_column('admission_enquiries','year_completed','SMALLINT NULL');
CALL mz_ensure_column('admission_enquiries','results_summary','TEXT NULL');
CALL mz_ensure_column('admission_enquiries','admin_comment','TEXT NULL');
CALL mz_ensure_column('admission_enquiries','updated_at','DATETIME NULL');

UPDATE admission_enquiries
SET status='Submitted'
WHERE status IS NULL OR status='' OR status='Pending';

UPDATE admission_enquiries
SET application_no=CONCAT('MZS-',YEAR(created_at),'-LEGACY',LPAD(id,6,'0'))
WHERE application_no IS NULL OR application_no='';

-- Resolve any duplicate non-empty references before enforcing uniqueness.
UPDATE admission_enquiries a
JOIN admission_enquiries b
  ON a.application_no=b.application_no AND a.id>b.id
SET a.application_no=CONCAT('MZS-',YEAR(a.created_at),'-DUP',LPAD(a.id,8,'0'));

CALL mz_ensure_index('admission_enquiries','uq_admission_application_no',
                     'UNIQUE INDEX `uq_admission_application_no` (`application_no`)');

-- Reference table used by the admin class summary.
CREATE TABLE IF NOT EXISTS admission_classes (
    class_code VARCHAR(30) PRIMARY KEY,
    display_name VARCHAR(80) NOT NULL,
    level_group VARCHAR(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO admission_classes(class_code,display_name,level_group) VALUES
('S.1','Senior One','O-Level'),('S.2','Senior Two','O-Level'),
('S.3','Senior Three','O-Level'),('S.4','Senior Four','O-Level'),
('S.5','Senior Five','A-Level'),('S.6','Senior Six','A-Level');

-- Document records. INT matches admission_enquiries.id in the original schema.
CREATE TABLE IF NOT EXISTS admission_documents (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    document_type VARCHAR(40) NOT NULL,
    stored_name VARCHAR(100) NOT NULL,
    original_name VARCHAR(190) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admission_documents_application(application_id),
    CONSTRAINT fk_admission_documents_application
      FOREIGN KEY(application_id) REFERENCES admission_enquiries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- History fields match the PHP inserts/selects exactly.
CREATE TABLE IF NOT EXISTS admission_status_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    old_status VARCHAR(30) NULL,
    new_status VARCHAR(30) NOT NULL,
    changed_by VARCHAR(100) NOT NULL,
    comment TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admission_history_application(application_id),
    CONSTRAINT fk_admission_history_application
      FOREIGN KEY(application_id) REFERENCES admission_enquiries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Repair the earlier manual patch that may have created changed_by as INT.
CALL mz_ensure_column('admission_status_history','changed_by',"VARCHAR(100) NOT NULL DEFAULT 'System'");
ALTER TABLE admission_status_history MODIFY changed_by VARCHAR(100) NOT NULL;
CALL mz_ensure_column('admission_status_history','comment','TEXT NULL');
CALL mz_ensure_column('admission_status_history','admin_id','INT NULL');

CREATE TABLE IF NOT EXISTS admission_notification_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    channel VARCHAR(20) NOT NULL,
    recipient VARCHAR(190) NOT NULL,
    notification_status VARCHAR(30) NOT NULL,
    provider_response TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admission_notification_application(application_id),
    CONSTRAINT fk_admission_notification_application
      FOREIGN KEY(application_id) REFERENCES admission_enquiries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Required by includes/functions.php for administrator login throttling.
CREATE TABLE IF NOT EXISTS admin_login_attempts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    ip_hash CHAR(64) NOT NULL,
    attempted_at DATETIME NOT NULL,
    INDEX idx_login_ip_time(ip_hash,attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed a history row for legacy applications only where no history exists.
INSERT INTO admission_status_history(application_id,old_status,new_status,changed_by,comment)
SELECT a.id,NULL,COALESCE(NULLIF(a.status,''),'Submitted'),'System',
       'Existing application imported into the admissions portal'
FROM admission_enquiries a
WHERE NOT EXISTS (
    SELECT 1 FROM admission_status_history h WHERE h.application_id=a.id
);

DROP PROCEDURE IF EXISTS mz_ensure_column;
DROP PROCEDURE IF EXISTS mz_ensure_index;

CREATE DATABASE IF NOT EXISTS mt_zion_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mt_zion_school;
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'administrator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS admin_login_attempts(id BIGINT AUTO_INCREMENT PRIMARY KEY,ip_hash CHAR(64) NOT NULL,attempted_at DATETIME NOT NULL,INDEX idx_login_ip_time(ip_hash,attempted_at));
CREATE TABLE IF NOT EXISTS settings(setting_key VARCHAR(100) PRIMARY KEY,setting_value TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS slides(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(200) NOT NULL,subtitle TEXT, image VARCHAR(255),button_text VARCHAR(80),button_link VARCHAR(255),is_active TINYINT(1) DEFAULT 1,sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS achievers(id INT AUTO_INCREMENT PRIMARY KEY,category ENUM('UACE','UCE') NOT NULL,name VARCHAR(150) NOT NULL,achievement VARCHAR(200),image VARCHAR(255),sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS news(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(200) NOT NULL,summary TEXT,body LONGTEXT NOT NULL,image VARCHAR(255),is_published TINYINT(1) DEFAULT 1,published_at DATETIME DEFAULT CURRENT_TIMESTAMP,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS gallery(id INT AUTO_INCREMENT PRIMARY KEY,image VARCHAR(255) NOT NULL,caption VARCHAR(200),category VARCHAR(30) NOT NULL DEFAULT 'students',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS staff(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(150) NOT NULL,role VARCHAR(150),image VARCHAR(255),bio TEXT,sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS board_members(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(150) NOT NULL,role VARCHAR(150),image VARCHAR(255),bio TEXT,sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS services(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(180) NOT NULL,description TEXT,sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS portfolio(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(180) NOT NULL,description TEXT,image VARCHAR(255),sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS student_statistics(id INT AUTO_INCREMENT PRIMARY KEY,class_name VARCHAR(30) NOT NULL,boys INT DEFAULT 0,girls INT DEFAULT 0,total INT DEFAULT 0,sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS contact_messages(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(150),email VARCHAR(190),phone VARCHAR(50),subject VARCHAR(200),message TEXT,reference_code VARCHAR(24) NOT NULL UNIQUE,status VARCHAR(30) NOT NULL DEFAULT 'Received',admin_reply TEXT NULL,replied_at DATETIME NULL,updated_at DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS admission_enquiries(id INT AUTO_INCREMENT PRIMARY KEY,full_name VARCHAR(150),phone VARCHAR(50),email VARCHAR(190),class_applying VARCHAR(30),message TEXT,status VARCHAR(30) NOT NULL DEFAULT 'Pending',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT IGNORE INTO settings(setting_key,setting_value) VALUES
('school_name','Mt Zion Secondary School Mubende'),('phone','+256 772824530'),('email','mtzionssmubende2010@gmail.com'),('address','Mt Zion, Mubende, Uganda'),
('about_heading','Mt Zion Secondary School – Fostering Excellence Since 2010'),
('about_text','Nestled in the heart of Kalonga, our school is dedicated to cultivating young leaders through academic rigour, character development, and community engagement. We offer O-Level and A-Level curricula that prepare students for success in national examinations and life beyond the classroom.'),
('mission','To empower students with the knowledge, skills and values needed to thrive in an ever-changing world.'),
('vision','To be a centre of excellence nurturing responsible and successful citizens.'),
('admissions_text','Contact the school for current admission requirements, fees and available places.');
INSERT INTO student_statistics(class_name,boys,girls,total,sort_order) SELECT 'S.6',61,44,105,1 WHERE NOT EXISTS(SELECT 1 FROM student_statistics);
INSERT INTO student_statistics(class_name,boys,girls,total,sort_order) SELECT 'S.5',79,54,133,2 WHERE (SELECT COUNT(*) FROM student_statistics)=1;
INSERT INTO student_statistics(class_name,boys,girls,total,sort_order) SELECT 'S.4',91,63,154,3 WHERE (SELECT COUNT(*) FROM student_statistics)=2;
INSERT INTO student_statistics(class_name,boys,girls,total,sort_order) SELECT 'S.3',106,67,173,4 WHERE (SELECT COUNT(*) FROM student_statistics)=3;
INSERT INTO student_statistics(class_name,boys,girls,total,sort_order) SELECT 'S.2',167,98,265,5 WHERE (SELECT COUNT(*) FROM student_statistics)=4;
INSERT INTO student_statistics(class_name,boys,girls,total,sort_order) SELECT 'S.1',198,175,373,6 WHERE (SELECT COUNT(*) FROM student_statistics)=5;
-- After importing, create your administrator using the one-time setup script.

CREATE TABLE IF NOT EXISTS contact_message_history (id BIGINT AUTO_INCREMENT PRIMARY KEY, message_id INT NOT NULL, old_status VARCHAR(30) NULL, new_status VARCHAR(30) NOT NULL, reply TEXT NULL, changed_by VARCHAR(120) NOT NULL DEFAULT 'Administrator', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_contact_history_message(message_id), CONSTRAINT fk_contact_history_message FOREIGN KEY(message_id) REFERENCES contact_messages(id) ON DELETE CASCADE);

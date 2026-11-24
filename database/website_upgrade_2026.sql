USE mt_zion_school;

-- 1. Add the gallery category column only when it is missing.
SET @has_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='gallery' AND COLUMN_NAME='category');
SET @ddl = IF(@has_col=0, 'ALTER TABLE gallery ADD COLUMN category VARCHAR(30) NOT NULL DEFAULT ''students''', 'SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Restore categories for the existing photos without deleting or replacing any photo.
UPDATE gallery SET category = CASE
  WHEN LOWER(CONCAT(COALESCE(caption,''),' ',COALESCE(image,''))) LIKE '%agric%' THEN 'agriculture'
  WHEN LOWER(CONCAT(COALESCE(caption,''),' ',COALESCE(image,''))) LIKE '%sport%' THEN 'sports'
  ELSE COALESCE(NULLIF(category,''),'students') END
WHERE category IS NULL OR category='' OR category='students'
   OR LOWER(CONCAT(COALESCE(caption,''),' ',COALESCE(image,''))) LIKE '%agric%'
   OR LOWER(CONCAT(COALESCE(caption,''),' ',COALESCE(image,''))) LIKE '%sport%';

-- 2. Add contact tracking columns if they are not already present.
SET @has_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='contact_messages' AND COLUMN_NAME='reference_code');
SET @ddl = IF(@has_col=0, 'ALTER TABLE contact_messages ADD COLUMN reference_code VARCHAR(24) NULL', 'SELECT 1'); PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @has_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='contact_messages' AND COLUMN_NAME='status');
SET @ddl = IF(@has_col=0, 'ALTER TABLE contact_messages ADD COLUMN status VARCHAR(30) NOT NULL DEFAULT ''Received''', 'SELECT 1'); PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @has_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='contact_messages' AND COLUMN_NAME='admin_reply');
SET @ddl = IF(@has_col=0, 'ALTER TABLE contact_messages ADD COLUMN admin_reply TEXT NULL', 'SELECT 1'); PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @has_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='contact_messages' AND COLUMN_NAME='replied_at');
SET @ddl = IF(@has_col=0, 'ALTER TABLE contact_messages ADD COLUMN replied_at DATETIME NULL', 'SELECT 1'); PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @has_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='contact_messages' AND COLUMN_NAME='updated_at');
SET @ddl = IF(@has_col=0, 'ALTER TABLE contact_messages ADD COLUMN updated_at DATETIME NULL', 'SELECT 1'); PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Ensure the parent table supports the message-history foreign key.
ALTER TABLE contact_messages ENGINE=InnoDB;
UPDATE contact_messages SET reference_code=CONCAT('MZ',DATE_FORMAT(created_at,'%y%m'),LPAD(id,6,'0')) WHERE reference_code IS NULL OR reference_code='';
SET @has_index = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='contact_messages' AND INDEX_NAME='uq_contact_reference');
SET @ddl = IF(@has_index=0, 'ALTER TABLE contact_messages ADD UNIQUE KEY uq_contact_reference (reference_code)', 'SELECT 1'); PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;
CREATE TABLE IF NOT EXISTS contact_message_history (
 id BIGINT AUTO_INCREMENT PRIMARY KEY, message_id INT NOT NULL, old_status VARCHAR(30) NULL,
 new_status VARCHAR(30) NOT NULL, reply TEXT NULL, changed_by VARCHAR(120) NOT NULL DEFAULT 'Administrator',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_contact_history_message(message_id),
 CONSTRAINT fk_contact_history_message FOREIGN KEY(message_id) REFERENCES contact_messages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Restore the original public-facing school copy and editable headings only where fields are blank.
INSERT INTO settings(setting_key,setting_value) VALUES
('home_hero_title','Welcome to Mt Zion Secondary School Mubende'),
('home_hero_subtitle','Inspiring futures since 2010.'),
('home_about_heading','Mt Zion Secondary School – Fostering Excellence Since 2010'),
('home_about_text','Nestled in the heart of Kalonga, our school is dedicated to cultivating young leaders through academic rigor, character development, and community engagement. Accredited by the Ministry of Education, we offer O-Level and A-Level curricula (UCE & UACE) that prepare students for success in both national exams and life beyond the classroom.'),
('home_about_extra','Our mission is to empower students with the knowledge, skills, and values needed to thrive in an ever-changing world. Ranked among Uganda''s top secondary schools, we pride ourselves on outstanding exam results, a vibrant extracurricular program, and a supportive, inclusive environment.'),
('home_achievers_uace_heading','2025 UACE ACADEMIC GIANTS'),('home_achievers_uce_heading','2025 UCE ACADEMIC GIANTS'),
('home_news_heading','ANNOUNCEMENTS'),('home_gallery_heading','GALLERY HIGHLIGHTS'),('home_stats_heading','STUDENT STATISTICS'),
('home_cta_heading','Ready to Join Mt Zion?'),('home_cta_text','Become part of our vibrant community and unlock your potential.'),
('about_page_heading','About - Mt Zion Secondary School'),('about_page_intro','Our story, mission, and values'),
('about_page_vision','To be a model school for Academic success of all students with a purposeful mission for national development.'),
('about_page_mission','Producing all round professional class of people who are disciplined,skilled,confident,enterprising & God Fearing.'),
('about_page_values','Integrity|We uphold honesty, transparency, and accountability in all endeavors.\nExcellence|We strive for the highest standards in academics, sports, and character development.\nCommunity|We foster a supportive, inclusive environment where every student thrives.'),
('about_motto','Determination with Devotion for Excellence'),('about_beyond_heading','Beyond the Classroom'),('about_extracurricular_heading','Extra-Curricular Activities'),
('about_extracurricular_text','We offer a wide range of sports, artistic and creative activities, and career mentorship to help develop our students'' talents and interests. Our teachers have the qualifications and experience to meet high academic expectations.\nEach year, we hold a cultural day featuring music, dance, and drama inter-house competitions. Sports include football, rugby, basketball, volleyball, and more.\nOur dedicated staff, along with alumni groups, work together to give students flexibility in accessing additional programs and courses.'),
('academics_heading','Our Services (Academics)'),('academics_intro','Dive into our subjects and see how our students perform across all subjects.'),
('academics_olevel_compulsory','Biology\nChemistry\nEnglish\nGeography\nHistory\nMathematics\nPhysics'),
('academics_olevel_optional','Agriculture\nChinese\nChristian Religious Education\nEntrepreneurship\nFrench\nICT\nIPS\nKiswahili\nLiterature\nLugbarati\nPerforming Arts\nTechnical Drawing'),
('academics_alevel_principal','Biology\nChemistry\nEnglish\nGeography\nHistory\nMathematics\nPhysics\nAgriculture\nDivinity\nEntrepreneurship\nIPS\nKiswahili\nLiterature\nLuganda\nEconomics'),
('academics_alevel_subsidiaries','General Paper\nSubsidiary ICT\nSubsidiary Mathematics'),
('academics_olevel_heading','O-Level'),('academics_alevel_heading','A-Level'),('academics_exam_heading','Exam Records (2025)'),
('academics_olevel_grades','A|98\nB|95\nC|76\nD|89\nE|15'),('academics_alevel_grades','A|26\nB|78\nC|45\nD|23\nE|11'),
('portfolio_heading','Our Portfolio'),('portfolio_intro','Stay informed about our past achievements.'),('portfolio_history_heading','Brief History about Mt Zion'),
('portfolio_history_text','Mt Zion Secondary School Mubende started in 2010 as a community based school that offers affordable education to the people of Kalonga and other surrounding communities on the foundation of Seventh Day Adventist church by Elder Kakyope Ivan Davis.\nMt Zion Secondary School is a leading, God fearing academic institution in Greater Wamala Region, renowned as a premier center for science education and excellent UCE/UACE results.\nOperating under the motto "The Best Don''t Rest," it fosters academics, musical, and sporting talents while serving a diverse students population.\nThe school consistently performs well in UCE and UACE, often producing top candidates in the region.'),
('gallery_heading','Gallery'),('gallery_intro','Browse some of our campus photos.'),('contact_heading','Contact Us'),('contact_intro','Get in touch with Mt Zion Secondary School Mubende.'),
('message_track_heading','Track Your Message'),('footer_text','DEVELOPED BY ALINITWE DENIS')
ON DUPLICATE KEY UPDATE setting_value=IF(settings.setting_value IS NULL OR TRIM(settings.setting_value)='',VALUES(setting_value),settings.setting_value);
